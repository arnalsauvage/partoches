<?php
require_once dirname(__DIR__, 2) . '/autoload.php';
require_once LIB_DIR . '/configMysql.php';

header('Content-Type: text/plain; charset=utf-8');

$db = $_SESSION['mysql'];

echo "=== 1. CHECK CHANSON COVER COLUMN IN PROD DB ===\n";
$res = $db->query("SELECT id, nom, cover FROM chanson WHERE cover IS NOT NULL AND cover != '' LIMIT 30");
if ($res) {
    echo "Found " . $res->num_rows . " chansons with non-empty cover in DB:\n";
    while ($row = $res->fetch_assoc()) {
        echo "ID {$row['id']} ('{$row['nom']}'): cover = '{$row['cover']}'\n";
    }
} else {
    echo "Query error: " . $db->error . "\n";
}

echo "\n=== 2. CHECK DOCUMENT TABLE FOR IMAGES IN PROD DB ===\n";
$res2 = $db->query("SELECT * FROM document WHERE (nom LIKE '%.jpg' OR nom LIKE '%.png' OR nom LIKE '%.webp' OR nom LIKE '%.jpeg') LIMIT 30");
if ($res2) {
    echo "Found " . $res2->num_rows . " image documents in document table:\n";
    while ($row = $res2->fetch_assoc()) {
        echo "Doc ID {$row['id']}: nom='{$row['nom']}', nomTable='{$row['nomTable']}', idTable='{$row['idTable']}', version='{$row['version']}'\n";
    }
} else {
    echo "Query error: " . $db->error . "\n";
}

echo "\n=== 3. CHECK PHYSICAL FILES IN data/chansons/ ON PROD DISK ===\n";
$baseData = PUBLIC_DATA_DIR . '/chansons';
echo "PUBLIC_DATA_DIR chansons path: $baseData\n";
if (is_dir($baseData)) {
    $folders = glob($baseData . '/*', GLOB_ONLYDIR);
    echo "Found " . count($folders) . " song folders in data/chansons/\n";
    $imgCount = 0;
    foreach (array_slice($folders, 0, 30) as $folder) {
        $id = basename($folder);
        $files = glob($folder . '/*.{jpg,jpeg,png,webp,JPG,PNG}', GLOB_BRACE);
        if (!empty($files)) {
            echo "Folder $id has images:\n";
            foreach ($files as $f) {
                echo "   - " . basename($f) . " (" . filesize($f) . " bytes)\n";
                $imgCount++;
            }
        }
    }
    echo "Sample checked. Total image files found in first 30 folders: $imgCount\n";
} else {
    echo "Directory data/chansons does not exist or is not readable!\n";
}
