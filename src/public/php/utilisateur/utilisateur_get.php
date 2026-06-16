<?php
/**
 * Traitement des actions utilisateur (MAJ, INS, SUPPR)
 */
require_once dirname(__DIR__, 3) . "/autoload.php";
require_once PHP_DIR . "/navigation/menu.php";

$utilisateurListe = "utilisateur_liste.php";

// Récupération sécurisée du mode et de l'id
$mode = $_POST['mode'] ?? $_GET['mode'] ?? '';
$idTarget = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

// --- VÉRIFICATION DES DROITS ---
$isAdmin = estAdmin();
$isSelf = ((int)$_SESSION['id'] === $idTarget);

if (!$isAdmin && !$isSelf) {
    // Si on n'est pas admin et qu'on ne se modifie pas soi-même, on n'a rien à faire ici
    redirection($utilisateurListe . "?msg=AUTH_DENIED");
    exit();
}

// Un non-admin ne peut jamais supprimer (même lui-même via ce script)
if ($mode == "SUPPR" && !$isAdmin) {
    redirection($utilisateurListe . "?msg=AUTH_DENIED");
    exit();
}

// Un admin ne peut pas se supprimer lui-même (sécurité)
if ($mode == "SUPPR" && $isSelf) {
    redirection($utilisateurListe . "?msg=AUTH_DENIED");
    exit();
}

// --- TRAITEMENT ---

if ($mode == "MAJ") {
    // Récupération des données du formulaire
    $flogin = $_POST['flogin'] ?? '';
    $fmdp = $_POST['fmdp'] ?? '';
    $fprenom = $_POST['fprenom'] ?? '';
    $fnom = $_POST['fnom'] ?? '';
    $fimage = $_POST['fimage'] ?? '';
    $fsite = $_POST['fsite'] ?? '';
    $femail = $_POST['femail'] ?? '';
    $fsignature = $_POST['fsignature'] ?? '';

    // Gestion des privilèges et compteurs (Seul l'admin peut les changer)
    if ($isAdmin) {
        $fprivilege = (int)($_POST['fprivilege'] ?? 0);
        $fnbreLogins = (int)($_POST['fnbreLogins'] ?? 0);
    } else {
        // On récupère les valeurs actuelles pour ne pas les écraser
        $current = Utilisateur::chercheUtilisateur($idTarget);
        $fnbreLogins = $current[10] ?? 0;
        $fprivilege = $current[11] ?? 0;
    }

    modifieUtilisateur($idTarget, $flogin, $fmdp, $fprenom, $fnom, $fimage, $fsite, $femail, $fsignature, $fnbreLogins, $fprivilege);
}

if ($mode == "SUPPR" && $isAdmin && $idTarget > 0) {
    supprimeUtilisateur($idTarget);
}

if ($mode == "INS" && $isAdmin) {
    // Création (Seul l'admin peut créer)
    $flogin = $_POST['flogin'] ?? '';
    $fmdp = $_POST['fmdp'] ?? '';
    $fprenom = $_POST['fprenom'] ?? '';
    $fnom = $_POST['fnom'] ?? '';
    $fimage = $_POST['fimage'] ?? '';
    $fsite = $_POST['fsite'] ?? '';
    $femail = $_POST['femail'] ?? '';
    $fsignature = $_POST['fsignature'] ?? '';
    $fprivilege = (int)($_POST['fprivilege'] ?? 0);

    creeUtilisateur($flogin, $fmdp, $fprenom, $fnom, $fimage, $fsite, $femail, $fsignature, $fprivilege);
}

redirection($utilisateurListe);
