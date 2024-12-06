<?php

@session_start();

require_once 'includes/setup_smarty.php';
require_once 'includes/database.php';

$is_admin = (isset($_SESSION['logged_user']['is_admin']) && $_SESSION['logged_user']['is_admin'] == true) ? true : false;
$connected_id = (isset($_SESSION['logged_user']['id']) && $_SESSION['logged_user']['id']) ? $_SESSION['logged_user']['id'] : null;

function calc_scale($min, $average, $max) {
	$scale_A = ($average - $min)/3;
	$scale_B = ($max - $average)/3;
	$scale[0] = $min + $scale_A;
	$scale[1] = $min + 2*$scale_A;
	$scale[2] = $average + $scale_B;
	$scale[3] = $average + 2*$scale_B;
	return $scale;	
}

function get_scaled_resascore($scaled_value, $scale) {
	if ($scaled_value <= $scale[0]) return 'a';
	if ($scaled_value <= $scale[1]) return 'b';
	if ($scaled_value <= $scale[2]) return 'c';
	if ($scaled_value <= $scale[3]) return 'd';
	return 'e';
}

function interpolateColor($argument) {
    // Ensure the argument is within the valid range (0 to 10)
    $argument = max(0, min(10, $argument));

    // Split the color values into individual components
    list($r1, $g1, $b1) = sscanf('#E55451', "#%02x%02x%02x");
    list($r2, $g2, $b2) = sscanf('#3EA055', "#%02x%02x%02x");

    // Calculate the interpolated color components
    $r = $r1 + ($r2 - $r1) * ($argument / 10);
    $g = $g1 + ($g2 - $g1) * ($argument / 10);
    $b = $b1 + ($b2 - $b1) * ($argument / 10);

    // Format the interpolated color as a hexadecimal string
    $interpolatedColor = "#" . sprintf("%02x%02x%02x", $r, $g, $b);

    return $interpolatedColor;
}

if(isset($_GET['id'])) {
	$event_id = $_GET['id'];
} elseif (isset($_GET['event_id'])) {
	$event_id = $_GET['event_id'];
} else {
	echo "undefined event";
	exit();
}

//---------------------- Chargement de la BDD, toutes les requetes peuvent utiliser cette variable pour charger la bdd
$database = connectToDatabase();

$results = _getWithQuery($database, 'SELECT U.*, S.timestamp, s.is_waiting_list as wl, total FROM user_subscription_stats AS U, subscriptions AS S WHERE S.event_id = '.$event_id.' AND U.id = S.user_id ORDER BY timestamp', False);

$total = _getWithQuery($database, 'SELECT count(*) AS total from events_last_stats', true);
$users = [];

$stats = _getWithQuery($database, 'SELECT count(id) as distinct_ids, min(total) as min, max(total) as max, sum(total) as sum, avg(total) as average FROM user_subscription_stats', True);

$scale = calc_scale($stats['min'], $stats['average'], $stats['max']);

foreach ($results as $result) {
	$anonymise = (!$is_admin) && $result["id"] != $connected_id;
	$users[] = array("anonymise" => $anonymise, "subscription_date" => $result["timestamp"], "waiting_list" => (bool)$result["wl"], "id" => $result["id"],  "display_name" => $result["display_name"], "total" => $result["total"], "score" => $result["total"], "resascore" => get_scaled_resascore($result["total"], $scale));
}

$smarty->assign("users", $users);
$smarty->assign('qty_seances', $total['total']);

$smarty->display("bootstrap-evenement-stats.tpl");

?>