<?php

require_once 'includes/database.php';


class UserRegistrationException extends Exception { }

function add_user_in_normal_event(PDO $database, $event, $event_phase, $user_id) {
	try {
		$database->beginTransaction();
		
		$event_id = $event['id'];
		$event_max_places = $event['places_max'] > 0 ? (int)$event['places_max'] : null;
		
		if (isUserAlreadyRegistered($database, $event_id, $user_id)) throw new UserRegistrationException("Already registered");
		
		# No queue
		if ($event_max_places) {
			if (!check_availability_event($database, $event_id)) throw new UserRegistrationException("Evènement plein");
			$insert_query = 'INSERT INTO subscriptions (event_id, user_id, is_waiting_list, timestamp) SELECT :event_id, :user_id, 0, CURRENT_TIMESTAMP WHERE (SELECT COUNT(*) FROM subscriptions WHERE event_id = :event_id) < (SELECT places_max FROM events WHERE id = :event_id)';
		} else {
			$insert_query = 'INSERT INTO subscriptions (event_id, user_id, is_waiting_list, timestamp) VALUES (:event_id, :user_id, 0, CURRENT_TIMESTAMP)';
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