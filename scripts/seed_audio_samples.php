<?php
require_once __DIR__ . '/../src/public/autoload.php';
require_once __DIR__ . '/../src/public/php/lib/configMysql.php';
require_once __DIR__ . '/../src/public/php/media/MediaRepository.php';

$db = $_SESSION['mysql'];

// 1. Inserer un lien audio dans lienurl si absent
$resLien = $db->query("SELECT id FROM lienurl WHERE url LIKE '%.mp3%' LIMIT 1");
if ($resLien && $resLien->num_rows === 0) {
    $db->query("INSERT INTO lienurl (nomTable, idTable, url, type, description, date, idUser) 
                VALUES ('chanson', 1, 'https://example.com/chanson_test.mp3', 'audio', 'Enregistrement audio MP3', NOW(), 1)");
    echo "Lien audio exemple inséré dans lienurl.\n";
}

// 2. Inserer un media audio dans media si absent
$resMedia = $db->query("SELECT id FROM media WHERE type IN ('mp3', 'audio', 'm4a', 'aac') LIMIT 1");
if ($resMedia && $resMedia->num_rows === 0) {
    $db->query("INSERT INTO media (type, titre, image, auteur, lien, description, tags, datePub, hits)
                VALUES ('mp3', 'Ma Chanson Audio (MP3)', 'defaut.png', 1, 'https://example.com/demo.mp3', 'Extrait audio MP3 Canopée', 'mp3 audio test', NOW(), 0)");
    echo "Média MP3 exemple inséré dans media.\n";
}

echo "Base de données prête avec données de test audio.\n";
