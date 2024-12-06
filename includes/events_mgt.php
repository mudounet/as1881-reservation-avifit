<?php

require_once 'includes/globals.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

$database = connectToDatabase();

function getClosedDays($startTimestamp, $endTimestamp) {
	// https://calendrier.api.gouv.fr/jours-feries/alsace-moselle.json
	$response = file_get_contents("data/jours-feries-alsace-moselle.json");

	if ($response !== false) {
		$holidays = json_decode($response, true);

		if (!empty($holidays)) {
			$results = array();
			foreach ($holidays as $date => $name) {
				$timestamp = strtotime($date);
				if ($timestamp > $endTimestamp) continue;
				if ($timestamp < $startTimestamp) continue;
				$results[$date] = $name; // 
			}
			
			return $results;
		}
	} else {
		echo "Erreur lors de la récupération des données de jours fériés.";
		exit;
	}
}

function generateAutoEvents($startTimestamp, $endTimestamp) {
	global $database;

	// Load the auto_events.xml file
	$autoEvents = simplexml_load_file('data/auto_events.xml');
	
	// Get closed days
	$closedDays = getClosedDays($startTimestamp, $endTimestamp);

	// Load the existing events from events.xml, by timestamp
	$existingAutoEvents = [];
	$results = _getWithQuery($database, 'SELECT ts_sxb_start, category_auto FROM events WHERE category_auto IS NOT NULL AND ts_sxb_start >= '.$startTimestamp.' ORDER BY ts_sxb_start', False);
	foreach ($results as $event) {
		$existingAutoEvents[(string)$event['ts_sxb_start']][(string)$event['category_auto']] = 1; // On se sert de l'index uniquement, donc on met n'importe quelle valeur
	}
	
	foreach ($autoEvents->weekly_event as $auto_event) {
		$validityStart = isset($auto_event['valideDebut']) ? strtotime((string) $auto_event['valideDebut']) : false;
		$validityEnd = isset($auto_event['valideFin']) ? strtotime((string) $auto_event['valideFin']) : false;
		
		$dayIndex = JOURS_FR[strtolower((string) $auto_event['jour'])];
		$id = (string) $auto_event['autoId'];
		$auto_evt_closed_day = isset($auto_event['ferie']) && $auto_event['ferie'] == 'oui';
		
		// génération des jours de l'intervalle
		for ($timestamp = $startTimestamp; $timestamp <= $endTimestamp; $timestamp += 86400) {
			$date = date('Y-m-d', $timestamp);
			$ts_start = generateTimeStamp($date, $auto_event['heureDebut'], TIMEZONE);
			$ts_end = generateTimeStamp($date, $auto_event['heureFin'], TIMEZONE);
			$closed_day = isset($closedDays[$date]);

			if ($dayIndex != date('w', $timestamp)) continue; // la date de l'intervalle ne tombe pas un jour valide	
			if ($validityStart && $validityStart > $timestamp) continue; // Cet evènement n'est pas actif, car il n'a pas commencé
			if ($validityEnd && $validityEnd < $timestamp) break; // la date de validité de l'évenement est dépassée, il est inutile de continuer
			if ($closed_day && !$auto_evt_closed_day) continue; // Cet évènement tombe un jour férié et n'a pas la propriété ferie = "oui"
			if (!$closed_day && $auto_evt_closed_day) continue; // Cet évènement ne tombe pas un jour férié et a la propriété ferie = "oui"
			if (isset($existingAutoEvents[$ts_start][$id])) continue;  // l'évènement existe déjà dans le fichier XML d'évènements
			
			createEvent($database,
				$ts_start,
				$ts_end,
				null,
				null,
				(string) $auto_event['categorie'],
				(string) $auto_event['referent'],
				$id,
				isset($auto_event['places_min']) ? (int)$auto_event['places_min'] : null, // Places mini
				isset($auto_event['places_max']) ? (int)$auto_event['places_max'] : null, // Places max
				null,
				1, // 1 is the identifier of the AUTO user,
				isset($auto_event['liste_attente']) ? (bool)$auto_event['liste_attente'] : false
				);
		}
	}
	return true;
}

$dateActuelle = strtotime(date("Y-m-d")); // Date du jour
generateAutoEvents($dateActuelle, $dateActuelle + DAYS_FUTURE * 60 * 60 * 24);

?>