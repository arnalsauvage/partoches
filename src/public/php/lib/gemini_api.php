<?php
// ATTENTION : Ce fichier contient ta logique d'appel à l'API Gemini.
// Assure-toi que ta clé API n'est JAMAIS exposée publiquement.
// Idéalement, utilise une variable d'environnement ou un fichier de configuration
// situé en dehors du répertoire public de ton serveur web.

// Charger la clé API depuis params.ini en utilisant FichierIni
// Assure-toi que FichierIni est disponible (il devrait l'être via les includes globaux ou vérifier son include)
require_once __DIR__ . '/../lib/FichierIni.php'; // Chemin relatif vers FichierIni
$ini_objet = new FichierIni();
// Chemin vers params.ini (relatif au dossier lib/)
$configPath = __DIR__ . '/../../conf/params.ini'; 
$ini_objet->m_load_fichier($configPath);

// Récupère la clé depuis la section [gemini] dans params.ini
$apiKey = $ini_objet->m_valeur('GEMINI_API_KEY', 'gemini'); 

// Fallback si jamais la clé n'est pas dans le ini (optionnel, mais bon pour la robustesse)
if (!$apiKey) {
    $apiKey = getenv('GEMINI_API_KEY');
}

// Le prompt que tu veux envoyer au modèle
$prompt = "Raconte-moi une blague sur les ordinateurs.";

// L'URL de l'API pour le modèle que nous avons trouvé
// Note : J'utilise v1beta ici car c'est ce qui était dans l'exemple, mais la version stable est souvent v1.
// Vérifie la documentation officielle de Google pour la version la plus à jour et stable.
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $apiKey;

// Les données à envoyer, au format JSON
$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ]
];
$jsonData = json_encode($data);

// Initialisation de cURL
$ch = curl_init($url);

// Configuration des options de cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Pour retourner la réponse au lieu de l'afficher
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); // Spécifier le type de contenu
curl_setopt($ch, CURLOPT_POST, true); // C'est une requête POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Les données à envoyer

// Exécution de la requête
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Récupère le code de statut HTTP
curl_close($ch);

// Traitement de la réponse
if ($httpCode !== 200) {
    echo "Oups, l'API a retourné une erreur (Code: $httpCode) : <br>";
    echo htmlspecialchars($response); // htmlspecialchars pour la sécurité
} else {
    $responseData = json_decode($response, true);
    
    // On va chercher le texte en suivant la structure que nous avons découverte
    // Assure-toi que $responseData['candidates'] et ses sous-éléments existent avant d'y accéder
    $joke = 'Impossible de récupérer la blague.';
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $joke = $responseData['candidates'][0]['content']['parts'][0]['text'];
    }
    
    // Et voilà !
    echo nl2br(htmlspecialchars($joke)); // nl2br pour les sauts de ligne, htmlspecialchars pour la sécurité
}

?>