<?php
const PRIVILEGE = 'privilege';
const NOM_FIC = 'nomFic';
require_once("lib/utilssi.php");
require_once("document.php");
require_once("chanson.php");

if ($_SESSION [PRIVILEGE] <= 1) {
    redirection($nomTable . "_liste.php");
}

$nomTable = "chanson";
$_chanson = new Chanson();

if (isset ($_GET ['id']) && is_numeric($_GET ['id'])) {
    $id = $_GET ['id'];
    $mode = $_GET ['mode'];
    //echo "On est en get <br> " ;
}

if (isset ($_POST ['id']) && is_numeric($_POST ['id'])) {
    //echo "On est en post !!!  \n\n\n ";
    $id = $_POST ['id'];
    $fnom = $_POST ['fnom'];
    $finterprete = $_POST ['finterprete'];
    $fannee = $_POST ['fannee'];
    $ftempo = $_POST ['ftempo'];
    $fmesure = $_POST ['fmesure'];
    $fpulsation = $_POST ['fpulsation'];
    if (isset($_POST ['fhits']) && is_numeric($_POST ['fhits'])) {
        $fhits = $_POST ['fhits'];
    }
    $ftonalite = $_POST ['ftonalite'];
    $mode = $_POST ['mode'];
    if (isset($_POST ['fidUser'])) {
        $fidUser = $_POST ['fidUser'];
    }
    $fdate = $_POST['fdate'];
}
//else
//    echo "On est PAS enPOST!!!";

// On gère 4 cas : création d'une chanson, modif, suppression chanson ou suppression d'un doc de la chanson
if ($mode == "MAJ") {
    if ($_SESSION [PRIVILEGE] < 3) {
        // On doit recharger les hits, le user et la date pour qu'ils ne soient remis à zéro
        $_chanson->chercheChanson($id);
        $fhits = $_chanson->getHits();
        $fidUser = $_chanson->getIdUser();
        $fdate = $_chanson->getDatePub();
    } else {
        $fdate = dateTexteVersMysql($fdate);
    }
    $_chanson->__construct($id, $fnom, $finterprete, $fannee, $fidUser, $ftempo, $fmesure, $fpulsation, $fdate, $fhits, $ftonalite);
    $_chanson->creeModifieChansonBDD();
}

// Gestion de la demande de suppression
if ($id && $mode == "SUPPR" && $_SESSION [PRIVILEGE] > 1) {
    $_chanson = new Chanson($id);
    $_chanson->supprimeChanson();
    redirection($nomTable . "_liste.php");
}

if ($mode == "INS") {
    // echo "FHits = " . $fhits;
    $fhits = 0;
    // Seul admin peut attribuer une chanson à un autre
    if ($_SESSION [PRIVILEGE] < 3) {
        $fidUser = $_SESSION ['id'];
    }
    $_chanson = new Chanson($fnom, $finterprete, $fannee, $fidUser, $ftempo, $fmesure, $fpulsation, $fhits, $ftonalite);
    $id = $_chanson->creeChansonBDD();
}

// Gestion de la demande de suppression de document dans la chanson
if ($mode == "SUPPRDOC" && $_SESSION [PRIVILEGE] > 1) {
    // 	echo "Appel avec mode = $mode, id = $id, idDoc = " . $_GET ['idDoc'] . " idSongbook = " . $_GET ['idSongbook'];
    supprimeDocument($_GET ['idDoc']);
}

// Gestion de la demande de renommage de document dans la chanson
if ($mode == "RENDOC" && $_SESSION [PRIVILEGE] > 1) {
    //echo "Appel avec idDoc = " . $_POST ['idDoc'] . " nomDoc = " . $_POST ['nomDoc'];
    $retour = renommeDocument($_POST ['idDoc'], $_POST ['nomDoc']);
    if ($retour == 1) {
        echo "Tout s'est bien passé";
    } else {
        {
            echo "La demande n'a pas été traitée... Erreur " . $retour;
        }
    }
}

// Gestion de la demande de suppression de fichier dans la chanson
if ($mode == "SUPPRFIC" && $_SESSION [PRIVILEGE] > 1) {
    // echo "Appel avec mode = $mode, id = $id, nomFic = " . $_GET ['nomFic'];
    unlink($_GET[NOM_FIC]);
}

if ($mode == "RESTAUREDOC") {

    $repertoire = "../data/chansons/" . $_POST ['id'] . "/";
    $size = filesize($repertoire . $_POST [NOM_FIC]);
    $version = creeModifieDocument($_POST [NOM_FIC], $size, "chanson", $id);
    // Il faut renommer le doc en lui accolant son numéro de version
    rename($repertoire . $_POST [NOM_FIC], $repertoire . composeNomVersion($_POST [NOM_FIC], $version));
}

// On fait une redirection dans tous les cas, sauf la demande de restauration d'un fichier - appel ajax
if ($mode != "RESTAUREDOC" && $mode != "RENDOC") {
    redirection($nomTable . "_form.php?id=$id");
}
