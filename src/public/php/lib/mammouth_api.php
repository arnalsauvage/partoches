<?php
// Script PHP pour API Mammouth.ai (compatible OpenAI)
// Charge la clé depuis params.ini (section [mammouth] ou [gemini] fallback)

require_once __DIR__ . '/../lib/FichierIni.php';
$ini_objet = new FichierIni();
$configPath = realpath(__DIR__ . '/../../conf/params.ini');
if (!file_exists($configPath)) {
    die("Erreur : params.ini introuvable à $configPath");
}
$ini_objet->m_load_fichier($configPath);

// Priorité : clé Mammouth (ajoutez MAMMOUTH_API_KEY=... dans [mammouth] de params.ini)
$apiKey = trim($ini_objet->m_valeur('MAMMOUTH_API_KEY', 'mammouth') ?? '');
if (!$apiKey) {
    // Fallback Gemini (si pas de section mammouth)
    $apiKey = trim($ini_objet->m_valeur('GEMINI_API_KEY', 'gemini') ?? '');
}
if (!$apiKey) {
    $apiKey = trim(getenv('MAMMOUTH_API_KEY') ?? getenv('GEMINI_API_KEY') ?? '');
}
if (!$apiKey || strlen($apiKey) < 20) {
    die("Clé API manquante/invalide. Ajoutez MAMMOUTH_API_KEY dans params.ini [mammouth] ou env.");
}

// Le prompt
$prompt = "Raconte-moi une blague drôle sur les ordinateurs.";

// URL Mammouth (OpenAI-compatible : /v1/chat/completions)
$url = 'https://api.mammouth.ai/v1/chat/completions';

// Données JSON (format OpenAI standard)
$data = [    'model' => 'gpt-5-mini', // Ou 'gemini-2.5-flash', 'claude-3.5-sonnet' etc. (liste : docs.mammouth.ai)
    'messages' => [        ['role' => 'user', 'content' => $prompt]
    ],
    'max_tokens' => 600, // Limite réponse (économise crédits)
    'temperature' => 0.8
];
$jsonData = json_encode($data);

// cURL
$ch = curl_init($url);
curl_setopt_array($ch, [    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey  // Bearer au lieu de ?key= (OpenAI style)
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $jsonData
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Traitement

// Traitement avec DEBUG
if ($httpCode !== 200) {
    echo "Erreur API (Code: $httpCode) : <br>";
    echo htmlspecialchars($response);
} else {
    echo "<h3>DEBUG - Réponse brute :</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";

    $responseData = json_decode($response, true);

    echo "<h3>DEBUG - Structure décodée :</h3>";
    echo "<pre>";
    var_dump($responseData);
    echo "</pre>";

    $joke = 'Impossible de récupérer la blague.';

    // Test du chemin d'accès
    if (isset($responseData['choices'][0]['message']['content'])) {
        $joke = trim($responseData['choices'][0]['message']['content']);
        echo "<h3>✅ Blague trouvée :</h3>";
    } else {
        echo "<h3>❌ Chemin choices[0]['message']['content'] introuvable</h3>";
    }

    echo nl2br(htmlspecialchars($joke));
}
