<?php

require_once 'globals.php';
require_once 'setup_smarty.php';
require_once 'functions.php';

//---------------------- Paramètres	 /!\ Important
$arrayAdmin = [];
include 'admins.php';

$fmtDateComplete = new IntlDateFormatter( "fr_FR" ,IntlDateFormatter::FULL, IntlDateFormatter::FULL, TIMEZONE,IntlDateFormatter::GREGORIAN,'eeee dd MMMM yyyy à HH:mm');

/////////////////////////////////////////////////////////////////////////
// Lancement du code automatisé toutes les demis journées
/////////////////////////////////////////////////////////////////////////
$current_time = time();
$last_exec = file_get_contents(LAST_EXEC_FILE);
// Check if the current time is greater than the last time the script was executed
if ($last_exec === false || $current_time > (int)$last_exec + 43200) {
	require_once 'events_mgt.php';
	file_put_contents(LAST_EXEC_FILE, $current_time);
}

$smarty = new Smarty_Aviron();

$protocol = empty($_SERVER["HTTPS"]) ? "http" : "https";
$domain = $_SERVER["SERVER_NAME"];
$port = $_SERVER["SERVER_PORT"];
$disp_port = ($protocol == "http" && $port == 80)
	|| ($protocol == "https" && $port == 443) ? "" : ":$port";

$base_url = preg_replace('/^'.preg_quote($_SERVER["DOCUMENT_ROOT"], '/').'/', "", __FILE__);
$base_url = str_replace('\\', '/', $base_url);

// put em all together to get the complete base URL
$baseURL = "${protocol}://${domain}${disp_port}${base_url}";


//----------------------  Fonctions	& Pré-requis
function getByPostOrGet($property, $defaults) {
	if (isset($property) && array_key_exists((string)$property, $_GET)) return trim($_GET[$property]);
	if (isset($property) && array_key_exists((string)$property, $_POST)) return trim($_POST[$property]);
	return trim($defaults);
}

function traiterFileAttente($wl, $baseURL, $GP_eventID) {
	list($start_timestamp, $type) = explode("-", $GP_eventID); // On explose l'identifiant pour pouvoir manipuler le contenu
	
	global $fmtDateComplete;
	$dateHuman = $fmtDateComplete->format((int)$start_timestamp);

	// On génère les emails pour les personnes en liste d'attente
	foreach ($wl->xpath("//wl[@id='$GP_eventID']") as $el) {
		$name=$el['name'];
		$mail=$el['email'];
		$headers = <<<HEADER
From: [TEST !!!] AS1881 <avironstrasbourg1881@gmail.com >
Reply-To: avironstrasbourg1881@gmail.com
MIME-Version: 1.0
Content-Type: text/html; charset=UTF-8
HEADER;
		
		$subject = "AS1881 - Une place vient de se libérer pour le \"$dateHuman\" !";
		
		$mailBody = <<<BODY
Bonjour $name,
<br/><br/>Une place vient de se libérer pour $type du <b>$dateHuman</b> ! Une chance !<br/><br/>
Si la place est toujours disponible, vous pouvez utiliser ou copier le lien suivant pour la retrouver la liste des sessions :<br><a href="$baseURL?name=$name&email=$mail" target="_blank">$baseURL?name=$name&email=$mail</a><br/><br/>
Ce lien vous authentifie automatiquement.<br/><br/>	
<i>Ce mail est automatique, NE PAS REPONDRE ! </i><br/><br/>					
A bientôt !
BODY;
		
		// On envoie le mail
		mail($mail, $subject, $mailBody, $headers);
		// echo $message; // Debug
	}

}


//---------------------- Chargement de la BDD, toutes les requetes peuvent utiliser cette variable pour charger la bdd
if (!($xml = simplexml_load_file(SUBSCRIPTION_FILE))) $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><data/>');
if (!($wl = simplexml_load_file(WAITING_LIST_FILE))) $wl = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><data/>');
if (!($eventsXml = simplexml_load_file(EVENT_FILE))) $eventsXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><events/>');

//---------------------- Inscription, Désinscription et Waiting List
// On vérifie si on a des données en POST ou en GET
$GP_name = getByPostOrGet('name', null);
$GP_email = getByPostOrGet('email', null);
$GP_eventID = getByPostOrGet('id', null);
$action = getByPostOrGet('act', null);

// Vérification de l'identifiant de l'évènement s'il existe
$participantsMaxId = null;
if($GP_eventID) {
	try {
		if (!preg_match('/^(\d+)-([a-zA-Z0-9_]+)$/', $GP_eventID, $matches)) throw new Exception("'$GP_eventID' Pas valide" );
		$q = getEvent($eventsXml, $matches[1], $matches[2]); // On query uniquement le xml pour la date demandée
		if(isset($q['places'])) $participantsMaxId = (int)$q['places'];
	} catch (Exception $e) {
		http_response_code(400); // Set the HTTP status code to 400 Bad Request
		echo 'id invalide : '.$e->getMessage();//,  $e->getMessage(), "\n";
		exit;
	}
}

# Vérification de la validité de la personne identifiée
$isAdmin = false;
$isValidUser = false;
if ($GP_name != "" && $GP_email != "") {
	$isValidUser = true;
	// Si on a un post ou un get d'email et de name, l'inscription est ouverte
	$unlockInsc = true;
	$unlockStyle = "unlocked";
	$loginURL = "$baseURL?name=$GP_name&email=$GP_email"; // On génère l'URL complète pour que l'utilisateur puisse le mettre en favoris

	// On détermine si la personne est admin
	foreach ($arrayAdmin as $k => $v) {
		if ($k == $GP_name && $v == $GP_email) {
			$isAdmin = true;
		}
	}
} else {
	$unlockStyle = "";
	$loginURL = $baseURL . "?anonymous";
}

if ($action) {
	if (!$isValidUser) {
		http_response_code(401); // 401 Unauthorized: The user needs to authenticate to access the requested resource.
		echo "Mauvaise authentification"; // Send back an error message in the response body
		exit;
	}
	
	if(($action == "event_add" || $action == "adminRemove" || $action == "event_get_raw" || $action == "event_edit") && !$isAdmin) {
		http_response_code(403); // 403 Forbidden: The user is authenticated, but does not have the necessary permissions to access the requested resource.
		echo "Droits admin requis"; // Send back an error message in the response body
		exit;	
	}
		
	// Si on est sur un act REMOVE, alors on supprime le truc
	if ($action == "adminRemove" && $isAdmin) {
		$GP_targetName = getByPostOrGet('targetName', null);
		$GP_targetEmail = getByPostOrGet('targetEmail', null);
		
		if(!$GP_targetName || !$GP_targetEmail) {
			http_response_code(400); //400 Bad Request: The server could not understand the request due to invalid syntax or missing parameters.
			echo "Mauvais arguments"; // Send back an error message in the response body
			exit;	
		}
		
		// Suppression de la liste normale
		foreach ($xml->xpath("//insc[@email='$GP_targetEmail' and @name='$GP_targetName' and @id='$GP_eventID']") as $el) {
			$domRef = dom_import_simplexml($el);
			$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
			saveXmlFile($xml, SUBSCRIPTION_FILE);
		}
		
		// Suppression de la liste d'attente
		foreach ($wl->xpath("//wl[@email='$GP_targetEmail' and @name='$GP_targetName' and @id='$GP_eventID']") as $el) {
			$domRef = dom_import_simplexml($el);
			$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
			$saveXmlFile($wl, WAITING_LIST_FILE);
		}

		traiterFileAttente($wl, $baseURL, $GP_eventID);
	} elseif ($action == "event_add" && $isAdmin) {
		$referee = getByPostOrGet('referee', '');
		$places = getByPostOrGet('places', -1);
		
		$event = [
			'time_start_sxb' => generateTimeStamp($_POST['startDate'], $_POST['startTime'], TIMEZONE),
			'time_end_sxb' => generateTimeStamp($_POST['startDate'],$_POST['endTime'], TIMEZONE),
			'categorie' => $_POST['cat'],
			'referent' => $referee,
			'submitter' => $GP_name,
			'places' => $places,
			'titre' =>  $_POST['title'],
			'' => $_POST['desc']
		];
		
		addEvent($event, $eventsXml);
		saveXmlFile($eventsXml, EVENT_FILE);
	} elseif ($action == 'event_edit' && $isAdmin) {
		unset($_POST['act']);
		if(isset($_POST["CDATA"]) && $_POST["CDATA"] !=  '') $_POST[''] = $_POST["CDATA"];
		
		$event = getEvent($eventsXml, $_POST['time_start_sxb'], $_POST['autoId']);
		editEvent($eventsXml, $event, $_POST);
		
		// Redirect to the same page with the query string
		header("Location: $baseURL$_SERVER[REQUEST_URI]");
		exit;
	} elseif ($action == 'event_get_raw' && $isAdmin) {
		preg_match('/^(\d+)-([a-zA-Z0-9_]+)$/', $GP_eventID, $matches);
		
		$event = getEvent($eventsXml, $matches[1], $matches[2]); // On query uniquement le xml pour la date demandée
		$result = [];
		$result["CDATA"] = (string)$event; // pour récupérer CDATA
		foreach( $event->attributes() as $key => $value) { // On parcourt chaque attribut
			if (isset($key) && $key != '') $result[$key] = (string)$value;
		}
		
		if(!isset($result['disactivation'])) $result['disactivation'] = '';
		
		// Set Content-Type header to application/json
		header('Content-Type: application/json');

		// Return event data as JSON
		echo json_encode($result);
		exit;
	} elseif ($action == "add") {
		// Gestion de l'inscription à la séance
		// Si on est sur un act ADD, alors on termine l'inscription
		// On vérifie qu'on a pas déjà une inscription avec ce nom et cet email
		$xmlWriteQuery = $xml->xpath("//insc[@email= '$GP_email' and @name='$GP_name' and @id='$GP_eventID']");
		$xmlWriteCount = $xml->xpath("//insc[@id= '$GP_eventID']"); // On compte le nombre d'inscription avant d'aller plus loin

		if (count($xmlWriteQuery) > 0) { // Si on trouve une inscription dans cette date avec ce nom et cet email, on arrête le script
			http_response_code(400); // Set the HTTP status code to 400 Bad Request
			echo "Vous êtes déjà inscrit sur cette session"; // Send back an error message in the response body
			exit;
		} elseif (count($xmlWriteCount) >= $participantsMaxId) {
			http_response_code(400); // Set the HTTP status code to 400 Bad Request
			echo "Dernière place prise"; // Send back an error message in the response body
			exit;
		}

		// Si on a atteint le nombre max de participant pendant le raffraichissement, on arrête le script
		// On écrit le fichier XML pour les inscriptions
		else {
			$cs = $xml->addChild("insc", ""); // On ajoute une nouvelle entrée
			$cs->addAttribute("id", $GP_eventID);
			$cs->addAttribute("name", $GP_name);
			$cs->addAttribute("email", $GP_email);

			saveXmlFile($xml, SUBSCRIPTION_FILE);

			// On en profite pour se retirer de la waiting list le cas échéant
			foreach ($wl->xpath("//wl[ @email='$GP_email' and @name='$GP_name' and @id='$GP_eventID']") as $el) {
				$domRef = dom_import_simplexml($el);
				$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
				saveXmlFile($wl, WAITING_LIST_FILE);
			}
		}
	} elseif ($action == "remove") { // Si on est sur un act REMOVE, alors on supprime le truc
		foreach ($xml->xpath("//insc[ @email='$GP_email' and @name='$GP_name' and @id='$GP_eventID']") as $el) {
			$domRef = dom_import_simplexml($el);
			$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
			saveXmlFile($xml, SUBSCRIPTION_FILE);
		}

		traiterFileAttente($wl, $baseURL, $GP_eventID);
	} elseif ($action == "waitingListRemove") {
		// Gestion de la waiting list
		// Si on est sur un act waitinListRemove, alors on supprime le truc
		foreach ($wl->xpath("//wl[@email='$GP_email' and @name='$GP_name' and @id='$GP_eventID']") as $el) {
			$domRef = dom_import_simplexml($el);
			$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
			saveXmlFile($wl, WAITING_LIST_FILE);
		}
	} elseif ($action == "waitingListAdd") {
		// Si on est sur un act waitingListADD, alors on y go
		// On vérifie qu'on a pas déjà une inscription avec ce nom et cet email
		$wlWriteQuery = $wl->xpath("//wl[@id='$GP_eventID' and @name='$GP_name' and @email='$GP_email']");

		if (count($wlWriteQuery) > 0) {
			// Si on trouve une inscription dans cette date avec ce nom et cet email, on arrête le script
			http_response_code(400); // Set the HTTP status code to 400 Bad Request
			echo "Vous êtes déjà inscrit sur cette liste"; // Send back an error message in the response body
			exit;
		}

		// On écrit le fichier XML pour les inscriptions
		else {
			$cs = $wl->addChild("wl", ""); // On ajoute une nouvelle entrée
			$cs->addAttribute("id", $GP_eventID);
			$cs->addAttribute("name", $GP_name);
			$cs->addAttribute("email", $GP_email);
			saveXmlFile($wl, WAITING_LIST_FILE);
		}
	} else {
		http_response_code(400); // Set the HTTP status code to 400 Bad Request
		echo "L'action demandée n'existe pas"; // Send back an error message in the response body
		exit;
	}
	
	http_response_code(200); // Opération réussie
	echo "L'action demandée a réussie"; // Send back an error message in the response body
	exit;
}

//---------------------- Gestion des filtres
$listFilters = []; // On prépare un array pour stocker les statuts des filtres
$quickFilterList = []; // on utilise l'index pour identifier rapidement les filtres qui sont actifs lors du filtrage des évènements

foreach (CATEGORIES as $key => $texte) {
	$filtreActif = getByPostOrGet($key, '') == 'on' ? 1 : 0;
	if($filtreActif) $quickFilterList[$key] = 1;
	
	$filter = [
		"categorie" => $key,
		"actif" => $filtreActif,
		"text" => $texte,
		];
	array_push($listFilters, $filter);
}

$fmt = new IntlDateFormatter( "fr_FR" ,IntlDateFormatter::FULL, IntlDateFormatter::FULL, TIMEZONE,IntlDateFormatter::GREGORIAN,'yyyy-MM-dd-HH-mm-eeee-MMMM');
$listCards = [];
foreach ($eventsXml->event as $event) {
	// Boucle qui passera chaque évènement en revue
	
	if (isset($event['categorie']) && array_key_exists((string)$event['categorie'], $quickFilterList)) continue; // L'evènement est filtré, donc on passe à la suite
	
	if((int)$event['time_end_sxb'] < $current_time) continue; // La date de début est passée, on passe à la suite. TODO : utiliser la date de fin plutôt... 
	$event_timestamp = (int)$event['time_start_sxb'];
	$cardId = $event_timestamp.'-'.$event['autoId'];
	
	list($start_year, $start_month, $start_day, $start_hour, $start_minutes, $start_weekday, $start_monthName) = explode("-", $fmt->format($event_timestamp));
	list(,,, $end_hour, $end_minutes,,) = explode("-", $fmt->format((int)$event['time_end_sxb']));
	
	$card = [];
	$card["jourFR"] = $start_weekday;
	$card["heureDebut"] = $start_hour.'h'.$start_minutes;
	$card["heureFin"] = $end_hour.'h'.$end_minutes;
	$card["dateJour"] = $start_day;
	$card["moisFR"] = $start_monthName;
	$card["annee"] = $start_year;
	$card["mois"] = $start_month;
	$card["heureDebut"] = $start_hour.'h'.$start_minutes;
	$card["heureFin"] = $end_hour.'h'.$end_minutes;
	$CDATA = (string)$event;
	if ((string)$event != "") $card["description"] = nl2br((string)$event); // pour récupérer CDATA
	foreach( $event->attributes() as $key => $value) { // On parcourt chaque attribut
		if (isset($key) && $key != '') $card[$key] = $value;
	}
	
	if (isset($event['places']) && (int)$event['places'] > 0) {
		// Gestion des inscrits
		$listInscrits = []; // On reset la liste des inscrit
		$inscMe = false; // On remet à false le fait d'être inscrit
		foreach ($xml->xpath("//insc[@id= '$cardId' ]") as $q) { // On query uniquement le xml pour la date demandée
			$inscrit = array('name' => $q["name"], 'email' => $q["email"]);
			if ($q["name"] == $GP_name && $q["email"] == $GP_email) {
				array_unshift($listInscrits, $inscrit); // Si on est inscrit on met en évidence son inscription et on permet de se désinscrire
				$inscMe = true;
			} else {
				array_push($listInscrits, $inscrit);
			}
		}

		// Gestion de la waiting list
		$wlInscrits = []; // On reset la waiting list au cas où
		$wlMe = false;
		foreach ($wl->xpath("//wl[@id= '$cardId']") as $q) { // On query uniquement le xml pour la date demandée
			$inscrit = array('name' => $q["name"], 'email' => $q["email"]);
			if ($q["name"] == $GP_name && $q["email"] == $GP_email) {
				// Si on est présent dans la waiting list, on indique que le statut "wlMe" est true
				array_unshift($wlInscrits, $inscrit); // Si on est en liste d'attente on met en évidence son inscription et on permet de se désinscrire
				$wlMe = true;
			} else {
				array_push($wlInscrits, $inscrit);
			}
		}
		
		$card["participantsMax"] = (int)$event['places'];
		$card["listInscrits"] = $listInscrits;
		$card["listAttenteInscrits"] = $wlInscrits;
		$card["inscMe"] = $inscMe;
		$card["wlMe"] = $wlMe;
	}

	$card["cardId"] = $cardId;

	array_push($listCards, $card);
}

// Sort array of events
usort($listCards, 'cmp'); 

function cmp($a, $b){
	return $a['time_start_sxb'] - $b['time_start_sxb'];
}

$smarty->assign("GP_name", $GP_name);
$smarty->assign("GP_email", $GP_email);

$smarty->assign("baseURL", $baseURL); // URL sans les filtres
$smarty->assign("loginURL", $loginURL); // URL sans les filtres et avec l'identifiant eventuel
$smarty->assign("isAdmin", $isAdmin);

$smarty->assign("todayDateFr", $fmtDateComplete->format($current_time));

$smarty->assign("unlockStyle", $unlockStyle);
$smarty->assign("dateDisplay", $listCards);

$smarty->assign("listFilters", $listFilters);

$smarty->display("bootstrap.tpl");

?>