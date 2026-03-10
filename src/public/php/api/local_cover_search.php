<?php
header("Content-Type: application/json; charset=UTF-8");

// Constante pour le dossier des chansons (à adapter si nécessaire)
// Supposons que $_DOSSIER_CHANSONS soit défini ailleurs ou ici directement.
// Pour l'exemple, nous allons le définir ici.
define('_DOSSIER_CHANSONS', 'data/chansons/'); // Chemin relatif depuis la racine du projet

try {
    $idChanson = $_GET['id'] ?? null;

    if (empty($idChanson) || !is_numeric($idChanson)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Song ID is required and must be numeric.']);
        exit;
    }

    $chansonDir = __DIR__ . '/../../' . _DOSSIER_CHANSONS . $idChanson . '/';
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
                // Construire l'URL relative depuis la racine web
                $covers[] = '/' . _DOSSIER_CHANSONS . $idChanson . '/' . $file;
            }
        }
    } else {
        // Optionnel: retourner un message si le dossier n'existe pas ou n'est pas accessible
        // echo json_encode(['error' => 'No directory found for this song ID or not readable.']);
        // exit;
    }

    echo json_encode(['local_covers' => $covers]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
