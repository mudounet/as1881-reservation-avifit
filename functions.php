<?php

function getEvent($eventsXml, $timestamp, $category) {
	$match = $eventsXml->xpath("//event[@autoId='$category' and @time_start_sxb='$timestamp']");// On query uniquement le xml pour la date demandée
	if (count($match) == 0) $match = $eventsXml->xpath("//event[@categorie='$category' and @time_start_sxb='$timestamp']");// On query uniquement le xml pour la date demandée
		
	if (count($match) == 1) return $match[0];
	throw new Exception ("Nombre d'évènements récupéré invalide : ".count($match));
}

function editEvent($eventsXml, $eventXML, $data) {
	foreach($data as $key => $value) {
		if (isset($key) && $key != '')
			$eventXML[$key] = $value;
		else {
			// Si la clef n'est pas définie... Alors on ajoute $value en tant que CDATA
			$dom = dom_import_simplexml($eventXML);
			$dom->appendChild($dom->ownerDocument->createCDATASection($value)); 
		}
	}
	saveXmlFile($eventsXml, EVENT_FILE);
}

function addEvent($event, $eventsXml) {
	$child = $eventsXml->addChild('event'); // On ajoute une nouvelle entrée
	
	foreach($event as $key => $value) {
		if (isset($key) && $key != '')
			$child->addAttribute($key, $value);
		else {
			// Si la clef n'est pas définie... Alors on ajoute $value en tant que CDATA
			$dom = dom_import_simplexml($child);
			$dom->appendChild($dom->ownerDocument->createCDATASection($value)); 
		}
	}
}

function generateTimeStamp($date, $time, $timezone) {
	return \DateTime::createFromFormat('Y-m-d H:i T', $date.' '.$time.' '.$timezone)->getTimestamp(); // Creation du timestamp en tenant compte de l'heure et du decalage horaire
}

function saveXmlFile($xml, $file) {
	$dom = new DOMDocument("1.0");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($xml->asXML()); // On charge le résultat dans un DOM Doc
	return $dom->save($file); // On écrit le résultat
}

?>