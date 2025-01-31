<?php

//require_once 'includes/globals.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';


@session_start();

function redirect_to_main_page($GP_eventID) {
	header("Refresh:0; url=index.php#event_$GP_eventID");
	exit;
}

//---------------------- Chargement de la BDD, toutes les requetes peuvent utiliser cette variable pour charger la bdd
$database = connectToDatabase();

if (!isset($_SESSION["logged_user"])) http_error_code("Mauvaise authentification", 401); // 401 Unauthorized: The user needs to authenticate to access the requested resource.

$GP_eventID = getByPostOrGet('id', null);
$action = getByPostOrGet('act', null);

// Vérification de l'identifiant de l'évènement s'il existe
$event_min_places = null;
$event_max_places = null;
$event_phase = null;
if($GP_eventID) {
	try {
		if (!preg_match('/^(\d+)$/', $GP_eventID, $matches)) throw new Exception("'$GP_eventID' Pas valide" );
		$event = getEvent($database, $GP_eventID); // On interroge uniquement la base de donnée pour l'identifiant

		$event_min_places = $event['places_min'] > 0 ? (int)$event['places_min'] : null;
		$event_max_places = $event['places_max'] > 0 ? (int)$event['places_max'] : null;
		$is_tensed_event = (bool)$event['tense_activity']; // Seules les sessions d'avifit sont des évènements spéciaux
		
		
		
		// As an action is requested, we update 
		if ($event_max_places && $is_tensed_event) {
			require_once 'includes/tense_activity.php';
			$current_time = time();
			$event_phase = determine_phase($current_time, $event["ts_sxb_start"], $event["ts_sxb_end"]);
			compute_list($database, $event_phase, $GP_eventID);
		} else {
			require_once 'includes/normal_activity.php';
		}
	} catch (Exception $e) {
		http_response_code(400); // Set the HTTP status code to 400 Bad Request
		echo 'id invalide : '.$e->getMessage();
		exit;
	}
}

if ($action) {
	$isAdmin = $_SESSION["logged_user"]['is_admin'];
	if(($action == "event_add" || $action == "adminRemove" || $action == "event_get_raw" || $action == "event_edit") && !$isAdmin) http_error_code("Droits admin requis", 403); // 403 Forbidden: The user is authenticated, but does not have the necessary permissions to access the requested resource.
	
	try {
		// Si on est sur un act REMOVE, alors on supprime le truc
		if ($action == "adminRemove" && $isAdmin) {
			$GP_targetId = getByPostOrGet('targetId', null);
			
			if(!$GP_targetId) http_error_code("Mauvais arguments");
			
			remove_user_in_event($database, $GP_eventID, $GP_targetId);
			
			redirect_to_main_page($GP_eventID);
		} elseif ($action == "event_add" && $isAdmin) {
			createEvent($database, 
				generateTimeStamp($_POST['startDate'], $_POST['startTime'], TIMEZONE), 
				generateTimeStamp($_POST['startDate'],$_POST['endTime'], TIMEZONE), 
				getByPostOrGet('title', null),
				getByPostOrGet('desc', null),
				getByPostOrGet('cat', 'CATEGORIE_INVALIDE'),
				getByPostOrGet('referee', null), 
				null, // autoid
				getByPostOrGet('places_min', null),
				getByPostOrGet('places_max', null),
				null, // Desactivation
				$_SESSION["logged_user"]['id'],
				(bool)getByPostOrGet('tense_activity', false),
			);
				
			// Redirect to the same page with the query string
			redirect_to_main_page($GP_eventID);
		} elseif ($action == 'event_edit' && $isAdmin) {
			updateEvent($database, $GP_eventID, $_POST['ts_sxb_start'], $_POST['ts_sxb_end'], $_POST['title'], $_POST['description'], $_POST['category_id'], $_POST['referee'], $_POST['places_min'], $_POST['places_max'], $_POST['disactivation_text'], $_POST['tense_activity']);
			
			// Redirect to the same page with the query string
			redirect_to_main_page($GP_eventID);
		} elseif ($action == 'event_get_raw' && $isAdmin) {
			// Set Content-Type header to application/json
			header('Content-Type: application/json');

			// Return event data as JSON
			echo json_encode($event);
			exit;
		} elseif ($action == "add") {
			// Gestion de l'inscription à la séance
			if ($is_tensed_event) {
				add_user_in_tense_event($database, $event_phase, $GP_eventID, $_SESSION["logged_user"]['id']);
			} else {
				add_user_in_normal_event($database, $event, $event_phase, $_SESSION["logged_user"]['id']);
			}
			
		} elseif ($action == "remove") { // Si on est sur un act REMOVE, alors on supprime le truc
			remove_user_in_event($database, $GP_eventID, $_SESSION["logged_user"]['id']);
		} else {
			http_error_code("L'action demandée n'existe pas");
		}
	} catch (Exception $e) {
		http_error_code($e->getMessage());
	}

	http_success_code("L'action demandée a réussie");
}

?>