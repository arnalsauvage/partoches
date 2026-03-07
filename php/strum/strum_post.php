<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/utilssi.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/strum/strum.php";

// Vérification des droits
if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_MEMBRE"] ?? 1)) {
    echo "Erreur : Accès refusé.";
    exit();
}

$id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
$mode = $_POST['mode'] ?? ($_GET['mode'] ?? '');

// 1. SUPPRESSION
if ($mode == "SUPPR" || $mode == "DEL") {
    if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
        echo "Erreur : Droit de suppression insuffisant.";
        exit();
    }
    $s = new Strum($id);
    if ($s->getId() > 0) {
        $s->supprimeBDD();
        echo "ok";
    } else {
        echo "Erreur : Strum introuvable.";
    }
    exit();
}

// 2. ENREGISTREMENT (INS / MAJ)
if ($mode == "INS" || $mode == "MAJ" || $mode == "NEW" || $mode == "UPDATE") {
    $strumPattern = $_POST['strum'] ?? '';
    $description = $_POST['description'] ?? '';
    $unite = (int)($_POST['unite'] ?? 8);
    $longueur = (int)($_POST['longueur'] ?? 8);
    $swing = (int)($_POST['swing'] ?? 0);

    // Pour aider la saisie, on a pu mettre des tirets, on les remet en espaces pour la BDD
    $strumPattern = str_replace("-", " ", $strumPattern);

    $s = new Strum($id);
    $s->setStrum($strumPattern);
    $s->setDescription($description);
    $s->setUnite($unite);
    $s->setLongueur($longueur);
    $s->setSwing($swing);

    $newId = $s->enregistreBDD();
    if ($newId > 0) {
        echo "ok id:$newId";
    } else {
        echo "Erreur lors de l'enregistrement en base.";
    }
    exit();
}

echo "Erreur : Mode non reconnu.";
