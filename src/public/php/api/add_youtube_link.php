<?php
/**
 * API Webhook : Ajout automatique d'un lien YouTube sur une chanson
 * Conforme à l'architecture Django/Partoches
 */
header("Content-Type: application/json; charset=UTF-8");

$autoload = file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

require_once dirname(__DIR__) . "/lib/configMysql.php";
require_once dirname(__DIR__) . "/lib/utilssi.php";
require_once dirname(__DIR__) . "/liens/LienUrl.php";
require_once dirname(__DIR__) . "/chanson/Chanson.php";

// Définition de la clé API secrète
$expectedToken = "canopee-tutos-secret-key-2026";
if (isset($conf) && is_array($conf) && !empty($conf['api']['secret_token'])) {
    $expectedToken = $conf['api']['secret_token'];
}

try {
    // Vérification de la méthode HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    $data = [];
    if ($method === 'POST') {
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true) ?? $_POST;
    } else {
        $data = $_GET;
    }

    // Vérification du token
    $providedToken = $_SERVER['HTTP_X_API_KEY'] ?? ($data['token'] ?? '');
    if (empty($providedToken) || $providedToken !== $expectedToken) {
        http_response_code(401);
        echo json_encode(['error' => 'Non autorisé : token invalide ou manquant.']);
        exit;
    }

    // Action spéciale : Normalisation de tous les anciens 'video' vers 'Vidéo'
    if (isset($data['action']) && $data['action'] === 'normalize_types') {
        $sql = "UPDATE lienurl SET type = 'Vidéo' WHERE LOWER(type) = 'video'";
        $_SESSION['mysql']->query($sql);
        $affected = $_SESSION['mysql']->affected_rows;
        echo json_encode([
            'success' => true,
            'message' => "Normalisation terminée : $affected ligne(s) mise(s) à jour vers 'Vidéo'."
        ]);
        exit;
    }

    $idChanson = isset($data['idChanson']) ? (int)$data['idChanson'] : (isset($data['id']) ? (int)$data['id'] : 0);
    $url = trim($data['url'] ?? '');
    $description = trim($data['description'] ?? '');
    $type = !empty($data['type']) ? trim($data['type']) : 'Vidéo';

    if ($idChanson <= 0 || empty($url)) {
        http_response_code(400);
        echo json_encode(['error' => 'Paramètres idChanson et url obligatoires.']);
        exit;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format d\'URL invalide.']);
        exit;
    }

    // Vérifier l'existence de la chanson
    $chanson = Chanson::load($idChanson);
    if (!$chanson || $chanson->getId() <= 0) {
        http_response_code(404);
        echo json_encode(['error' => "Chanson ID $idChanson introuvable."]);
        exit;
    }

    // Vérifier si le lien existe déjà
    $existant = LienUrl::chercheLienurlUrlTableId($url, 'chanson', $idChanson);
    if ($existant != NULL) {
        // Mise à jour du type et de la description existants
        $idLien = is_array($existant) ? ($existant['id'] ?? $existant[0]) : 0;
        if ($idLien > 0) {
            LienUrl::modifieLienurl($idLien, $url, $type, $description, 'chanson', $idChanson, date('d/m/Y'), 1, 0);
        }
        echo json_encode([
            'success' => true,
            'message' => 'Lien déjà présent : type et description actualisés avec succès.',
            'idChanson' => $idChanson,
            'url' => $url,
            'type' => $type,
            'description' => $description,
            'mis_a_jour' => true
        ]);
        exit;
    }

    // Créer le lien avec le type 'Vidéo' (V majuscule et accent)
    $res = LienUrl::creeLienurl(
        $url,
        $type,
        $description,
        'chanson',
        $idChanson,
        date('d/m/Y'),
        1,
        0
    );

    if ($res) {
        echo json_encode([
            'success' => true,
            'message' => 'Lien Vidéo associé avec succès.',
            'idChanson' => $idChanson,
            'titreChanson' => $chanson->getNom(),
            'url' => $url,
            'type' => $type,
            'description' => $description
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Échec lors de la création du lien dans la base de données.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
