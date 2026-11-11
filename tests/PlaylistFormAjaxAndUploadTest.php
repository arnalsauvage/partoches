<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/public/php/playlist/playlist.php';
require_once __DIR__ . '/../src/public/php/playlist/PlaylistFormService.php';
require_once __DIR__ . '/../src/public/php/liens/lienChansonPlaylist.php';

class PlaylistFormAjaxAndUploadTest extends TestCase
{
    private string $tempImgPath = '';

    protected function setUp(): void
    {
        $_SESSION['privilege'] = 2; // Éditeur
        $_SESSION['id'] = 1;
    }

    protected function tearDown(): void
    {
        if (!empty($this->tempImgPath) && file_exists($this->tempImgPath)) {
            @unlink($this->tempImgPath);
        }
    }

    public function testHandleIllustrationUploadRejectsErrors(): void
    {
        $file = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 0
        ];
        $result = PlaylistFormService::handleIllustrationUpload($file, 1, 'Ma Playlist');
        $this->assertNull($result);
    }

    public function testHandleIllustrationUploadRejectsOversized(): void
    {
        $file = [
            'name' => 'huge.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => sys_get_temp_dir() . '/fake.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 10 * 1024 * 1024 // 10MB
        ];
        $result = PlaylistFormService::handleIllustrationUpload($file, 1, 'Ma Playlist');
        $this->assertNull($result);
    }

    public function testHandleIllustrationUploadRejectsNonImageMime(): void
    {
        // Créer un faux fichier texte
        $fakeTxt = sys_get_temp_dir() . '/fake.txt';
        file_put_contents($fakeTxt, 'ceci est un fichier texte');
        $this->tempImgPath = $fakeTxt;

        $file = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => $fakeTxt,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($fakeTxt)
        ];
        $result = PlaylistFormService::handleIllustrationUpload($file, 1, 'Ma Playlist');
        $this->assertNull($result);
    }

    public function testHandleIllustrationUploadSuccessValidPng(): void
    {
        // Créer une image PNG valide minimale avec GD
        $img = imagecreatetruecolor(50, 50);
        $tempPng = sys_get_temp_dir() . '/test_upload_' . uniqid() . '.png';
        imagepng($img, $tempPng);
        imagedestroy($img);
        $this->tempImgPath = $tempPng;

        $file = [
            'name' => 'test_cover.png',
            'type' => 'image/png',
            'tmp_name' => $tempPng,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempPng)
        ];

        $filename = PlaylistFormService::handleIllustrationUpload($file, 999, 'Canopée Live 2026');
        $this->assertNotNull($filename);
        $this->assertStringStartsWith('canopee_live_2026_999_', $filename);
        $this->assertStringEndsWith('.png', $filename);

        $targetDir = defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR . '/playlists' : dirname(__DIR__, 2) . '/data/playlists';
        $fullUploadedPath = $targetDir . '/' . $filename;
        $this->assertFileExists($fullUploadedPath);

        // Nettoyage du fichier créé
        @unlink($fullUploadedPath);
    }

    public function testRechercheChansonSqlPreparedWithTonalite(): void
    {
        $db = $_SESSION['mysql'] ?? ($GLOBALS['mysqli'] ?? null);
        $this->assertNotNull($db, "Connexion MySQL active attendue.");

        $query = 'Port';
        $searchTerm = '%' . $query . '%';

        $stmt = $db->prepare("SELECT id, nom, interprete, tonalite FROM chanson WHERE nom LIKE ? OR interprete LIKE ? ORDER BY nom ASC LIMIT 20");
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        $stmt->close();

        $this->assertNotEmpty($rows, "La recherche 'Port' doit retourner au moins une chanson.");
        $first = $rows[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('nom', $first);
        $this->assertArrayHasKey('interprete', $first);
        $this->assertArrayHasKey('tonalite', $first);
        $this->assertStringContainsString('Port', $first['nom']);
    }

    public function testAddSongAndDuplicateCheck(): void
    {
        $db = $_SESSION['mysql'] ?? ($GLOBALS['mysqli'] ?? null);
        $this->assertNotNull($db);

        // Créer une playlist de test
        creePlaylist("Test Playlist US-083", "Description test", "01/01/2026", "test.jpg", 0, 0, "[]");
        $res = $db->query("SELECT id FROM playlist WHERE nom = 'Test Playlist US-083' ORDER BY id DESC LIMIT 1");
        $row = $res->fetch_assoc();
        $testPlaylistId = (int)$row['id'];

        // Trouver une chanson
        $resCh = $db->query("SELECT id FROM chanson LIMIT 1");
        $chRow = $resCh->fetch_assoc();
        $testChansonId = (int)$chRow['id'];

        // 1. Ajout réussi
        $msg1 = PlaylistFormService::addSong($testPlaylistId, $testChansonId);
        $this->assertEquals("Morceau ajouté avec succès !", $msg1);

        // 2. Détection de doublon
        $msg2 = PlaylistFormService::addSong($testPlaylistId, $testChansonId);
        $this->assertEquals("Ce morceau est déjà dans la playlist.", $msg2);

        // Nettoyage
        supprimePlaylist($testPlaylistId);
    }
}
