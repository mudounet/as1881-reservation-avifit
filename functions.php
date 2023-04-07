<?php

function addEvent($event, $eventsXml) {
	$child = $eventsXml->addChild('event'); // On ajoute une nouvelle entrée
	
	foreach( $event as $key => $value) {
		if (isset($key) && $key != '')
			$child->addAttribute($key, $value);
		else {
			// Si la clef n'est pas définie... Alors on ajoute $value en tant que CDATA
			$dom = dom_import_simplexml($child);
			$dom->appendChild($dom->ownerDocument->createCDATASection($value)); 
		}
	}
}

function saveXmlFile($xml, $file) {
	$dom = new DOMDocument("1.0");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($xml->asXML()); // On charge le résultat dans un DOM Doc
	return $dom->save($file); // On écrit le résultat
}

?>