<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclusion de FichierIni pour lire params.ini
require_once __DIR__ . '/../lib/FichierIni.php'; // Chemin relatif vers FichierIni

try {
    $query = $_GET['q'] ?? '';
    if (empty($query)) {
        echo json_encode(['results' => []]);
        exit;
    }

    // --- Récupération des clés Discogs depuis params.ini ---
    $ini_objet = new FichierIni();
    // Chemin vers params.ini (relatif au dossier api/)
    $configPath = __DIR__ . '/../../conf/params.ini'; 
    $ini_objet->m_load_fichier($configPath);

    // Récupère les clés, en retirant les espaces blancs au début et à la fin
    $key = trim($ini_objet->m_valeur('DISCOGSCONSUMERKEY', 'discogs'));
    $secret = trim($ini_objet->m_valeur('DISCOGSCONSUMERSECRET', 'discogs'));
    // --- Fin récupération clés ---

    if (empty($key) || empty($secret)) {
        // Retourner une erreur JSON si les clés ne sont pas trouvées dans params.ini
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Discogs API credentials not configured in params.ini [discogs] section']);
        exit;
    }

    $discogsUrl = "https://api.discogs.com/database/search?q=" . urlencode($query) . "&type=release&format=Single";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $discogsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AteliersCanopeeApp/1.0');

    // Authentification via Header en utilisant les clés récupérées
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Discogs key=$key, secret=$secret",
        "Content-Type: application/json" // Ajouté pour la cohérence
    ]);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Peut être nécessaire dans certains environnements pour les SSL

    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        // Lever une exception plus informative en cas d'erreur HTTP
        throw new Exception("Error calling Discogs API: HTTP " . $httpCode . " - " . $output);
    }

    $data = json_decode($output, true);
    $covers = [];
    $count = 0;
    
    // Parcourir les résultats et extraire les informations pertinentes pour les covers
    // On peut limiter le nombre de covers à retourner, par exemple les 5 premières
    if (isset($data['results']) && is_array($data['results'])) {
        foreach ($data['results'] as $result) {
            if ($count >= 5) { // Limiter à 5 covers
                break;
            }
            // S'assurer que 'cover_image' existe et que l'année est présente pour être pertinent
            if (!empty($result['cover_image']) && !empty($result['year'])) {
                $covers[] = [
                    'url' => $result['cover_image'],
                    'title' => $result['title'] ?? 'N/A',
                    'year' => $result['year'],
                    'artist' => $result['artist'] ?? 'N/A' // Peut-être récupérer l'artiste ici aussi
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