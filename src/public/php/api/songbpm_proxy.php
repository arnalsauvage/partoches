<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../lib/FichierIni.php';

try {
    $artist = $_GET['artist'] ?? '';
    $title = $_GET['title'] ?? '';

    if (empty($artist) || empty($title)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Artist and title are required.']);
        exit;
    }

    // --- Récupération de la clé API SongBPM depuis params.ini ---
    $ini_objet = new FichierIni();
    $configPath = __DIR__ . '/../../conf/params.ini';
    $ini_objet->m_load_fichier($configPath);

    // Clé récupérée depuis la section [general] comme indiqué
    $apiKey = trim($ini_objet->m_valeur('cleGetSongBpm', 'general'));

    if (empty($apiKey)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'SongBPM API key (cleGetSongBpm) not configured in params.ini [general] section']);
        exit;
    }

    // --- Appel à l'API SongBPM ---
    // L'API est disponible sous https://api.getsong.co/. L'ancienne URL getSongBPM.com est redirigée.
    // Format de recherche : "song:{titre} artist:{artiste}" avec les espaces remplacés par des '+'
    $title_encoded_for_lookup = str_replace(' ', '+', $title);
    $artist_encoded_for_lookup = str_replace(' ', '+', $artist);
    $lookup = "song:" . $title_encoded_for_lookup . " artist:" . $artist_encoded_for_lookup;
    $songBpmUrl = "https://api.getsong.co/search/?api_key=" . urlencode($apiKey) . "&type=both&lookup=" . urlencode($lookup);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $songBpmUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        // Si l'API externe renvoie une erreur (4xx ou 5xx), on la retransmet directement
        // et on inclut l'URL d'appel pour faciliter le débogage.
        http_response_code($httpCode);
        echo json_encode([
            'error' => "Error from SongBPM API.",
            'details' => json_decode($output, true) ?: $output,
            'called_url' => $songBpmUrl // Ajoute l'URL appelée
        ]);
        exit; // Important pour arrêter l'exécution ici
    }
    
    // La documentation indique que la réponse peut contenir plusieurs chansons.
    // On prend la première qui correspond.
    $data = json_decode($output, true);
    $song_data = null;
    if (isset($data['search'][0])) {
        $song_data = $data['search'][0];
    }

    if ($song_data === null || !isset($song_data['tempo'])) {
         echo json_encode(['error' => 'BPM not found for this song.']);
         exit;
    }
    
    // On renvoie juste le tempo pour garder la réponse simple
    echo json_encode(['tempo' => $song_data['tempo']]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
