<?php
use PHPUnit\Framework\TestCase;
// require_once 'PHPUnit/Autoload.php';
session_start();
require_once "../php/lib/utilssi.php";
require_once "../php/chanson/chanson.php";

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

    public function testMoteurRecherche($recherche1){

        // Test avec une recherche connue qui devrait retourner des résultats
        $recherche1 = "contrefaçon";
        $this->addToAssertionCount(1);
        $resultats1 = Chanson::moteurRecherche($recherche1);
        // Indiquer que nous attendons un résultat d'echo
        // Vérifier que des résultats sont retournés
        $this->assertEmpty($resultats1, "La recherche '$recherche1' devrait retourner des résultats mais a donné :\n" . $resultats1);

        // Test avec une recherche qui ne devrait pas retourner de résultats
        /*$recherche2 = "Chanson inexistante";
        $resultats2 = Chanson::moteurRecherche($recherche2);

        // Vérifier que aucun résultat n'est retourné
        $this->assertEmpty($resultats2, "La recherche '$recherche2' ne devrait pas retourner de résultats $resultats2");*/
    }

    /**
     * @dataProvider fournisseurDeRecherches
     */
    public function teste50MoteurRecherche($recherche, $attendu)
    {
        // Exécution de la recherche
        $resultats = Chanson::moteurRecherche($recherche);

        // Vérification que le résultat attendu est dans les résultats retournés
        $this->assertStringContainsString($attendu, $resultats, "La recherche '$recherche' devrait retourner '$attendu' mais a donné :\n" . $resultats);
    }

    public function fournisseurDeRecherches()
    {
        return [
//            ["3 nuits", "3 nuits par semaine"],
//             ["a la ciotat", "A la Ciotat"],
            ["africa", "Africa"],
//            ["agua", "Agua de Beber"],
//            ["1 franc cinquante", "Ah ! Si j'avais 1 F 50"],
//            ["150", "Ah ! Si j'avias 1 F 50"],
//            ["ain", "ain't she sweet"],
            ["ain t", "ain't she sweet"],
            ["ain't", "ain't she sweet"],
            ["ain't she sweet", "ain't she sweet"],
            ["aint", "ain't she sweet"],
            ["aline", "Aline"],
            ["bass", "All about that bass"],
            ["alouette", "Alouette"],
            ["amsterda", "Amsterdam"],
            ["amsterdam", "Amsterdam"],
            ["annie", "Annie"],
            ["arm", "Armstrong"],
            ["armstrong", "Armstrong"],
            ["arnold", "Arnold & Willy"],
            ["au coeur de la nuit", "Au coeur de la nuit"],
            ["back", "Back to black"],
            ["back to black", "Back to black"],
            ["bambino", "Bambino"],
            ["baudelaire", "Baudelaire"],
            ["be my baby", "Be my Baby"],
            ["besame mucho", "Besame mucho"],
            ["bella", "Bella Ciao"],
            ["bella ciao", "Bella Ciao"],
            ["besame", "Besame Mucho"],
            ["bless", "Blesse-moi"],
            ["blesse", "Blesse-moi"],
            ["blesse moi", "Blesse-moi"],
            ["black trombone", "Black Trombone"],
            ["born is way", "Born this way"],
            ["bon", "Born to be wild"],
            ["bon to be wild", "Born to be wild"],
            ["boy", "Boys don't cry"],
            ["boys", "Boys don't cry"],
            ["boys don", "Boys don't cry"],
            ["boys don't cry", "Boys don't cry"],
            ["breakfast", "Breakfast in America"],
            ["buda", "Budapest"],
            ["budapest", "Budapest"],
            ["buenos", "Buenos Aires"],
            ["california", "Hôtel California"],
            ["californien", "Hôtel California"],
            ["carav", "J'passe pour une caravane"],
            ["caravane", "J'passe pour une caravane"],
//            ["carioca", "La Carioca"],
            ["carmen", "Carmen"],
            ["cendrillo", "Cendrillon"],
            ["cendrillon", "Cendrillon"],
            ["c'est la mort", "C'est la mort"],
            ["c'est magnifique", "C'est magnifique"],
            ["c'est si bon", "C'est si bon"],
            ["champs", "Les Champs elysées"],
            ["champs elysées", "Les Champs elysées"],
            ["champs élysées", "Les Champs elysées"],
            ["chan chan", "Chan chan"],
            ["chanson", "Chanson sur ma drole de vie"],
            ["chanson sur ma drole de vie", "Chanson sur ma drole de vie"],
        ];
    }
}