<?php
/**
 * Script de diagnostic pour la génération de Songbooks en production.
 */
header('Content-Type: text/plain; charset=utf-8');

echo "--- DIAGNOSTIC PARTOCHES ---\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";

echo "\n--- EXTENSIONS ---\n";
$extensions = ['mbstring', 'gd', 'mysqli', 'zlib', 'iconv'];
foreach ($extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "✅ Installée" : "❌ MANQUANTE") . "\n";
}

echo "\n--- DOSSIERS DATA (Permissions) ---\n";
$dossiers = [
    '../src/public/data/songbooks/',
    '../src/public/data/chansons/'
];
foreach ($dossiers as $d) {
    if (is_dir($d)) {
        echo "$d: ✅ Existe, " . (is_writable($d) ? "✅ Éscriptible" : "❌ NON ÉSCRIPTIBLE") . "\n";
    } else {
        echo "$d: ❌ INTROUVABLE (Vérifiez le chemin)\n";
    }
}

echo "\n--- TEST ÉCRITURE ---\n";
$testFile = '../src/public/data/songbooks/test_write.txt';
if (@file_put_contents($testFile, "test")) {
    echo "Test écriture: ✅ Réussi\n";
    @unlink($testFile);
} else {
    echo "Test écriture: ❌ ÉCHOUÉ\n";
}

echo "\n--- FIN DIAGNOSTIC ---\n";
