<?php
include_once ("lib/utilssi.php");
include ("menu.php");

$table = "chanson";
$sortie = "";

// Chargement des donnees de la chanson si l'identifiant est fourni
if (isset ( $_GET ['id'] ) && $_GET ['id'] != "") {
	$marequete = "select * from $table where id = '" . $_GET ['id'] . "'";
	$resultat = ExecRequete ( $marequete, $idconnect );
	$donnee = LigneSuivante ( $resultat );
	$mode = "MAJ";
} else {
	$mode = "INS";
	$donnee [0] = 0;
	$donnee [1] = "";
	$donnee [2] = "";
	$donnee [3] = "";
}

if ($mode == "MAJ")
	$sortie .= "<H1> Mise à jour - " . $table . "</H1>";
else
	$sortie .= "<H1> Création - " . $table . "</H1>";

// Création du formulaire
$f = new Formulaire ( "POST", $table . "_get.php", $sortie );
$sortie .= $f->debutTable ();

$f->champCache ( "id", $donnee [0] );
$f->champTexte ( "Nom :", "fnom", $donnee[1], 64, 32 );
$f->champTexte ( "Interprète :", "finterprete", $donnee[2], 64, 32 );
$f->champTexte ( "Annee :", "fannee", $donnee [3], 4, 4 );
$f->finTable ();
$f->champCache ( "mode", $mode );
$f->champValider ( "Valider la saisie", "valider" );
$sortie .= $f->fin();
$sortie .= envoieFooter ( "Bienvenue chez nous !" );
echo $sortie;
?>