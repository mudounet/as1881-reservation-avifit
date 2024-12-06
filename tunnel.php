<?php

@session_start();

require_once 'includes/setup_smarty.php';
require_once 'includes/database.php';

$action = $_GET['act'];
$main_message = null;
$list_conditions = [];

try {
	$GP_eventID = $_GET['id'];
	if (!preg_match('/^(\d+)$/', $GP_eventID, $matches)) throw new Exception("'$GP_eventID' Pas valide" );
	$event = getEvent($database, $GP_eventID); // On interroge uniquement la base de donnée pour l'identifiant

	$event_min_places = $event['places_min'] > 0 ? (int)$event['places_min'] : null;
	$event_max_places = $event['places_max'] > 0 ? (int)$event['places_max'] : null;
	$is_tense_event = (bool) $event['tense_activity']; // Seules les sessions d'avifit sont des évènements spéciaux
} catch (Exception $e) {
	http_response_code(400); // Set the HTTP status code to 400 Bad Request
	echo 'id invalide : '.$e->getMessage();
	exit;
}


switch ($action) {
    case 'add':
        $main_message = "Conditions d'utilisation";
		
		$list_conditions[] = "contrôler que l'évènement n'a pas été annulé en dernière minute";
		if ($is_tense_event) $list_conditions[] = "accepter l'inscription automatique jusqu'à 24h à l'avance";
		$list_conditions[] = "se désinscrire dès qu'il y a un risque d'indisponibilité";
		if ($is_tense_event) $list_conditions[] = "être présent⸱e à la séance, sous peine de sanction";
		$button_message = "J'ai compris, m'inscrire";
        break;
    case 'remove':
        $main_message = "Vous vous engagez à";
		if ($is_tense_event) {
			$list_conditions[] = "prévenir le groupe WhatsApp dédié à l'évènement";
		} else {
			$list_conditions[] = "prévenir l'organisateur, dans la mesure du possible";
		}
		$button_message = "J'ai compris, me désinscrire";
        break;
    default:
        echo "Opération non supportée. Signaler le problème à l'administrateur.";
        exit;
}

$smarty->assign("main_message", $main_message);
$smarty->assign("button_message", $button_message);
$smarty->assign('action', $action);
$smarty->assign('id', $_GET['id']);
$smarty->assign("list_conditions", $list_conditions);

$smarty->display("bootstrap-tunnel.tpl");

?>