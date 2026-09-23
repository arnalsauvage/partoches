<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/public/php/utilisateur/Utilisateur.php';

class UtilisateurTest extends TestCase
{
    public function testInstanciationUtilisateur()
    {
        $u = new Utilisateur(1);
        $this->assertEquals(1, $u->getId());
        $this->assertNotEmpty($u->getLogin());
    }

    public function testChercheUtilisateur()
    {
        $donnee = Utilisateur::chercheUtilisateur(1);
        $this->assertNotEquals(0, $donnee);
        $this->assertIsArray($donnee);
    }
}
