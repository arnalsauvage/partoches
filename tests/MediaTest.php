<?php
use PHPUnit\Framework\TestCase;
const PHPUNIT_RUNNING = true;
session_start();
$_SERVER['DOCUMENT_ROOT'] = "../";
require_once __DIR__ . "/../src/public/php/lib/utilssi.php";
require_once __DIR__ . "/../src/public/php/media/Media.php"; // Assurez-vous que le chemin est correct

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
        $data = [
            'type' => self::TYPE_MEDIA,
            'titre' => self::TITRE_MEDIA,
            'image' => self::IMAGE_MEDIA,
            'auteur' => self::AUTEUR_MEDIA,
            'lien' => self::LIEN_MEDIA,
            'description' => self::DESCRIPTION_MEDIA,
            'tags' => self::TAGS_MEDIA
        ];
        $media = new Media($data);
        $this->assertEquals(self::TITRE_MEDIA, $media->getTitre());
        $this->assertEquals(self::TYPE_MEDIA, $media->getType());
    }

    protected function setUp(): void
    {
        $media = new Media();
        $media->resetMediaTable(); // Utilise maintenant les valeurs par défaut
    }

    public function testEnregistreBDD()
    {
        $data = [
            'type' => self::TYPE_MEDIA,
            'titre' => self::TITRE_MEDIA,
            'image' => self::IMAGE_MEDIA,
            'auteur' => self::AUTEUR_MEDIA,
            'lien' => self::LIEN_MEDIA,
            'description' => self::DESCRIPTION_MEDIA,
            'tags' => self::TAGS_MEDIA
        ];
        $media = new Media($data);
        $mediaId = $media->persist();

        // On recharge le média pour vérifier
        $mediaCharge = new Media();
        $mediaCharge->chercheMedia($mediaId);
        $this->assertEquals(self::TITRE_MEDIA, $mediaCharge->getTitre());
        $this->assertEquals(self::TYPE_MEDIA, $mediaCharge->getType());

        // On le supprime en BDD
        $mediaCharge->supprimeMediaBDD();
    }

    public function testChercheMediaBDD()
    {
        $data = [
            'type' => self::TYPE_MEDIA,
            'titre' => self::TITRE_MEDIA,
            'image' => self::IMAGE_MEDIA,
            'auteur' => self::AUTEUR_MEDIA,
            'lien' => self::LIEN_MEDIA,
            'description' => self::DESCRIPTION_MEDIA,
            'tags' => self::TAGS_MEDIA
        ];
        $media = new Media($data);
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
        $data = [
            'type' => self::TYPE_MEDIA,
            'titre' => self::TITRE_MEDIA,
            'image' => self::IMAGE_MEDIA,
            'auteur' => self::AUTEUR_MEDIA,
            'lien' => self::LIEN_MEDIA,
            'description' => self::DESCRIPTION_MEDIA,
            'tags' => self::TAGS_MEDIA
        ];
        $media = new Media($data);
        $media->persist();

        $infos = $media->infosMedia();
        $this->assertStringContainsString(self::TITRE_MEDIA, $infos);

        // On le supprime en BDD
        $media->supprimeMediaBDD();
    }


    public function testChercheMediasParType()
    {
        $data = [
            'type' => self::TYPE_MEDIA,
            'titre' => self::TITRE_MEDIA,
            'image' => self::IMAGE_MEDIA,
            'auteur' => self::AUTEUR_MEDIA,
            'lien' => self::LIEN_MEDIA,
            'description' => self::DESCRIPTION_MEDIA,
            'tags' => self::TAGS_MEDIA
        ];
        $media = new Media($data);
        $media->persist();

        $medias = Media::chercheMediasParType(self::TYPE_MEDIA);
        $this->assertNotEmpty($medias);

        // On le supprime en BDD
        $media->supprimeMediaBDD();
    }

    public static function fournisseurDeRecherches()
    {
        return [
            ["Titre de Test", "Titre de Test"],
            ["autre titre", "autre titre"],
            // Ajoutez d'autres cas de test ici
        ];
    }

    public function testTransformeDocumentEnMedia()
    {

        // Appeler la méthode transformeDocumentEnMedia
        $media = new Media();
        $media->transformeDocumentEnMedia(1305);

        // Vérifier que l'objet Media contient les bonnes valeurs
        $this->assertEquals("Haere Mai", $media->getTitre());
        $this->assertStringContainsString("Partoche", $media->getDescription());
        $this->assertEquals(1, $media->getAuteur()); // Identifiant de l'utilisateur
        $this->assertEquals("2022-03-25", $media->getDatePub());
        $this->assertEquals("partoche", $media->getType());
        $this->assertEquals("partoche 1955", $media->getTags());
        $this->assertStringContainsString('haereMai', $media->getImage());
        $this->assertStringContainsString('doc=1305', $media->getLien());
    }

    public function testAjouteDocumentAvecPartocheReelle()
    {
        // Créer une instance de Media
        $mediaManager = new Media();

        // Définir l'ID de la partoche à ajouter
        $idPartoche = 1824;

        // Appeler la méthode ajouteDocument
        $mediaManager->ajouteDocument($idPartoche);
        
        $mediaManager->chercheMedia($mediaManager->getId());
        $this->assertStringContainsString("Disco", $mediaManager->getTitre());
    }

    public function testResetAvecDernieresPartoches()
    {
        // Crée une instance de Media
        $mediaManager = new Media();

        // Appelle la méthode pour réinitialiser avec les 50 dernières partoches
        $result = $mediaManager->resetAvecDernieresPartoches(500);

        // Vérifie que la méthode retourne true (succès)
        $this->assertTrue($result, "La méthode resetAvecDernieresPartoches doit retourner true");

        // Compte le nombre de médias en base
        $mysqli = $_SESSION[Media::MYSQL];
        $res = $mysqli->query("SELECT COUNT(*) AS count FROM media WHERE type='partoche'");
        $row = $res->fetch_assoc();
        $count = (int)$row['count'];

        $this->assertLessThanOrEqual(500, $count, "Il doit y avoir au maximum 500 médias en base après reset");
    }


    public function testResetAvecDernieresVideos()
    {
        // Crée une instance de Media
        $mediaManager = new Media();

        // Appelle la méthode pour réinitialiser avec les dernières vidéos
        $result = $mediaManager->resetAvecDernieresVideos(2);

        // Vérifie que la méthode retourne true (succès)
        $this->assertTrue($result, "La méthode testResetAvecDernieresVideos doit retourner true");

        // Compte le nombre de médias en base
        $mysqli = $_SESSION[Media::MYSQL];
        $res = $mysqli->query("SELECT COUNT(*) AS count FROM media WHERE type LIKE 'vid%'");
        $row = $res->fetch_assoc();
        $count = (int)$row['count'];

        $this->assertGreaterThanOrEqual(0, $count, "Il peut y avoir des médias en base après reset");
    }

    public function testResetMediasDistribues()
    {
        // Crée une instance de Media
        $mediaManager = new Media();

        // Choisit un nombre total de médias à réinitialiser
        $totalMedias = 100;

        // Appelle la méthode pour réinitialiser distribuée
        $nbTraites = $mediaManager->resetMediasDistribues($totalMedias);

        // Vérifie que les nombres retournés sont cohérents
        $this->assertIsArray($nbTraites);
        
        $somme = array_sum($nbTraites);
        // Dans notre nouvelle version, la somme peut être supérieure ou égale car on indexe tout
        $this->assertGreaterThanOrEqual($totalMedias, $somme, "La somme des médias traités doit être au moins égale au total demandé");

        // Vérifie que des médias existent en base
        $mysqli = $_SESSION[Media::MYSQL];

        $resVideos = $mysqli->query("SELECT COUNT(*) AS count FROM media WHERE type LIKE 'vid%'");
        $rowVideos = $resVideos->fetch_assoc();
        $countVideos = (int)$rowVideos['count'];
        $this->assertGreaterThanOrEqual(0, $countVideos);
    }

}
