<?php
// require_once 'PHPUnit/Autoload.php';

use PHPUnit\Framework\TestCase;

class ChansonTest extends TestCase
{
    function setUp()
    {
        @session_start();
    }

    public function testConstructeur()
    {
        $_chanson = new Chanson("Lila Louis 987", "Olive", 1998, 1, 120, "4/4", "binaire", 0, "Bm");
        $this->assertEquals("Olive", $_chanson->getInterprete());
        $this->assertEquals("Lila Louis 987", $_chanson->getNom());
    }

    public function testEnregistreBDD()
    {
        $_chanson = new Chanson("Lila Louis 987", "Olive", 1998, 1, 120, "4/4", "binaire", 0, "F#");
        $_id = $_chanson->creeChansonBDD();
        // On crée un  autre objet pour écraser les valeurs
        $_chanson = new Chanson("Bordeaux", "Tests", 2012, 1, 80, "3/4", "ternaire", 0, "Bm");
        // On vérifie que l'on peut le recharger
        $_chanson = new Chanson($_id);
        $this->assertEquals("Olive", $_chanson->getInterprete());
        $this->assertEquals("Lila Louis 987", $_chanson->getNom());
        // On le supprime en BDD
        $_chanson->supprimeChanson();
    }

    public function testChercheChansonBDD()
    {
        $_chanson = new Chanson("Lila Louis 987", "Olive", 1998, 1, 120, "4/4", "binaire", 0, "F#");
        $_id = $_chanson->creeChansonBDD();
        // On crée un  autre objet pour écraser les valeurs
        $_chanson = new Chanson("Bordeaux", "Tests", 2012, 1, 80, "3/4", "ternaire", 0, "Bm");
        // On vérifie que l'on peut le recharger
        $_chanson->chercheChansonParLeNom("Lila Louis 987");
        $this->assertEquals("Olive", $_chanson->getInterprete());
        $this->assertEquals("Lila Louis 987", $_chanson->getNom());
        // On le supprime en BDD
        $_chanson->supprimeChanson();
        $this->assertEquals(0, $_chanson->chercheChansonParLeNom("Lila Louis 987"));
    }
}