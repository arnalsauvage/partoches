<?php
include_once ("lib/utilssi.php");
include_once("menu.php");
include_once("playlist.php");
include_once("lienChansonPlaylist.php");
include_once("document.php");
include_once("utilisateur.php");
$table = "playlist";
$sortie = "";
$monImage = "";

// On augmente le compteur de vues du playlist
augmenteHits($table, $_GET ['id']);

// On récupère les fichiers du Playlist
$fichiersDuPlaylist = fichiersPlaylist($_GET ['id']);

//On cherche une image pour illustrer la playlist parmi les images dispos
foreach ($fichiersDuPlaylist as $fichier) {
//	echo $fichier [0] . " " . $fichier [1] . " " . $fichier [2] . " <br>";
	if (stristr ( $fichier [1], "jpg" ) || stristr ( $fichier [1], "png" ))
		$monImage = $fichier;
}

// On charge le tableau des utilisateurs
$tabUsers = portraitDesUtilisateurs();

$donnee = chercheplaylist ( $_GET ['id'] );
$sortie .= "<h2>$donnee[1]</h2>"; // Titre

if ($_SESSION ['privilege'] > 1)
	$sortie .= Ancre ( $playlistForm . "?id=" . $_GET ['id'], Image ( ($cheminImages . $iconeEdit), 32, 32, "modifier" ) );

if ("" != $monImage) {
	$sortie .= Image ( $monImage [0] . $monImage [1], 200, "", "pochette" );
}
$sortie .= $donnee [2] . "-" . $donnee [3] ."-". $donnee [5] . " hit(s)<br>\n";

foreach ($fichiersDuPlaylist as $fichier) {
	$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
	if (! file_exists (  "../images/icones/" . $fichier [2] . ".png"))
		$icone = Image ( "../images/icones/fichier.png" , 32, 32, "icone" );
	$sortie .= "$icone <a href= '" . htmlentities($fichier [0] . $fichier [1]) . "' target='_blank'> " . htmlentities($fichier[1]) . "</a> <br>\n";
}

$sortie .= "<h2>Liste des documents dans cette playlist</h2>";

// TODO : afficher une vignette de chaque chanson relative au document

$lignes = chercheLiensChansonPlaylist ( 'idPlaylist', $_GET ['id'], "ordre", true );
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

$sortie .= envoieFooter ();
echo $sortie;
// TODO ajouter un bouton : supprimer fichiers
