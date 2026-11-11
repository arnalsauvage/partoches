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

$idPlaylist = (int)($_POST['id'] ?? 0);
$playlistNom = trim($_POST['nom'] ?? 'Playlist');
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

// Limiter à 4 pochettes max
$covers = array_slice($covers, 0, 4);

try {
    $fileName = PlaylistFormService::generateMosaic($covers, $playlistNom, $idPlaylist);
    
    // Si la playlist existe déjà en BDD, mise à jour immédiate de la colonne image
    if ($idPlaylist > 0) {
        $db = $_SESSION['mysql'] ?? ($GLOBALS['mysqli'] ?? null);
        if ($db) {
            $stmt = $db->prepare("UPDATE playlist SET image = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $fileName, $idPlaylist);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    echo json_encode([
        'success' => true,
        'fileName' => $fileName,
        'imageUrl' => '/data/playlists/' . $fileName . '?v=' . time(),
        'message' => 'Pochette générée avec succès !'
    ]);
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la génération : ' . $e->getMessage()]);
}
