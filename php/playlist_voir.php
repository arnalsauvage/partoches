<?php
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("playlist.php");
require_once("lienChansonPlaylist.php");
require_once("document.php");
require_once("utilisateur.php");
$table = "playlist";
$sortie = "";
$monImage = "";

if (isset($_GET ['id']) && is_numeric($_GET ['id'])) {
    $idPlaylist = $_GET ['id'];
} else {
    echo "erreur #1 dans playlis_voir.php";
    return;
}

// On augmente le compteur de vues du playlist
augmenteHits($table, $idPlaylist);

// On récupère les fichiers du Playlist
$fichiersDuPlaylist = fichiersPlaylist($idPlaylist);

//On cherche une image pour illustrer la playlist parmi les images dispos
foreach ($fichiersDuPlaylist as $fichier) {
//	echo $fichier [0] . " " . $fichier [1] . " " . $fichier [2] . " <br>";
    if (stristr($fichier [1], "jpg") || stristr($fichier [1], "png"))
        $monImage = $fichier;
}

// On charge le tableau des utilisateurs
$tabUsers = portraitDesUtilisateurs();

$donnee = chercheplaylist($idPlaylist);
$sortie .= "<h2>$donnee[1]</h2>"; // Titre

if ($_SESSION ['privilege'] > 1)
    $sortie .= Ancre($playlistForm . "?id=" . $idPlaylist, Image(($cheminImages . $iconeEdit), 32, 32, "modifier"));

if ("" != $monImage) {
    $sortie .= Image($monImage [0] . $monImage [1], 200, "", "pochette");
}
$sortie .= $donnee [2] . "-" . $donnee [3] . "-" . $donnee [5] . " hit(s)<br>\n";

foreach ($fichiersDuPlaylist as $fichier) {
    $icone = Image("../images/icones/" . $fichier [2] . ".png", 32, 32, "icone");
    if (!file_exists("../images/icones/" . $fichier [2] . ".png"))
        $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
    $sortie .= "$icone <a href= '" . htmlentities($fichier [0] . $fichier [1]) . "' target='_blank'> " . htmlentities($fichier[1]) . "</a> <br>\n";
}

$sortie .= "<h2>Liste des documents dans cette playlist</h2>";

// TODO : afficher une vignette de chaque chanson relative au document

$lignes = chercheLiensChansonPlaylist('idPlaylist', $idPlaylist, "ordre", true);
$listeDocs = "";
while ($ligne = $lignes->fetch_row()) {
    $ligneDoc = chercheDocument($ligne [1]);
    $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $fichier = "../".$_DOSSIER_CHANSONS" . $ligneDoc [6] . "/" . composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $extension = substr(strrchr($ligneDoc [1], '.'), 1);
    $icone = Image("../images/icones/" . $extension . ".png", 32, 32, "icone");

    if (!file_exists("../images/icones/" . $extension . ".png"))
        $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
    $vignetteChanson = Image("../".$_DOSSIER_CHANSONS" . $ligneDoc[6] . "/" . imageTableId("chanson", $ligneDoc [6]), 64, 64, "chanson");
    $vignettePublicateur = Image("../images" . $tabUsers[$ligneDoc [7]][1], 48, 48, $tabUsers[$ligneDoc [7]][0]);
    $sortie .= $vignettePublicateur . $vignetteChanson . $icone;
    $sortie .= "<a href= 'getdoc.php?doc=" . $ligneDoc [0] . "' target='_blank'> " . htmlentities($fichierCourt) . "</a> <br>\n";
}

$sortie .= envoieFooter();
echo $sortie;
// TODO ajouter un bouton : supprimer fichiers
