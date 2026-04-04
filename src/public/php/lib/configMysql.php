<?php
require_once __DIR__ .'/FichierIni.php';
require_once __DIR__ .'/mysql.php';

// --- INCLUSION DE L'AUTOLOADER MAISON (Django) ---
require_once dirname(__DIR__, 3) . '/autoload.php';

// --- CHEMIN DU DOSSIER PHP (Django) ---
if (!defined('PHP_DIR')) {
    define('PHP_DIR', dirname(__DIR__));
}

if (!isset($configMysql)) {
    $configMysql = TRUE;

    // --- STRATÉGIE DE CONNEXION (Django Style) ---
    // 1. On cherche en priorité les variables d'environnement (Docker / PHPUnit Bootstrap)
    $monserveur = $_ENV['DATABASE_HOST'] ?? $_SERVER['DATABASE_HOST'] ?? null;
    $mabase = $_ENV['DATABASE_NAME'] ?? $_SERVER['DATABASE_NAME'] ?? null;
    $LOGIN = $_ENV['DATABASE_USER'] ?? $_SERVER['DATABASE_USER'] ?? null;
    $MOTDEPASSE = $_ENV['DATABASE_PASSWORD'] ?? $_SERVER['DATABASE_PASSWORD'] ?? null;

    // 2. Si non trouvées, on se rabat sur les fichiers .ini
    if (!$monserveur) {
        if (defined('PHPUNIT_RUNNING') && PHPUNIT_RUNNING) {
            $fichier = dirname(__DIR__, 3) . "/data/conf/params_test.ini";
        } else {
            $fichier = dirname(__DIR__, 3) . "/data/conf/params.ini";
        }

        if (file_exists($fichier)) {
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

    $mysqli = new mysqli($monserveur, $LOGIN, $MOTDEPASSE, $mabase);
    
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

    // === AUTO-MIGRATION BY DJANGO (Correctif colonne manquante) ===
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
    if (isset($ini_objet)) {
        $_SESSION['titreSite'] = $ini_objet->m_valeur("titreSite", "general");
        $_SESSION['logoSite'] = $ini_objet->m_valeur("logoSite", "general");
        $_SESSION['loginParam'] = $ini_objet->m_valeur("loginParam", "general");
    }
}
//	echo "connexion : $idconnect";
//	return($idconnect);

function convertitDateJJMMAAAAversMySql($date)
{
    // On convertit la date au format mysql : "JJ/MM/AAAA" devient "AAAA-MM-JJ"
    // echo "Ancienne date : $date ";
    $date = explode('/', $date);
    $new_date = $date[2] . '-' . $date[1] . '-' . $date[0];
    // echo " , New date : " . $new_date . "<br>";
    return $new_date;
}

function convertitDateMySqlVersJJMMAAAA($date)
{
    // On vérifie que la date est au format MySQL : "AAAA-MM-JJ"
    $date = explode('-', $date);

    // On s'assure qu'on a bien trois éléments
    if (count($date) === 3) {
        $new_date = $date[2] . '/' . $date[1] . '/' . $date[0];
        return $new_date;
    } else {
        // Gérer le cas où la date n'est pas au format attendu
        return null; // ou une exception, selon vos besoins
    }
}
