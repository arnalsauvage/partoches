<?php
/**
 * CONTRÔLEUR : audit_orphelins.php
 * Interface et API AJAX d'audit des fichiers orphelins (non référencés) et doublons MD5.
 */

require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
require_once __DIR__ . '/DataAuditService.php';

// Sécurité : Seul l'administrateur a accès
if (!estAdmin()) {
    if (isset($_GET['action']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json', true, 403);
        echo json_encode(['success' => false, 'error' => "Accès refusé. Privilèges d'administrateur requis."]);
        exit();
    }
    die("🎸 Désolé Arnal, seul le patron du club peut accéder à l'audit des fichiers !");
}

$service = new DataAuditService();
$action = $_GET['action'] ?? ($_POST['action'] ?? null);

// Mode API AJAX
if ($action) {
    header('Content-Type: application/json');

    try {
        if ($action === 'scan_orphans') {
            $orphans = $service->findOrphanFiles();
            $totalSize = array_sum(array_column($orphans, 'size'));
            echo json_encode([
                'success' => true,
                'count' => count($orphans),
                'totalSize' => $totalSize,
                'orphans' => $orphans
            ]);
            exit();
        }

        if ($action === 'scan_duplicates') {
            $duplicates = $service->findDuplicateFiles();
            $totalWasted = array_sum(array_column($duplicates, 'totalWastedSize'));
            echo json_encode([
                'success' => true,
                'count' => count($duplicates),
                'totalWastedSize' => $totalWasted,
                'duplicates' => $duplicates
            ]);
            exit();
        }

        if ($action === 'delete_orphans' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $selectedFiles = $input['orphan_files'] ?? ($_POST['orphan_files'] ?? []);

            if (!empty($selectedFiles) && is_array($selectedFiles)) {
                $deletedCount = $service->deleteOrphanFiles($selectedFiles);
                echo json_encode([
                    'success' => true,
                    'deletedCount' => $deletedCount,
                    'deletedFiles' => $selectedFiles
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => "Aucun fichier sélectionné."
                ]);
            }
            exit();
        }

        if ($action === 'scan_integrity') {
            $orphanDirs = $service->findOrphanDirectories();
            $missingFiles = $service->findMissingFilesFromDb();
            $orphanRelations = $service->findOrphanDbRelations();

            echo json_encode([
                'success' => true,
                'orphanDirs' => $orphanDirs,
                'orphanDirsCount' => count($orphanDirs),
                'missingFiles' => $missingFiles,
                'missingFilesCount' => count($missingFiles),
                'orphanRelations' => $orphanRelations,
                'orphanRelationsCount' => count($orphanRelations)
            ]);
            exit();
        }

        if ($action === 'delete_orphan_dir' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $relDir = $input['rel_dir'] ?? ($_POST['rel_dir'] ?? null);

            if (!empty($relDir)) {
                $deleted = $service->deleteOrphanDirectory($relDir);
                echo json_encode([
                    'success' => $deleted,
                    'deletedDir' => $relDir
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => "Chemin de dossier non spécifié."]);
            }
            exit();
        }

        if ($action === 'clean_orphan_relations' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $cleanedCount = $service->cleanOrphanDbRelations();
            echo json_encode([
                'success' => true,
                'cleanedCount' => $cleanedCount
            ]);
            exit();
        }

        echo json_encode(['success' => false, 'error' => 'Action invalide']);
        exit();

    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
}

// Rendu HTML via la vue Canopée (Chargement instantané sans scan synchrone)
$headHtml = envoieHead("Django Audit - Fichiers Orphelins & Doublons", "../../css/styles-communs.css");
$pasDeMenu = true;
require_once PHP_DIR . "/navigation/menu.php";

echo $headHtml;
echo $MENU_HTML;
require __DIR__ . '/views/audit_orphelins_view.phtml';
echo envoieFooter();
