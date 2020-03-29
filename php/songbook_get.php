<?php
const PRIVILEGE = 'privilege';
include_once("lib/utilssi.php");
include_once("menu.php");
include_once("songbook.php");
include_once("lienDocSongbook.php");
$nomTable = "songbook";

// Les modifs sont reservées aux utilisateurs authentifiés et habilités
if ($_SESSION [PRIVILEGE] <= 1) {
    redirection($nomTable . "_liste.php");
}
// On gère 6 cas : création d'une songbook, modif, suppression, ou suppression d'un docJoint, duplication songbook, liste songbooks
if (isset($_POST ['mode'])) {
    $mode = $_POST ['mode'];
} elseif (isset($_GET ['mode'])) {
    $mode = $_GET ['mode'];
}

// On récupère l'identifiant du songbook passé par POST ou GET
if (isset ($_GET ['id'])) {
    $id = $_GET ['id'];
}
if (isset ($_POST ['id'])) {
    $id = $_POST ['id'];
}

// En mode création ou mise à jour, on récupère les données du formulaire
if (($mode == "MAJ") || ($mode == "INS")) {

    $id = $_POST ['id'];
    $fnom = $_SESSION ['mysql']->real_escape_string($_POST ['fnom']);
    $fdescription = $_SESSION ['mysql']->real_escape_string($_POST ['fdescription']);
    $fimage = $_POST ['fimage'];

    // Seul admin peut modifier hits et date
    if ($_SESSION [PRIVILEGE] > 2) {
        $fdate = $_POST ['fdate'];
        $fhits = $_POST ['fhits'];
    }
}

// Cas de la duplication
if (isset($_GET ['DUP'])) {
    $mode = "DUP";
    $id = $_GET ['DUP'];
    dupliqueSongbook($id);
}

// Cas de la mise à jour
if ($mode == "MAJ") {
        // On récupère les valeurs de hits et date en base, car ils ne sont pas dans le formulaire
    $songbook = chercheSongbook($id);
    // Seul admin peut modifier hits et date
    if ($_SESSION [PRIVILEGE] < 2) {
        $fhits = $songbook[5];
        $fdate = dateMysqlVersTexte($songbook[3]);
    }

    /** @noinspection PhpUndefinedVariableInspection */
    modifiesSongbook($id, $fnom, $fdescription, $fdate, $fimage, $fhits);
}

// Cas de l'ajout d'un Songbook
if ($mode == "INS") {
    $fhits = 0;
    $fdate = date("d/m/Y");
    /** @noinspection PhpUndefinedVariableInspection */
    creeSongbook($fnom, $fdescription, $fdate, $fimage, $fhits);
}

// Gestion de la demande de suppression
if (isset($id) && ($mode == "SUPPR")) {
    supprimeSongbook($id);
}

// Gestion de la demande de suppression de document dans le songbook
if ($mode == "SUPPRDOC") {
    //	echo "Appel avec mode = $mode, id = $id, idDoc = " . $_GET ['idDoc'] . " idSongbook = " . $_GET ['idSongbook'];
    supprimeLienIdDocIdSongbook($_GET ['idDoc'], $_GET ['idSongbook']);
}

// Gestion de la demande de suppression de document dans le songbook
if ($mode == "SUPPRFIC" && $_SESSION [PRIVILEGE] > 1) {
    // echo "Appel avec mode = $mode, nomFic = $_GET['nomFic'] , idDoc = " . $_GET ['idDoc'] . " idSongbook = " . $_GET ['idSongbook'];
    unlink("../data/songbooks/" . $_GET['idSongbook'] . "/" . $_GET['nomFic']);
    supprimeDocument($_GET ['idDoc']);
}

// Ce cas est appelé en ajax, donc on ne redirigera pas
if ($mode == "GENEREPDF") {
    creeSongbookPdf($id);
}

// On fait une redirection dans tous les cas, sauf la demande de génération de PDF - appel ajax
if ($mode != "GENEREPDF") {
    redirection($nomTable . "_liste.php");
}