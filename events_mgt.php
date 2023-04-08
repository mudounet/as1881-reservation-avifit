<?php

require_once 'globals.php';
require_once 'functions.php';

function removeOldEvents() {
	if (!($xml = simplexml_load_file(SUBSCRIPTION_FILE))) $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><data/>');
	if (!($wl = simplexml_load_file(WAITING_LIST_FILE))) $wl = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><data/>');
	if (!($eventsXml = simplexml_load_file(EVENT_FILE))) $eventsXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><events/>');

	//---------------------- Grooming - On fait le ménage dans la BDD
	// On détermine la date avant laquelle toutes les entrées sont supprimées
	$dateDeNettoyage = date("Ymd") - DAYS_PAST . "1830"; // On supprime 10 jours avant la date du jour
	// echo $dateDeNettoyage;
	foreach ($xml->xpath("//insc[ translate(@date,'-','') < $dateDeNettoyage ]") as $el) {
		// On mouline dans la liste des inscrits
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		saveXmlFile($xml, SUBSCRIPTION_FILE);
	}

	foreach ($wl->xpath("//wl[ translate(@date,'-','') < $dateDeNettoyage ]") as $el) {
		// On mouline dans la waiting list (WL)
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		saveXmlFile($wl, WAITING_LIST_FILE);
	}
}

function generateAutoEvents($startTimestamp, $endTimestamp) {

	// Load the auto_events.xml file
	$autoEvents = simplexml_load_file('auto_events.xml');

	// Load the existing events from events.xml, by timestamp
	
	$eventsXml = simplexml_load_file(EVENT_FILE);
	if ($eventsXml === false) $eventsXml = simplexml_load_string('<?xml version="1.0"?><events/>');
	
	$existingAutoEvents = [];
	foreach ($eventsXml->event as $event) {
		if(!isset($event['autoId'])) continue; // Ce n'est pas un évènement généré automatiquement
		$existingAutoEvents[(string)$event['time_start_sxb']][(string)$event['autoId']] = 1; // On se sert de l'index uniquement, donc on met n'importe quelle valeur
	}
	
	foreach ($autoEvents->weekly_event as $event) {
		$validityStart = isset($event['valideDebut']) ? strtotime((string) $event['valideDebut']) : false;
		$validityEnd = isset($event['valideFin']) ? strtotime((string) $event['valideFin']) : false;
		$places = isset($event['places']) ? (int)$event['places'] : -1;
		
		$dayIndex = JOURS_FR[strtolower((string) $event['jour'])];
		$id = (string) $event['autoId'];
		$newEvent = [
				'categorie' => (string) $event['categorie'],
				'referent' => (string) $event['referent'],
				'places' => (string)$places,
				'autoId' => $id];
		
		// génération des jours de l'intervalle
		for ($timestamp = $startTimestamp; $timestamp <= $endTimestamp; $timestamp += 86400) {
			$date = date('Y-m-d', $timestamp);
			$newEvent['time_start_sxb'] = \DateTime::createFromFormat('Y-m-d H:i T', $date.' '.$event['heureDebut'].TIMEZONE)->getTimestamp(); // Creation du timestamp en tenant compte de l'heure et du decalage horaire
			$newEvent['time_end_sxb'] = \DateTime::createFromFormat('Y-m-d H:i T', $date.' '.$event['heureFin'].TIMEZONE)->getTimestamp(); // Creation du timestamp en tenant compte de l'heure et du decalage horaire

			if ($dayIndex != date('w', $timestamp)) continue; // la date de l'intervalle ne tombe pas un jour valide	
			if ($validityStart && $validityStart > $timestamp) continue; // Cet evènement n'est pas actif, car il n'a pas commencé
			if ($validityEnd && $validityEnd < $timestamp) break; // la date de validité de l'évenement est dépassée, il est inutile de continuer
			if (isset($existingAutoEvents[$newEvent['time_start_sxb']][$newEvent['autoId']])) continue;  // l'évènement existe déjà dans le fichier XML d'évènements
					
			$newEvents[] = $newEvent;
		}
	}
	
	if(!isset($newEvents)) return true; // Pas de nouveaux elements, pas besoin de continuer
	
	foreach ($newEvents as $event) {
		addEvent($event, $eventsXml);
	}
	
	return saveXmlFile($eventsXml, EVENT_FILE);  //saving generated xml file; 
}



$dateActuelle = strtotime(date("Y-m-d")); // Date du jour
removeOldEvents();
generateAutoEvents($dateActuelle, $dateActuelle + DAYS_FUTURE * 60 * 60 * 24);

?>