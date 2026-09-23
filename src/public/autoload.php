<?php
/**
 * Autoloader personnalisé (Django Style)
 * Centralisation des chemins absolus (compatible local et déploiement FTP Prod)
 */

if (!defined('PUBLIC_DIR')) {
    define('PUBLIC_DIR', __DIR__);
}
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', file_exists(dirname(__DIR__) . '/data/conf/params.ini') ? dirname(__DIR__) : PUBLIC_DIR);
}
if (!defined('VENDOR_DIR')) {
    define('VENDOR_DIR', PUBLIC_DIR . '/vendor');
    define('PUBLIC_URL', '');
    define('VENDOR_URL', '');
}
if (!defined('DATA_DIR')) {
    define('DATA_DIR', is_dir(ROOT_DIR . '/data') ? ROOT_DIR . '/data' : PUBLIC_DIR . '/data');
}
if (!defined('PUBLIC_DATA_DIR')) {
    define('PUBLIC_DATA_DIR', PUBLIC_DIR . '/data');
}
if (!defined('CONF_DIR')) {
    define('CONF_DIR', DATA_DIR . '/conf');
}
if (!defined('PHP_DIR')) {
    define('PHP_DIR', PUBLIC_DIR . '/php');
}
if (!defined('LIB_DIR')) {
    define('LIB_DIR', PHP_DIR . '/lib');
}

// --- DOSSIERS DE DONNÉES (Django - Chemins Absolus) ---
$_DOSSIER_DATA = PUBLIC_DATA_DIR . '/';
$_DOSSIER_CHANSONS = PUBLIC_DATA_DIR . '/chansons/';
$_DOSSIER_SONGBOOKS = PUBLIC_DATA_DIR . '/songbooks/';
$_DOSSIER_UTILISATEURS = PUBLIC_DATA_DIR . '/utilisateurs/';
$_DOSSIER_LOGS = DATA_DIR . '/logs/';

// --- ENREGISTREMENT DE L'AUTOLOADER ---
spl_autoload_register(function ($class) {
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
    
    $fallbackFile = PHP_DIR . '/' . $class . '.php';
    if (file_exists($fallbackFile)) {
        require_once $fallbackFile;
    }
});

// --- INITIALISATION DE L'ENVIRONNEMENT ---
if (!isset($FichierUtilsSi) && file_exists(LIB_DIR . '/utilssi.php')) {
    require_once LIB_DIR . '/utilssi.php';
}
