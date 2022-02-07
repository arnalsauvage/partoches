<?php
const IMAGES_ICONES = "../images/icones/";
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("songbook.php");
require_once("lienDocSongbook.php");
require_once("document.php");
require_once("utilisateur.php");
require_once ("chansonListe.php");

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
    $sortie .= Ancre($songbookForm . "?id=" . $idSongbook, Image(($cheminImages . $iconeEdit), 32, 32, "modifier"));
}

if ("" != $monImage) {
    $repertoire = "../data/songbooks/" . $idSongbook . "/";
    $sortie .= Image($repertoire . $monImage, 200, "", "pochette");
}

$sortie .= $donnee [2] . "-" . $donnee [3] . "-" . $donnee [5] . " hit(s)<br>\n";

$sortie .= "<h2>Liste des fichiers rattachés à ce songbook</h2>";

// On récupère les fichiers du Songbook
$fichiersDuSongbook = fichiersSongbook($idSongbook);

foreach ($fichiersDuSongbook as $fichier) {
    $icone = Image(IMAGES_ICONES . $fichier [2] . ".png", 32, 32, "icone");
    if (!file_exists(IMAGES_ICONES . $fichier [2] . ".png")) {
        $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
    }
    $sortie .= "$icone <a href= '" . htmlentities($fichier [0] . $fichier [1]) . "' target='_blank'> " . htmlentities($fichier[1]) . "</a> ";
    $sortie .= intval(filesize(($fichier [0] . $fichier [1])) / 1024) . " (ko) <br>\n";
}

$sortie .= "<h2>Liste des documents dans ce songbook</h2>";

$lignes = chercheLiensDocSongbook('idSongbook', $idSongbook, "ordre");
$listeDocs = "";
while ($ligne = $lignes->fetch_row()) {
    $ligneDoc = chercheDocument($ligne [1]);
    $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $lienChanson = "./chanson_voir.php?id=" . $ligneDoc [6];
    $fichier = "../".$_DOSSIER_CHANSONS. "/" . $ligneDoc [6] . "/" . composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $extension = substr(strrchr($ligneDoc [1], '.'), 1);
    $icone = Image(IMAGES_ICONES . $extension . ".png", 32, 32, "icone");

    if (!file_exists(IMAGES_ICONES . $extension . ".png")) {
        $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
    }
    $vignetteChanson = Image("../".$_DOSSIER_CHANSONS. "/" . $ligneDoc[6] . "/" . imageTableId("chanson", $ligneDoc [6]), 64, 64, "chanson");
    $vignetteChanson = Ancre($lienChanson,$vignetteChanson);
    $vignettePublicateur = Image("../vignettes/" . $tabUsers[$ligneDoc [7]][1], 48, 48, $tabUsers[$ligneDoc [7]][0]);
    $sortie .= $vignettePublicateur . $vignetteChanson . $icone;
    $sortie .= "<a href= '" . $fichier . "' target='_blank'> " . htmlentities($fichierCourt) . "</a>";
    $sortie .= " (" . intval($ligneDoc[2] / 1024) . " ko)";
    // echo "chanson " . $ligneDoc[6];
    $maChanson = $listeChansons->recupereChanson($ligneDoc[6]);
    if ($maChanson != null) {
        $sortie .= " - " . $maChanson->getNom() . " - " . $maChanson->getTonalite() . " - " . $maChanson->getAnnee() . " - " . $maChanson->getTempo() . " bpm";
    }
    $sortie .= " <br>\n";
}

$sortie .= envoieFooter();
echo $sortie;
