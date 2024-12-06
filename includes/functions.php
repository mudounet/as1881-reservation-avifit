<?php

$arrayAdmin = [];
include 'data/admins.php';
require_once 'includes/database.php';

function login(PDO $database, $user, $email) {
	global $arrayAdmin;
	
	if ($user == "" || $email == "") return null;
	$email = strtolower($email);
	$user = getUser($database, $user, $email);
	
	if(!isset($user)) return null;
	
	update_last_access_user($database, $user['id']);
	
	$user['is_admin'] = (isset($user['is_admin'])) ? 1 : 0;
	
	// On détermine si la personne est admin
	foreach ($arrayAdmin as $k => $v) {
		if ($k == $user['display_name'] && $v == $user['email']) $user['is_admin'] = 1;
	}
		
	return $user;
}

function getByPostOrGet($property, $defaults) {
	if (isset($property) && array_key_exists((string)$property, $_GET)) return trim($_GET[$property]);
	if (isset($property) && array_key_exists((string)$property, $_POST)) return trim($_POST[$property]);
	return trim($defaults);
}

function traiterFileAttente($baseURL, $GP_eventID) {
	return false;
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

function http_error_code($reason, $error_code = 400) {
	http_response_code($error_code); // Set the HTTP status code to 400 Bad Request
	echo $reason; // Send back an error message in the response body
	exit;
}

function http_success_code($message) {
	http_response_code(200); // Opération réussie
	echo $message; // Send back an error message in the response body
	exit;
}

function generateTimeStamp($date, $time, $timezone) {
	return \DateTime::createFromFormat('Y-m-d H:i T', $date.' '.$time.' '.$timezone)->getTimestamp(); // Creation du timestamp en tenant compte de l'heure et du decalage horaire
}
?>