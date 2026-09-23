<?php
use PHPUnit\Framework\TestCase;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../src/public/php/lib/utilssi.php";
require_once __DIR__ . "/../src/public/php/strum/Strum.php";


class strumTest extends TestCase
{
    public function testConstructeur0()
    {
        // Etant donné que je n'ai pas de valeurs
        $id_attendu = 0;
        $strum_attendu = "";
        $unite_attendue = 8;
        $longueur_attendue = 8;
        $description_attendue = "";

        // Quand je crée un objet sans parametres
        $_strum = new Strum();

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($id_attendu, $_strum->getId());
        $this->assertEquals($strum_attendu, $_strum->getStrum());
        $this->assertEquals($description_attendue, $_strum->getDescription());
        $this->assertEquals($longueur_attendue, $_strum->getLongueur());
        $this->assertEquals($unite_attendue, $_strum->getUnite());
    }

    public function testConstructeur5()
    {
        // Etant données les valeurs suivantes
        $id_attendu = 5;
        $strum_attendu = "B B B BHB BHBHBH";
        $unite_attendue = 4;
        $longueur_attendue = 16;
        $description_attendue = "Un strum de test";

        // Quand je crée un objet
        $_strum = new strum($id_attendu, $strum_attendu, $unite_attendue, $longueur_attendue, $description_attendue);

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($id_attendu, $_strum->getId());
        $this->assertEquals($strum_attendu, $_strum->getStrum());
        $this->assertEquals($description_attendue, $_strum->getDescription());
        $this->assertEquals($longueur_attendue, $_strum->getLongueur());
        $this->assertEquals($unite_attendue, $_strum->getUnite());
    }

    public function testConstructeur4()
    {
        // Etant données les valeurs suivantes
        $id_attendu = 0;
        $strum_attendu = "B B B BHB BHBHBH";
        $unite_attendue = 4;
        $longueur_attendue = 16;
        $description_attendue = "Un strum de test";

        // Quand je crée un objet
        $_strum = new strum($strum_attendu, $unite_attendue, $longueur_attendue, $description_attendue);

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($id_attendu, $_strum->getId());
        $this->assertEquals($strum_attendu, $_strum->getStrum());
        $this->assertEquals($description_attendue, $_strum->getDescription());
        $this->assertEquals($longueur_attendue, $_strum->getLongueur());
        $this->assertEquals($unite_attendue, $_strum->getUnite());
    }

    public function testConstructeur1()
    {
        // Etant données les valeurs suivantes en bdd dans la table strum
        $id_attendu = 2;
        $strum_attendu = "B BH HB";
        $unite_attendue = 8;
        $longueur_attendue = 8;
        $description_attendue = "Boléro";

        // Quand je crée un objet
        $_strum = new strum($id_attendu);

        // Alors j'obtiens l'objet avec les valeurs attendues
        if ($_strum->getId() > 0) {
            $this->assertEquals($id_attendu, $_strum->getId());
            $this->assertEquals($strum_attendu, $_strum->getStrum());
        } else {
            $this->markTestSkipped("Le strum #$id_attendu n'existe pas en BDD.");
        }
    }

    public function testChercheStrumParId_Ok()
    {
        $strum_attendu = "B BH HBbhbhbhbhb hb hb h bh bhb h";
        $unite_attendue = 8;
        $longueur_attendue = strlen($strum_attendu);
        $description_attendue = "Boléro inattendu";

        // Etant données les valeurs suivantes
        $new_strum = new Strum($strum_attendu,$unite_attendue, $longueur_attendue, $description_attendue);
        $new_strum->enregistreBDD();
        $id_attendu = $new_strum->getId();
        $_strum = new strum();

        // Quand je cherche le strum par son id
        $_strum->chercheStrumParId($id_attendu);

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($id_attendu, $_strum->getId());
        $this->assertEquals($strum_attendu, $_strum->getStrum());
        $this->assertEquals($description_attendue, $_strum->getDescription());
        $this->assertEquals($longueur_attendue, $_strum->getLongueur());
        $this->assertEquals($unite_attendue, $_strum->getUnite());

        // Suppression de la donnée
        $_strum->supprimeBDD();
    }

    public function testrenvoieUniteEnFrancais()
    {
        $_unite_attendue = "croches";
        $_unite_attendue2 = "double-croches";
        $_unite_attendue3 = "noires";

        $_strum = new strum();
        $_uniteRendue = $_strum->renvoieUniteEnFrancais();
        $_strum->setUnite(16);
        $_uniteRendue2 = $_strum->renvoieUniteEnFrancais();
        $_strum->setUnite(4);
        $_uniteRendue3 = $_strum->renvoieUniteEnFrancais();

        $this->assertEquals($_unite_attendue, $_uniteRendue);
        $this->assertEquals($_unite_attendue2, $_uniteRendue2);
        $this->assertEquals($_unite_attendue3, $_uniteRendue3);
    }

    public function testchercheStrumParChaine_Ok()
    {
        $strum_attendu = "B BH HBbhbhbhbhb hb hb h bh bhb h";
        $unite_attendue = 8;
        $longueur_attendue = strlen($strum_attendu);
        $description_attendue = "Boléro inattendu";

        // Etant données les valeurs suivantes
        $new_strum = new Strum($strum_attendu,$unite_attendue, $longueur_attendue, $description_attendue);
        $new_strum->enregistreBDD();
        $id_attendu = $new_strum->getId();
        $_strum = new strum();

        // Quand je cherche le strum par sa chaine strum
        $_strum->chercheStrumParChaine($strum_attendu);

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($id_attendu, $_strum->getId());
        $this->assertEquals($strum_attendu, $_strum->getStrum());

        // Suppression de la donnée
        $_strum->supprimeBDD();
    }

    public function testRenommageStrum()
    {
        // On crée un strum de test pour éviter de dépendre de l'ID 1
        $_strum = new Strum("B BH HBH", 8, 8, "Strum Test Renommage");
        $_strum->enregistreBDD();
        $id = $_strum->getId();

        // Si je change sa chaîne strum
        $_strum->setStrum("B BH HHH");
        $_strum->enregistreBDD();
        
        $strum2 = new Strum($id);
        $this->assertEquals("B BH HHH", $strum2->getStrum());

        // Je nettoie
        $_strum->supprimeBDD();
    }
}
