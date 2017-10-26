<?php
include_once ("lib/utilssi.php");
include ("menu.php");
include ("chanson.php");

$nomTable = "chanson";

if ($_SESSION ['privilege'] <= 1)
	redirection ( $nomTable . "_liste.php" );

if ((isset ( $_GET ['id'] ))) {
	$id = $_GET ['id'];
	$mode = $_GET ['mode'];
}

if ((isset ( $_POST ['id'] ))) {
	$id = $_POST ['id'];
	$fnom = $_POST ['fnom'];
	$finterprete = $_POST ['finterprete'];
	$fannee = $_POST ['fannee'];
	$fidAuteur = $_POST ['fidAuteur'];
	$ftempo = $_POST ['ftempo'];
	$fmesure = $_POST ['fmesure'];
	$fpulsation = $_POST ['fpulsation'];
	$fhits = $_POST ['fhits'];
	$ftonalite = $_POST ['ftonalite'];
	$mode = $_POST ['mode'];
}

// On gère 3 cas : création d'une chanson, modif ou suppression
if ($mode == "MAJ") {
	modifieChanson ( $id, $fnom, $finterprete, $fannee, $fidAuteur, $ftempo, $fmesure, $fpulsation, $fhits, $ftonalite );
}

// Gestion de la demande de suppression
if ($id && $mode == "SUPPR" && $_SESSION ['privilege'] > 1) {
	supprimeChanson ( $id );
}

if ($mode == "INS") {
	creeChanson ( $fnom, $finterprete, $fannee, $fidAuteur, $ftempo, $fmesure, $fpulsation, $fhits, $ftonalite );
}
redirection ( $nomTable . "_liste.php" );
?>