<?php
/**
 * AJAX HANDLER : params_ajax.php
 * Fonctions de rendu pour les appels asynchrones de l'administration.
 */

function handleDiagnostic($adminService, $mysqli)
{
    echo "<h4><span class='glyphicon glyphicon-wrench'></span> Diagnostic du serveur</h4>";
    echo "<div class='well diag-output-well'>";
    echo "<strong>PHP Version :</strong> " . PHP_VERSION . "<br>";
    echo "<strong>Memory Limit :</strong> " . ini_get('memory_limit') . "<br>";
    
    echo "<hr><strong>Extensions :</strong><br>";
    foreach (['mbstring', 'gd', 'mysqli', 'zlib', 'iconv'] as $ext) {
        echo ($ext . ": " . (extension_loaded($ext) ? "✅ OK" : "❌ MANQUANTE")) . "<br>";
    }

    echo "<hr><strong>Permissions Dossiers :</strong><br>";
    $songbooksDir = (defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR : dirname(__DIR__, 2) . '/data') . '/songbooks/';
    $chansonsDir = (defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR : dirname(__DIR__, 2) . '/data') . '/chansons/';
    $migrationsDir = (defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR : dirname(__DIR__, 2) . '/data') . '/database/migrations/';

    $dossiers = [
        'Songbooks' => $songbooksDir,
        'Chansons' => $chansonsDir,
        'Migrations' => $migrationsDir
    ];
    foreach ($dossiers as $nom => $path) {
        if (is_dir($path)) {
            echo "$nom : " . (is_writable($path) ? "✅ Éscriptible" : "❌ LECTURE SEULE") . " <small>($path)</small><br>";
        } else {
            echo "$nom : ❌ INTROUVABLE <small>($path)</small><br>";
        }
    }

    echo "<hr><strong>Migrations BDD :</strong><br>";
    $status = $adminService->getMigrationsStatus();
    $pending = 0;
    foreach ($status as $mig) {
        if (!$mig['played']) {
            echo "⏳ En attente : " . $mig['name'] . " <br>";
            $pending++;
        } else {
            echo "✅ Appliquee : " . $mig['name'] . " <br>";
        }
    }
    if ($pending > 0) {
        echo "<br><button type='button' id='btnRunMigDj' class='btn btn-xs btn-primary'>Appliquer les $pending migrations</button>";
    } else {
        echo "<em>Toute la base est a jour. 🎸</em>";
    }
    echo "</div>";
}
