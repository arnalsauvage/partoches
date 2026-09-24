<?php
require_once dirname(__DIR__, 2) . '/autoload.php';
require_once LIB_DIR . '/configMysql.php';

header('Content-Type: text/plain; charset=utf-8');

$db = $_SESSION['mysql'];

echo "=== 1. CHECK GIT-TRACKED SONG FOLDERS ON PROD DISK ===\n";
$sampleIds = [77, 78, 80, 100, 104, 765, 768, 772];
foreach ($sampleIds as $id) {
    $folder = PUBLIC_DATA_DIR . "/chansons/$id";
    if (is_dir($folder)) {
        $files = glob($folder . '/*');
        $fileNames = array_map('basename', $files ?: []);
        echo "Folder $id EXISTS. Files (" . count($fileNames) . "): " . implode(', ', array_slice($fileNames, 0, 10)) . "\n";
    } else {
        echo "Folder $id DOES NOT EXIST!\n";
    }
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

echo "\n=== 3. SEARCHING FOR DATA AND CHANSONS DIRECTORIES ON HOSTINGER ===\n";

$candidates = [
    dirname(PUBLIC_DIR) . '/data',
    dirname(PUBLIC_DIR) . '/chansons',
    dirname(PUBLIC_DIR, 2) . '/data',
    dirname(PUBLIC_DIR, 2) . '/chansons',
    '/home/u715493341/data',
    '/home/u715493341/domains/partoches.canopee-musique.fr/data',
];

foreach ($candidates as $cand) {
    if (is_dir($cand)) {
        echo "FOUND DIR: $cand\n";
        $items = glob($cand . '/*');
        foreach (array_slice($items, 0, 15) as $it) {
            echo "   -> " . basename($it) . "\n";
        }
    } else {
        echo "NOT FOUND: $cand\n";
    }
}

// Let's also search for any 'pochette-Pauvres-Diables-v1.webp' or PDF file on the server using find command if possible or recursively in parent
echo "\n=== 4. PARENT DIR LISTING (" . dirname(PUBLIC_DIR) . ") ===\n";
$parentItems = glob(dirname(PUBLIC_DIR) . '/*');
foreach ($parentItems as $p) {
    $type = is_dir($p) ? "[DIR]" : "[FILE]";
    echo "   $type " . basename($p) . "\n";
}



