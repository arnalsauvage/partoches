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

    public function testUpdateChansonBDD()
    {
        $nomUnique = "CHANSON_A_MODIFIER_" . time();
        $c = new Chanson();
        $c->setNom($nomUnique);
        $c->setInterprete("Artiste Initial");
        $c->setAnnee(2025);
        $c->setIdUser(1);
        $c->setTempo(100);
        $c->setMesure("4/4");
        $c->setPulsation("binaire");
        $c->setHits(0);
        $c->setTonalite("G");
        $c->setTonaliteOriginale("Em");
        $id = $c->creeChansonBDD();
        $this->assertGreaterThan(0, $id);

        // Modification
        $cModif = Chanson::load($id);
        $cModif->setInterprete("Artiste Modifie");
        $cModif->setTempo(130);
        $cModif->modifieChansonBDD();

        // Verification de la persistance
        $cRecharge = Chanson::load($id);
        $this->assertEquals("Artiste Modifie", $cRecharge->getInterprete());
        $this->assertEquals(130, $cRecharge->getTempo());

        // Nettoyage
        $cRecharge->supprimeChansonBddFile();
    }

    public function testDupliqueChanson()
    {
        // Création de la chanson source
        $c = new Chanson();
        $c->setNom("Chanson Duplication Test " . time());
        $c->setInterprete("Artiste Test");
        $c->setAnnee(2020);
        $c->setIdUser(1);
        $c->setTempo(135);
        $c->setMesure("3/4");
        $c->setPulsation("ternaire");
        $c->setTonalite("Em");
        $c->setTonaliteOriginale("Dm");
        $c->setPublication(1);
        $sourceId = $c->save();
        $this->assertGreaterThan(0, $sourceId);

        // Ajout d'un lien URL
        require_once __DIR__ . "/../src/public/php/liens/LienUrl.php";
        LienUrl::creeLienurl("https://www.youtube.com/watch?v=testdup", "youtube", "Vidéo test", "chanson", $sourceId, date("d/m/Y"), 1, 10);

        // Ajout d'un strum
        require_once __DIR__ . "/../src/public/php/liens/LienStrumChanson.php";
        LienStrumChanson::creelienStrumChanson("B B H B", $sourceId, 0);

        // Simulation privilège admin
        $_SESSION['privilege'] = $GLOBALS['PRIVILEGE_ADMIN'] ?? 3;

        // Duplication
        $newId = Chanson::dupliqueChanson($sourceId);
        $this->assertGreaterThan(0, $newId);
        $this->assertNotEquals($sourceId, $newId);

        // Vérification des attributs de la copie
        $copie = Chanson::load($newId);
        $this->assertEquals($c->getNom(), $copie->getNom());
        $this->assertEquals("Artiste Test", $copie->getInterprete());
        $this->assertEquals(2020, $copie->getAnnee());
        $this->assertEquals(135, $copie->getTempo());
        $this->assertEquals("3/4", $copie->getMesure());
        $this->assertEquals("ternaire", $copie->getPulsation());
        $this->assertEquals("Em", $copie->getTonalite());
        $this->assertEquals("Dm", $copie->getTonaliteOriginale());
        $this->assertEquals(0, $copie->getHits(), "Les hits de la nouvelle chanson doivent être remis à 0");
        $this->assertEquals(1, $copie->getPublication(), "La chanson dupliquée doit être publiée");

        // Vérification des liens dupliqués
        $liensCopie = LienUrl::chercheLiensUrlsTableId("chanson", $newId);
        $this->assertNotNull($liensCopie);
        $this->assertGreaterThanOrEqual(1, $liensCopie->num_rows);

        // Vérification des strums dupliqués
        $strumsCopie = LienStrumChanson::chercheLiensStrumChanson("idChanson", $newId);
        $this->assertNotNull($strumsCopie);
        $this->assertGreaterThanOrEqual(1, $strumsCopie->num_rows);

        // Vérification absence de documents attachés
        require_once __DIR__ . "/../src/public/php/document/Document.php";
        $docsCopie = Document::chercheDocumentsTableId("chanson", $newId);
        $this->assertEquals(0, $docsCopie ? $docsCopie->num_rows : 0, "Aucun document attaché ne doit être dupliqué");

        // Nettoyage BDD
        $copie->supprimeChansonBddFile();
        $c->supprimeChansonBddFile();

        // Nettoyage des liens et strums de la source et de la copie
        $db = $_SESSION['mysql'];
        $db->query("DELETE FROM lienurl WHERE nomtable = 'chanson' AND (idtable = $sourceId OR idtable = $newId)");
        $db->query("DELETE FROM lienstrumchanson WHERE idChanson = $sourceId OR idChanson = $newId");
    }
}
