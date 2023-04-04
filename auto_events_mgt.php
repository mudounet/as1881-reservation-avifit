<?php

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

function generateAutoEvents($startTimestamp, $endTimestamp) {

	// Load the auto_events.xml file
	$autoEvents = simplexml_load_file('auto_events.xml');

	// Load the existing events from events.xml, by timestamp
	
	$eventsXml = simplexml_load_file('events.xml');
	if ($eventsXml === false) $eventsXml = simplexml_load_string("<events/>");
	
	$existingAutoEvents = [];
	foreach ($eventsXml->event as $event) {
		$timestamp = strtotime((string)$event->date);
		if(!isset($event->autoId)) continue; // Ce n'est pas un évènement généré automatiquement
		$existingAutoEvents[$timestamp][(string)$event->autoId] = 1; // On se sert de l'index uniquement, donc on met n'importe quelle valeur
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
			if ($dayIndex != date('w', $timestamp)) continue; // la date de l'intervalle ne tombe pas un jour valide
			
			if ($validityStart && $validityStart > $timestamp) continue; // Cet evènement n'est pas actif, car il n'a pas commencé
			if ($validityEnd && $validityEnd < $timestamp) break; // la date de validité de l'évenement est dépassée, il est inutile de continuer
			if (isset($existingAutoEvents[$timestamp][$id])) continue;  // l'évènement existe déjà dans le fichier XML d'évènements
			
			$newEvent['date'] = date('Y-m-d', $timestamp);
			$newEvent['timestamp'] = \DateTime::createFromFormat('Y-m-d H:i T', $newEvent['date'].' '.str_replace('h', ':', $newEvent['heureDebut']).' Europe/Paris')->getTimestamp();
			$newEvents[] = $newEvent;
		}
	}
	
	usort($newEvents, 'cmp'); // Sort array of events

	// Write everything to new file
	$eventsXml = new SimpleXMLElement('<?xml version="1.0"?><events/>');
	
	foreach ($newEvents as $event) {
		$child = $eventsXml->addChild('event');
		foreach( $event as $key => $value) {
			$child->addAttribute($key, $value);
		}
	}
	
	$result = $eventsXml->asXML('events.xml'); //saving generated xml file; 
	return $result;
}

function cmp($a, $b){
    return $a['timestamp'] - $b['timestamp'];
}

$dateActuelle = strtotime(date("Y-m-d")); // Date du jour
generateAutoEvents($dateActuelle, $dateActuelle + DAYS_FUTURE * 60 * 60 * 24);

?>