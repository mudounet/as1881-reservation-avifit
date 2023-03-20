<?php

/////////////////////////////////////////////////////////////////////////
// Smarty
/////////////////////////////////////////////////////////////////////////

require "setup.php";

$smarty = new Smarty_Aviron();

// base directory
$base_dir = __DIR__;

// server protocol
$protocol = empty($_SERVER["HTTPS"]) ? "http" : "https";

// domain name
$domain = $_SERVER["SERVER_NAME"];

$doc_root = $_SERVER["DOCUMENT_ROOT"];

// base url
$base_url = preg_replace("!^${doc_root}!", "", $base_dir);
$base_url = "";

// server port
$port = $_SERVER["SERVER_PORT"];
$disp_port = ($protocol == "http" && $port == 80)
	|| ($protocol == "https" && $port == 443) ? "" : ":$port";

// put em all together to get the complete base URL
$baseURL = "${protocol}://${domain}${disp_port}${base_url}";

//---------------------- Paramètres   /!\ Important
$participantsMax = 12; // Nombre maximal de participants (par défaut : 12)
$dateDebloquante = 15; // Jour du mois débloquant les inscriptions du mois suivant (par défaut : le 15 du mois)
$dateDebloLimit = 1; // Nombre de mois débloqués en arrivant au jour de la variable précédente (par défaut : 1)
$datePurge = 10; // Nombre de jours avant lequel les informations sont supprimées (RGPD toussa) (par défaut : 10)
$arraySeances = [
	"Dimanche" => "",
	"Lundi" => "1830",
	"Mardi" => "",
	"Mercredi" => "1830,1930",
	"Jeudi" => "",
	"Vendredi" => "1830",
	"Samedi" => "",
]; // On liste les séances qu'on veut, on met les horaires au format HHmm, séparés par une "," , pour pouvoir les manipuler comme des nombres par la suite
$arrayAnimateur = [
	"Dimanche" => "",
	"Lundi" => "",
	"Mardi" => "",
	"Mercredi" => "JP",
	"Jeudi" => "",
	"Vendredi" => "Fred",
	"Samedi" => "",
]; // On liste les animateurs des séances
$arrayAdmin = [];
include 'admins.php';
//----------------------  Fonctions	& Pré-requis
function getBetweenDates($startDate, $endDate)
{
	$rangArray = [];

	$startDate = strtotime($startDate);
	$endDate = strtotime($endDate);

	for ($currentDate = $startDate; $currentDate < $endDate; $currentDate += 86400) {
		$date = date("w-Y-m-d", $currentDate);
		$rangArray[] = $date;
	}
	return $rangArray;
}

date_default_timezone_set("Europe/Paris"); // On définit la timezone sur notre fuseau horaire
$dayHuman = [
	"Dimanche",
	"Lundi",
	"Mardi",
	"Mercredi",
	"Jeudi",
	"Vendredi",
	"Samedi",
]; // On liste les jours de la semaine en français
$monthHuman = [
	"Janvier",
	"Février",
	"Mars",
	"Avril",
	"Mai",
	"Juin",
	"Juillet",
	"Aout",
	"Septembre",
	"Octobre",
	"Novembre",
	"Décembre",
]; // On liste les mois de l'année en français

// On prépare les limites
$dateToday = date("Y-m-d");
$dateTodayPieces = explode("-", $dateToday); // 0 : Année ;  1 : Mois ;  2 : jour
$dateTodayPiecesToHuman = $dateTodayPieces[1] - 1; // Pas envie d'en faire un int...
$dateTodayHuman = $dateTodayPieces[2]
	. " "
	. $monthHuman[$dateTodayPiecesToHuman]
	. " "
	. $dateTodayPieces[0]; // On a la date du jour au format compréhensible

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
$GP_name = "";
$GP_email = "";
$GP_date = "";
$isAdmin = false;

if ($_GET["name"] != "") {
	$GP_name = $_GET["name"];
} elseif ($_POST["name"] != "") {
	$GP_name = $_POST["name"];
}

if ($_GET["email"] != "") {
	$GP_email = $_GET["email"];
} elseif ($_POST["email"] != "") {
	$GP_email = $_POST["email"];
}
$GP_date = $_GET["date"];

//echo 'Debug :'.$GP_date.' - '.$GP_email.' - '.$GP_name;

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
	$myURL = $baseURL . "?anonymous";
}

// Gestion de l'inscription à la séance
// Si on est sur un act ADD, alors on termine l'inscription
if ($_GET["act"] == "add"
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
if ($_GET["act"] == "remove" && $GP_name != "" && $GP_email != "") {
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
if ($_GET["act"] == "adminRemove"
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
if ($_GET["act"] == "waitingListRemove" && $GP_name != "" && $GP_email != "") {
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
if ($_GET["act"] == "waitingListAdd"
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
$listFiltersInCSS = ""; // On vide le contenu qui sera envoyé dans la CSS au cas où
$listFiltersInURL = ""; // On vide la liste des filtres pour commencer
$listFiltersInHref = ""; // On vide la liste des items dans le href qu'on va faire
$listFiltersArray = []; // On prépare un array pour stocker les statuts des filtres
$listFiltersI = 0;
if ($unlockInsc != true) {
	$urlSafety = "?anonymous";
}

// Il faut que je repasse deux fois par cette boucle pour pouvoir avoir l'état de TOUS les filtres. J'ai pas trouvé de moyen d'optimiser la chose.
foreach ($arraySeances as $as => $ask) {
	$listSeanceDuJour = explode(",", $ask); // On éclate la liste des horaires du jours

	foreach ($listSeanceDuJour as $i) {
		// On commence par établir l'URL pour maintenir les différents éléments à travers les manipulations
		if ($i != "") {
			$aski = substr_replace($i, "h", 2, 0); // On rajoute un "H" pour une lecture facile
			$cssFiltersName = $as . "" . $aski; // On génère le nom de la classe CSS pour l'affichage/désaffichage

			if ($_GET[$cssFiltersName] == "hide") {
				$listFiltersArray[$cssFiltersName] = "hide";
				$listFiltersI++; // On incrémente le compteur de filtre actif
			} elseif ($_GET[$cssFiltersName] == "show"
				or $_GET[$cssFiltersName] == "") {
				// Le but est de récupérer les items dans l'URL, de changer l'option pour CET element, mais de garder son statut pour générer les liens suivants
				$listFiltersInURL .= ""; // Dans l'URL actuelle, on ne mets rien si rien n'est précisé
				$listFiltersArray[$cssFiltersName] = "show";
			}
		}
	}
}

$listFilters = [];
foreach ($arraySeances as $as => $ask) {
	$listSeanceDuJour = explode(",", $ask); // On éclate la liste des horaires du jours

	foreach ($listSeanceDuJour as $i) {
		// On commence par établir l'URL pour maintenir les différents éléments à travers les manipulations
		if ($i != "") {
			$aski = substr_replace($i, "h", 2, 0); // On rajoute un "H" pour une lecture facile
			$cssFiltersName = $as . "" . $aski; // On génère le nom de la classe CSS pour l'affichage/désaffichage
			$listFiltersInHref = ""; // On reset l'HREF pour cette variable

			// On commence une boucle avec l'array qui contient les filtres
			foreach ($listFiltersArray as $filter => $filterkey) {
				if ($filter != $cssFiltersName && $filterkey == "hide") {
					// Si l'array ne correspond pas au filtre et que son statut est HIDE, on l'ajoute à l'URL pour cette phase là
					$listFiltersInHref .= "&" . $filter . "=hide";
				} elseif ($filter == $cssFiltersName && $filterkey == "hide") {
					$listFiltersInHref .= "";
					$listFiltersInClass = "filter-hidden";
					$listFiltersInURL .= "&" . $cssFiltersName . "=hide";
					$listFiltersInCSS .= "." . $cssFiltersName . " {display:none;}";
				} elseif ($filter == $cssFiltersName && $filterkey == "show") {
					$listFiltersInHref .= "&" . $cssFiltersName . "=hide";
					$listFiltersInClass = "filter-shown";
				}
			}

			$filter = [
				"url" => $myURL . $listFiltersInHref,
				"class" => $listFiltersInClass,
				"text" => $as . " à " . $aski,
			];
			array_push($listFilters, $filter);
		}
	}
}

//---------------------- Affichage
// Gestion des dates affichées
// On manipule les années
if ($dateTodayPieces[1] == 12) {
	$dateLimitYear = $dateTodayPieces[0] + 1;
}
// Si on est en décembre, la limite est fixé à l'an prochain
else {
	$dateLimitYear = $dateTodayPieces[0];
}

// On manipule les mois
if ($dateTodayPieces[2] >= $dateDebloquante) {
	$dateLimitMonth = $dateTodayPieces[1] + 1 + $dateDebloLimit;
}
// Si on est le $dateDebloquante du mois, on décale au mois suivant
else {
	$dateLimitMonth = $dateTodayPieces[1] + $dateDebloLimit;
}

$dateLimitMonthHuman = $monthHuman[$dateLimitMonth - 2];

// On définit le jour de la limite, qui sera toujours le 1er du mois suivant pour plus de facilité, et ca m'évite de prendre en compte les mois à 28, 29, 30 & 31 jours
$dateLimitDay = 1;

$dateLimit = $dateLimitYear . "-" . $dateLimitMonth . "-" . $dateLimitDay; // On reconstruit la date limite
$dates = getBetweenDates($dateToday, $dateLimit); // On liste les dates entre aujourd'hui et la date limite

$kId = 1; // On met le compteur de session à 1

$listCards = [];
foreach ($dates as $k) {
	// Boucle qui passera chaque jour en revue

	$kp = explode("-", $k); // On explose la date pour pouvoir manipuler le contenu - 0 = type jour, 1 = année, 2 = mois, 3 = jour
	$kp2Trim = $kp[2] - 1; // On enleve 1 pour tomber sur l'array
	$dayFr = $dayHuman[$kp[0]];

	if ($arraySeances[$dayFr] != "") {
		// Si le jour correspond à l'array du début, alors on affiche une ligne

		$listSeanceDuJour = explode(",", $arraySeances[$dayFr]); // On éclate la liste des horaires du jours

		foreach ($listSeanceDuJour as $i) {
			// On formate la date en un truc pas trop moche
			$iHuman = substr_replace($i, "h", 2, 0); // On rajoute un "H" pour une lecture facile

			// On récupère les inscrits et les gens de la waiting list
			$dateXmlQuery = $kp[1] . "-" . $kp[2] . "-" . $kp[3] . "-" . $i; // Je reconstruit la date

			// Gestion des inscrits
			$listInscrits = []; // On reset la liste des inscrit
			$inscMe = false; // On remet à false le fait d'être inscrit
			foreach ($xml->xpath("//insc[@date= '$dateXmlQuery' ]") as $q) { // On query uniquement le xml pour la date demandée
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
			foreach ($wl->xpath("//wl[@date= '$dateXmlQuery']") as $q) { // On query uniquement le xml pour la date demandée
				if ($q["name"] == $GP_name && $q["email"] == $GP_email) {
					// Si on est présent dans la waiting list, on indique que le statut "wlMe" est true
					array_unshift($wlInscrits, $q["name"]); // Si on est en liste d'attente on met en évidence son inscription et on permet de se désinscrire
					$wlMe = true;
				} else {
					array_push($wlInscrits, $q["name"]);
				}
			}

			$kId++;
			$card = [
				"class" => $cardCssDisplay . " " . $dayFr . "" . $iHuman,
				"jour" => $dayFr,
				"dateJour" => $kp[3],
				"mois" => $monthHuman[$kp2Trim],
				"annee" => $kp[1],
				"heureDebut" => $iHuman,
				"heureFin" => $iHuman,
				"animateur" => $arrayAnimateur[$dayFr],
				"listInscrits" => $listInscrits,
				"listAttenteInscrits" => $wlInscrits,
				"participantsMax" => $participantsMax,
				"dateXmlQuery" => $dateXmlQuery,
				"urlPrefix" => $urlIdentity . $listFiltersInURL,
				"inscMe" => $inscMe,
				"wlMe" => $wlMe,
			];

			array_push($listCards, $card);
		}
	}
}
$kCount = $kId - 1; // On calcule le nombre de séances

$smarty->assign("listFiltersInCSS", $listFiltersInCSS);
$smarty->assign("GP_name", $GP_name);
$smarty->assign("GP_email", $GP_email);

$smarty->assign("baseURL", $baseURL);
$smarty->assign("myURL", $myURL);
$smarty->assign("isAdmin", $isAdmin);

$smarty->assign("dateDebloquante", $dateDebloquante);
$smarty->assign("unlockStyle", $unlockStyle);
$smarty->assign("dateDisplay", $listCards);

$smarty->assign("kCount", $kCount);
$smarty->assign("dateLimitMonthHuman", $dateLimitMonthHuman);
$smarty->assign("listFilters", $listFilters);

$smarty->assign("listFiltersI", $listFiltersI);

$smarty->display("index.tpl");

?>