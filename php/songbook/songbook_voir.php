<?php
const IMAGES_ICONES = "../../images/icones/";
const RACINE = "../../";
const VIGNETTES = "../../vignettes/";
require_once("../lib/utilssi.php");

require_once("songbook.php");

require_once ("../chanson/chansonListe.php");
require_once("../document/document.php");
require_once("../liens/lienDocSongbook.php");
require_once("../navigation/menu.php");
require_once("../utilisateur/utilisateur.php");

global $songbookForm;
global $cheminImages;
global $iconeEdit;
global $_DOSSIER_CHANSONS;

$table = "songbook";
$sortie = "";
$monImage = "";

if (isset($_GET ['id']) && is_numeric($_GET ['id'])) {
    $idSongbook = $_GET ['id'];
} else {
    echo "erreur #1 dans songbook_voir.php";
    return;
}

// On augmente le compteur de vues du songbook
augmenteHits($table, $idSongbook);

// On choisit une des images du songbook
$monImage = imageTableId("songbook", $idSongbook);

// On charge le tableau des utilisateurs
$tabUsers = portraitDesUtilisateurs();

// On charge la liste des chansons
$listeChansons = new ChansonListe();
$listeChansons->chargeListeChansons();

$donnee = chercheSongbook($idSongbook);
$sortie .= "<h2>$donnee[1]</h2>"; // Titre

if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    $sortie .= ancre($songbookForm . "?id=" . $idSongbook, image(($cheminImages . $iconeEdit), 32, 32, "modifier"));
}

if ("" != $monImage) {
    $repertoire = "../data/songbooks/" . $idSongbook . "/";
    $sortie .= image($repertoire . $monImage, 200, "", "pochette");
}

$sortie .= $donnee [2] . "-" . $donnee [3] . "-" . $donnee [5] . " hit(s)<br>\n";

$sortie .= "<h2>Liste des fichiers rattachés à ce songbook</h2>";

// On récupère les fichiers du Songbook
$fichiersDuSongbook = fichiersSongbook($idSongbook);

foreach ($fichiersDuSongbook as $fichier) {
    $icone = image(IMAGES_ICONES . $fichier [2] . ".png", 32, 32, "icone");
    if (!file_exists(IMAGES_ICONES . $fichier [2] . ".png")) {
        $icone = image(IMAGES_ICONES . "fichier.png", 32, 32, "icone");
    }
    $sortie .= "$icone <a href= '" . htmlentities($fichier [0] . $fichier [1]) . "' target='_blank'> " . htmlentities($fichier[1]) . "</a> ";
    $sortie .= intval(filesize(($fichier [0] . $fichier [1])) / 1024) . " (ko) <br>\n";
}

$sortie .= "<h2>Liste des documents dans ce songbook</h2>";

$lignes = chercheLiensDocSongbook('idSongbook', $idSongbook, "ordre");
$listeDocs = "";
$nbChansons = 0;
while ($ligne = $lignes->fetch_row()) {
    $ligneDoc = chercheDocument($ligne [1]);
    $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $maChanson = $listeChansons->recupereChanson($ligneDoc[6]);
    $idPublicateurChanson = $maChanson->getIdUser();
    $lienChanson = "../chanson/chanson_voir.php?id=" . $ligneDoc [6];
    $fichier = RACINE .$_DOSSIER_CHANSONS. "/" . $ligneDoc [6] . "/" . composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $extension = substr(strrchr($ligneDoc [1], '.'), 1);
    $icone = image(IMAGES_ICONES . $extension . ".png", 32, 32, "icone");

    if (!file_exists(IMAGES_ICONES . $extension . ".png")) {
        $icone = image("../images/icones/fichier.png", 32, 32, "icone");
    }
    $vignetteChanson = image(RACINE .$_DOSSIER_CHANSONS. "/" . $ligneDoc[6] . "/" . imageTableId("chanson", $ligneDoc [6]), 64, 64, "chanson");
    $vignetteChanson = ancre($lienChanson,$vignetteChanson);
    $vignettePublicateur = image(VIGNETTES . $tabUsers[$idPublicateurChanson][1], 48, 48, $tabUsers[$idPublicateurChanson][0]);
    $sortie .= $vignettePublicateur . $vignetteChanson . $icone;
    $sortie .= "<a href= '" . $fichier . "' target='_blank'> " . htmlentities($fichierCourt) . "</a>";
    $sortie .= " (" . intval($ligneDoc[2] / 1024) . " ko)";
    // echo "chanson " . $ligneDoc[6];
    if ($maChanson != null) {
        $sortie .= " - " . $maChanson->getNom() . " - " . $maChanson->getTonalite() . " - " . $maChanson->getAnnee() . " - " . $maChanson->getTempo() . " bpm";
    }
    $sortie .= " <br>\n";
    $nbChansons++;
}
$sortie .= "$nbChansons chansons dans le songbook !";
$sortie .= envoieFooter();
echo $sortie;
