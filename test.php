<?php

session_start();

if (session_status() === PHP_SESSION_NONE) {
    echo "Marche pas";
} else {
	echo "OK";
	$_SESSION["hello"] = 22;
	
}

?>