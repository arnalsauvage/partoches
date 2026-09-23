<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/public/php/playlist/playlist.php';

class PlaylistTest extends TestCase
{
    public function testCherchePlaylists()
    {
        $res = cherchePlaylists('nom', '%', 'nom', true);
        $this->assertNotNull($res);
    }

    public function testPlaylistFormServicePrepareData()
    {
        // On s'assure d'avoir au moins le privilège membre/éditeur pour le test
        $_SESSION['privilege'] = 2;
        $_SESSION['id'] = 1;

        $context = PlaylistFormService::prepareData(0);
        $this->assertArrayHasKey('mode', $context);
        $this->assertEquals('INS', $context['mode']);
    }
}
