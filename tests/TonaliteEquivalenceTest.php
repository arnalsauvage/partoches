<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

// Mock de la session si nécessaire pour éviter les erreurs d'inclusion
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../php/lib/utilssi.php";
require_once __DIR__ . "/../php/chanson/chanson.php";

class TonaliteEquivalenceTest extends TestCase
{
    /**
     * Teste les équivalences pour Bb (Si bémol Majeur)
     */
    public function testBbEquivalents()
    {
        $equivalents = Chanson::getTonaliteEquivalents('Bb');
        
        $this->assertContains('Bb', $equivalents);
        $this->assertContains('A#', $equivalents);
        $this->assertNotContains('Bbm', $equivalents);
        $this->assertNotContains('A#m', $equivalents);
        $this->assertCount(2, $equivalents);
    }

    /**
     * Teste les équivalences pour Bbm (Si bémol Mineur)
     */
    public function testBbmEquivalents()
    {
        $equivalents = Chanson::getTonaliteEquivalents('Bbm');
        
        $this->assertContains('Bbm', $equivalents);
        $this->assertContains('A#m', $equivalents);
        $this->assertNotContains('Bb', $equivalents);
        $this->assertNotContains('A#', $equivalents);
        $this->assertCount(2, $equivalents);
    }

    /**
     * Teste les équivalences pour C (Do Majeur)
     */
    public function testCEquivalents()
    {
        $equivalents = Chanson::getTonaliteEquivalents('C');
        
        $this->assertContains('C', $equivalents);
        $this->assertContains('B#', $equivalents);
        $this->assertNotContains('Cm', $equivalents);
    }

    /**
     * Teste les équivalences pour Am (La Mineur)
     */
    public function testAmEquivalents()
    {
        $equivalents = Chanson::getTonaliteEquivalents('Am');
        
        $this->assertContains('Am', $equivalents);
        // Pas d'enharmonique simple pour A (à part G##m, non géré dans notre map simplifiée)
        $this->assertNotContains('A', $equivalents);
        $this->assertCount(1, $equivalents);
    }

    /**
     * Teste une tonalité sans enharmonique simple (D Majeur)
     */
    public function testDEquivalents()
    {
        $equivalents = Chanson::getTonaliteEquivalents('D');
        
        $this->assertContains('D', $equivalents);
        $this->assertNotContains('Dm', $equivalents);
        $this->assertCount(1, $equivalents);
    }
}
