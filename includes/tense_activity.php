<?php

require_once 'includes/database.php';

class UserRegistrationException extends Exception { }

define("EXTREME_CANCELLING_THRESHOLD_HOURS", 2*3600);
define("LATE_CANCELLING_THRESHOLD_HOURS", 6*3600);
define("QUITE_LATE_CANCELLING_THRESHOLD_HOURS", 12*3600);
define("NO_AUTO_ATTRIBUTION_THRESHOLD_HOURS", 24*3600);
define("AUTO_ATTRIBUTION_THRESHOLD_HOURS", 24*8*3600);

enum TenseActivity
{
	case CANDIDATURE_FILING;
	case AUTO_ATTRIBUTION;
	case NO_AUTO_ATTRIBUTION;
    case QUITE_LATE_CANCELLING;
    case LATE_CANCELLING;
    case TOO_LATE_CANCELLING;
    case EVENT_RUNNING;
	case EVENT_OVER;
}

function human_timediff($reference_timestamp, $timestamp) {
    $time_diff = $timestamp - $reference_timestamp;
    $future = $time_diff > 0;

    $time_diff = abs($time_diff);

    $units = array(
        "seconde" => [1.0, 120],
        "minute" => [60.0, 90],
        "heure" => [60.0, 48],
        "jour" => [24.0, 90],
        "semaine" => [7.0, -1],
    );
	
	$count = (float)$time_diff;
    foreach ($units as $unit => $values_array) {
		$count /= $values_array[0];
		
        if ($values_array[1] == -1 || $count <= $values_array[1]) {
			$count = floor($count);
			$result = ($future ? "dans " : "il y a ") . $count . " " . $unit . (($count == 1) ? "" : "s");
            return $result;
        }
    }
	
    return "à l'instant";
}

function determine_phase($reference_timestamp, $start_event_date, $end_event_date) {
	if ($reference_timestamp >= $end_event_date) return TenseActivity::EVENT_OVER;
	if ($reference_timestamp >= $start_event_date) return TenseActivity::EVENT_RUNNING;
	
	// Computation is required for further states
	$diff_period = (float)($start_event_date - $reference_timestamp);
	
	if ($diff_period <= EXTREME_CANCELLING_THRESHOLD_HOURS) return TenseActivity::TOO_LATE_CANCELLING;
	if ($diff_period <= LATE_CANCELLING_THRESHOLD_HOURS) return TenseActivity::LATE_CANCELLING;
	if ($diff_period <= QUITE_LATE_CANCELLING_THRESHOLD_HOURS) return TenseActivity::QUITE_LATE_CANCELLING;
	if ($diff_period <= NO_AUTO_ATTRIBUTION_THRESHOLD_HOURS) return TenseActivity::NO_AUTO_ATTRIBUTION;
	if ($diff_period <= AUTO_ATTRIBUTION_THRESHOLD_HOURS) return TenseActivity::AUTO_ATTRIBUTION;
	return TenseActivity::CANDIDATURE_FILING;
}

function compute_list(PDO $database, TenseActivity $activity_state, int $event_id) {
	$event_properties_and_stats = $database->prepare('SELECT is_waiting_list, count(s.is_waiting_list) AS count, e.places_min, e.places_max FROM events AS E, subscriptions AS S WHERE e.id = :event_id and e.id = s.event_id GROUP BY is_waiting_list');
	
	$event_properties_and_stats->execute([':event_id' => $event_id]);
	$event_stats = $event_properties_and_stats->fetchAll();
	
	$persons_in_waiting_list = 0;
	$persons_selected_for_event = 0;
	$places_min = 0;
	$places_max = 0;
	$total = 0;
	foreach ($event_stats as $stat) {
		$places_min = $stat["places_min"];
		$places_max = $stat["places_max"];
		if ($stat["is_waiting_list"] == 0) {
			$persons_selected_for_event = $stat["count"];
		} elseif ($stat["is_waiting_list"] == 1) {
			$persons_in_waiting_list = $stat["count"];
		}
		$total += $stat["count"];
	}
	
	$waiting_list_active = $persons_in_waiting_list > 0;
	$normal_list_active = $persons_selected_for_event > 0;

	if ($activity_state === TenseActivity::CANDIDATURE_FILING) {
		// The waiting list is in progress, so nothing to do
		return true;
	} elseif ($activity_state === TenseActivity::AUTO_ATTRIBUTION) {
		$update_query = null;
		if ($persons_in_waiting_list > 0 && $persons_selected_for_event == 0) {
			// As the list is empty, the initial election can be performed
			$update_stmt = $database->prepare('UPDATE subscriptions SET is_waiting_list = 0 WHERE id IN (SELECT s.id FROM user_subscription_stats AS U, subscriptions AS S WHERE S.event_id = :event_id AND U.id = S.user_id ORDER BY total, s.timestamp LIMIT :limit)');
			$update_stmt->execute([':event_id' => $event_id, ':limit' => $places_max]);
			return true;
		} elseif ($persons_selected_for_event > 0 && $persons_selected_for_event < $places_max) {
			// Some users are already in the normal queue, so election is already performed
			$update_stmt = $database->prepare('UPDATE subscriptions SET is_waiting_list = 0 WHERE event_id = :event_id AND user_id IN (SELECT user_id FROM subscriptions WHERE event_id = :event_id ORDER BY is_waiting_list, timestamp LIMIT :limit)');
			$update_stmt->execute([':event_id' => $event_id, ':limit' => $places_max]);
			return true;
		}
	} else {
		if (!$waiting_list_active) return true;
		$update_stmt = $database->prepare('UPDATE subscriptions SET is_waiting_list = 0 WHERE event_id = :event_id AND user_id IN (SELECT user_id FROM subscriptions WHERE event_id = :event_id ORDER BY is_waiting_list, timestamp LIMIT :limit)');
		$update_stmt->execute([':event_id' => $event_id, ':limit' => $places_max]);
		
		$delete_stmt = $database->prepare('DELETE from subscriptions WHERE event_id = :event_id AND is_waiting_list = 1');
		$delete_stmt->execute([':event_id' => $event_id]);
		return true;
	}
}


function add_user_in_tense_event(PDO $database, $event_phase, $event_id, $user_id) {
	try {
		$database->beginTransaction();
		if (isUserAlreadyRegistered($database, $event_id, $user_id)) throw new UserRegistrationException("Already registered");
		
		if ($event_phase === TenseActivity::CANDIDATURE_FILING || $event_phase === TenseActivity::AUTO_ATTRIBUTION) {
			$insert_query = 'INSERT INTO subscriptions (event_id, user_id, is_waiting_list, timestamp) VALUES (:event_id, :user_id, 1, CURRENT_TIMESTAMP)';
		} else {
			# No queue
			if (!check_availability_event($database, $event_id)) throw new UserRegistrationException("Evènement plein");
			$insert_query = 'INSERT INTO subscriptions (event_id, user_id, is_waiting_list, timestamp) SELECT :event_id, :user_id, 0, CURRENT_TIMESTAMP WHERE (SELECT COUNT(*) FROM subscriptions WHERE event_id = :event_id) < (SELECT places_max FROM events WHERE id = :event_id)';
		}
		
		$insert_statement = $database->prepare($insert_query);
		$success = $insert_statement->execute([':event_id' => (int)$event_id, ':user_id' => (int)$user_id]);
		
		if ($success) {
			$database->commit();
			return true;
		} else {
			throw new UserRegistrationException("statement failed");
		}
	} catch (PDOException $e) {
		$database->rollBack();
		echo "Error: " . $e->getMessage();
		return false;
	}
}

?>