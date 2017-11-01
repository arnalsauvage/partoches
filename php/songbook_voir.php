<?php
include_once ("lib/utilssi.php");
include_once("menu.php");
include_once("songbook.php");
include_once("lienDocSongbook.php");
include_once("document.php");
$table = "songbook";
$sortie = "";
$monImage = "";

$retour = fichiersSongbook ( $_GET ['id'] );

//On cherche une imag epour illustrer la songbook parmi les images dispos
foreach ( $retour as $fichier ) {
//	echo $fichier [0] . " " . $fichier [1] . " " . $fichier [2] . " <br>";
	if (stristr ( $fichier [1], "jpg" ) || stristr ( $fichier [1], "png" ))
		$monImage = $fichier;
}

$donnee = cherchesongbook ( $_GET ['id'] );
$sortie .= "<h2>$donnee[1]</h2>"; // Titre

if ($_SESSION ['privilege'] > 1)
	$sortie .= Ancre ( $songbookForm . "?id=" . $_GET ['id'], Image ( ($cheminImages . $iconeEdit), 32, 32, "modifier" ) );

if ("" != $monImage) {
	$sortie .= Image ( $monImage [0] . $monImage [1], 200, "", "pochette" );
}
$sortie .= $donnee [2] . "-" . $donnee [3] ."-". $donnee [5] . " hit(s)<br>\n";

foreach ( $retour as $fichier ) {
	$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
	if (! file_exists (  "../images/icones/" . $fichier [2] . ".png"))
		$icone = Image ( "../images/icones/fichier.png" , 32, 32, "icone" );
	$sortie .= "$icone <a href= '" . htmlentities($fichier [0] . $fichier [1]) . "' target='_blank'> " . htmlentities($fichier[1]) . "</a> <br>\n";
}

$sortie .= "<h2>Liste des documents dans ce songbook</h2>";

$lignes = chercheLiensDocSongbook ( 'idSongbook', $_GET ['id'], "ordre", true );
$listeDocs = "";
while ( $ligne = $lignes->fetch_row () ) {
	$ligneDoc = chercheDocument ( $ligne [1] );
	$fichierCourt = composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
	$fichier = "../data/chansons/" .$ligneDoc [6]. "/" . composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
	$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
	if (! file_exists ( "../images/icones/" . $fichier [2] . ".png" ))
		$icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );
		$sortie .= "<a href= '" . htmlentities ( $fichier ) . "' target='_blank'> " . htmlentities ( $fichierCourt ) . "</a> <br>\n";
}

$sortie .= envoieFooter ( "Bienvenue chez nous !" );
echo $sortie;
// TODO ajouter un bouton : supprimer fichiers
?>