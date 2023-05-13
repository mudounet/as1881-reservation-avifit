<?php

function getNewEventId() {
	return _getNewId(EVENT_ID_FILE);
}

function _getNewId($filename) {
	
	if(file_exists($filename)) {
		$fp = fopen($filename, "r+");
		if (flock($fp, LOCK_EX)) { // acquière un verrou exclusif
			$content = fread($fp, filesize($filename));
			$id = (int)$content + 1;
			rewind($fp); // Reset file pointer to beginning of file
			ftruncate($fp, 0);
			fwrite($fp, $id);
			fflush($fp);            // libère le contenu avant d'enlever le verrou
			flock($fp, LOCK_UN);    // Enlève le verrou
		} else {
			echo "Impossible de verrouiller le fichier !";
		}
		fclose($fp);
	} else {
		$id = 1;
		file_put_contents($filename, $id);
	}
	
	return $id;
}

function getEvent($eventsXml, $id) {
	$match = $eventsXml->xpath("//event[@id='$id']");// On query uniquement le xml pour la date demandée
		
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
	
	$child->addAttribute('id', getNewEventId());
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