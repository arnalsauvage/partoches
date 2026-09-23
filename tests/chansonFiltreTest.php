<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

// Hack pour les chemins en CLI
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');
}

// On simule une session si elle n'existe pas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../src/public/php/lib/utilssi.php";
require_once __DIR__ . "/../src/public/php/chanson/Chanson.php";

class ChansonFiltreTest extends TestCase
{
    private $chansonIds = [];
    private static $suffix;

    protected function setUp(): void
    {
        self::$suffix = "_" . time() . "_" . rand(100, 999);
        $_SESSION['privilege'] = 10; // Admin
        
        // 1. Chanson A - Unique
        $c1 = new Chanson();
        $c1->setNom("NOM_UNIQUE_A" . self::$suffix);
        $c1->setInterprete("INT_UNIQUE_X" . self::$suffix);
        $c1->setAnnee(2090);
        $c1->setIdUser(1);
        $c1->setTempo(231);
        $c1->setMesure("7/8");
        $c1->setPulsation("binaire");
        $c1->setHits(0);
        $c1->setTonalite("C#m");
        $c1->setTonaliteOriginale("F#m");
        $c1->setDatePub("2090-01-01");
        $c1->setPublication(1);
        $this->chansonIds[0] = $c1->creeChansonBDD();

        // 2. Chanson B - Unique
        $c2 = new Chanson();
        $c2->setNom("NOM_UNIQUE_B" . self::$suffix);
        $c2->setInterprete("INT_UNIQUE_Y" . self::$suffix);
        $c2->setAnnee(2091);
        $c2->setIdUser(99);
        $c2->setTempo(232);
        $c2->setMesure("5/4");
        $c2->setPulsation("ternaire");
        $c2->setHits(0);
        $c2->setTonalite("D#");
        $c2->setDatePub("2091-02-02");
        $this->chansonIds[1] = $c2->creeChansonBDD();

        // 3. Chanson C - Unique (même interprète que A)
        $c3 = new Chanson();
        $c3->setNom("NOM_UNIQUE_C" . self::$suffix);
        $c3->setInterprete("INT_UNIQUE_X" . self::$suffix);
        $c3->setAnnee(2092);
        $c3->setIdUser(1);
        $c3->setTempo(233);
        $c3->setMesure("7/8");
        $c3->setPulsation("binaire");
        $c3->setHits(0);
        $c3->setTonalite("F#");
        $c3->setDatePub("2092-03-03");
        $this->chansonIds[2] = $c3->creeChansonBDD();
    }

    protected function tearDown(): void
    {
        foreach ($this->chansonIds as $id) {
            if ($id > 0) {
                $c = Chanson::load($id);
                $c->supprimeChansonBddFile();
            }
        }
    }

    public function testFiltreNomRecherche()
    {
        $resultats = Chanson::chercheChansons("%NOM_UNIQUE_B" . self::$suffix . "%");
        $this->assertContains((string)$this->chansonIds[1], array_map('strval', $resultats));
    }

    public function testFiltreInterprete()
    {
        $resultats = Chanson::chercheChansons("%", "nom", true, "interprete", "INT_UNIQUE_X" . self::$suffix);
        // On vérifie qu'on a bien nos deux chansons (A et C)
        $idsTrouves = array_map('strval', $resultats);
        $this->assertContains((string)$this->chansonIds[0], $idsTrouves);
        $this->assertContains((string)$this->chansonIds[2], $idsTrouves);
    }

    public function testFiltreAnnee()
    {
        $resultats = Chanson::chercheChansons("%", "nom", true, "annee", "2091");
        $this->assertContains((string)$this->chansonIds[1], array_map('strval', $resultats));
    }

    public function testFiltreTempo()
    {
        $resultats = Chanson::chercheChansons("%", "nom", true, "tempo", "232");
        $this->assertContains((string)$this->chansonIds[1], array_map('strval', $resultats));
    }

    public function testFiltreMesure()
    {
        $resultats = Chanson::chercheChansons("%", "nom", true, "mesure", "5/4");
        $this->assertContains((string)$this->chansonIds[1], array_map('strval', $resultats));
    }

    public function testFiltrePulsation()
    {
        $resultats = Chanson::chercheChansons("%", "nom", true, "pulsation", "ternaire");
        $this->assertContains((string)$this->chansonIds[1], array_map('strval', $resultats));
    }

    public function testFiltreTonaliteOriginale()
    {
        $resultats = Chanson::chercheChansons("%", "nom", true, "tonalite_originale", "F#m");
        $this->assertContains((string)$this->chansonIds[0], array_map('strval', $resultats));
        $this->assertNotContains((string)$this->chansonIds[1], array_map('strval', $resultats));
    }

    public function testFiltreTonalite()
    {
        $resultats = Chanson::chercheChansons("%", "nom", true, "tonalite", "D#");
        $this->assertContains((string)$this->chansonIds[1], array_map('strval', $resultats));
    }

    public function testFiltrePublicateur()
    {
        $resultats = Chanson::chercheChansons("%", "nom", true, "contributeur", "99");
        $this->assertContains((string)$this->chansonIds[1], array_map('strval', $resultats));
    }

    public function testFiltreDate()
    {
        // On cherche par année de publication via LIKE
        $resultats = Chanson::chercheChansons("%", "nom", true, "datePub", "2091%");
        $this->assertContains((string)$this->chansonIds[1], array_map('strval', $resultats));
    }
}
