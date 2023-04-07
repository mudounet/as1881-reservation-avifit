<?php

/////////////////////////////////////////////////////////////////////////
// Smarty
/////////////////////////////////////////////////////////////////////////

require 'setup.php';
require 'functions.php';
date_default_timezone_set("Europe/Paris"); // On définit la timezone sur notre fuseau horaire

// Type d'évènements
$arrayCategories = [
	"CAT_AFT" => "Avifit",
	"CAT_TNK" => "Tank à ramer",
	"CAT_PRO" => "Séances compétition",
	"CAT_LSR" => "Séances loisirs",
	"CAT_CMT" => "Réunions du comité",
	"CAT_ORG" => "Réunions d'organisations"
];

//---------------------- Paramètres	 /!\ Important
$arrayAdmin = [];
include 'admins.php';
$fmtDateComplete = new IntlDateFormatter( "fr_FR" ,IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Paris',IntlDateFormatter::GREGORIAN,'eeee dd MMMM yyyy à HH:mm');

/////////////////////////////////////////////////////////////////////////
// Lancement du code automatisé toutes les demis journées
/////////////////////////////////////////////////////////////////////////
$current_time = time();
$last_exec = file_get_contents('last_exec.txt');
// Check if the current time is greater than the last time the script was executed
if ($last_exec === false || $current_time > (int)$last_exec + 43200) {
	require('events_mgt.php');
	file_put_contents('last_exec.txt', $current_time);
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
	if (isset($property) && array_key_exists((string)$property, $_GET)) return $_GET[$property];
	if (isset($property) && array_key_exists((string)$property, $_POST)) return $_POST[$property];
	return $defaults;
}

function traiterFileAttente($wl, $baseURL, $GP_eventID) {
	list($timestamp, $type) = explode("-", $GP_eventID); // On explose la date pour pouvoir manipuler le contenu - 0 = année, 1 = mois, 2 = jour, 3 = heure
	
	global $fmtDateComplete;
	$dateHuman = $fmtDateComplete->format((int)$timestamp);

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
if (!($xml = simplexml_load_file("data.xml"))) $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><data/>');
if (!($wl = simplexml_load_file("wl.xml"))) $wl = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><data/>');
if (!($eventsXml = simplexml_load_file('events.xml'))) $eventsXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><events/>');

//---------------------- Inscription, Désinscription et Waiting List
// On vérifie si on a des données en POST ou en GET
$GP_name = getByPostOrGet('name', '');
$GP_email = getByPostOrGet('email', '');
$GP_eventID = getByPostOrGet('id', null);

$participantsMaxId = null;
if($GP_eventID) {
	try {
		if (!preg_match('/^(\d+)-([a-zA-Z0-9_]+)$/', $GP_eventID, $matches)) throw new Exception('Pas valide');
		$match = false;
		foreach ($eventsXml->xpath("//event[@autoId='$matches[2]' and @timestamp='$matches[1]']") as $q) { // On query uniquement le xml pour la date demandée
			$match = true;
			if(isset($q['places'])) $participantsMaxId = (int)$q['places'];
		}
		if (!$match) throw new Exception('Pas valide');
	} catch (Exception $e) {
		echo 'id invalide';//,  $e->getMessage(), "\n";
		exit;
		
	}
}

$isAdmin = false;

if ($GP_name != "" && $GP_email != "") {
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

$action = getByPostOrGet('act', "");

if ($action == "event_add" && $isAdmin) {
	$referee = getByPostOrGet('referee', '');
	$places = getByPostOrGet('places', -1);
	
	$event = [
		'heureDebut' => str_replace(":", "h", $_POST['startTime']),
		'heureFin' => str_replace(":", "h", $_POST['endTime']),
		'categorie' => $_POST['cat'],
		'referent' => $referee,
		'submitter' => $GP_name,
		'places' => $places,
		'date' => $_POST['startDate'],
		'titre' =>  $_POST['title'],
		'timestamp' => \DateTime::createFromFormat('Y-m-d H:i T', $_POST['startDate'].' '.$_POST['startTime'].' Europe/Paris')->getTimestamp(),
		'' => $_POST['desc']
	];
	
	addEvent($event, $eventsXml);
	saveXmlFile($eventsXml, "events.xml");
}

// Gestion de l'inscription à la séance
// Si on est sur un act ADD, alors on termine l'inscription
if ($action == "add"
	&& $GP_name != ""
	&& $GP_email != ""
	&& $GP_eventID != "") {
	// On vérifie qu'on a pas déjà une inscription avec ce nom et cet email
	$xmlWriteQuery = $xml->xpath("//insc[@email= '$GP_email' and @name='$GP_name' and @id='$GP_eventID']");
	$xmlWriteCount = $xml->xpath("//insc[@id= '$GP_eventID']"); // On compte le nombre d'inscription avant d'aller plus loin

	if (count($xmlWriteQuery) > 0) {
		$smarty->assign(
			"error_user_message",
			"Vous êtes déjà inscrit sur cette session"
		);
	}
	
	// Si on trouve une inscription dans cette date avec ce nom et cet email, on arrête le script
	elseif (count($xmlWriteCount) >= $participantsMaxId) {
		$smarty->assign(
			"error_user_message",
			"Désolé ! La place a été prise le temps que vous cliquiez sur le bouton !"
		);
	}

	// Si on a atteint le nombre max de participant pendant le raffraichissement, on arrête le script
	// On écrit le fichier XML pour les inscriptions
	else {
		$cs = $xml->addChild("insc", ""); // On ajoute une nouvelle entrée
		$cs->addAttribute("id", $GP_eventID);
		$cs->addAttribute("name", $GP_name);
		$cs->addAttribute("email", $GP_email);

		saveXmlFile($xml, "data.xml");

		// On en profite pour se retirer de la waiting list le cas échéant
		foreach ($wl->xpath("//wl[ @email='$GP_email' and @name='$GP_name' and @id='$GP_eventID']") as $el) {
			$domRef = dom_import_simplexml($el);
			$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
			saveXmlFile($wl, "wl.xml");
		}
	}
}

// Si on est sur un act REMOVE, alors on supprime le truc
if ($action == "remove" && $GP_name != "" && $GP_email != "" && $GP_eventID != '') {
	foreach ($xml->xpath("//insc[ @email='$GP_email' and @name='$GP_name' and @id='$GP_eventID']") as $el) {
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		saveXmlFile($xml, "data.xml");
	}

	traiterFileAttente($wl, $baseURL, $GP_eventID);
}

$GP_targetName = getByPostOrGet('targetName', "");
$GP_targetEmail = getByPostOrGet('targetEmail', "");

// Si on est sur un act REMOVE, alors on supprime le truc
if ($action == "adminRemove"
	&& $GP_targetName != ""
	&& $GP_targetEmail != ""
	&& $GP_eventID != ''
	&& $isAdmin == true) {
	
	// Suppression de la liste normale
	foreach ($xml->xpath("//insc[@email='$GP_targetEmail' and @name='$GP_targetName' and @id='$GP_eventID']") as $el) {
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		saveXmlFile($xml, "data.xml");
	}
	
	// Suppression de la liste d'attente
	foreach ($wl->xpath("//wl[@email='$GP_targetEmail' and @name='$GP_targetName' and @id='$GP_eventID']") as $el) {
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		$saveXmlFile($wl, "wl.xml");
	}

	traiterFileAttente($wl, $baseURL, $GP_eventID);
}

// Gestion de la waiting list
// Si on est sur un act waitinListRemove, alors on supprime le truc
if ($action == "waitingListRemove" && $GP_name != "" && $GP_email != "") {
	foreach ($wl->xpath("//wl[@email='$GP_email' and @name='$GP_name' and @id='$GP_eventID']") as $el) {
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		saveXmlFile($wl, "wl.xml");
	}
}

// Si on est sur un act waitingListADD, alors on y go
if ($action == "waitingListAdd"
	&& $GP_name != ""
	&& $GP_email != ""
	&& $GP_eventID != "") {
	// On vérifie qu'on a pas déjà une inscription avec ce nom et cet email
	$wlWriteQuery = $wl->xpath("//wl[@id='$GP_eventID' and @name='$GP_name' and @email='$GP_email']");

	if (count($wlWriteQuery) > 0) {
		// Si on trouve une inscription dans cette date avec ce nom et cet email, on arrête le script
		$smarty->assign(
			"error_subscribe_db_message",
			"Vous êtes déjà inscrit sur cette liste d'attente"
		);
	}

	// On écrit le fichier XML pour les inscriptions
	else {
		$cs = $wl->addChild("wl", ""); // On ajoute une nouvelle entrée
		$cs->addAttribute("id", $GP_eventID);
		$cs->addAttribute("name", $GP_name);
		$cs->addAttribute("email", $GP_email);
		saveXmlFile($wl, "wl.xml");
	}
}

//---------------------- Gestion des filtres
$listFilters = []; // On prépare un array pour stocker les statuts des filtres
$quickFilterList = []; // on utilise l'index pour identifier rapidement les filtres qui sont actifs lors du filtrage des évènements

foreach ($arrayCategories as $key => $texte) {
	$filtreActif = getByPostOrGet($key, '') == 'on' ? 1 : 0;
	if($filtreActif) $quickFilterList[$key] = 1;
	
	$filter = [
		"categorie" => $key,
		"actif" => $filtreActif,
		"text" => $texte,
		];
	array_push($listFilters, $filter);
}

$fmt = new IntlDateFormatter( "fr_FR" ,IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Paris',IntlDateFormatter::GREGORIAN,'yyyy-MM-dd-HH-mm-eeee-MMMM');
$listCards = [];
foreach ($eventsXml->event as $event) {
	// Boucle qui passera chaque évènement en revue
	
	if (isset($event['categorie']) && array_key_exists((string)$event['categorie'], $quickFilterList)) continue; // L'evènement est filtré, donc on passe à la suite
	
	$event_timestamp = (int)$event['timestamp'];
	
	if($event_timestamp < $current_time) continue; // La date de début est passée, on passe à la suite. TODO : utiliser la date de fin plutôt... 
	
	list($year, $month, $day, $hour, $minutes, $weekday, $monthName) = explode("-", $fmt->format($event_timestamp));
	
	$card = [];
	$card["jourFR"] = $weekday;
	$card["dateJour"] = $day;
	$card["moisFR"] = $monthName;
	$card["annee"] = $year;
	$card["mois"] = $month;
	$CDATA = (string)$event;
	if ((string)$event != "") $card["description"] = (string)$event; // pour récupérer CDATA
	foreach( $event->attributes() as $key => $value) { // On parcourt chaque attribut
		if (isset($key) && $key != '') $card[$key] = $value;
	}



	if (isset($event['places']) && (int)$event['places'] > 0) {
		$cardId = $event_timestamp.'-'.$event['autoId'];
		
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
		
		$card["cardId"] = $cardId;
		$card["participantsMax"] = (int)$event['places'];
		$card["listInscrits"] = $listInscrits;
		$card["listAttenteInscrits"] = $wlInscrits;
		$card["inscMe"] = $inscMe;
		$card["wlMe"] = $wlMe;
	}

	array_push($listCards, $card);
}

// Sort array of events
usort($listCards, 'cmp'); 

function cmp($a, $b){
	return $a['timestamp'] - $b['timestamp'];
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