<?php
header("Content-Type: application/json; charset=UTF-8");

// --- INITIALISATION (Django Style) ---
// On remonte 3 niveaux pour trouver l'autoloader : api -> php -> public -> src/autoload.php
require_once dirname(__DIR__, 3) . '/autoload.php';

try {
    $query = $_GET['q'] ?? '';
    if (empty($query)) {
        echo json_encode(['results' => []]);
        exit;
    }

    // --- Récupération des clés Discogs via CONF_DIR (Chemin Absolu) ---
    $ini_objet = new FichierIni();
    $configPath = CONF_DIR . '/params.ini'; 
    $ini_objet->m_load_fichier($configPath);

    // Récupère les clés
    $key = trim($ini_objet->m_valeur('DISCOGSCONSUMERKEY', 'discogs'));
    $secret = trim($ini_objet->m_valeur('DISCOGSCONSUMERSECRET', 'discogs'));

    if (empty($key) || empty($secret)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Discogs API credentials not configured in params.ini [discogs] section']);
        exit;
    }

    // Recherche plus large : on enlève format=Single qui est trop restrictif
    $discogsUrl = "https://api.discogs.com/database/search?q=" . urlencode($query) . "&type=release";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $discogsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AteliersCanopeeApp/1.0');

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Discogs key=$key, secret=$secret",
        "Content-Type: application/json"
    ]);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Error calling Discogs API: HTTP " . $httpCode . " - " . $output);
    }

    $data = json_decode($output, true);
    $covers = [];
    $count = 0;
    
    if (isset($data['results']) && is_array($data['results'])) {
        foreach ($data['results'] as $result) {
            if ($count >= 5) break;
            if (!empty($result['cover_image']) && !empty($result['year'])) {
                $covers[] = [
                    'url' => $result['cover_image'],
                    'title' => $result['title'] ?? 'N/A',
                    'year' => $result['year'],
                    'artist' => $result['artist'] ?? 'N/A'
                ];
                $count++;
            }
        }
    }

    echo json_encode(['discogs_covers' => $covers]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
