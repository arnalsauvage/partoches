<?php
include_once ("lib/utilssi.php");
include_once("menu.php");
include_once("songbook.php");
include_once("lienDocSongbook.php");
include_once("document.php");
include_once("utilisateur.php");
$table = "songbook";
$sortie = "";
$monImage = "";

// On augmente le compteur de vues du songbook
augmenteHits($table, $_GET ['id']);

// On récupère les fichiers du Songbook
$fichiersDuSongbook = fichiersSongbook($_GET ['id']);

//On cherche une image pour illustrer la songbook parmi les images dispos
// foreach ($fichiersDuSongbook as $fichier) {
// //	echo $fichier [0] . " " . $fichier [1] . " " . $fichier [2] . " <br>";
// 	if (stristr ( $fichier [1], "jpg" ) || stristr ( $fichier [1], "png" ))
// 		$monImage = $fichier;
// }

// On choisit une des images du songbook
$monImage = imageTableId("songbook", $_GET ['id']);

// On charge le tableau des utilisateurs
$tabUsers = portraitDesUtilisateurs();

$donnee = cherchesongbook ( $_GET ['id'] );
$sortie .= "<h2>$donnee[1]</h2>"; // Titre

if ($_SESSION ['privilege'] > 1)
	$sortie .= Ancre ( $songbookForm . "?id=" . $_GET ['id'], Image ( ($cheminImages . $iconeEdit), 32, 32, "modifier" ) );

if ("" != $monImage) {
	$repertoire = "../data/songbooks/$id/";
	$sortie .= Image ( $repertoire . $monImage, 200, "", "pochette" );
}

$sortie .= $donnee [2] . "-" . $donnee [3] ."-". $donnee [5] . " hit(s)<br>\n";

foreach ($fichiersDuSongbook as $fichier) {
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
	$extension = substr(strrchr($ligneDoc [1], '.'), 1);
	$icone = Image("../images/icones/" . $extension . ".png", 32, 32, "icone");

	if (!file_exists("../images/icones/" . $extension . ".png"))
		$icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );
	$vignetteChanson = Image("../data/chansons/" . $ligneDoc[6] . "/" . imageTableId("chanson", $ligneDoc [6]), 64, 64, "chanson");
	$vignettePublicateur = Image("../images" . $tabUsers[$ligneDoc [7]][1], 48, 48, $tabUsers[$ligneDoc [7]][0]);
	$sortie .= $vignettePublicateur . $vignetteChanson . $icone;
	$sortie .= "<a href= 'getdoc.php?doc=" . $ligneDoc [0] . "' target='_blank'> " . htmlentities($fichierCourt) . "</a> <br>\n";
}

$sortie .= envoieFooter ( "Bienvenue chez nous !" );
echo $sortie;
?>