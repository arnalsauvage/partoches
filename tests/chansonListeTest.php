<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../src/autoload.php";


class chansonListeTest extends TestCase
{
    const LILA_LOUIS_987 = "Lila Louis 987";
    const OLIVE = "Olive";
    const C = 1998;

    public function testChercheChansonsStatique()
    {
        // On charge la liste des chansons via la méthode moderne
        $ids = Chanson::chercheChansons("");
        $nbChansons = count($ids);
        $this->assertGreaterThan(0, $nbChansons, "Il devrait y avoir au moins une chanson en BDD");
        
        // On vérifie qu'on peut charger la première chanson
        if ($nbChansons > 0) {
            $chanson = new Chanson($ids[0]);
            $this->assertNotEmpty($chanson->getNom());
        }
    }

    public function testEnregistreBDD()
    {
        $_chanson = new Chanson(self::LILA_LOUIS_987, self::OLIVE, self::C, 1, 120, "4/4", "binaire", 0, "F#");
        $_id = $_chanson->creeChansonBDD();
        // On crée un  autre objet pour écraser les valeurs
        $_chanson = new Chanson("Bordeaux", "Tests", 2012, 1, 80, "3/4", "ternaire", 0, "Bm");
        // On vérifie que l'on peut le recharger
        $_chanson = new Chanson($_id);
        $this->assertEquals(self::OLIVE, $_chanson->getInterprete());
        $this->assertEquals(self::LILA_LOUIS_987, $_chanson->getNom());
        // On le supprime en BDD
        $_chanson->supprimeChansonBddFile();
    }

    public function testChercheChansonBDD()
    {
        $_chanson = new Chanson(self::LILA_LOUIS_987, self::OLIVE, self::C, 1, 120, "4/4", "binaire", 0, "F#");
        $_chanson->creeChansonBDD();
        // On crée un  autre objet pour écraser les valeurs
        $_chanson = new Chanson("Bordeaux", "Tests", 2012, 1, 80, "3/4", "ternaire", 0, "Bm");
        // On vérifie que l'on peut le recharger
        $_chanson->chercheChansonParLeNom(self::LILA_LOUIS_987);
        $this->assertEquals(self::OLIVE, $_chanson->getInterprete());
        $this->assertEquals(self::LILA_LOUIS_987, $_chanson->getNom());
        // On le supprime en BDD
        $_chanson->supprimeChansonBddFile();
        $this->assertEquals(0, $_chanson->chercheChansonParLeNom(self::LILA_LOUIS_987));
    }
}
