<?php
header("Content-Type: application/json; charset=UTF-8");

// --- INITIALISATION (Django Style) ---
// On remonte 3 niveaux pour trouver l'autoloader : api -> php -> public -> src/autoload.php
require_once dirname(__DIR__, 3) . '/autoload.php';

try {
    global $_DOSSIER_CHANSONS;
    $idChanson = $_GET['id'] ?? null;

    if (empty($idChanson) || !is_numeric($idChanson)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Song ID is required and must be numeric.']);
        exit;
    }

    // On utilise le chemin global absolu défini dans params.php (inclus via autoload)
    $chansonDir = $_DOSSIER_CHANSONS . $idChanson . '/';
    $covers = [];

    // Vérifier si le répertoire existe et est lisible
    if (is_dir($chansonDir) && is_readable($chansonDir)) {
        $files = scandir($chansonDir);
        $allowedExtensions = ['jpg', 'jpeg', 'gif', 'webp', 'png'];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array(strtolower($extension), $allowedExtensions)) {
                // Pour le navigateur, on garde un chemin relatif à la racine du domaine
                $covers[] = "./data/chansons/" . $idChanson . '/' . $file;
            }
        }
    }

    echo json_encode(['local_covers' => $covers]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
