<?php
use PHPUnit\Framework\TestCase;
if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (session_status() === PHP_SESSION_NONE) session_start();
$_SERVER['DOCUMENT_ROOT'] = "../";
require_once __DIR__ . "/../src/autoload.php";

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

    protected function setUp(): void
    {
        MediaRepository::truncateTable();
    }

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
        $mediaId = MediaRepository::persist($media);

        // On recharge le média pour vérifier
        $mediaCharge = MediaRepository::chercheMedia($mediaId);
        $this->assertNotNull($mediaCharge);
        $this->assertEquals(self::TITRE_MEDIA, $mediaCharge->getTitre());
        $this->assertEquals(self::TYPE_MEDIA, $mediaCharge->getType());

        // On le supprime en BDD
        MediaRepository::supprimeMediaBDD($mediaId);
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
        MediaRepository::persist($media);
        $id = $media->getId();

        // On vérifie que l'on peut le recharger
        $mediaCharge = MediaRepository::chercheMedia($id);
        $this->assertNotNull($mediaCharge);
        $this->assertEquals(self::TITRE_MEDIA, $mediaCharge->getTitre());

        // On le supprime en BDD
        MediaRepository::supprimeMediaBDD($id);
        $this->assertNull(MediaRepository::chercheMedia($id));
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
        MediaRepository::persist($media);

        $medias = MediaRepository::chercheMediasParType(self::TYPE_MEDIA);
        $this->assertNotEmpty($medias);

        // On le supprime en BDD
        MediaRepository::supprimeMediaBDD($media->getId());
    }

    public function testTransformeDocumentEnMedia()
    {
        $media = new Media();
        MediaService::transformeDocumentEnMedia($media, 1305);

        // Vérifier que l'objet Media contient les bonnes valeurs
        $this->assertEquals("Haere Mai", $media->getTitre());
        $this->assertStringContainsString("Partoche", $media->getDescription());
        $this->assertEquals(1, $media->getAuteur());
        $this->assertEquals("2022-03-25", $media->getDatePub());
        $this->assertEquals("partoche", $media->getType());
        $this->assertEquals("partoche 1955", $media->getTags());
    }

    public function testResetMediasDistribues()
    {
        // On demande un reset distribué
        $nbTraites = MediaService::resetMediasDistribues(10);

        // Vérifie que les nombres retournés sont cohérents
        $this->assertIsArray($nbTraites);
        $this->assertCount(4, $nbTraites);

        // Vérifie que des médias existent en base
        $count = MediaRepository::compteTousLesMedias(['tous']);
        $this->assertGreaterThan(0, $count);
    }

    public function testRenderer()
    {
        $media = new Media([
            'type' => 'partoche',
            'titre' => 'Test Renderer',
            'lien' => 'http://test.com/doc=123'
        ]);
        
        $html = MediaRenderer::afficheComposantMedia($media);
        $this->assertStringContainsString('Test Renderer', $html);
        $this->assertStringContainsString('badge bg-danger', $html); // Danger = Partoche
        $this->assertStringContainsString('🎵', $html); // Emoji partoche
    }

    public function testUpdateMediaBDD()
    {
        $data = [
            'type' => 'mp3',
            'titre' => 'Titre Avant Modif',
            'image' => 'http://example.com/image.jpg',
            'auteur' => 1,
            'lien' => 'http://example.com/media.mp3',
            'description' => 'Description initiale',
            'tags' => 'tag1, tag2'
        ];
        $media = new Media($data);
        $mediaId = MediaRepository::persist($media);
        $this->assertGreaterThan(0, $mediaId);

        // Modification
        $mediaModif = MediaRepository::chercheMedia($mediaId);
        $mediaModif->setTitre('Titre Apres Modif');
        $mediaModif->setDescription('Description mise a jour');
        MediaRepository::persist($mediaModif);

        // Rechargement
        $mediaApres = MediaRepository::chercheMedia($mediaId);
        $this->assertEquals('Titre Apres Modif', $mediaApres->getTitre());
        $this->assertEquals('Description mise a jour', $mediaApres->getDescription());

        // Nettoyage
        MediaRepository::supprimeMediaBDD($mediaId);
    }

    public function testEstExtensionAudio()
    {
        $this->assertTrue(MediaService::estExtensionAudio('mp3'));
        $this->assertTrue(MediaService::estExtensionAudio('M4A'));
        $this->assertTrue(MediaService::estExtensionAudio('.aac'));
        $this->assertTrue(MediaService::estExtensionAudio('ogg'));
        $this->assertTrue(MediaService::estExtensionAudio('wav'));

        $this->assertFalse(MediaService::estExtensionAudio('pdf'));
        $this->assertFalse(MediaService::estExtensionAudio('jpg'));
        $this->assertFalse(MediaService::estExtensionAudio('png'));
    }

    public function testEstAudioAccessible()
    {
        $_SESSION['privilege'] = 0;
        $this->assertFalse(MediaService::estAudioAccessible());

        $_SESSION['privilege'] = 1;
        $this->assertTrue(MediaService::estAudioAccessible());

        $_SESSION['privilege'] = 2;
        $this->assertTrue(MediaService::estAudioAccessible());
    }

    public function testRendererRestrictedAudio()
    {
        $_SESSION['privilege'] = 0; // Invité
        $media = new Media([
            'type' => 'mp3',
            'titre' => 'Audio Restreint',
            'lien' => 'http://test.com/chanson.mp3'
        ]);

        $html = MediaRenderer::afficheComposantMedia($media);
        $this->assertStringContainsString('Audio Restreint', $html);
        $this->assertStringContainsString('Connexion requise', $html);
    }
}
