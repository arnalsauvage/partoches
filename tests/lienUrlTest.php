<?php
use PHPUnit\Framework\TestCase;
// require_once 'PHPUnit/Autoload.php';
session_start();
require_once "../php/lib/utilssi.php";
require_once "../php/lienUrl.php";

class lienUrlTest extends TestCase
{

    public function testcreeLienurlOk()
    {
        // Etant données les valeurs suivantes

        $lienUrl_attendu = "http://testlien/fr/bidule.html";
        $type_attendu = "vidéo";
        $description_attendue = "une vidéo";
        $table_attendue = "chanson";
        $id_attendu=12;
        $date_attendue = "24/12/2021";
        $idUserAttendu = 4;
        $hitsAttendus = 20;

        // Quand je cree un lienUrl en bdd
        creeLienurl($lienUrl_attendu, $type_attendu, $description_attendue, $table_attendue, $id_attendu, $date_attendue, $idUserAttendu, $hitsAttendus);
        $lien = chercheLiensUrlsTableId($table_attendue, $id_attendu)->fetch_row();
        $idgenere = $lien[0];

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($table_attendue, $lien[1]);
        $this->assertEquals($id_attendu, $lien[2]);
        $this->assertEquals($lienUrl_attendu, $lien[3]);
        $this->assertEquals($type_attendu, $lien[4]);
        $this->assertEquals($description_attendue, $lien[5]);
        $this->assertEquals($date_attendue, dateMysqlVersTexte($lien[6]));
        $this->assertEquals($idUserAttendu, $lien[7]);
        $this->assertEquals($hitsAttendus, $lien[8]);


        // Suppression de la donnée
        supprimeLienurl($idgenere);
    }

    public function testcreeLienurlKoExisteDeja()
    {
        // Etant données les valeurs suivantes

        $lienUrl_attendu = "http://testlien/fr/bidule.html";
        $type_attendu = "vidéo";
        $description_attendue = "une vidéo";
        $table_attendue = "chanson";
        $id_attendu=12;
        $date_attendue = "24/12/2021";
        $idUserAttendu = 4;
        $hitsAttendus = 20;

        // Quand je cree un lienUrl en bdd
        creeLienurl($lienUrl_attendu, $type_attendu, $description_attendue, $table_attendue, $id_attendu, $date_attendue, $idUserAttendu, $hitsAttendus);
        $lien = chercheLiensUrlsTableId($table_attendue, $id_attendu)->fetch_row();
        $idgenere = $lien[0];

        // Et que j'essaye de créer à nouveau un lien avec la même url
        $retour = creeLienurl($lienUrl_attendu, "audio", "autre desc", $table_attendue, $id_attendu, $date_attendue, $idUserAttendu, $hitsAttendus);

        // Alors j'obtiens un refus
        $this->assertEquals(false, $retour);

        // Suppression de la donnée
        supprimeLienurl($idgenere);
    }
    public function testAjouteHitk()
    {
        // Etant données les valeurs suivantes

        $lienUrl_attendu = "http://testlien/fr/bidule.html";
        $type_attendu = "vidéo";
        $description_attendue = "une vidéo";
        $table_attendue = "chanson";
        $id_attendu=12;
        $date_attendue = "24/12/2021";
        $idUserAttendu = 4;
        $hitsAttendus = 20;
        creeLienurl($lienUrl_attendu, $type_attendu, $description_attendue, $table_attendue, $id_attendu, $date_attendue, $idUserAttendu, $hitsAttendus);

        // Quand j'ajoute un hit au lienUrl en bdd

        $lien = chercheLiensUrlsTableId($table_attendue, $id_attendu)->fetch_row();
        $idgenere = $lien[0];
        ajouteUnHit($idgenere);
        $hitsAttendus++;
        $lien = chercheLiensUrlsTableId($table_attendue, $id_attendu)->fetch_row();

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals($hitsAttendus, $lien[8]);

        // Suppression de la donnée
        supprimeLienurl($idgenere);
    }
}