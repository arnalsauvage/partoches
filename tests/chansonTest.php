<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../src/public/php/lib/utilssi.php";
require_once __DIR__ . "/../src/public/php/chanson/Chanson.php";

class ChansonTest extends TestCase
{
    const LILA_LOUIS_987 = "Lila Louis 987";
    const OLIVE = "Olive";
    const C = 1998;

    public function testConstructeur()
    {
        $_chanson = new Chanson(self::LILA_LOUIS_987, self::OLIVE , self::C, 1, 120, "4/4", "binaire", 0, "Bm");
        $this->assertEquals(self::OLIVE, $_chanson->getInterprete());
        $this->assertEquals(self::LILA_LOUIS_987, $_chanson->getNom());
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

    public function testNormalize(){
        $this->assertEquals("aeiou", Chanson::normalize("àéîôù"));
    }

    public function testMoteurRecherche(){
        // On crée une chanson unique pour le test
        $nomUnique = "CHANSON_MYSTERIEUSE_" . time();
        $c = new Chanson($nomUnique, "Artiste Inconnu", 2026, 1, 120, "4/4", "binaire", 0, "C");
        $c->creeChansonBDD();

        // Test avec une recherche connue qui devrait retourner des résultats
        $resultats = Chanson::moteurRecherche($nomUnique);
        
        // Nettoyage
        $c->supprimeChansonBddFile();

        // Vérifier que des résultats sont retournés
        $this->assertNotEmpty($resultats, "Le moteur de recherche devrait trouver la chanson unique.");
        $this->assertStringContainsString($nomUnique, $resultats);
    }

    /**
     * @dataProvider fournisseurDeRecherches
     */
    public function teste50MoteurRecherche($recherche, $attendu)
    {
        // Exécution de la recherche
        $resultats = Chanson::moteurRecherche($recherche);

        // Vérification que le résultat attendu est dans les résultats retournés (normalisé pour être robuste)
        $normAttendu = Chanson::normalize($attendu);
        $normResultats = Chanson::normalize($resultats);
        
        $this->assertStringContainsString($normAttendu, $normResultats, "La recherche '$recherche' devrait retourner '$attendu' mais a donné :\n" . $resultats);
    }

    public static function fournisseurDeRecherches()
    {
        return [
            ["arnold", "Arnold & Willy"],
            ["black", "Black Trombone"],
            ["stand", "Stand by me"],
            ["laisse", "Laisse béton"],
            ["bikini", "Itsi bitsi petit bikini"],
            ["salade", "Salade de fruits"],
            ["amourette", "Pour une amourette"],
            ["sympathique", "Sympathique"],
            ["gorille", "Le gorille"],
            ["harley", "Harley Davidson"],
            ["clandestino", "Clandestino"],
            ["plage", "L'amour à la plage"],
            ["breath", "Every breath you take"],
            ["bahia", "Bahia"],
            ["dinosaur", "I'm a little dinosaur"],
            ["lady", "Ukulele Lady"],
            ["navidad", "Feliz Navidad"],
            ["danser", "Lili voulait aller danser"],
            ["ron ron", "Da doo ron ron"],
            ["lovely", "Lovely Day"],
            ["desaparecido", "Desaparecido"],
            ["vent", "Le vent nous portera"],
            ["yeye", "Chez les yé-yé"],
            ["boogie", "Cow cow boogie"],
            ["tico", "Tico tico"],
            ["new york", "New-York avec toi"],
        ];
    }
}
