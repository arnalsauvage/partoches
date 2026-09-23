<?php
/**
 * PAGE : chanson_voir.php
 * Affiche le détail d'une chanson avec une UX moderne (Refactorisé SOLID).
 */

require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
require_once __DIR__ . "/ChansonService.php";
require_once __DIR__ . "/ChansonVoirRenderer.php";
require_once PHP_DIR . "/liens/lienurl_voir.php";

// --- RÉCUPÉRATION DES DONNÉES ---
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Erreur : Identifiant de chanson invalide.");
}

$idChanson = (int)$_GET['id'];
$_chanson = Chanson::load($idChanson);

// --- SÉCURITÉ PUBLICATION ---
if ($_chanson->getPublication() == 0 && !estAdmin()) {
    if (!isset($_SESSION['id']) || $_SESSION['id'] != $_chanson->getIdUser()) {
        header('Location: chanson_liste.php');
        exit();
    }
}

// Augmenter le compteur de vues
augmenteHits("chanson", $idChanson);

// --- PRÉPARATION DES DONNÉES VIA LE SERVICE ---
$viewData = ChansonService::getChansonViewData($_chanson);

// --- RENDU HTML VIA LE RENDERER ---
$headHtml = envoieHead("Partoches - " . $_chanson->getNom(), "../../css/index.css");
$pasDeMenu = true;
require_once PHP_DIR . "/navigation/menu.php";

echo $headHtml;
echo $MENU_HTML;
echo ChansonVoirRenderer::render($viewData);
echo envoieFooter();
