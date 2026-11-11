<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/public/autoload.php';
require_once __DIR__ . '/../src/public/php/lib/utilssi.php';
require_once __DIR__ . '/../src/public/php/lib/configMysql.php';
require_once __DIR__ . '/../src/public/php/playlist/playlist.php';
require_once __DIR__ . '/../src/public/php/playlist/PlaylistFormService.php';
require_once __DIR__ . '/../src/public/php/liens/lienChansonPlaylist.php';

class PlaylistFormAjaxAndUploadTest extends TestCase
{
    private string $tempImgPath = '';
    private int $testSongId = 0;
    private int $testPlaylistId = 0;

    protected function setUp(): void
    {
        global $mysqli;
        if (!isset($_SESSION['mysql']) || !$_SESSION['mysql']) {
            $_SESSION['mysql'] = $mysqli;
        }

        $_SESSION['privilege'] = 2; // Éditeur
        $_SESSION['id'] = 1;

        // Créer une chanson de test autonome pour garantir la compatibilité CI / GitHub Actions
        $db = $_SESSION['mysql'];
        if ($db) {
            $db->query("INSERT INTO chanson (nom, interprete, annee, idUser, tempo, mesure, pulsation, datePub, hits, tonalite, publication) 
                        VALUES ('Vieux Port Canopée Test', 'Arnal & Friends', 2026, 1, 120, '4/4', 'binaire', NOW(), 0, 'Am', 1)");
            $this->testSongId = (int)$db->insert_id;
        }
    }

    protected function tearDown(): void
    {
        $db = $_SESSION['mysql'] ?? ($GLOBALS['mysqli'] ?? null);
        if ($db) {
            if ($this->testSongId > 0) {
                $db->query("DELETE FROM chanson WHERE id = {$this->testSongId}");
            }
            if ($this->testPlaylistId > 0) {
                $db->query("DELETE FROM lienchansonplaylist WHERE id_playlist = {$this->testPlaylistId}");
                $db->query("DELETE FROM playlist WHERE id = {$this->testPlaylistId}");
            }
        }

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
            if ((int)$r['id'] === $this->testSongId) {
                $rows[] = $r;
            }
        }
        $stmt->close();

        $this->assertNotEmpty($rows, "La recherche 'Port' doit retourner la chanson créée en setUp.");
        $first = $rows[0];
        $this->assertEquals($this->testSongId, (int)$first['id']);
        $this->assertEquals('Vieux Port Canopée Test', $first['nom']);
        $this->assertEquals('Arnal & Friends', $first['interprete']);
        $this->assertEquals('Am', $first['tonalite']);
    }

    public function testAddSongAndDuplicateCheck(): void
    {
        $db = $_SESSION['mysql'] ?? ($GLOBALS['mysqli'] ?? null);
        $this->assertNotNull($db);

        // Créer une playlist de test
        creePlaylist("Test Playlist US-083 CI", "Description test", "01/01/2026", "test.jpg", 0, 0, "[]");
        $res = $db->query("SELECT id FROM playlist WHERE nom = 'Test Playlist US-083 CI' ORDER BY id DESC LIMIT 1");
        $row = $res->fetch_assoc();
        $this->testPlaylistId = (int)$row['id'];

        // 1. Ajout réussi avec la chanson créée en setUp
        $msg1 = PlaylistFormService::addSong($this->testPlaylistId, $this->testSongId);
        $this->assertEquals("Morceau ajouté avec succès !", $msg1);

        // 2. Détection de doublon
        $msg2 = PlaylistFormService::addSong($this->testPlaylistId, $this->testSongId);
        $this->assertEquals("Ce morceau est déjà dans la playlist.", $msg2);
    }

    public function testThumbnailGenerationOnTheFly(): void
    {
        $chansonDir = dirname(__DIR__) . '/src/public/data/chansons/' . $this->testSongId;
        if (!is_dir($chansonDir)) {
            mkdir($chansonDir, 0755, true);
        }

        // Créer une fausse couverture source PNG
        $sourceImg = imagecreatetruecolor(200, 200);
        $sourcePath = $chansonDir . '/test-cover.png';
        imagepng($sourceImg, $sourcePath);
        imagedestroy($sourceImg);

        $expectedMiniPath = $chansonDir . '/test-cover-mini.webp';
        if (file_exists($expectedMiniPath)) {
            unlink($expectedMiniPath);
        }

        // Appel de la méthode : doit générer la vignette mini.webp à la volée
        $coverUrl = "/data/chansons/{$this->testSongId}/test-cover.png";
        $thumbUrl = PlaylistFormService::getThumbnailUrlForCover($coverUrl);

        $this->assertEquals("/data/chansons/{$this->testSongId}/test-cover-mini.webp", $thumbUrl);
        $this->assertFileExists($expectedMiniPath);
        $this->assertGreaterThan(0, filesize($expectedMiniPath));

        // Deuxième appel : la vignette existe déjà, doit renvoyer immédiatement la même URL
        $thumbUrlCached = PlaylistFormService::getThumbnailUrlForCover($coverUrl);
        $this->assertEquals($thumbUrl, $thumbUrlCached);

        // Nettoyage
        @unlink($sourcePath);
        @unlink($expectedMiniPath);
        @rmdir($chansonDir);
    }
}
