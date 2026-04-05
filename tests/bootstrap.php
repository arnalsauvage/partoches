<?php
/**
 * Bootstrap pour les tests PHPUnit (Django Style)
 * Version All-In-One (src/)
 */

// On définit que PHPUnit tourne
if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

// On force la session AVANT toute inclusion (pour PHPUnit)
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

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
