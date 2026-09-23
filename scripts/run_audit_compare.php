<?php
/**
 * Script CLI : Comparaison d'audit Prod vs Local
 */

$racine = dirname(__DIR__); // /var/www/html
$extensions_autorisees = ['php', 'css', 'js', 'html', 'phtml', 'sql', 'ini'];
$fichiers_a_ignorer = ['.git', '.idea', 'vendor', 'node_modules', 'rendered_html', 'cypress', 'tests', 'documentation'];

// 1. Générer le rapport local
$localData = [];
$it = new RecursiveDirectoryIterator($racine);
foreach (new RecursiveIteratorIterator($it) as $file) {
    if ($file->isDir()) continue;
    
    $relative_path = str_replace($racine . DIRECTORY_SEPARATOR, '', $file->getPathname());
    $relative_path = str_replace('\\', '/', $relative_path); // Normalisation des slashs
    
    // Normaliser les préfixes de chemins si nécessaire (ex: src/public/ -> public/)
    $normalized_path = preg_replace('/^src\//', '', $relative_path);

    foreach ($fichiers_a_ignorer as $ignore) {
        if (str_contains($normalized_path, $ignore)) continue 2;
    }

    $ext = strtolower(pathinfo($normalized_path, PATHINFO_EXTENSION));
    if (in_array($ext, $extensions_autorisees)) {
        $localData[$normalized_path] = [
            'taille' => $file->getSize(),
            'hash'   => md5_file($file->getPathname()),
            'modif'  => date("Y-m-d H:i:s", $file->getMTime()),
            'real_path' => $relative_path
        ];
    }
}

// 2. Lire le CSV de PROD
$prodCsvFile = $racine . '/src/data/temp/audit_prod.csv';
$prodData = [];

if (($handle = fopen($prodCsvFile, "r")) !== FALSE) {
    $headers = fgetcsv($handle, 1000, ",");
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (count($row) < 3) continue;
        $path = str_replace('\\', '/', trim($row[0]));
        $normalized_path = preg_replace('/^src\//', '', $path);
        
        $prodData[$normalized_path] = [
            'taille' => trim($row[1]),
            'hash'   => trim($row[2]),
            'modif'  => trim($row[3] ?? '')
        ];
    }
    fclose($handle);
} else {
    die("Impossible de lire le fichier de PROD : $prodCsvFile\n");
}

// 3. Comparer
$diff = [
    'ajouter'   => [], // Présent en local, absent en prod
    'maj'       => [], // Présent aux deux endroits, mais hash différent
    'supprimer' => []  // Présent en prod, absent en local
];

foreach ($localData as $path => $info) {
    if (!isset($prodData[$path])) {
        $diff['ajouter'][] = $path;
    } elseif ($prodData[$path]['hash'] !== $info['hash']) {
        $diff['maj'][] = [
            'path' => $path,
            'local_hash' => $info['hash'],
            'prod_hash'  => $prodData[$path]['hash'],
            'local_modif' => $info['modif'],
            'prod_modif'  => $prodData[$path]['modif']
        ];
    }
}

foreach ($prodData as $path => $info) {
    if (!isset($localData[$path])) {
        $diff['supprimer'][] = $path;
    }
}

// 4. Affichage du rapport
echo "==================================================\n";
echo "🕵️‍♂️ RAPPORT DE COMPARAISON AUDIT (LOCAL VS PROD)\n";
echo "==================================================\n\n";

echo "➕ FICHIERS À AJOUTER EN PROD (" . count($diff['ajouter']) . ") :\n";
foreach ($diff['ajouter'] as $f) {
    echo "  - $f\n";
}
echo "\n";

echo "🔄 FICHIERS MODIFIÉS À METTRE À JOUR EN PROD (" . count($diff['maj']) . ") :\n";
foreach ($diff['maj'] as $item) {
    echo "  - {$item['path']} (Local: {$item['local_modif']} vs Prod: {$item['prod_modif']})\n";
}
echo "\n";

echo "❌ FICHIERS EN PROD ABSENTS EN LOCAL (" . count($diff['supprimer']) . ") :\n";
foreach ($diff['supprimer'] as $f) {
    echo "  - $f\n";
}
echo "\n==================================================\n";
