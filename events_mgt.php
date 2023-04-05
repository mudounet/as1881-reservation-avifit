<?php

require 'functions.php';

//  libxml functionality it is possible to suppress all XML errors when loading the document and then iterate over the errors. 
libxml_use_internal_errors(true);

define('JOURS_FR' ,[
	"dimanche" => 0,
	"lundi" => 1,
	"mardi" => 2,
	"mercredi" => 3,
	"jeudi" => 4,
	"vendredi" => 5,
	"samedi" => 6,
]);

define('DAYS_FUTURE', 15);
define('DAYS_PAST', 5);

removeOldEvents() {
	if (!($xml = simplexml_load_file("data.xml"))) $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><data/>');
	if (!($wl = simplexml_load_file("wl.xml"))) $wl = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><data/>');
	if (!($eventsXml = simplexml_load_file('events.xml'))) $eventsXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><events/>');

	//---------------------- Grooming - On fait le ménage dans la BDD
	// On détermine la date avant laquelle toutes les entrées sont supprimées
	$dateDeNettoyage = date("Ymd") - DAYS_PAST . "1830"; // On supprime 10 jours avant la date du jour
	// echo $dateDeNettoyage;
	foreach ($xml->xpath("//insc[ translate(@date,'-','') < $dateDeNettoyage ]") as $el) {
		// On mouline dans la liste des inscrits
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		saveXmlFile($xml, "data.xml");
	}

	foreach ($wl->xpath("//wl[ translate(@date,'-','') < $dateDeNettoyage ]") as $el) {
		// On mouline dans la waiting list (WL)
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		saveXmlFile($wl, "wl.xml");
	}
}

function generateAutoEvents($startTimestamp, $endTimestamp) {

	// Load the auto_events.xml file
	$autoEvents = simplexml_load_file('auto_events.xml');

	// Load the existing events from events.xml, by timestamp
	
	$eventsXml = simplexml_load_file('events.xml');
	if ($eventsXml === false) $eventsXml = simplexml_load_string('<?xml version="1.0"?><events/>');
	
	$existingAutoEvents = [];
	foreach ($eventsXml->event as $event) {
		if(!isset($event['autoId'])) continue; // Ce n'est pas un évènement généré automatiquement
		$existingAutoEvents[(string)$event['timestamp']][(string)$event['autoId']] = 1; // On se sert de l'index uniquement, donc on met n'importe quelle valeur
	}
	
	foreach ($autoEvents->weekly_event as $event) {
		$validityStart = isset($event['valideDebut']) ? strtotime((string) $event['valideDebut']) : false;
		$validityEnd = isset($event['valideFin']) ? strtotime((string) $event['valideFin']) : false;
		$places = isset($event['places']) ? (int)$event['places'] : -1;
		
		$dayIndex = JOURS_FR[strtolower((string) $event['jour'])];
		$id = (string) $event['autoId'];
		$newEvent = [
				'heureDebut' => (string) $event['heureDebut'],
				'heureFin' => (string) $event['heureFin'],
				'categorie' => (string) $event['categorie'],
				'referent' => (string) $event['referent'],
				'places' => (string)$places,
				'autoId' => $id];
		
		// génération des jours de l'intervalle
		for ($timestamp = $startTimestamp; $timestamp <= $endTimestamp; $timestamp += 86400) {
			$newEvent['date'] = date('Y-m-d', $timestamp);
			$newEvent['timestamp'] = \DateTime::createFromFormat('Y-m-d H:i T', $newEvent['date'].' '.str_replace('h', ':', $newEvent['heureDebut']).' Europe/Paris')->getTimestamp(); // Creation du timestamp en tenant compte de l'heure et du decalage horaire
			
			if ($dayIndex != date('w', $timestamp)) continue; // la date de l'intervalle ne tombe pas un jour valide	
			if ($validityStart && $validityStart > $timestamp) continue; // Cet evènement n'est pas actif, car il n'a pas commencé
			if ($validityEnd && $validityEnd < $timestamp) break; // la date de validité de l'évenement est dépassée, il est inutile de continuer
			if (isset($existingAutoEvents[$newEvent['timestamp']][$newEvent['autoId']])) continue;  // l'évènement existe déjà dans le fichier XML d'évènements
					
			$newEvents[] = $newEvent;
		}
	}
	
	if(!isset($newEvents)) return true; // Pas de nouveaux elements, pas besoin de continuer
	
	foreach ($newEvents as $event) {
		addEvent($event, $eventsXml);
	}
	
	return saveXmlFile($eventsXml, 'events.xml');  //saving generated xml file; 
}



$dateActuelle = strtotime(date("Y-m-d")); // Date du jour
removeOldEvents();
generateAutoEvents($dateActuelle, $dateActuelle + DAYS_FUTURE * 60 * 60 * 24);

?>