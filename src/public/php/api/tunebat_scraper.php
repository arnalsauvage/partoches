<?php
header("Content-Type: application/json; charset=UTF-8");

// Configuration pour la recherche Google et le scraping
// Utilisation d'un User-Agent pour simuler un navigateur
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0'); // Un User-Agent de navigateur standard pour le scraping

/**
 * Fonction pour effectuer une requête cURL.
 * @param string $url L'URL à interroger.
 * @param array $headers Options d'en-têtes supplémentaires.
 * @return string|false La réponse HTML ou false en cas d'erreur.
 */
function fetchUrl(string $url, array $headers = []): string|false
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Peut être nécessaire pour certains sites
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Suivre les redirections
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Limiter les redirections
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || $html === false) {
        error_log("cURL Error: HTTP Code $httpCode for $url");
        return false;
    }
    return $html;
}

/**
 * Recherche le BPM pour un artiste et un titre donnés en scrapant Google et Tunebat.
 * @param string $artist Nom de l'artiste.
 * @param string $title Titre de la chanson.
 * @return array Résultat au format ['tempo' => int] ou ['error' => string].
 */
function searchBpm(string $artist, string $title): array
{
    $query = urlencode($title) . " " . urlencode($artist) . " site:tunebat.com";
    $googleSearchUrl = "https://www.google.com/search?q=" . $query;

    // 1. Chercher le lien Tunebat via Google
    $googleHtml = fetchUrl($googleSearchUrl);
    if (!$googleHtml) {
        return ['error' => 'Impossible de rechercher sur Google.'];
    }

    $tunebatUrl = null;
    // Regex pour trouver le premier lien Tunebat dans les résultats Google
    // Note : Cette regex est fragile et peut casser si Google change son HTML.
    if (preg_match('/<a href="([^"]+)"[^>]*>.*?<b><div[^>]*>Tunebat<\/div><\/b>/i', $googleHtml, $matches)) {
        $tunebatUrl = $matches[1];
        // Nettoyage simple pour s'assurer que l'URL est absolue et correcte
        if (strpos($tunebatUrl, 'http') !== 0) {
            // Tenter de construire une URL absolue si elle est relative (peu probable pour Google mais par sécurité)
            // Ici, on assume que Google renvoie des URLs absolues.
            // Si la regex n'est pas parfaite et donne un chemin relatif, il faudrait le corriger.
        }
    }

    if (!$tunebatUrl) {
        return ['error' => 'Aucun lien Tunebat trouvé sur Google pour cette recherche.'];
    }

    // 2. Scraper la page Tunebat trouvée
    $tunebatHtml = fetchUrl($tunebatUrl);
    if (!$tunebatHtml) {
        return ['error' => "Impossible de récupérer la page Tunebat : $tunebatUrl"];
    }

    // Regex pour trouver le BPM sur la page Tunebat
    // Note : Cette regex est également fragile et dépend de la structure HTML de Tunebat.
    if (preg_match('/<div class="bpm">(\d+)<\/div>/i', $tunebatHtml, $bpmMatches)) {
        if (isset($bpmMatches[1]) && is_numeric($bpmMatches[1])) {
            return ['tempo' => (int)$bpmMatches[1]];
        }
    }

    return ['error' => 'BPM non trouvé sur la page Tunebat.'];
}

// --- Traitement de la requête ---
$artist = $_GET['artist'] ?? '';
$title = $_GET['title'] ?? '';

if (empty($artist) || empty($title)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Artist and title are required.']);
    exit;
}

// On utilise la fonction de recherche pour obtenir le BPM
$result = searchBpm($artist, $title);

if (isset($result['tempo'])) {
    echo json_encode(['tempo' => $result['tempo']]);
} else {
    http_response_code(404); // Not Found or Error
    echo json_encode(['error' => $result['error'] ?? 'Unknown error during BPM search.']);
}
?>
