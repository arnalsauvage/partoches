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
        $c->setNom("Chanson Duplication Source " . time());
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
        $resLien = LienUrl::chercheLiensUrlsTableId("chanson", $sourceId);
        $rowLien = $resLien ? $resLien->fetch_assoc() : null;
        $lienId = $rowLien ? (int)$rowLien['id'] : 0;
        $this->assertGreaterThan(0, $lienId);

        // Ajout d'un strum
        require_once __DIR__ . "/../src/public/php/liens/LienStrumChanson.php";
        $strumId = LienStrumChanson::creelienStrumChanson("B B H B", $sourceId, 0);
        $this->assertGreaterThan(0, $strumId);

        // Ajout d'un document attaché
        require_once __DIR__ . "/../src/public/php/document/Document.php";
        Document::creeDocument("test-dup.pdf", 42, "chanson", $sourceId);
        $rowDoc = Document::chercheDocumentNomTableId("test-dup.pdf", "chanson", $sourceId);
        $docId = $rowDoc ? (int)$rowDoc['id'] : 0;
        $this->assertGreaterThan(0, $docId);

        // Simulation privilège admin
        $_SESSION['privilege'] = $GLOBALS['PRIVILEGE_ADMIN'] ?? 3;

        // CAS 1 : Duplication par défaut (aucun élément sélectionné, nom source conservé)
        $newIdDefaut = Chanson::dupliqueChanson($sourceId);
        $this->assertGreaterThan(0, $newIdDefaut);
        $this->assertNotEquals($sourceId, $newIdDefaut);

        $copieDefaut = Chanson::load($newIdDefaut);
        $this->assertEquals($c->getNom(), $copieDefaut->getNom());
        $this->assertEquals("Artiste Test", $copieDefaut->getInterprete());
        $this->assertEquals(2020, $copieDefaut->getAnnee());
        $this->assertEquals(135, $copieDefaut->getTempo());
        $this->assertEquals("3/4", $copieDefaut->getMesure());
        $this->assertEquals("ternaire", $copieDefaut->getPulsation());
        $this->assertEquals("Em", $copieDefaut->getTonalite());
        $this->assertEquals("Dm", $copieDefaut->getTonaliteOriginale());
        $this->assertEquals(0, $copieDefaut->getHits(), "Hits remis à 0");
        $this->assertEquals(1, $copieDefaut->getPublication(), "Chanson publiée");

        // Vérification que RIEN n'est copié par défaut (désélectionnés par défaut)
        $docsDefaut = Document::chercheDocumentsTableId("chanson", $newIdDefaut);
        $this->assertEquals(0, $docsDefaut ? $docsDefaut->num_rows : 0, "Aucun document copié par défaut");
        $strumsDefaut = LienStrumChanson::chercheLiensStrumChanson("idChanson", $newIdDefaut);
        $this->assertEquals(0, $strumsDefaut ? $strumsDefaut->num_rows : 0, "Aucun strum copié par défaut");
        $liensDefaut = LienUrl::chercheLiensUrlsTableId("chanson", $newIdDefaut);
        $this->assertEquals(0, $liensDefaut ? $liensDefaut->num_rows : 0, "Aucun lien copié par défaut");

        // CAS 2 : Duplication sélective (avec sélection de doc, strum, lien et nouveau titre)
        $nouveauTitre = "Chanson Dupliquee Personnalisee " . time();
        $newIdSelectif = Chanson::dupliqueChanson(
            $sourceId,
            [$docId],
            [$strumId],
            [$lienId],
            $nouveauTitre
        );
        $this->assertGreaterThan(0, $newIdSelectif);

        $copieSelective = Chanson::load($newIdSelectif);
        $this->assertEquals($nouveauTitre, $copieSelective->getNom(), "Le nouveau titre doit être appliqué");

        // Vérification des éléments copiés sélectivement
        $docsSelectif = Document::chercheDocumentsTableId("chanson", $newIdSelectif);
        $this->assertNotNull($docsSelectif);
        $this->assertEquals(1, $docsSelectif->num_rows, "Le document sélectionné doit être copié");

        $strumsSelectif = LienStrumChanson::chercheLiensStrumChanson("idChanson", $newIdSelectif);
        $this->assertNotNull($strumsSelectif);
        $this->assertEquals(1, $strumsSelectif->num_rows, "Le strum sélectionné doit être copié");

        $liensSelectif = LienUrl::chercheLiensUrlsTableId("chanson", $newIdSelectif);
        $this->assertNotNull($liensSelectif);
        $this->assertEquals(1, $liensSelectif->num_rows, "Le lien sélectionné doit être copié");

        // Nettoyage BDD
        $copieDefaut->supprimeChansonBddFile();
        $copieSelective->supprimeChansonBddFile();
        $c->supprimeChansonBddFile();

        $db = $_SESSION['mysql'];
        $db->query("DELETE FROM lienurl WHERE nomtable = 'chanson' AND (idtable = $sourceId OR idtable = $newIdDefaut OR idtable = $newIdSelectif)");
        $db->query("DELETE FROM lienstrumchanson WHERE idChanson = $sourceId OR idChanson = $newIdDefaut OR idChanson = $newIdSelectif");
        $db->query("DELETE FROM document WHERE nomTable = 'chanson' AND (idTable = $sourceId OR idTable = $newIdDefaut OR idTable = $newIdSelectif)");
    }
}
