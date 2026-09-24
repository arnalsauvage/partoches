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

echo "\n=== 2. CHECK DISK DIRECTORY STRUCTURE ON PROD ===\n";
echo "PUBLIC_DIR: " . PUBLIC_DIR . "\n";
echo "ROOT_DIR: " . ROOT_DIR . "\n";
echo "DATA_DIR: " . DATA_DIR . "\n";
echo "PUBLIC_DATA_DIR: " . PUBLIC_DATA_DIR . "\n";

$publicItems = glob(PUBLIC_DIR . '/*');
echo "Items in PUBLIC_DIR (" . count($publicItems) . " items):\n";
foreach (array_slice($publicItems, 0, 40) as $item) {
    $type = is_dir($item) ? "[DIR]" : "[FILE]";
    echo "   $type " . basename($item) . "\n";
}

if (is_dir(PUBLIC_DIR . '/data')) {
    echo "\nItems in PUBLIC_DIR/data:\n";
    $dataItems = glob(PUBLIC_DIR . '/data/*');
    foreach ($dataItems as $item) {
        $type = is_dir($item) ? "[DIR]" : "[FILE]";
        echo "   $type " . basename($item) . "\n";
    }
} else {
    echo "\nPUBLIC_DIR/data IS NOT A DIRECTORY!\n";
}

if (is_dir(dirname(PUBLIC_DIR) . '/data')) {
    echo "\nItems in parent data dir (" . dirname(PUBLIC_DIR) . "/data):\n";
    $parentDataItems = glob(dirname(PUBLIC_DIR) . '/data/*');
    foreach ($parentDataItems as $item) {
        $type = is_dir($item) ? "[DIR]" : "[FILE]";
        echo "   $type " . basename($item) . "\n";
    }
}


