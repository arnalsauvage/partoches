<?php
include_once ("lib/utilssi.php");
include ("menu.php");

$nomTable = "utilisateur";
$utilisteurListe = "utilisateur_liste.php";

// include_once ("params.php");
$sortie = envoieHead ( "Menu", "../css/index.css" );
$table = "utilisateur";
entreBalise ( "Utilisateurs", "H1" );

$fimage = "/utilisateur/" . $_POST ['fimage'];
// Un non-admin ne peut changer ses privilèges
if ($_SESSION ['privilege'] < 3) {
	$fprivilege = $_SESSION ['privilege'];
}

// On gère 3 cas : création d'utilisateur, modif ou suppressions
if ($mode == "MAJ") {
	modifieUtilisateur ( $id, $flogin, $fmdp, $fprenom, $fnom, $fimage, $fsite, $femail, $fsignature, $fdateDernierLogin, $fnbreLogins, $fprivilege );
}
// Gestion de la demande de suppression
if ($id && $mode == "SUPPR") {
	if ($_SESSION ['privilege'] > 2) {
		supprimeUtilisateur ( $idUtilisateur );
	}
}
if ($mode == "INS") {
	// Entrer une nouvelle fiche utilisateur
	creeUtilisateur ( '$flogin', '$fmdp', '$fprenom', '$fnom', '$fimage', '$fsite', '$femail', '$fsignature', '$fprivilege' );
}
	redirection ( $utilisteurListe );
?>