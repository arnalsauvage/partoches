<?php
/**
 * Autoloader personnalisé (Django Style)
 * Version All-In-One : Tout est regroupé dans src/
 */

// --- INITIALISATION DES CONSTANTES GLOBALES ---
// Le dossier où se trouve l'autoloader (src/)
$baseAppDir = __DIR__;

// Définit la constante PHP_DIR (pointant vers src/public/php)
if (!defined('PHP_DIR')) {
    define('PHP_DIR', $baseAppDir . '/public/php');
}

// --- INITIALISATION DE L'ENVIRONNEMENT ---
// On inclut utilssi.php qui gère session_start(), la connexion MySQL, etc.
require_once PHP_DIR . '/lib/utilssi.php';

// --- ENREGISTREMENT DE L'AUTOLOADER ---
spl_autoload_register(function ($class) {
    $baseAppDir = __DIR__;
    
    // Liste des dossiers où tes classes PHP sont rangées
    $subDirs = [
        'chanson', 'document', 'lib', 'liens', 'media', 'navigation', 
        'note', 'playlist', 'songbook', 'strum', 'utilisateur'
    ];

    foreach ($subDirs as $dir) {
        $file = $baseAppDir . '/public/php/' . $dir . '/' . $class . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Cas particulier : si la classe est au niveau supérieur dans php/
    $fallbackFile = $baseAppDir . '/public/php/' . $class . '.php';
    if (file_exists($fallbackFile)) {
        require_once $fallbackFile;
    }
});
