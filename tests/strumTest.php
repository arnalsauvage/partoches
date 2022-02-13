<?php
use PHPUnit\Framework\TestCase;
// require_once 'PHPUnit/Autoload.php';
session_start();
require_once "../php/lib/utilssi.php";
require_once "../php/strum.php";


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
        $_strum = new strum();

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
        $description_attendue = "BolÃ©ro";

        // Quand je crée un objet
        $_strum = new strum($id_attendu);

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($id_attendu, $_strum->getId());
        $this->assertEquals($strum_attendu, $_strum->getStrum());
        $this->assertEquals($description_attendue, $_strum->getDescription());
        $this->assertEquals($longueur_attendue, $_strum->getLongueur());
        $this->assertEquals($unite_attendue, $_strum->getUnite());
    }

    public function testChercheStrumParId_Ok()
    {
        $strum_attendu = "B BH HBbhbhbhbhb hb hb h bh bhb h";
        $unite_attendue = 8;
        $longueur_attendue = strlen($strum_attendu);
        $description_attendue = "Boléro inattendu";

        // Etant données les valeurs suivantes
        $new_strum = new Strum($strum_attendu,$unite_attendue, $longueur_attendue, $description_attendue);
        $new_strum->creestrumBDD();
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
        $_strum->supprimestrumBDD();
    }

    public function testrenvoieUniteEnFrancais()
    {
        // Etant donné que je n'ai pas de valeurs

        $_unite_attendue = "croches";
        $_unite_attendue2 = "double-croches";
        $_unite_attendue3 = "noires";

        // Quand je crée un objet sans parametres
        $_strum = new strum();
        $_uniteRendue = $_strum->renvoieUniteEnFrancais();
        $_strum->setUnite(16);
        $_uniteRendue2 = $_strum->renvoieUniteEnFrancais();
        $_strum->setUnite(4);
        $_uniteRendue3 = $_strum->renvoieUniteEnFrancais();

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($_unite_attendue, $_uniteRendue);
        $this->assertEquals($_unite_attendue2, $_uniteRendue2);
        $this->assertEquals($_unite_attendue3, $_uniteRendue3);
    }

    public function testchansonsDuStrum()
    {
        // TODO : ce tests dépend des données en bases, il faudrait faire un mock

        // Etant données les valeurs suivantes en bdd dans la table strum
        $strum_attendu = "B BH HB";
        $chansons_attendues = " - strum utilisé dans  - Une belle histoire - Le tÃ©lÃ©phone cellulaire (ne m'appelle plus)";

        // Quand j'appelle la méthode statique
        $chansons_obtenues = Strum::chansonsDuStrumChaine($strum_attendu);

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($chansons_attendues, $chansons_obtenues);
    }

    public function testchercheStrumParChaine_Ok()
    {
        $strum_attendu = "B BH HBbhbhbhbhb hb hb h bh bhb h";
        $unite_attendue = 8;
        $longueur_attendue = strlen($strum_attendu);
        $description_attendue = "Boléro inattendu";

        // Etant données les valeurs suivantes
        $new_strum = new Strum($strum_attendu,$unite_attendue, $longueur_attendue, $description_attendue);
        $new_strum->creestrumBDD();
        $id_attendu = $new_strum->getId();
        $_strum = new strum();

        // Quand je cherche le strum par sa chaine strum
        $_strum->chercheStrumParChaine($strum_attendu);

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($id_attendu, $_strum->getId());
        $this->assertEquals($strum_attendu, $_strum->getStrum());
        $this->assertEquals($description_attendue, $_strum->getDescription());
        $this->assertEquals($longueur_attendue, $_strum->getLongueur());
        $this->assertEquals($unite_attendue, $_strum->getUnite());

        // Suppression de la donnée
        $_strum->supprimestrumBDD();
    }
    public function testnettoieValeursEscapeStrings()
    {
        // Etant données les valeurs suivantes
        $id_attendu = 5;
        $strum_attendu = "B B B BHB \\\\BHBHBH";
        $strum_envoye = "B B B BHB \\BHBHBH";
        $unite_attendue = 4;
        $longueur_attendue = 16;
        $description_envoye = "Un strum de test'\\nd'enfer";
        $description_attendue = "Un strum de test\'\\\\nd\\'enfer";

        // Quand je cherche le strum par sa chaîne strum
        // Quand je crée un objet
        $_strum = new strum($id_attendu, $strum_envoye, $unite_attendue, $longueur_attendue, $description_envoye);
        $_strum->nettoieValeursEscapeStrings();

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($strum_attendu, $_strum->getStrum());
        $this->assertEquals($description_attendue, $_strum->getDescription());
    }

    public function testNettoieChaineStrum()
    {
        // Etant données les valeurs suivantes

        $strum_attendu = "B BhXhBh";
        $strum_envoye = "B-Bh(X)hBh";

        // Quand je cherche le strum par sa chaîne strum
        // Quand je crée un objet
        $_strum = new strum();
        $_strum->setStrum($strum_envoye);
        $_strum->nettoieChaineStrum();

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($strum_attendu, $_strum->getStrum());
    }

    public function testRenommageStrum()
    {
        // Etant donné un strum utilisé dans plusieurs chansons
        $_strum = new strum(1);
        $chansons_rattachees_origine = $_strum->chansonsDuStrum();

        // Si je change sa chaîne strum
        $_strum->setStrum("B BH HHH");
        $_strum->modifieStrumBDD();
        $chansons_rattachees_ensuite = $_strum->chansonsDuStrum();

        // Alors les chansons rattachées sont toujours rattachées à ce nouveau strum
        $this->assertEquals($chansons_rattachees_origine, $chansons_rattachees_ensuite);

        // Je rétablis les données
        $_strum->setStrum("B BH HBH");
        $_strum->modifieStrumBDD();
    }

/*  Modèle de Mock
    public function testAvecMock()
    {
        $table = array(
            array(
                'task_id' => '1',
                'task_desc' => 'Task One Test'
            ),
            array(
                'task_id' => '2',
                'task_desc' => 'Task Two Test'
            )
        );

        $dbase = $this->getMockBuilder('Database')
            ->getMock();

        $dbase->method('resultSet')
            ->will($this->returnValue($table));
    }
*/
}