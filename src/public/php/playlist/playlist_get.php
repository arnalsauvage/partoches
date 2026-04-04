<?php
require_once __DIR__ . "/../lib/utilssi.php";
$pasDeMenu = true; // Empêche menu.php de faire un echo, pour que header() fonctionne
require_once __DIR__ . "/../navigation/menu.php";
require_once("playlist.php");

$nomTable = "playlist";

// Sécurité : au moins membre pour voir (redirection déjà faite dans menu?), 
// mais ici on traite des données donc PRIVILEGE_EDITEUR minimum.
if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"]) {
    redirection($nomTable . "_liste.php");
}

// Récupération des variables de pilotage
$mode = $_POST['mode'] ?? $_GET['mode'] ?? '';
$id = $_POST['id'] ?? $_GET['id'] ?? null;

// Initialisation des données du formulaire pour éviter les warnings
$fnom = $_POST['fnom'] ?? '';
$fdescription = $_POST['fdescription'] ?? '';
$fimage = $_POST['fimage'] ?? '';
$fdate = $_POST['fdate'] ?? date("d/m/Y");
$fhits = $_POST['fhits'] ?? 0;
$ftype = $_POST['ftype'] ?? 0;

// Gestion des critères dynamiques
$criteresArr = [];
if ($ftype == 1) {
    if (!empty($_POST['dyn_tonalite'])) $criteresArr['tonalite'] = $_POST['dyn_tonalite'];
    if (!empty($_POST['dyn_tempo'])) $criteresArr['tempo_famille'] = $_POST['dyn_tempo'];
    if (!empty($_POST['dyn_saison'])) $criteresArr['saison'] = $_POST['dyn_saison'];
    if (!empty($_POST['dyn_strum'])) $criteresArr['idStrum'] = $_POST['dyn_strum'];
}
$fcriteres = json_encode($criteresArr);

// Nettoyage SQL
if ($_SESSION['mysql']) {
    $fnom = $_SESSION['mysql']->real_escape_string($fnom);
    $fdescription = $_SESSION['mysql']->real_escape_string($fdescription);
}

// TRAITEMENT
if ($mode == "MAJ" && $id) {
    if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
        // Seul l'admin peut changer hits et date, donc on les recharge si on n'est pas admin
        $playlist = cherchePlaylist($id);
        if ($playlist) {
            $fhits = $playlist['hits'] ?? 0;
            $fdate = dateMysqlVersTexte($playlist['date_creation'] ?? $playlist['date'] ?? date("Y-m-d"));
        }
    }
    modifiePlaylist($id, $fnom, $fdescription, $fdate, $fimage, $fhits, $ftype, $fcriteres);
}

if ($mode == "INS") {
    // Par défaut à la création
    $fhits = 0;
    $fdate = date("d/m/Y");
    creePlaylist($fnom, $fdescription, $fdate, $fimage, $fhits, $ftype, $fcriteres);
}

// Gestion de la demande de suppression
if ($id && $mode == "SUPPR") {
    supprimePlaylist($id);
}

// Redirection finale
redirection($nomTable . "_liste.php");
