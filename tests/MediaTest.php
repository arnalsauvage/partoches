<?php
use PHPUnit\Framework\TestCase;

session_start();
$_SERVER['DOCUMENT_ROOT'] = "../";
require_once  "../php/lib/utilssi.php";
require_once "../php/media/media.php"; // Assurez-vous que le chemin est correct

class MediaTest extends TestCase
{
    const TITRE_MEDIA = "Titre de Test";
    const TYPE_MEDIA = "mp3";
    const IMAGE_MEDIA = "http://example.com/image.jpg";
    const AUTEUR_MEDIA = 1;
    const LIEN_MEDIA = "http://example.com/media.mp3";
    const DESCRIPTION_MEDIA = "Ceci est une description de test.";
    const TAGS_MEDIA = "test, media";
    const DATE_PUB_MEDIA = "2023-10-01";
    const HITS_MEDIA = 0;

    public function testConstructeur()
    {
        $media = new Media(self::TYPE_MEDIA, self::TITRE_MEDIA, self::IMAGE_MEDIA, self::AUTEUR_MEDIA, self::LIEN_MEDIA, self::DESCRIPTION_MEDIA, self::TAGS_MEDIA);
        $this->assertEquals(self::TITRE_MEDIA, $media->getTitre());
        $this->assertEquals(self::TYPE_MEDIA, $media->getType());
    }

    public function testEnregistreBDD()
    {
        $media = new Media(self::TYPE_MEDIA, self::TITRE_MEDIA, self::IMAGE_MEDIA, self::AUTEUR_MEDIA, self::LIEN_MEDIA, self::DESCRIPTION_MEDIA, self::TAGS_MEDIA);
        $mediaId = $media->persist();

        // On crée un autre objet pour écraser les valeurs
        $media = new Media("autre type", "autre titre", "http://example.com/autre.jpg", 1, "http://example.com/autre.mp3", "autre description", "autre, tags");

        // On vérifie que l'on peut le recharger
        $media->chercheMedia($mediaId);
        $this->assertEquals(self::TITRE_MEDIA, $media->getTitre());
        $this->assertEquals(self::TYPE_MEDIA, $media->getType());

        // On le supprime en BDD
        $media->supprimeMediaBDD();
    }

    public function testChercheMediaBDD()
    {
        $media = new Media(self::TYPE_MEDIA, self::TITRE_MEDIA, self::IMAGE_MEDIA, self::AUTEUR_MEDIA, self::LIEN_MEDIA, self::DESCRIPTION_MEDIA, self::TAGS_MEDIA);
        $media->persist();
        $id = $media->getId();
        $media = new Media();

        // On vérifie que l'on peut le recharger
        $media->chercheMedia($id);
        $this->assertEquals(self::TITRE_MEDIA, $media->getTitre());
        $this->assertEquals(self::TYPE_MEDIA, $media->getType());

        // On le supprime en BDD
        $media->supprimeMediaBDD();
        $this->assertEquals(0, $media->chercheMedia($media->getId()));
    }

    public function testInfosMedia()
    {
        $media = new Media(self::TYPE_MEDIA, self::TITRE_MEDIA, self::IMAGE_MEDIA, self::AUTEUR_MEDIA, self::LIEN_MEDIA, self::DESCRIPTION_MEDIA, self::TAGS_MEDIA);
        $media->persist();

        $infos = $media->infosMedia();
        $this->assertStringContainsString(self::TITRE_MEDIA, $infos);
        $this->assertStringContainsString(self::TYPE_MEDIA, $infos);

        // On le supprime en BDD
        $media->supprimeMediaBDD();
    }


    public function testChercheMediasParType()
    {
        $media = new Media(self::TYPE_MEDIA, self::TITRE_MEDIA, self::IMAGE_MEDIA, self::AUTEUR_MEDIA, self::LIEN_MEDIA, self::DESCRIPTION_MEDIA, self::TAGS_MEDIA);
        $media->persist();

        $medias = Media::chercheMediasParType(self::TYPE_MEDIA);
        $this->assertNotEmpty($medias);

        // On le supprime en BDD
        $media->supprimeMediaBDD();
    }

    public function testChercheMediasParTitre()
    {
        $media = new Media(self::TYPE_MEDIA, self::TITRE_MEDIA, self::IMAGE_MEDIA, self::AUTEUR_MEDIA, self::LIEN_MEDIA, self::DESCRIPTION_MEDIA, self::TAGS_MEDIA);
            $media->persist();

        $medias = Media::chercheMediasParTitre(self::TITRE_MEDIA);
        $this->assertNotEmpty($medias);

        // On le supprime en BDD
        $media->supprimeMediaBDD();
    }

    public function testMoteurRecherche()
    {
        // Test avec une recherche connue qui devrait retourner des résultats
        $recherche = self::TITRE_MEDIA;
        $media = new Media(self::TYPE_MEDIA, self::TITRE_MEDIA, self::IMAGE_MEDIA, self::AUTEUR_MEDIA, self::LIEN_MEDIA, self::DESCRIPTION_MEDIA, self::TAGS_MEDIA);
        $media->persist();

        $resultats = Media::moteurRecherche($recherche);
        $this->assertStringContainsString(self::TITRE_MEDIA, $resultats, "La recherche '$recherche' devrait retourner des résultats.");

        // On le supprime en BDD
        $media->supprimeMediaBDD();
    }

    /**
     * @dataProvider fournisseurDeRecherches
     */
    public function testMoteurRechercheAvecFournisseur($recherche, $attendu)
    {
        // Exécution de la recherche
        $media = new Media(self::TYPE_MEDIA, $attendu, self::IMAGE_MEDIA, self::AUTEUR_MEDIA, self::LIEN_MEDIA, self::DESCRIPTION_MEDIA, self::TAGS_MEDIA);
        $media->persist();

        $resultats = Media::moteurRecherche($recherche);

        // Vérification que le résultat attendu est dans les résultats retournés
        $this->assertStringContainsString($attendu, $resultats, "La recherche '$recherche' devrait retourner '$attendu'.");

        // On le supprime en BDD
        $media->supprimeMediaBDD();
    }

    public function testNormalize()
    {
        // Test des caractères accentués
        $this->assertEquals("aeiou", Media::normalize("àéîôù"));

        // Test de la conversion en minuscules
        $this->assertEquals("test", Media::normalize("TeSt"));

        // Test de la suppression des caractères non alphanumériques
        $this->assertEquals("test", Media::normalize("test!@#"));
        $this->assertEquals("test", Media::normalize("test$%^&*()"));

        // Test de la gestion des espaces multiples
        $this->assertEquals("test", Media::normalize("   test   "));
        $this->assertEquals("test test", Media::normalize("test    test"));

        // Test de la normalisation avec des caractères accentués et des espaces
        $this->assertEquals("a b c", Media::normalize("à b c"));
        $this->assertEquals("a b c", Media::normalize("À   B   C"));

        // Test de la suppression des caractères spéciaux
        $this->assertEquals("hello world", Media::normalize("hello@world!"));
        $this->assertEquals("hello world", Media::normalize("hello#world$"));

        // Test de la chaîne vide
        $this->assertEquals("", Media::normalize(""));

        // Test de la chaîne avec uniquement des espaces
        $this->assertEquals("", Media::normalize("     "));
    }

    public function fournisseurDeRecherches()
    {
        return [
            ["Titre de Test", "Titre de Test"],
            ["autre titre", "autre titre"],
            // Ajoutez d'autres cas de test ici
        ];
    }

    public function testTransformePartocheEnMedia()
    {

        // Appeler la méthode transformePartocheEnMedia
        $media = new Media();
        $media->transformePartocheEnMedia(1305);

        // Vérifier que le tableau retourné contient les bonnes valeurs

// Vérifier que l'objet Media contient les bonnes valeurs

        $this->assertEquals("Haere Mai", $media->getTitre());
        $this->assertEquals("partoche de la chanson de VSALELE 1955", $media->getDescription());
        $this->assertEquals(80, $media->getAuteur()); // Identifiant de l'utilisateur
        $this->assertEquals("2021-02-12", $media->getDatePub());
        $this->assertEquals("partoche", $media->getType());
        $this->assertEquals("partoche chanson 1955", $media->getTags());
        $this->assertEquals('./data/chansons/336/haereMai-v1.jpg', $media->getImage());
        $this->assertEquals('./php/document/getdoc.php?doc=1305', $media->getLien());
    }

// idDoc = 1305 pour haere mai

    public function testAjoutePartocheAvecPartocheReelle()
    {
        // Créer une instance de MediaManager
        $mediaManager = new Media();

        // Définir l'ID de la partoche à ajouter
        $idPartoche = 1824;

        // Appeler la méthode ajoutePartoche

        $mediaManager->ajoutePartoche($idPartoche);
        $mediaManager->chercheMediasParTitre(('Le dernier jour du disco'));

        $this->assertEquals("Dernier Jour Du Disco", $mediaManager->getTitre());

    }

    public function testResetAvecDernieresPartoches()
    {
        // Crée une instance de Media
        $mediaManager = new Media();

        // Appelle la méthode pour réinitialiser avec les 50 dernières partoches
        $result = $mediaManager->resetAvecDernieresPartoches(50);

        // Vérifie que la méthode retourne true (succès)
        $this->assertTrue($result, "La méthode resetAvecDernieresPartoches doit retourner true");

        // Compte le nombre de médias en base pour vérifier que c'est au maximum 50
        $mysqli = $_SESSION[Media::MYSQL];
        $res = $mysqli->query("SELECT COUNT(*) AS count FROM media");
        $row = $res->fetch_assoc();
        $count = (int)$row['count'];

        $this->assertLessThanOrEqual(50, $count, "Il doit y avoir au maximum 50 médias en base après reset");

        // Optionnel : vérifier que les médias sont bien les derniers (exemple de test simple)
        $res = $mysqli->query("SELECT datePub FROM media ORDER BY datePub DESC LIMIT 1");
        $dernier = $res->fetch_assoc();
        $this->assertNotNull($dernier, "Il doit y avoir au moins un média dans la table");
        $this->assertNotEmpty($dernier['datePub'], "Le dernier média doit avoir une date de publication");
    }


}
