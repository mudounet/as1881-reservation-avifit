<?php


define('JOURS_FR' ,[
	"dimanche" => 0,
	"lundi" => 1,
	"mardi" => 2,
	"mercredi" => 3,
	"jeudi" => 4,
	"vendredi" => 5,
	"samedi" => 6,
]);

define('LAST_EXEC_FILE', 'data/last_exec.txt');
define('SQLITE_DB_PATH', 'data/database.sqlite');

define('TIMEZONE', 'Europe/Paris');
define('DAYS_FUTURE', 15);
define('DAYS_PAST', 5);
// Type d'évènements
define('CATEGORIES' ,[
	"CAT_AFT" => "Avifit",
	"CAT_TNK" => "Tank à ramer",
	"CAT_PRO" => "Séances compétition",
	"CAT_AVR_LSR" => "Séances aviron loisirs",
	"CAT_CMT" => "Réunions du comité",
	"CAT_ORG" => "Réunions d'organisations",
	"CAT_EVT_CLB" => "Évènements du club",
	"CAT_EVT_EXT" => "Évènements extérieurs au club",
	"CAT_ERGOPLUS" => "Ergomètre sportif",
]);


date_default_timezone_set(TIMEZONE); // On définit le décalage horaire pour les calculs avec PHP sur notre fuseau horaire
libxml_use_internal_errors(true); //  libxml functionality it is possible to suppress all XML errors when loading the document and then iterate over the errors. 

?>