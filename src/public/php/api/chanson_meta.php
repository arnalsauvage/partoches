<?php
/**
 * API : Récupération des métadonnées d'une chanson pour publication vidéo
 */
header("Content-Type: application/json; charset=UTF-8");

$autoload = file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

require_once dirname(__DIR__) . "/lib/configMysql.php";
require_once dirname(__DIR__) . "/chanson/Chanson.php";

$idChanson = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($idChanson <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètre id obligatoire.']);
    exit;
}

try {
    $chanson = Chanson::load($idChanson);
    if (!$chanson || $chanson->getId() <= 0) {
        http_response_code(404);
        echo json_encode(['error' => "Chanson ID $idChanson introuvable."]);
        exit;
    }

    echo json_encode([
        'id' => $chanson->getId(),
        'nom' => $chanson->getNom(),
        'interprete' => $chanson->getInterprete(),
        'annee' => $chanson->getAnnee(),
        'tempo' => $chanson->getTempo(),
        'tonalite' => $chanson->getTonalite(),
        'cover' => $chanson->getCover(),
        'url_fiche' => "https://partoches.canopee-musique.fr/php/chanson/chanson_voir.php?id=" . $chanson->getId()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
