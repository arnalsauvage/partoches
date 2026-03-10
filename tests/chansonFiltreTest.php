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
if (session_status() == PHP_SESSION_NONE) {
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
        $c1 = new Chanson("NOM_UNIQUE_A" . self::$suffix, "INT_UNIQUE_X" . self::$suffix, 2090, 1, 231, "7/8", "binaire", 0, "C#m");
        // On force le format SQL pour la date dans le test car creeChansonBDD utilise convertitDateJJMMAAAAversMySql
        $c1->setDatePub("01/01/2090"); 
        $this->chansonIds[0] = $c1->creeChansonBDD();

        // 2. Chanson B - Unique
        $c2 = new Chanson("NOM_UNIQUE_B" . self::$suffix, "INT_UNIQUE_Y" . self::$suffix, 2091, 99, 232, "5/4", "ternaire", 0, "D#");
        $c2->setDatePub("02/02/2091");
        $this->chansonIds[1] = $c2->creeChansonBDD();
        
        // 3. Chanson C - Unique (même interprète que A)
        $c3 = new Chanson("NOM_UNIQUE_C" . self::$suffix, "INT_UNIQUE_X" . self::$suffix, 2092, 1, 233, "7/8", "binaire", 0, "F#");
        $c3->setDatePub("03/03/2092");
        $this->chansonIds[2] = $c3->creeChansonBDD();
    }

    protected function tearDown(): void
    {
        foreach ($this->chansonIds as $id) {
            if ($id > 0) {
                $c = new Chanson($id);
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
