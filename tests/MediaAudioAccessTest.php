<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (session_status() === PHP_SESSION_NONE) session_start();
$_SERVER['DOCUMENT_ROOT'] = "../";
require_once __DIR__ . "/../src/autoload.php";
require_once PHP_DIR . "/media/MediaService.php";
require_once PHP_DIR . "/media/MediaRenderer.php";

class MediaAudioAccessTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION['privilege'] = 0;
        unset($_SESSION['acces_audio_ateliers']);
        unset($_SERVER['HTTP_X_CANOPEE_PROXY']);
        unset($_SERVER['HTTP_X_CANOPEE_TOKEN']);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_ORIGIN']);
        unset($_GET['proxy_token']);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['acces_audio_ateliers']);
        unset($_SERVER['HTTP_X_CANOPEE_PROXY']);
        unset($_SERVER['HTTP_X_CANOPEE_TOKEN']);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_ORIGIN']);
        unset($_GET['proxy_token']);
    }

    public function testEstExtensionAudio()
    {
        $this->assertTrue(MediaService::estExtensionAudio('mp3'));
        $this->assertTrue(MediaService::estExtensionAudio('.M4A'));
        $this->assertTrue(MediaService::estExtensionAudio('aac'));
        $this->assertTrue(MediaService::estExtensionAudio('ogg'));
        $this->assertFalse(MediaService::estExtensionAudio('pdf'));
        $this->assertFalse(MediaService::estExtensionAudio('png'));
    }

    public function testEstAudioAccessibleInvite()
    {
        $_SESSION['privilege'] = $GLOBALS["PRIVILEGE_INVITE"] ?? 0;
        $this->assertFalse(MediaService::estAudioAccessible());
    }

    public function testEstAudioAccessibleMembre()
    {
        $_SESSION['privilege'] = $GLOBALS["PRIVILEGE_MEMBRE"] ?? 1;
        $this->assertTrue(MediaService::estAudioAccessible());
    }

    public function testEstAudioAccessibleViaProxyHeader()
    {
        $_SESSION['privilege'] = 0;
        $_SERVER['HTTP_X_CANOPEE_PROXY'] = 'ateliers';
        $this->assertTrue(MediaService::estAudioAccessible());
    }

    public function testEstAudioAccessibleViaTokenHeader()
    {
        $_SESSION['privilege'] = 0;
        $_SERVER['HTTP_X_CANOPEE_TOKEN'] = 'canopee_ateliers_secret_token_2026';
        $this->assertTrue(MediaService::estAudioAccessible());
    }

    public function testEstAudioAccessibleViaRefererAteliers()
    {
        $_SESSION['privilege'] = 0;
        $_SERVER['HTTP_REFERER'] = 'https://ateliers.canopee-musique.fr/chansons/123';
        $this->assertTrue(MediaService::estAudioAccessible());
    }

    public function testEstAudioAccessibleViaGetToken()
    {
        $_SESSION['privilege'] = 0;
        $_GET['proxy_token'] = 'canopee_ateliers_secret_token_2026';
        $this->assertTrue(MediaService::estAudioAccessible());
    }

    public function testMediaRendererRestreintChansonUrl()
    {
        $_SESSION['privilege'] = 0;
        $media = new Media([
            'type' => 'mp3',
            'titre' => 'Chanson Audio Test',
            'image' => 'cover.jpg',
            'auteur' => 1,
            'lien' => './php/document/getdoc.php?doc=999',
            'description' => 'Audio test',
            'tags' => 'audio'
        ]);

        $html = MediaRenderer::afficheComposantMedia($media);
        $this->assertStringContainsString('Connexion requise', $html);
        $this->assertStringNotContainsString('getdoc.php?doc=999', $html);
    }
}

