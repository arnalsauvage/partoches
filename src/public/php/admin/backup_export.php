<?php
/**
 * CONTRÔLEUR : backup_export.php
 * Génère et déclenche le téléchargement de la sauvegarde ZIP complète (BDD + Data + Conf).
 */

require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
require_once __DIR__ . '/BackupService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

set_time_limit(300);
ini_set('memory_limit', '512M');

// Sécurité : Seul l'administrateur peut exporter la sauvegarde
if (!estAdmin()) {
    header("HTTP/1.0 403 Forbidden");
    die("🎸 Accès refusé : Seul l'administrateur peut générer la sauvegarde intégrale.");
}

try {
    $service = new BackupService();
    $zipPath = $service->createBackupZip();

    if (!file_exists($zipPath)) {
        throw new Exception("Le fichier zip de sauvegarde n'a pas pu être généré.");
    }

    $filename = 'partoches-backup-' . date('Y-m-d-His') . '.zip';
    $filesize = filesize($zipPath);

    // Entêtes HTTP pour le téléchargement direct
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $filesize);
    header('Pragma: no-cache');
    header('Expires: 0');

    // Réponse rapide pour les requêtes HEAD (tests / vérifications d'entêtes)
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
        @unlink($zipPath);
        exit();
    }

    // Lecture et envoi du fichier
    readfile($zipPath);

    // Suppression du fichier temporaire sur le serveur
    @unlink($zipPath);
    exit();

} catch (Throwable $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo "<h1>Erreur lors de la génération de la sauvegarde</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='params.php'>Retour au paramétrage</a></p>";
    exit();
}
