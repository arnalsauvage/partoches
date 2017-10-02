<?php
include_once("lib/utilssi.php");
include ("menu.php");

$nomTable = "chanson";
$chansonForm = "chanson_form.php";
$chansonGet = "chanson_get.php";
$chansonVoir = "chanson_voir.php";
$chansonListe = "chanson_liste.php";

//include_once ("params.php");
$sortie = envoieHead("Menu", "../css/index.css");
$table = "chanson";
entreBalise("chansons","H1");

$fnom = addslashes($fnom);
$finterprete = addslashes($finterprete);
$fannee = addslashes($fannee);

// On gère 3 cas :  création d'chanson, modif ou suppression
  
if($mode=="MAJ"){
	$marequete = "UPDATE $nomTable SET nom='$fnom', interprete='$finterprete', annee='$fannee' WHERE id=$id";
	$resultat = ExecRequete ( $marequete, $idconnect);
	redirection ($chansonListe);
}
  
// Gestion de la demande de suppression
if($id && $mode == "SUPPR"){
	if($_SESSION['privilege']>1){
		$marequete = "DELETE FROM $nomTable WHERE id = '$id'";
		$resultat = ExecRequete ( $marequete, $idconnect);
		redirection ($chansonListe); 
	}
}

if($mode=="INS"){
	// Entrer une nouvelle fiche chanson
	$marequete = "INSERT INTO $nomTable 
	(interprete, nom, annee)
	VALUES
	('$finterprete', '$fnom', '$fannee')";
	$resultat = ExecRequete ( $marequete, $idconnect);
	// echo "<P> Lancement de la requête : $marequete </p>";
	$numligne = 0;
	// echo "<p> Résultat : $resultat</p>";
	redirection($chansonListe);
}
?>