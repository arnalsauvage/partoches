<?php
require_once __DIR__ . '/../src/autoload.php';

use PHPUnit\Framework\TestCase;

class ChansonTest extends TestCase
{
    const LILA_LOUIS_987 = "Lila Louis 987";
    const OLIVE = "Olive";
    const C = 1998;

    public function testConstructeur()
    {
        $_chanson = new Chanson();
        $_chanson->setNom(self::LILA_LOUIS_987);
        $_chanson->setInterprete(self::OLIVE);
        $_chanson->setAnnee(self::C);
        $_chanson->setTonalite("Bm");
        $_chanson->setTonaliteOriginale("Am");
        $this->assertEquals(self::OLIVE, $_chanson->getInterprete());
        $this->assertEquals(self::LILA_LOUIS_987, $_chanson->getNom());
        $this->assertEquals("Am", $_chanson->getTonaliteOriginale());
    }

    public function testEnregistreBDD()
    {
        $_chanson = new Chanson();
        $_chanson->setNom(self::LILA_LOUIS_987);
        $_chanson->setInterprete(self::OLIVE);
        $_chanson->setAnnee(self::C);
        $_chanson->setIdUser(1);
        $_chanson->setTempo(120);
        $_chanson->setMesure("4/4");
        $_chanson->setPulsation("binaire");
        $_chanson->setHits(0);
        $_chanson->setTonalite("F#");
        $_chanson->setTonaliteOriginale("F#");
        $_id = $_chanson->creeChansonBDD();
        // On crée un  autre objet pour écraser les valeurs
        $_chanson = new Chanson();
        $_chanson->setNom("Bordeaux");
        $_chanson->setInterprete("Tests");
        $_chanson->setAnnee(2012);
        $_chanson->setIdUser(1);
        $_chanson->setTempo(80);
        $_chanson->setMesure("3/4");
        $_chanson->setPulsation("ternaire");
        $_chanson->setHits(0);
        $_chanson->setTonalite("Bm");
        $_chanson->setTonaliteOriginale("Gm");
        // On vérifie que l'on peut le recharger
        $_chanson = Chanson::load($_id);
        $this->assertEquals(self::OLIVE, $_chanson->getInterprete());
        $this->assertEquals(self::LILA_LOUIS_987, $_chanson->getNom());
        // On le supprime en BDD
        $_chanson->supprimeChansonBddFile();
    }

    public function testChercheChansonBDD()
    {
        $_chanson = new Chanson();
        $_chanson->setNom(self::LILA_LOUIS_987);
        $_chanson->setInterprete(self::OLIVE);
        $_chanson->setAnnee(self::C);
        $_chanson->setIdUser(1);
        $_chanson->setTempo(120);
        $_chanson->setMesure("4/4");
        $_chanson->setPulsation("binaire");
        $_chanson->setHits(0);
        $_chanson->setTonalite("F#");
        $_chanson->setTonaliteOriginale("F#");
        $_chanson->creeChansonBDD();
        // On crée un  autre objet pour écraser les valeurs
        $_chanson = new Chanson();
        $_chanson->setNom("Bordeaux");
        $_chanson->setInterprete("Tests");
        $_chanson->setAnnee(2012);
        $_chanson->setIdUser(1);
        $_chanson->setTempo(80);
        $_chanson->setMesure("3/4");
        $_chanson->setPulsation("ternaire");
        $_chanson->setHits(0);
        $_chanson->setTonalite("Bm");
        $_chanson->setTonaliteOriginale("Gm");
        // On vérifie que l'on peut le recharger
        $_chanson->chercheChansonParLeNom(self::LILA_LOUIS_987);
        $this->assertEquals(self::OLIVE, $_chanson->getInterprete());
        $this->assertEquals(self::LILA_LOUIS_987, $_chanson->getNom());
        // On le supprime en BDD
        $_chanson->supprimeChansonBddFile();
    }

    public function testMoteurRecherche(){
        // On crée une chanson unique pour le test
        $nomUnique = "CHANSON_MYSTERIEUSE_" . time();
        $c = new Chanson();
        $c->setNom($nomUnique);
        $c->setInterprete("Artiste Inconnu");
        $c->setAnnee(2026);
        $c->setIdUser(1);
        $c->setTempo(120);
        $c->setMesure("4/4");
        $c->setPulsation("binaire");
        $c->setHits(0);
        $c->setTonalite("C");
        $c->setTonaliteOriginale("Am");
        $c->creeChansonBDD();

        $resultat = Chanson::moteurRecherche("CHANSON_MYSTERIEUSE_" . time());
        $this->assertEquals(0, strpos($resultat, $nomUnique));
        $c->supprimeChansonBddFile();
    }
}
