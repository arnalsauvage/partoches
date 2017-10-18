<?php
include_once("lib/utilssi.php");
include ("menu.php");
include ("chanson.php");

$nomTable = "chanson";

$fnom = $_SESSION ['mysql']->real_escape_string($fnom);
$finterprete = $_SESSION ['mysql']->real_escape_string($finterprete);
$fannee = $_SESSION ['mysql']->real_escape_string($fannee);

// On gère 3 cas :  création d'une chanson, modif ou suppression
  
if($mode=="MAJ"){
	modifieChanson($id, $fnom, $finterprete, $fannee);
}
  
// Gestion de la demande de suppression
if($id && $mode == "SUPPR" && $_SESSION['privilege'] > 1 ){
		supprimeChanson($id);
	}

if($mode=="INS"){
	creeChanson( $fnom, $finterprete, $fannee);
}
redirection ($nomTable . "_liste.php");
?>