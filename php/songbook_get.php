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
	$id = $_POST ['id'];
	$fnom = $_SESSION ['mysql']->real_escape_string ( $_POST ['fnom'] );
	$description = $_SESSION ['mysql']->real_escape_string($_POST ['fdescription']);
	$fimage = $_POST ['fimage'];
	// Seul admin peut modifier hits et date
	if ($_SESSION ['privilege'] > 2) {
		$fdate = $_POST ['fdate'];
		$fhits = $_POST ['fhits'];
	}
}

// On récupère l'identifiant du songbook passé par POST ou GET

if (isset ($_GET ['id'])) {
	$id = $_GET ['id'];
}

if (isset ($_POST ['id'])) {
	$id = $_POST ['id'];
}

// Cas de la mise à jour
if ($mode == "MAJ") {
	if ($_SESSION ['privilege'] < 3) {
		// On récupère les valeurs de hits et date en base, car ils ne sont pas dans le formulaire
		$songbook = cherchesongbook($id);
		$fhits = $songbook[5];
		$fdate = dateMysqlVersTexte($songbook[3]);
	}
	modifiesongbook($id, $fnom, $fdescription, $fdate, $fimage, $fhits);
}

// Cas de l'ajout d'un Songbook
if ($mode == "INS") {
	$fhits = 0;
	$fdate = date("d/m/Y");
	creesongbook ( $fnom, $description, $fdate, $fimage, $fhits );
}

// Gestion de la demande de suppression
if ($id && ($mode == "SUPPR")) {
	supprimesongbook ( $id );
}

// Gestion de la demande de suppression de document dans le songbook
if ($mode == "SUPPRDOC" && $_SESSION ['privilege'] > 1) {
	// echo "Appel avec mode = $mode, id = $id, idDoc = " . $_GET ['idDoc'] . " idSongbook = " . $_GET ['idSongbook'];
	supprimeLienIdDocIdSongbook ( $_GET ['idDoc'], $_GET ['idSongbook'] );
}

redirection ( $nomTable . "_liste.php" );
?>