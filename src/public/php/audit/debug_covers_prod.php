<?php
require_once dirname(__DIR__, 2) . '/autoload.php';
require_once LIB_DIR . '/configMysql.php';

header('Content-Type: text/plain; charset=utf-8');

$db = $_SESSION['mysql'];

echo "=== 1. CHECK CHANSON COVER COLUMN & PHYSICAL FILE EXISTENCE ===\n";
$res = $db->query("SELECT id, nom, cover FROM chanson WHERE cover IS NOT NULL AND cover != '' LIMIT 50");
if ($res) {
    echo "Found " . $res->num_rows . " chansons with non-empty cover in DB:\n";
    while ($row = $res->fetch_assoc()) {
        $id = $row['id'];
        $cover = $row['cover'];
        $nom = $row['nom'];
        
        $cleanName = basename($cover);
        $directPath = PUBLIC_DATA_DIR . "/chansons/$id/$cleanName";
        $exists = file_exists($directPath) ? "YES" : "NO";
        
        echo "ID $id ('$nom'): cover DB='$cover', cleanName='$cleanName'\n";
        echo "   -> DirectPath: $directPath | Exists: $exists\n";

        // Check folder contents
        $folder = PUBLIC_DATA_DIR . "/chansons/$id";
        if (is_dir($folder)) {
            $files = glob($folder . '/*');
            $fileNames = array_map('basename', $files ?: []);
            echo "   -> Actual files in folder $id: " . implode(', ', $fileNames) . "\n";
        } else {
            echo "   -> Folder $folder DOES NOT EXIST!\n";
        }
    }
} else {
    echo "Query error: " . $db->error . "\n";
}

echo "\n=== 2. CHECK DOCUMENT TABLE IMAGES ===\n";
$res2 = $db->query("SELECT id, nom, nomTable, idTable, version FROM document WHERE nomTable='chanson' AND (nom LIKE '%.jpg' OR nom LIKE '%.png' OR nom LIKE '%.webp' OR nom LIKE '%.jpeg') LIMIT 15");
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        $idSong = $row['idTable'];
        $nomDoc = $row['nom'];
        $verDoc = $row['version'];
        
        $folder = PUBLIC_DATA_DIR . "/chansons/$idSong";
        $files = is_dir($folder) ? glob($folder . '/*') : [];
        $fileNames = array_map('basename', $files ?: []);
        echo "Doc ID {$row['id']} (chanson $idSong): nom='$nomDoc', version='$verDoc'\n";
        echo "   -> Files in folder $idSong: " . implode(', ', $fileNames) . "\n";
    }
}

