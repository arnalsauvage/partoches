<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("songbook.php");
include_once ("lienDocSongbook.php");
$nomTable = "songbook";

if ($_SESSION ['privilege'] <= 1)
	redirection ( $nomTable . "_liste.php" );
	
// On gère 4 cas : création d'une songbook, modif, suppression, ou suppression d'un docJoint

// En mode création ou mise à jour, on récupère les données du formulaire
if (($mode == "MAJ") || ($mode == "INS")) {
	$fnom = $_SESSION ['mysql']->real_escape_string ( $_POST ['fnom'] );
	$description = $_SESSION ['mysql']->real_escape_string ( $$_POST ['fdescription'] );
	$fdate = $_POST ['fdate'];
	$fimage = $_POST ['fimage'];
	$fhits = $_POST ['fhits'];
	$id = $_POST ['id'];
}

if (isset ( $_GET ['id'] )){
	$id = $_GET ['id'];
}

if (isset ( $_POST ['id'] )){
	$id = $_POST['id'];
}

if ($mode == "MAJ") {
	modifiesongbook ( $id, $fnom, $description, $fdate, $fimage, $fhits );
}

if ($mode == "INS") {
	creesongbook ( $fnom, $description, $fdate, $fimage, $fhits );
}

// Gestion de la demande de suppression
if ($id && $mode == "SUPPR" ) {
	supprimesongbook ( $id );
}

// Gestion de la demande de suppression de document dans le songbook
if ($mode == "SUPPRDOC" && $_SESSION ['privilege'] > 1) {
// 	echo "Appel avec mode = $mode, id = $id, idDoc = " . $_GET ['idDoc'] . " idSongbook = " . $_GET ['idSongbook'];
	supprimeLienIdDocIdSongbook ( $_GET ['idDoc'], $_GET ['idSongbook'] );
}

redirection ( $nomTable . "_liste.php" );
?>