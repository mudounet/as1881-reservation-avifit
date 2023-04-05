<?php

function addEvent($event, $eventsXml) {
	$child = $eventsXml->addChild('event'); // On ajoute une nouvelle entrée
	
	foreach( $event as $key => $value) {
		$child->addAttribute($key, $value);
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