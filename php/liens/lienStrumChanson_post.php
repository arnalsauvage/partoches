<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/utilssi.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/liens/lienStrumChanson.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/strum/strum.php";

// Vérification des droits
if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_MEMBRE"] ?? 1)) {
    echo "Erreur : Accès refusé.";
    exit();
}

$mode = $_POST['mode'] ?? ($_GET['mode'] ?? '');
$idChanson = (int)($_POST['idChanson'] ?? ($_GET['idChanson'] ?? 0));

// 1. SUPPRESSION
if ($mode == "DEL") {
    $idLien = (int)($_GET['id'] ?? 0);
    if ($idLien > 0) {
        supprimelienStrumChanson($idLien);
    }
    if ($idChanson > 0) {
        header("Location: ../chanson/chanson_form.php?id=$idChanson#tabs-3");
    } else {
        echo "ok";
    }
    exit();
}

// 2. CRÉATION
if ($mode == "NEW") {
    $idStrum = (int)($_POST['idStrum'] ?? 0);
    
    if ($idChanson > 0 && $idStrum > 0) {
        $s = new Strum($idStrum);
        $motif = $s->getStrum();
        creelienStrumChanson($motif, $idChanson, $idStrum);
        header("Location: ../chanson/chanson_form.php?id=$idChanson#tabs-3");
    } else {
        echo "Erreur : Paramètres manquants.";
    }
    exit();
}

echo "Erreur : Mode non reconnu.";
