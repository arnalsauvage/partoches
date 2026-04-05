<?php
/**
 * Autoloader personnalisé (Django Style)
 * Centralisation des chemins absolus
 */

// --- INITIALISATION DES CHEMINS ABSOLUS ---
//ROOT_DIR pointe vers le dossier src/
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', __DIR__);
}
if (!defined('DATA_DIR')) {
    define('DATA_DIR', ROOT_DIR . '/data');
}
if (!defined('CONF_DIR')) {
    define('CONF_DIR', DATA_DIR . '/conf');
}
if (!defined('PHP_DIR')) {
    define('PHP_DIR', ROOT_DIR . '/public/php');
}
if (!defined('LIB_DIR')) {
    define('LIB_DIR', PHP_DIR . '/lib');
}

// --- INITIALISATION DE L'ENVIRONNEMENT ---
// On n'inclut utilssi que si nécessaire (évite les conflits en mode PHPUnit)
if (!isset($FichierUtilsSi) && file_exists(LIB_DIR . '/utilssi.php')) {
    require_once LIB_DIR . '/utilssi.php';
}

// --- ENREGISTREMENT DE L'AUTOLOADER ---
spl_autoload_register(function ($class) {
    // Liste des dossiers où tes classes PHP sont rangées
    $subDirs = [
        'chanson', 'document', 'lib', 'liens', 'media', 'navigation', 
        'note', 'playlist', 'songbook', 'strum', 'utilisateur'
    ];

    foreach ($subDirs as $dir) {
        $file = PHP_DIR . '/' . $dir . '/' . $class . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Cas particulier : si la classe est au niveau supérieur dans php/
    $fallbackFile = PHP_DIR . '/' . $class . '.php';
    if (file_exists($fallbackFile)) {
        require_once $fallbackFile;
    }
});
