<?php

/////////////////////////////////////////////////////////////////////////
// Smarty
/////////////////////////////////////////////////////////////////////////

require "setup.php";

$smarty = new Smarty_Aviron();

$protocol = empty($_SERVER["HTTPS"]) ? "http" : "https";
$domain = $_SERVER["SERVER_NAME"];
$port = $_SERVER["SERVER_PORT"];
$disp_port = ($protocol == "http" && $port == 80)
	|| ($protocol == "https" && $port == 443) ? "" : ":$port";
	
$base_url = preg_replace('/^'.preg_quote($_SERVER["DOCUMENT_ROOT"]).'/', "", __DIR__);

// put em all together to get the complete base URL
$baseURL = "${protocol}://${domain}${disp_port}${base_url}";

// Type d'évènements
$arrayCategories = [
	"CATEGORIE_SEANCES_AVIFIT" => "Avifit",
	"CATEGORIE_SEANCES_TANKARAMER" => "Tank à ramer",
	"CATEGORIE_SEANCES_COMPETITION" => "Séances compétition",
	"CATEGORIE_SEANCES_LOISIRS" => "Séances loisirs",
];

//---------------------- Paramètres	 /!\ Important
$participantsMax = 12; // Nombre maximal de participants (par défaut : 12)
$dateDebloquante = 15; // Jour du mois débloquant les inscriptions du mois suivant (par défaut : le 15 du mois)
$dateDebloLimit = 1; // Nombre de mois débloqués en arrivant au jour de la variable précédente (par défaut : 1)
$datePurge = 10; // Nombre de jours avant lequel les informations sont supprimées (RGPD toussa) (par défaut : 10)

$arrayAdmin = [];
include 'admins.php';
//----------------------  Fonctions	& Pré-requis
function getByPostOrGet($property, $defaults) {
	if (isset($property) && array_key_exists((string)$property, $_GET)) return $_GET[$property];
	if (isset($property) && array_key_exists((string)$property, $_POST)) return $_POST[$property];
	return $defaults;
}

date_default_timezone_set("Europe/Paris"); // On définit la timezone sur notre fuseau horaire

//---------------------- Chargement de la BDD, toutes les requetes peuvent utiliser cette variable pour charger la bdd
if (!($xml = simplexml_load_file("data.xml"))) {
	$smarty->assign(
		"error_subscribe_db_message",
		"Echec de chargement de la base de données des inscriptions"
	);
}
if (!($wl = simplexml_load_file("wl.xml"))) {
	$smarty->assign(
		"error_wait_list_db_message",
		"Echec de chargement de la base de données de la liste d'attente"
	);
}
if (!($eventsXml = simplexml_load_file('events.xml'))) {
	$smarty->assign(
		"error_wait_list_db_message",
		"Echec de chargement de la base de données des évènements"
	);
}

//---------------------- Grooming - On fait le ménage dans la BDD
// On détermine la date avant laquelle toutes les entrées sont supprimées
$dateDeNettoyage = date("Ymd") - $datePurge . "1830"; // On supprime 10 jours avant la date du jour
// echo $dateDeNettoyage;
foreach ($xml->xpath("//insc[ translate(@date,'-','') < $dateDeNettoyage ]") as $el) {
	// On mouline dans la liste des inscrits
	$domRef = dom_import_simplexml($el);
	$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
	$dom = new DOMDocument("1.0");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true; // Préservation de la présentation
	$dom->loadXML($xml->asXML()); // On charge le résultat dans un DOM Doc
	$dom->save("data.xml"); // On écrit le résultat
}

foreach ($wl->xpath("//wl[ translate(@date,'-','') < $dateDeNettoyage ]") as $el) {
	// On mouline dans la waiting list (WL)
	$domRef = dom_import_simplexml($el);
	$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
	$dom = new DOMDocument("1.0");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true; // Préservation de la présentation
	$dom->loadXML($wl->asXML()); // On charge le résultat dans un DOM Doc
	$dom->save("wl.xml"); // On écrit le résultat
}
//---------------------- Inscription, Désinscription et Waiting List
// On vérifie si on a des données en POST ou en GET
$GP_name = getByPostOrGet('name', '');
$GP_email = getByPostOrGet('email', '');
$GP_date = getByPostOrGet('date', '');
$isAdmin = false;

if ($GP_name != "" && $GP_email != "") {
	// Si on a un post ou un get d'email et de name, l'inscription est ouverte
	$unlockInsc = true;
	$unlockStyle = "unlocked";
	$urlIdentity = "?name=" . $GP_name . "&email=" . $GP_email;
	$myURL = $baseURL . "/" . $urlIdentity; // On génère l'URL complète pour que l'utilisateur puisse le mettre en favoris

	// On détermine si la personne est admin
	foreach ($arrayAdmin as $k => $v) {
		if ($k == $GP_name && $v == $GP_email) {
			$isAdmin = true;
		}
	}
} else {
	$unlockStyle = "";
	$myURL = $baseURL . "?anonymous";
}

$action = getByPostOrGet('act', "");
// Gestion de l'inscription à la séance
// Si on est sur un act ADD, alors on termine l'inscription
if ($action == "add"
	&& $GP_name != ""
	&& $GP_email != ""
	&& $GP_date != "") {
	// On vérifie qu'on a pas déjà une inscription avec ce nom et cet email
	$xmlWriteQuery = $xml->xpath(
		"//insc[@email= '$GP_email' and @name='$GP_name' and @date='$GP_date']"
	);
	$xmlWriteCount = $xml->xpath("//insc[@date= '$GP_date']"); // On compte le nombre d'inscription avant d'aller plus loin

	if (count($xmlWriteQuery) > 0) {
		$smarty->assign(
			"error_user_message",
			"Vous êtes déjà inscrit sur cette session"
		);
	}
	// Si on trouve une inscription dans cette date avec ce nom et cet email, on arrête le script
	elseif (count($xmlWriteCount) >= $participantsMax) {
		$smarty->assign(
			"error_user_message",
			"Désolé ! La place a été prise le temps que vous cliquiez sur le bouton !"
		);
	}

	// Si on a atteint le nombre max de participant pendant le raffraichissement, on arrête le script
	// On écrit le fichier XML pour les inscriptions
	else {
		$cs = $xml->addChild("insc", ""); // On ajoute une nouvelle entrée
		$cs->addAttribute("date", $GP_date);
		$cs->addAttribute("name", $GP_name);
		$cs->addAttribute("email", $GP_email);

		$dom = new DOMDocument("1.0");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true; // Préservation de la présentation
		$dom->loadXML($xml->asXML()); // On charge le résultat dans un DOM Doc
		$dom->save("data.xml"); // On écrit le résultat

		// On en profite pour se retirer de la waiting list le cas échéant
		foreach ($wl->xpath(
			'//wl[ @email="'
				. $GP_email
				. '" and @name="'
				. $GP_name
				. '" and @date="'
				. $GP_date
				. '"]'
		) as $el) {
			$domRef = dom_import_simplexml($el);
			$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
			$dom = new DOMDocument("1.0");
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true; // Préservation de la présentation
			$dom->loadXML($wl->asXML()); // On charge le résultat dans un DOM Doc
			$dom->save("wl.xml"); // On écrit le résultat
		}
	}
}

// Si on est sur un act REMOVE, alors on supprime le truc
if ($action == "remove" && $GP_name != "" && $GP_email != "") {
	foreach ($xml->xpath(
		'//insc[ @email="'
			. $GP_email
			. '" and @name="'
			. $GP_name
			. '" and @date="'
			. $GP_date
			. '"]'
	) as $el) {
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		$dom = new DOMDocument("1.0");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true; // Préservation de la présentation
		$dom->loadXML($xml->asXML()); // On charge le résultat dans un DOM Doc
		$dom->save("data.xml"); // On écrit le résultat
	}

	$kp = explode("-", $GP_date); // On explose la date pour pouvoir manipuler le contenu - 0 = année, 1 = mois, 2 = jour, 3 = heure
	$kp2Trim = $kp[1] - 1; // On enleve 1 pour tomber sur l'array
	$monthFr = $monthHuman[$kp2Trim];
	$hourFr = substr_replace($kp[3], "h", 2, 0); // On rajoute un "H" pour une lecture facile
	$dateHuman = $dayFr
		. " "
		. $kp[2]
		. " "
		. $monthFr
		. " "
		. $kp[0]
		. " à "
		. $hourFr
		. ""; // On transforme la date

	// On génère les emails pour les personnes en liste d'attente
	foreach ($wl->xpath('//wl[@date="' . $GP_date . '"]') as $el) {
		$mailBody = '
						Bonjour '
			. $el["name"]
			. '<br/><br/>
						Une place vient de se libérer pour la séance d\'AVIFIT du <b>'
			. $dateHuman
			. '</b> ! Une chance !<br/><br/>
						Si la place est toujours disponible, vous pouvez utiliser le lien suivant pour la retrouver la liste des sessions : <a href="'
			. $baseURL
			. "/?name="
			. $el["name"]
			. "&email="
			. $el["email"]
			. '" target="_blank">Liste des séances d\'Avifit disponibles BETA TEST</a>.<br/><br/>
						Ce lien vous authentifie automatiquement.<br/><br/>	
						<i>Ce mail est automatique, NE PAS REPONDRE ! </i><br/><br/>					
						A bientôt !';

		// On prépare le mail à envoyer pour confirmer
		$to = $el["email"];
		$subject = "AS1881 - Une place vient de se libérer pour le "
			. $dateHuman
			. " !";
		$message = $mailBody;

		$headers = "From: [TEST !!!] AS1881 - Avifit <avironstrasbourg1881@gmail.com >"
			. "\r\n";
		$headers .= "Reply-To: avironstrasbourg1881@gmail.com " . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		// On envoie le mail
		mail($to, $subject, $message, $headers);
		// echo $message; // Debug
	}
}

// Si on est sur un act REMOVE, alors on supprime le truc
if ($action == "adminRemove"
	&& $_GET["targetName"] != ""
	&& $_GET["targetEmail"] != ""
	&& $_GET["date"] != ""
	&& $isAdmin == true) {
	foreach ($xml->xpath(
		'//insc[ @email="'
			. $_GET["targetEmail"]
			. '" and @name="'
			. $_GET["targetName"]
			. '" and @date="'
			. $_GET["date"]
			. '"]'
	) as $el) {
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		$dom = new DOMDocument("1.0");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true; // Préservation de la présentation
		$dom->loadXML($xml->asXML()); // On charge le résultat dans un DOM Doc
		$dom->save("data.xml"); // On écrit le résultat
	}

	$kp = explode("-", $GP_date); // On explose la date pour pouvoir manipuler le contenu - 0 = année, 1 = mois, 2 = jour, 3 = heure
	$kp2Trim = $kp[1] - 1; // On enleve 1 pour tomber sur l'array
	$monthFr = $monthHuman[$kp2Trim];
	$hourFr = substr_replace($kp[3], "h", 2, 0); // On rajoute un "H" pour une lecture facile
	$dateHuman = $dayFr
		. " "
		. $kp[2]
		. " "
		. $monthFr
		. " "
		. $kp[0]
		. " à "
		. $hourFr
		. ""; // On transforme la date

	// On génère les emails pour les personnes en liste d'attente
	foreach ($wl->xpath('//wl[@date="' . $GP_date . '"]') as $el) {
		$mailBody = '
						Bonjour '
			. $el["name"]
			. '<br/><br/>
						Une place vient de se libérer pour la séance d\'AVIFIT du <b>'
			. $dateHuman
			. '</b> ! Une chance !<br/><br/>
						Si la place est toujours disponible, vous pouvez utiliser le lien suivant pour la retrouver la liste des sessions : <a href="'
			. $baseURL
			. "/?name="
			. $el["name"]
			. "&email="
			. $el["email"]
			. '" target="_blank">Liste des séances d\'Avifit disponibles BETA TEST</a>.<br/><br/>
						Ce lien vous authentifie automatiquement.<br/><br/>	
						<i>Ce mail est automatique, NE PAS REPONDRE ! </i><br/><br/>					
						A bientôt !';

		// On prépare le mail à envoyer pour confirmer
		$to = $el["email"];
		$subject = "AS1881 - Une place vient de se libérer pour le "
			. $dateHuman
			. " !";
		$message = $mailBody;

		$headers = "From: [TEST !!!] AS1881 - Avifit <avironstrasbourg1881@gmail.com >"
			. "\r\n";
		$headers .= "Reply-To: avironstrasbourg1881@gmail.com " . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		// On envoie le mail
		mail($to, $subject, $message, $headers);
	}
}

// Gestion de la waiting list
// Si on est sur un act waitinListRemove, alors on supprime le truc
if ($action == "waitingListRemove" && $GP_name != "" && $GP_email != "") {
	foreach ($wl->xpath(
		'//wl[ @email="'
			. $GP_email
			. '" and @name="'
			. $GP_name
			. '" and @date="'
			. $GP_date
			. '"]'
	) as $el) {
		$domRef = dom_import_simplexml($el);
		$domRef->parentNode->removeChild($domRef); // On supprime le child du parent pour retomber sur notre entrée
		$dom = new DOMDocument("1.0");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true; // Préservation de la présentation
		$dom->loadXML($wl->asXML()); // On charge le résultat dans un DOM Doc
		$dom->save("wl.xml"); // On écrit le résultat
	}
}

// Si on est sur un act waitingListADD, alors on y go
if ($action == "waitingListAdd"
	&& $GP_name != ""
	&& $GP_email != ""
	&& $GP_date != "") {
	// On vérifie qu'on a pas déjà une inscription avec ce nom et cet email
	$wlWriteQuery = $wl->xpath(
		"//wl[@date= '$GP_date' and @name='$GP_name' and @email='$GP_email']"
	);

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
		$cs->addAttribute("date", $GP_date);
		$cs->addAttribute("name", $GP_name);
		$cs->addAttribute("email", $GP_email);
		$wl->asXML("wl.xml"); // On écrit dans un fichier XML
	}
}

//---------------------- Gestion des filtres
$listFilters = []; // On prépare un array pour stocker les statuts des filtres
$quickFilterList = []; // on utilise l'index pour identifier rapidement les filtres qui sont actifs lors du filtrage des évènements

foreach ($arrayCategories as $key => $texte) {
	$filtreActif = getByPostOrGet($key, '') == 'hide' ? 1 : 0;
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
	
	list($year, $month, $day, $hour, $minutes, $weekday, $monthName) = explode("-", $fmt->format((int)$event['timestamp']));
	
	$cardId = $event['timestamp'].'-'.$event['autoId'];
	
	// Gestion des inscrits
	$listInscrits = []; // On reset la liste des inscrit
	$inscMe = false; // On remet à false le fait d'être inscrit
	foreach ($xml->xpath("//insc[@date= '$cardId' ]") as $q) { // On query uniquement le xml pour la date demandée
		if ($q["name"] == $GP_name && $q["email"] == $GP_email) {
			array_unshift($listInscrits, $q["name"]); // Si on est inscrit on met en évidence son inscription et on permet de se désinscrire
			$inscMe = true;
		} else {
			array_push($listInscrits, $q["name"]);
		}
	}

	// Gestion de la waiting list
	$wlInscrits = []; // On reset la waiting list au cas où
	$wlMe = false;
	foreach ($wl->xpath("//wl[@date= '$cardId']") as $q) { // On query uniquement le xml pour la date demandée
		if ($q["name"] == $GP_name && $q["email"] == $GP_email) {
			// Si on est présent dans la waiting list, on indique que le statut "wlMe" est true
			array_unshift($wlInscrits, $q["name"]); // Si on est en liste d'attente on met en évidence son inscription et on permet de se désinscrire
			$wlMe = true;
		} else {
			array_push($wlInscrits, $q["name"]);
		}
	}

	$card = [
		"categorie" => $event['categorie'],
		"cardId" => $cardId,
		"jourFR" => $weekday,
		"dateJour" => $day,
		"moisFR" => $monthName,
		"annee" => $year,
		"mois" => $month,
		"heureDebut" => $event['heureDebut'],
		"heureFin" => $event['heureFin'],
		"animateur" => $event['referent'],
		"listInscrits" => $listInscrits,
		"listAttenteInscrits" => $wlInscrits,
		"participantsMax" => $participantsMax,
		"inscMe" => $inscMe,
		"wlMe" => $wlMe,
	];

	array_push($listCards, $card);
}

$smarty->assign("GP_name", $GP_name);
$smarty->assign("GP_email", $GP_email);

$smarty->assign("baseURL", $baseURL);
$smarty->assign("myURL", $myURL);
$smarty->assign("isAdmin", $isAdmin);

$smarty->assign("dateDebloquante", $dateDebloquante);
$smarty->assign("unlockStyle", $unlockStyle);
$smarty->assign("dateDisplay", $listCards);

$smarty->assign("listFilters", $listFilters);

$smarty->display("index.tpl");

?>