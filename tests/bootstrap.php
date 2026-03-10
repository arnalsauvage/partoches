<?php
/**
 * Bootstrap pour les tests PHPUnit
 * Version All-In-One (src/)
 */

// On définit que PHPUnit tourne
define('PHPUNIT_RUNNING', true);

// On charge l'autoloader maison (Django Style)
require_once __DIR__ . '/../src/autoload.php';

// On simule un DOCUMENT_ROOT pour que les scripts PHP trouvent leurs billes
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../src/public');

// --- CONFIGURATION BDD POUR DOCKER (PHPUnit) ---
// On surcharge les variables d'environnement pour configMysql.php
$_ENV['DATABASE_HOST'] = 'db';
$_ENV['DATABASE_USER'] = 'root';
$_ENV['DATABASE_PASSWORD'] = 'root';
$_ENV['DATABASE_NAME'] = 'dbPartoches';

// On force la session pour les tests
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// On charge utilssi pour avoir les fonctions globales
require_once __DIR__ . '/../src/public/php/lib/utilssi.php';
