<?php

session_start();
if (session_status() === PHP_SESSION_NONE) {
	echo "Marche pas";
	exit();
}

require_once 'includes/globals.php';
require_once 'includes/setup_smarty.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';
require_once 'includes/tense_activity.php';

$maintenance = false;

if (isset($_SESSION["logged_user"])) {
	// Session is active
	if (isset($_GET["logout"])) {
		@session_destroy();
		header("Refresh:0; url=index.php");
	}
} else {
	try {
		$GP_name = getByPostOrGet('name', null);
		$GP_email = getByPostOrGet('email', null);
		
		# Vérification de la validité de la personne identifiée
		$user = login($database, $GP_name, $GP_email);
		if ($user) $_SESSION["logged_user"] = $user;
	} catch (Exception $ex) {
		// jump to this part
		// if an exception occurred
	}
}

if ($maintenance && !(isset($_SESSION['logged_user']['is_admin']) && $_SESSION['logged_user']['is_admin'] == true)) {
	$smarty->display("maintenance.tpl");
	exit;
}

//---------------------- Paramètres	 /!\ Important
$fmtDateComplete = new IntlDateFormatter( "fr_FR" ,IntlDateFormatter::FULL, IntlDateFormatter::FULL, TIMEZONE,IntlDateFormatter::GREGORIAN,'eeee dd MMMM yyyy à HH:mm');

/////////////////////////////////////////////////////////////////////////
// Lancement du code automatisé toutes les demis journées
/////////////////////////////////////////////////////////////////////////
$current_time = time();
$last_exec = file_get_contents(LAST_EXEC_FILE);
// Check if the current time is greater than the last time the script was executed
if ($last_exec === false || $current_time > (int)$last_exec + 43200) {
	require_once 'includes/events_mgt.php';
	file_put_contents(LAST_EXEC_FILE, $current_time);
}

$protocol = empty($_SERVER["HTTPS"]) ? "http" : "https";
$domain = $_SERVER["SERVER_NAME"];
$port = $_SERVER["SERVER_PORT"];
$disp_port = ($protocol == "http" && $port == 80)
	|| ($protocol == "https" && $port == 443) ? "" : ":$port";

$script_name = preg_replace('/^'.preg_quote($_SERVER["DOCUMENT_ROOT"], '/').'/', "", __FILE__);
$script_name = str_replace('\\', '/', $script_name);

// put em all together to get the complete base URL
$server_url = "{$protocol}://{$domain}{$disp_port}";

//---------------------- Chargement de la BDD, toutes les requetes peuvent utiliser cette variable pour charger la bdd
$database = connectToDatabase();

//---------------------- Inscription, Désinscription et Waiting List
// On vérifie si on a des données en POST ou en GET


//---------------------- Gestion des filtres
$listFilters = []; // On prépare un array pour stocker les statuts des filtres
$quickFilterList = []; // on utilise l'index pour identifier rapidement les filtres qui sont actifs lors du filtrage des évènements

$usedCategories = getUsedCategories($database, $current_time);
foreach ($usedCategories as $category) {
	$filtreActif = getByPostOrGet('c'.$category['id'], '') == 'on' ? 1 : 0;
	if($filtreActif) array_push($quickFilterList, $category['id']);
	
	$filter = [
		"categorie" => 'c'.$category['id'],
		"actif" => $filtreActif,
		"text" => $category['description'],
		];
	array_push($listFilters, $filter);
}

$selected_events = _getWithQuery($database, 'SELECT e.*, c.textual_id as categorie FROM events e JOIN categories c ON c.id = e.category_id WHERE ts_sxb_end >= '.$current_time.' AND category_id NOT IN ('.implode(',', $quickFilterList).') ORDER BY ts_sxb_start', False);
$fmt = new IntlDateFormatter( "fr_FR" ,IntlDateFormatter::FULL, IntlDateFormatter::FULL, TIMEZONE,IntlDateFormatter::GREGORIAN,'yyyy-MM-dd-HH-mm-eeee-MMMM');
$listCards = [];

$stmt_event_subscription = $database->prepare('SELECT	u.*, s.is_waiting_list FROM users u, subscriptions s WHERE s.event_id = :event_id and u.id = s.user_id ORDER BY s.timestamp', [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
foreach ($selected_events as $event) {
	// Boucle qui passera chaque évènement en revue
	if ($event['disactivation_text'] == 'HIDDEN') { continue; }
	$event_start = (int)$event['ts_sxb_start'];
	$cardId = $event['id'];
	$event_min_places = $event['places_min'] > 0 ? (int)$event['places_min'] : null;
	$event_max_places = $event['places_max'] > 0 ? (int)$event['places_max'] : null;
	
	list($start_year, $start_month, $start_day, $start_hour, $start_minutes, $start_weekday, $start_monthName) = explode("-", $fmt->format($event_start));
	list(,,, $end_hour, $end_minutes,,) = explode("-", $fmt->format((int)$event['ts_sxb_end']));
	
	$card = [];
	$card["jourFR"] = $start_weekday;
	$card["heureDebut"] = $start_hour.'h'.$start_minutes;
	$card["heureFin"] = $end_hour.'h'.$end_minutes;
	$card["dateJour"] = $start_day;
	$card["moisFR"] = $start_monthName;
	$card["annee"] = $start_year;
	$card["mois"] = $start_month;
	
	if ($event['description'] && $event['description'] != "") $card["description"] = nl2br($event['description']);
	foreach( $event as $key => $value) { // On parcourt chaque attribut
		if (isset($key) && $key != '') $card[$key] = $value;
	}
	
	if ($event_min_places || $event_max_places) {
		// Gestion des inscrits
		$listInscrits = []; // On reset la liste des inscrit
		$wlInscrits = []; // On reset la waiting list au cas où
		$inscMe = false; // On remet à false le fait d'être inscrit
		$wlMe = false; // Gestion de la waiting list
		
		$event_end = (int)$event['ts_sxb_end'];
		$event_phase = determine_phase($current_time, $event_start, $event_end);
		compute_list($database, $event_phase, $event['id']);
		$event_availability = check_availability_event($database, $event['id']);
		
		
		$stmt_event_subscription->execute([':event_id' => $event['id']]);
		$subscriptions_list = $stmt_event_subscription->fetchAll();
		foreach ($subscriptions_list as $subscription) {
			$inscrit = array('name' => $subscription["display_name"], 'id' => $subscription["id"]);
			
			if (isset($_SESSION['logged_user']) && $subscription['id'] == $_SESSION['logged_user']['id']) {
				if($subscription["is_waiting_list"]) {
					array_unshift($wlInscrits, $inscrit); // Si on est en liste d'attente on met en évidence son inscription et on permet de se désinscrire
					$wlMe = true;
				} else {
					array_unshift($listInscrits, $inscrit); // Si on est inscrit on met en évidence son inscription et on permet de se désinscrire
					$inscMe = true;
				}
			} else {
				if($subscription["is_waiting_list"]) {
					array_push($wlInscrits, $inscrit);
				} else {
					array_push($listInscrits, $inscrit);
				}
			}
		}
		
		$card["listInscrits"] = $listInscrits;
		$card["listAttenteInscrits"] = $wlInscrits;
		$card["inscMe"] = $inscMe;
		$card["wlMe"] = $wlMe;
		$card["event_full"] = !$event_availability && !($event_phase === TenseActivity::AUTO_ATTRIBUTION || $event_phase === TenseActivity::CANDIDATURE_FILING);
		if ($event_phase === TenseActivity::CANDIDATURE_FILING) $card["opening_date"] = human_timediff($current_time + AUTO_ATTRIBUTION_THRESHOLD_HOURS, $event_start);
	}

	$card["cardId"] = $cardId;

	array_push($listCards, $card);
}

if (isset($_SESSION['logged_user'])) {
	$smarty->assign("GP_name", $_SESSION['logged_user']['display_name']);
	$smarty->assign("GP_email", $_SESSION['logged_user']['email']);
	$smarty->assign("isAdmin", $_SESSION['logged_user']['is_admin']);
	$smarty->assign("loginURL", "?name={$_SESSION['logged_user']['display_name']}&email={$_SESSION['logged_user']['email']}"); // URL sans les filtres et avec l'identifiant eventuel
} else {
	$smarty->assign("GP_name", null);
	$smarty->assign("GP_email", null);
	$smarty->assign("isAdmin", false);
	$smarty->assign("loginURL", "?anonymous");
}

$smarty->assign("serverURL", $server_url);

$smarty->assign("todayDateFr", $fmtDateComplete->format($current_time));

$smarty->assign("dateDisplay", $listCards);

$smarty->assign("listFilters", $listFilters);
$smarty->assign("listCategories", getAllCategories($database, $current_time));

$smarty->display("bootstrap.tpl");

?>