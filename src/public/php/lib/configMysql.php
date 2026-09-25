<?php
require_once __DIR__ .'/FichierIni.php';
require_once __DIR__ .'/mysql.php';

// --- INCLUSION DE L'AUTOLOADER MAISON (Django) ---
$autoloaderPaths = [
    dirname(__DIR__, 3) . '/autoload.php',
    dirname(__DIR__, 2) . '/autoload.php',
    dirname(__DIR__, 1) . '/autoload.php',
    __DIR__ . '/autoload.php'
];

foreach ($autoloaderPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

// --- CHEMIN DU DOSSIER PHP (Django) ---
if (!defined('PHP_DIR')) {
    define('PHP_DIR', dirname(__DIR__));
}

if (!isset($configMysql)) {
    $configMysql = TRUE;

    // --- STRATÉGIE DE CONNEXION (Django Style) ---
    // 1. On cherche en priorité les variables d'environnement (Docker / PHPUnit Bootstrap)
    $monserveur = getenv('DATABASE_HOST') ?: ($_ENV['DATABASE_HOST'] ?? $_SERVER['DATABASE_HOST'] ?? null);
    $mabase = getenv('DATABASE_NAME') ?: ($_ENV['DATABASE_NAME'] ?? $_SERVER['DATABASE_NAME'] ?? null);
    $LOGIN = getenv('DATABASE_USER') ?: ($_ENV['DATABASE_USER'] ?? $_SERVER['DATABASE_USER'] ?? null);
    $MOTDEPASSE = getenv('DATABASE_PASSWORD') ?: ($_ENV['DATABASE_PASSWORD'] ?? $_SERVER['DATABASE_PASSWORD'] ?? null);

    // 2. Si non trouvées, on se rabat sur les fichiers .ini
    if (!$monserveur) {
        $iniFiles = [
            (defined('PHPUNIT_RUNNING') && PHPUNIT_RUNNING) ? dirname(__DIR__, 3) . "/data/conf/params_test.ini" : null,
            dirname(__DIR__, 3) . "/data/conf/params.ini",
            dirname(__DIR__, 2) . "/data/conf/params.ini",
            dirname(__DIR__, 2) . "/conf/params.ini",
            dirname(__DIR__, 1) . "/conf/params.ini",
            __DIR__ . "/params.ini"
        ];
        $fichier = null;
        foreach ($iniFiles as $f) {
            if ($f && file_exists($f)) {
                $fichier = $f;
                break;
            }
        }

        if ($fichier && file_exists($fichier)) {
            $ini_objet = new FichierIni ();
            $ini_objet->m_load_fichier($fichier);
            $monserveur = $ini_objet->m_valeur("monServeur", "mysql");
            $mabase = $ini_objet->m_valeur("maBase", "mysql");
            $LOGIN = $ini_objet->m_valeur("login", "mysql");
            $MOTDEPASSE = $ini_objet->m_valeur("motDePasse", "mysql");
        }
    }

    // Valeurs par défaut si tout a échoué
    $monserveur = $monserveur ?: "localhost";
    $mabase = $mabase ?: "dbPartoches";
    $LOGIN = $LOGIN ?: "root";
    $MOTDEPASSE = $MOTDEPASSE ?: "";

    try {
        $mysqli = new mysqli($monserveur, $LOGIN, $MOTDEPASSE, $mabase);
    } catch (Throwable $e) {
        usleep(200000); // 200ms pause
        $mysqli = new mysqli($monserveur, $LOGIN, $MOTDEPASSE, $mabase);
    }
    
    // Gestion du mode Debug (Django)
    if (isset($ini_objet) && $ini_objet->m_valeur("display_errors", "admin") == "1") {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    if ($mysqli->connect_error) {
        die(' Erreur #1 configMysql : Impossible de créer une connexion persistante ! ' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
    }

    // === AUTO-MIGRATION BY DJANGO (Correctif tables & colonnes manquantes) ===
    $mysqli->query("CREATE TABLE IF NOT EXISTS `utilisateur` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `login` varchar(255) NOT NULL,
      `mdp` varchar(255) NOT NULL,
      `prenom` varchar(255) DEFAULT NULL,
      `nom` varchar(255) DEFAULT NULL,
      `image` varchar(255) DEFAULT 'utilisateur/defaut.png',
      `site` varchar(255) DEFAULT NULL,
      `email` varchar(255) DEFAULT NULL,
      `signature` text DEFAULT NULL,
      `dateDernierLogin` date DEFAULT NULL,
      `nbreLogins` int(11) DEFAULT 0,
      `privilege` int(11) DEFAULT 1,
      PRIMARY KEY (`id`),
      UNIQUE KEY `login` (`login`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $res_users = $mysqli->query("SELECT COUNT(*) FROM utilisateur");
    if ($res_users && (int)$res_users->fetch_row()[0] === 0) {
        $mysqli->query("INSERT INTO utilisateur (id, login, mdp, prenom, nom, image, site, email, signature, dateDernierLogin, nbreLogins, privilege) VALUES (1, 'invite', '', 'Invité', 'Visiteur', 'utilisateur/defaut.png', '', 'invite@canopee.fr', '', NOW(), 0, 0)");
    }

    $res_token = $mysqli->query("SHOW COLUMNS FROM utilisateur LIKE 'token_activation'");
    if ($res_token && $res_token->num_rows == 0) {
        $mysqli->query("ALTER TABLE utilisateur ADD COLUMN token_activation VARCHAR(255) NULL DEFAULT NULL AFTER privilege, ADD COLUMN est_actif TINYINT(1) NOT NULL DEFAULT 1 AFTER token_activation");
    }

    $res_django = $mysqli->query("SHOW COLUMNS FROM chanson LIKE 'publication'");
    if ($res_django && $res_django->num_rows == 0) {
        $mysqli->query("ALTER TABLE chanson ADD COLUMN publication TINYINT(1) DEFAULT 1 AFTER cover");
    }
    // ==============================================================

    if ($mysqli->select_db($mabase) == false) {
        $error = "Erreur #2 configMysql : Impossible de selectionner la base !";
        return (0);
    }
    $_SESSION ['mysql'] = $mysqli;

    // === CHARGEMENT DES PARAMÈTRES EN SESSION (Django) ===
    // On charge le fichier ini pour les paramètres de session, même si MySQL est géré par l'ENV
    if (!isset($ini_objet)) {
        $fichier = dirname(__DIR__, 3) . "/data/conf/params.ini";
        if (file_exists($fichier)) {
            $ini_objet = new FichierIni ();
            $ini_objet->m_load_fichier($fichier);
        }
    }

    if (isset($ini_objet)) {
        $_SESSION['titreSite'] = $ini_objet->m_valeur("titreSite", "general");
        $_SESSION['sousTitreSite'] = $ini_objet->m_valeur("sousTitreSite", "general");
        $_SESSION['logoSite'] = $ini_objet->m_valeur("logoSite", "general");
        $_SESSION['loginParam'] = $ini_objet->m_valeur("loginParam", "general");
        $_SESSION['urlSite'] = $ini_objet->m_valeur("urlSite", "general");
        $_SESSION['emailAdmin'] = $ini_objet->m_valeur("EmailAdmin", "general");
    }
}
//	echo "connexion : $idconnect";
//	return($idconnect);

if (!function_exists('convertitDateJJMMAAAAversMySql')) {
    function convertitDateJJMMAAAAversMySql($date)
    {
        // On convertit la date au format mysql : "JJ/MM/AAAA" devient "AAAA-MM-JJ"
        $date = explode('/', $date);
        $new_date = $date[2] . '-' . $date[1] . '-' . $date[0];
        return $new_date;
    }
}

if (!function_exists('convertitDateMySqlVersJJMMAAAA')) {
    function convertitDateMySqlVersJJMMAAAA($date)
    {
        // On vérifie que la date est au format MySQL : "AAAA-MM-JJ"
        $date = explode('-', $date);

        // On s'assure qu'on a bien trois éléments
        if (count($date) === 3) {
            $new_date = $date[2] . '/' . $date[1] . '/' . $date[0];
            return $new_date;
        } else {
            return null;
        }
    }
}
