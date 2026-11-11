<?php
// Inclusion de l'autoloader
require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
require_once __DIR__ . '/PlaylistFormService.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
    echo json_encode(['success' => false, 'error' => 'Accès refusé. Privilège éditeur requis.']);
    if (!defined('PHPUNIT_RUNNING')) exit();
    return;
}

$action = trim($_POST['action'] ?? 'preview'); // 'preview', 'apply', 'cancel'
$idPlaylist = (int)($_POST['id'] ?? 0);
$playlistNom = trim($_POST['nom'] ?? 'Playlist');

// 1. ACTION : ANNULER L'APERÇU
if ($action === 'cancel') {
    $previewFile = trim($_POST['preview_file'] ?? '');
    if (!empty($previewFile)) {
        PlaylistFormService::cancelMosaicPreview($previewFile);
    }
    echo json_encode(['success' => true, 'message' => 'Aperçu annulé.']);
    if (!defined('PHPUNIT_RUNNING')) exit();
    return;
}

// 2. ACTION : VALIDER L'APERÇU (ENREGISTREMENT DÉFINITIF)
if ($action === 'apply') {
    $previewFile = trim($_POST['preview_file'] ?? '');
    if (empty($previewFile)) {
        echo json_encode(['success' => false, 'error' => 'Aucun aperçu à valider.']);
        if (!defined('PHPUNIT_RUNNING')) exit();
        return;
    }

    $finalFile = PlaylistFormService::applyMosaicPreview($previewFile, $playlistNom, $idPlaylist);
    if (!$finalFile) {
        echo json_encode(['success' => false, 'error' => "Impossible d'appliquer l'aperçu (fichier introuvable)."]);
        if (!defined('PHPUNIT_RUNNING')) exit();
        return;
    }

    echo json_encode([
        'success' => true,
        'isApplied' => true,
        'fileName' => $finalFile,
        'imageUrl' => '/data/playlists/' . $finalFile . '?v=' . time(),
        'message' => 'Pochette validée et enregistrée avec succès !'
    ]);
    if (!defined('PHPUNIT_RUNNING')) exit();
    return;
}

// 3. ACTION : PRÉVISUALISATION (PAR DÉFAUT)
$coversRaw = $_POST['covers'] ?? [];
$covers = [];
if (is_array($coversRaw)) {
    $covers = array_filter($coversRaw);
} elseif (is_string($coversRaw)) {
    $covers = array_filter(explode(',', $coversRaw));
}

if (empty($covers)) {
    echo json_encode(['success' => false, 'error' => 'Veuillez sélectionner au moins une pochette.']);
    if (!defined('PHPUNIT_RUNNING')) exit();
    return;
}

$gridSize = (int)($_POST['grid_size'] ?? 0);
$colorOrder = trim($_POST['color_order'] ?? 'manual');
if (!in_array($colorOrder, ['manual', 'rainbow', 'brightness', 'warm_cold'])) {
    $colorOrder = 'manual';
}

$maxSlots = ($gridSize > 0) ? min(25, max(1, $gridSize)) : 25;
$covers = array_slice($covers, 0, $maxSlots);

try {
    // Génère un fichier temporaire avec préfixe preview_ sans impacter la BDD
    $fileName = PlaylistFormService::generateMosaic($covers, $playlistNom, $idPlaylist, $gridSize, $colorOrder, true);

    echo json_encode([
        'success' => true,
        'isPreview' => true,
        'fileName' => $fileName,
        'imageUrl' => '/data/playlists/' . $fileName . '?v=' . time(),
        'message' => 'Aperçu généré ! Vous pouvez valider ou tester un autre agencement.'
    ]);
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la génération : ' . $e->getMessage()]);
}
