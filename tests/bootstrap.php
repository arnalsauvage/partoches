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

// On charge utilssi pour avoir les fonctions globales
// (utilssi est dans src/public/php/lib/)
require_once __DIR__ . '/../src/public/php/lib/utilssi.php';
