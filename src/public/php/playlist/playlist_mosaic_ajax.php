<?php
ob_start();

// Inclusion de l'autoloader
require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
require_once __DIR__ . '/PlaylistFormService.php';

if (!function_exists('sendMosaicJsonResponse')) {
    /**
     * Envoie une réponse JSON propre en purgeant tout buffer parasite (warnings/notices).
     */
    function sendMosaicJsonResponse(array $data, int $statusCode = 200): void {
        if (ob_get_length()) {
            ob_clean();
        }
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data);
        if (defined('PHPUNIT_RUNNING') && PHPUNIT_RUNNING) {
            if (ob_get_level() > 1) {
                ob_end_flush();
            }
            return;
        }
        exit();
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
    sendMosaicJsonResponse(['success' => false, 'error' => 'Accès refusé. Privilège éditeur requis.']);
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
    sendMosaicJsonResponse(['success' => true, 'message' => 'Aperçu annulé.']);
    return;
}

// 2. ACTION : VALIDER L'APERÇU (ENREGISTREMENT DÉFINITIF)
if ($action === 'apply') {
    $previewFile = trim($_POST['preview_file'] ?? '');
    if (empty($previewFile)) {
        sendMosaicJsonResponse(['success' => false, 'error' => 'Aucun aperçu à valider.']);
        return;
    }

    $finalFile = PlaylistFormService::applyMosaicPreview($previewFile, $playlistNom, $idPlaylist);
    if (!$finalFile) {
        sendMosaicJsonResponse(['success' => false, 'error' => "Impossible d'appliquer l'aperçu (fichier introuvable)."]);
        return;
    }

    sendMosaicJsonResponse([
        'success' => true,
        'isApplied' => true,
        'fileName' => $finalFile,
        'imageUrl' => '/data/playlists/' . $finalFile . '?v=' . time(),
        'message' => 'Pochette validée et enregistrée avec succès !'
    ]);
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
    sendMosaicJsonResponse(['success' => false, 'error' => 'Veuillez sélectionner au moins une pochette.']);
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

    sendMosaicJsonResponse([
        'success' => true,
        'isPreview' => true,
        'fileName' => $fileName,
        'imageUrl' => '/data/playlists/' . $fileName . '?v=' . time(),
        'message' => 'Aperçu généré ! Vous pouvez valider ou tester un autre agencement.'
    ]);
} catch (\Throwable $e) {
    sendMosaicJsonResponse(['success' => false, 'error' => 'Erreur lors de la génération : ' . $e->getMessage()]);
}
