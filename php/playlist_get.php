<?php
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("playlist.php");
$nomTable = "playlist";

if ($_SESSION ['privilege'] <= 1)
    redirection($nomTable . "_liste.php");

// On gère 3 cas : création d'une playlist, modif, suppression

// En mode création ou mise à jour, on récupère les données du formulaire
if (($mode == "MAJ") || ($mode == "INS")) {
    $id = $_POST ['id'];
    $fnom = $_SESSION ['mysql']->real_escape_string($_POST ['fnom']);
    $description = $_SESSION ['mysql']->real_escape_string($_POST ['fdescription']);
    $fimage = $_POST ['fimage'];
    // Seul admin peut modifier hits et date
    if ($_SESSION ['privilege'] > 2) {
        $fdate = $_POST ['fdate'];
        $fhits = $_POST ['fhits'];
    }
}

if (isset ($_GET ['id']) && is_numeric($_GET ['id'])) {
    $id = $_GET ['id'];
}

if (isset ($_POST ['id']) && is_numeric($_POST ['id'])) {
    $id = $_POST ['id'];
}

if ($mode == "MAJ") {
    if ($_SESSION ['privilege'] < 3) {
        // On doit recharger les hits et la date pour qu'ils ne soient remis à zéro
        $playlist = chercheplaylist($id);
        $fhits = $playlist[5];
        $fdate = dateMysqlVersTexte($playlist[3]);
    }
    /** @noinspection PhpUndefinedVariableInspection */
    modifieplaylist($id, $fnom, $description, $fdate, $fimage, $fhits);
}

if ($mode == "INS") {
    $fhits = 0;
    $fdate = date("d/m/Y");
    /** @noinspection PhpUndefinedVariableInspection */
    creeplaylist($fnom, $description, $fdate, $fimage, $fhits);
}

// Gestion de la demande de suppression
if ($id && $mode == "SUPPR") {
    supprimeplaylist($id);
}

// redirection ( $nomTable . "_liste.php" );
