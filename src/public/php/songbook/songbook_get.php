<?php
const PRIVILEGE = 'privilege';
const NOM_FIC = 'nomFic';
const ID_DOC = 'idDoc';
const ID_SONGBOOK = 'id';
include_once __DIR__ . "/../lib/utilssi.php";
include_once __DIR__ . "/../navigation/menu.php";
include_once("Songbook.php");
include_once __DIR__ . "/../liens/lienDocSongbook.php";
$nomTable = "songbook";

$id = (int)($_POST[ID_SONGBOOK] ?? ($_GET[ID_SONGBOOK] ?? 0));
$mode = $_POST['mode'] ?? ($_GET['mode'] ?? '');

// Les modifs sont réservées aux utilisateurs authentifiés et habilités
if (($_SESSION[PRIVILEGE] ?? 0) <= $GLOBALS["PRIVILEGE_INVITE"]) {
    redirection($nomTable . "_liste.php");
}

if ($mode !== '' && strlen($mode) > 12) {
    echo "Erreur n#1 dans songbook_get.php";
    return;
}

// On gère 6 cas : création d'une songbook, modif, suppression, ou suppression d'un docJoint, duplication songbook, liste songbooks
// En mode création ou mise à jour, on récupère les données du formulaire
if (($mode == "MAJ") || ($mode == "INS")) {

    $id = $_POST [ID_SONGBOOK];
    $fnom = $_POST ['fnom'];
    $fdescription = $_POST ['fdescription'];
    $fimage = $_POST ['fimage'];
    $ftype = $_POST['ftype'];

    // Seul admin peut modifier hits et date
    if ($_SESSION [PRIVILEGE] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
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
    modifiesSongbook($id, $fnom, $fdescription, $fdate, $fimage, $fhits, $ftype);
}

// Cas de l'ajout d'un Songbook
if ($mode == "INS") {
    $fhits = 0;
    $fdate = date("d/m/Y");
    /** @noinspection PhpUndefinedVariableInspection */
    creeSongbook($fnom, $fdescription, $fdate, $fimage, $fhits, $ftype);
}

// Gestion de la demande de suppression
if (isset($id) && ($mode == "SUPPR")) {
    supprimeSongbook($id);
}

// Gestion de la demande de suppression de document dans le songbook
if ($mode == "SUPPRDOC") {
    echo "Appel avec mode = $mode, id = $id, idDoc = " . $_GET [ID_DOC] . " idSongbook = " . $_GET [ID_SONGBOOK];
    supprimeLienIdDocIdSongbook($_GET [ID_DOC], $_GET [ID_SONGBOOK]);
}

// Gestion de la demande de suppression de fichier dans le songbook
if (($mode == "SUPPRFIC") && ($_SESSION [PRIVILEGE] > 1)) {
    // echo "Appel avec mode = $mode, nomFic = $_GET[NOM_FIC] , idDoc = " . $_POST ['idDoc'] . " idSongbook = " . $_GET ['id'];
    // unlink("../../data/songbooks/" . $id . "/" . $_GET[NOM_FIC]);
    supprimeDocument($_GET [ID_DOC]);
}

// Gestion de la demande de suppression de fichier de la corbeille du songbook
if ($mode == "SUPPRFICPOU" && $_SESSION [PRIVILEGE] > 1) {
    echo "Appel avec mode = $mode, nomFic =" . $_GET[NOM_FIC] . " ,  idSongbook = " . $_GET [ID_SONGBOOK];
    unlink("../../data/songbooks/" . $id . "/" . $_GET[NOM_FIC]);
}

// Ce cas est appelé en ajax, donc on ne redirigera pas
if ($mode == "GENEREPDF") {
    creeSongbookPdf($id);
}

// On fait une redirection dans tous les cas, sauf la demande de génération de PDF - appel ajax
if (($mode != "GENEREPDF") && ($mode != "RESTAUREDOC")) {
    redirection($nomTable . "_form.php?id=$id");
}
function menageNomVersion($nomFic) :string
{
    $nom = substr($nomFic, 0, strlen($nomFic) - 4);
    $nomExtension = substr($nomFic, -3);

    $fin = substr($nom, -3);
    if (substr($fin, 0, 2) == "-v") {
        echo "fin = $fin <br>";
        $nom = (substr($nom, 0, strlen($nom) - 3));
        echo "nom = $nom <br>";
    }
    $fin = substr($nom, -4);
    if (substr($fin, 0, 2) == "-v") {
        echo "fin = $fin <br>";
        $nom = (substr($nom, 0, strlen($nom) - 4));
        echo "nom = $nom <br>";
    }
    return ($nom . "." . $nomExtension);
}

if ($mode == "RESTAUREDOC") {
    $repertoire = "../data/songbooks/" . $_POST [ID_SONGBOOK] . "/";
    // echo "Répertoire : " . $repertoire . "<BR>";
    $size = filesize($repertoire . $_POST[NOM_FIC]);
    $nomDeFichier = menageNomVersion($_POST[NOM_FIC]);
    $version = creeModifieDocument($nomDeFichier, $size, "songbook", $id);
    // echo "version " . $version . " du doc " .$_POST[NOM_FIC] . "de taille $size demandé.";
}
if ($mode == "TEST") {
    echo "test menageNomVersion bidule-v1= " . menageNomVersion("bidule-v1.jpg") . " doit être égal à bidule.jpg <br>";
    echo "test menageNomVersion bidule-v12= " . menageNomVersion("bidule-v12.pdf") . " doit être égal à bidule.pdf <br>";
    echo "test menageNomVersion bidule-v9= " . menageNomVersion("bidule-v9.doc") . " doit être égal à bidule.doc <br>";
}