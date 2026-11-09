<?php
use PHPUnit\Framework\TestCase;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../src/public/php/lib/utilssi.php";
require_once __DIR__ . "/../src/public/php/liens/LienUrl.php";
require_once __DIR__ . "/../src/public/php/liens/lienurl_voir.php";

class LienUrlTest extends TestCase
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

    public function testPrepareYoutubeEmbedUrlFormatsDivers()
    {
        // URL watch standard
        $url1 = "https://www.youtube.com/watch?v=1poKE-rBeI4";
        $this->assertEquals("https://www.youtube-nocookie.com/embed/1poKE-rBeI4", prepareYoutubeEmbedUrl($url1));

        // URL raccourcie youtu.be
        $url2 = "https://youtu.be/1poKE-rBeI4";
        $this->assertEquals("https://www.youtube-nocookie.com/embed/1poKE-rBeI4", prepareYoutubeEmbedUrl($url2));

        // URL avec paramètres additionnels (&t=..., &feature=...)
        $url3 = "https://www.youtube.com/watch?v=1poKE-rBeI4&feature=related&t=45s";
        $this->assertEquals("https://www.youtube-nocookie.com/embed/1poKE-rBeI4", prepareYoutubeEmbedUrl($url3));

        // URL format Shorts
        $url4 = "https://www.youtube.com/shorts/1poKE-rBeI4";
        $this->assertEquals("https://www.youtube-nocookie.com/embed/1poKE-rBeI4", prepareYoutubeEmbedUrl($url4));

        // URL déjà au format embed youtube.com
        $url5 = "https://www.youtube.com/embed/1poKE-rBeI4";
        $this->assertEquals("https://www.youtube-nocookie.com/embed/1poKE-rBeI4", prepareYoutubeEmbedUrl($url5));

        // URL déjà au format youtube-nocookie
        $url6 = "https://www.youtube-nocookie.com/embed/1poKE-rBeI4";
        $this->assertEquals("https://www.youtube-nocookie.com/embed/1poKE-rBeI4", prepareYoutubeEmbedUrl($url6));
    }

    public function testAfficheLienVideoConformiteHtmlEtCss()
    {
        $lienData = [
            1, // id
            'chanson', // nomTable
            763, // idTable
            'https://www.youtube.com/watch?v=1poKE-rBeI4', // url
            'Vidéo', // type
            'Clip officiel de Gracias a la vida' // description
        ];

        $html = afficheLien($lienData);

        // Vérification de la présence du domaine sans cookie et des attributs iframe
        $this->assertStringContainsString("youtube-nocookie.com/embed/1poKE-rBeI4", $html);
        $this->assertStringContainsString("loading='lazy'", $html);
        $this->assertStringContainsString("referrerpolicy='strict-origin-when-cross-origin'", $html);
        $this->assertStringContainsString("allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'", $html);

        // Vérification Règle 4 de la Constitution : Aucun attribut style="..."
        $this->assertStringNotContainsString('style="', $html);
        $this->assertStringNotContainsString("style='", $html);
    }
}
