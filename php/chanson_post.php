<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("document.php");
include_once ("chanson.php");

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
	$fidUser = $_POST ['fidUser'];
	$ftempo = $_POST ['ftempo'];
	$fmesure = $_POST ['fmesure'];
	$fpulsation = $_POST ['fpulsation'];
	$fhits = $_POST ['fhits'];
	$ftonalite = $_POST ['ftonalite'];
	$mode = $_POST ['mode'];
}

// On gère 4 cas : création d'une chanson, modif, suppression chanson ou suppression d'un doc de la chanson
if ($mode == "MAJ") {
	if ($_SESSION ['privilege'] < 3) {
		// On doit recharger les hits et la date pour qu'ils ne soient remis à zéro
		$chanson = chercheChanson($id);
		$fhits = $chanson[9];
		$fdate = dateMysqlVersTexte($chanson[7]);
	}
	modifieChanson($id, $fnom, $finterprete, $fannee, $fidUser, $ftempo, $fmesure, $fpulsation, $fhits, $ftonalite);
}

// Gestion de la demande de suppression
if ($id && $mode == "SUPPR" && $_SESSION ['privilege'] > 1) {
	supprimeChanson ( $id );
}

if ($mode == "INS") {
    $id = creeChanson($fnom, $finterprete, $fannee, $fidUser, $ftempo, $fmesure, $fpulsation, $fhits, $ftonalite);
}

// Gestion de la demande de suppression de document dans la chanson
if ($mode == "SUPPRDOC" && $_SESSION ['privilege'] > 1) {
	// 	echo "Appel avec mode = $mode, id = $id, idDoc = " . $_GET ['idDoc'] . " idSongbook = " . $_GET ['idSongbook'];
	supprimeDocument ( $_GET ['idDoc']);
}

// Gestion de la demande de suppression de fichier dans la chanson
if ($mode == "SUPPRFIC" && $_SESSION ['privilege'] > 1) {
    // echo "Appel avec mode = $mode, id = $id, nomFic = " . $_GET ['nomFic'];
    unlink($_GET['nomFic']);
}
redirection($nomTable . "_form.php?id=$id");
