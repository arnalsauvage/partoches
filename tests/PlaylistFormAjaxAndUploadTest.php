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

    public function testGenerateMosaicWithCovers(): void
    {
        $chansonDir = dirname(__DIR__) . '/src/public/data/chansons/' . $this->testSongId;
        if (!is_dir($chansonDir)) {
            mkdir($chansonDir, 0755, true);
        }

        $covers = [];
        for ($i = 1; $i <= 4; $i++) {
            $img = imagecreatetruecolor(100, 100);
            $path = $chansonDir . "/tile_$i.png";
            imagepng($img, $path);
            imagedestroy($img);
            $covers[] = "/data/chansons/{$this->testSongId}/tile_$i.png";
        }

        $mosaicFileName = PlaylistFormService::generateMosaic($covers, 'Test Mosaïque US', 999);
        $this->assertNotEmpty($mosaicFileName);
        $this->assertStringStartsWith('test-mosaique-us_999_', $mosaicFileName);
        $this->assertStringEndsWith('.jpg', $mosaicFileName);

        $playlistsDir = dirname(__DIR__) . '/src/public/data/playlists';
        $fullPath = $playlistsDir . '/' . $mosaicFileName;
        $this->assertFileExists($fullPath);
        $this->assertGreaterThan(0, filesize($fullPath));

        // Nettoyage
        @unlink($fullPath);
        for ($i = 1; $i <= 4; $i++) {
            @unlink($chansonDir . "/tile_$i.png");
        }
        @rmdir($chansonDir);
    }

    public function testPlaylistMosaicAjaxEndpointRejectsNonEditor(): void
    {
        $_SESSION['privilege'] = 0; // Visiteur non éditeur
        ob_start();
        require dirname(__DIR__) . '/src/public/php/playlist/playlist_mosaic_ajax.php';
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Accès refusé', $data['error']);
        $_SESSION['privilege'] = 2; // Rétablir
    }

    public function testPlaylistMosaicAjaxEndpointPreviewSuccess(): void
    {
        $_SESSION['privilege'] = 2; // Éditeur
        $_POST = [
            'action' => 'preview',
            'id' => 5,
            'nom' => 'Test Playlist',
            'covers' => ['/data/playlists/fraschetta.jpg'],
            'grid_size' => 4,
            'color_order' => 'manual'
        ];

        ob_start();
        require dirname(__DIR__) . '/src/public/php/playlist/playlist_mosaic_ajax.php';
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->assertIsArray($data, "Output doit être du JSON valide: " . $output);
        $this->assertTrue($data['success'] ?? false, "Response success doit être true: " . $output);
        $this->assertTrue($data['isPreview'] ?? false);
        $this->assertNotEmpty($data['fileName'] ?? '');
    }

    public function testGenerateMosaicGridNxNAndColorHarmony(): void
    {
        $chansonDir = dirname(__DIR__) . '/src/public/data/chansons/' . $this->testSongId;
        if (!is_dir($chansonDir)) {
            mkdir($chansonDir, 0755, true);
        }

        // Créer 9 images de couleurs distinctes (Rouge, Vert, Bleu, etc.)
        $colors = [
            [255, 0, 0],   // Rouge (H ~ 0)
            [0, 255, 0],   // Vert (H ~ 120)
            [0, 0, 255],   // Bleu (H ~ 240)
            [255, 255, 0], // Jaune (H ~ 60)
            [0, 255, 255], // Cyan (H ~ 180)
            [255, 0, 255], // Magenta (H ~ 300)
            [50, 50, 50],  // Sombre
            [200, 200, 200],// Clair
            [255, 140, 0]  // Orange (H ~ 33)
        ];

        $covers = [];
        foreach ($colors as $idx => $rgb) {
            $img = imagecreatetruecolor(50, 50);
            $col = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
            imagefill($img, 0, 0, $col);
            $path = $chansonDir . "/tile_color_{$idx}.png";
            imagepng($img, $path);
            imagedestroy($img);
            $covers[] = "/data/chansons/{$this->testSongId}/tile_color_{$idx}.png";
        }

        // 1. Test extraction HSL
        $hsl = PlaylistFormService::getImageDominantHsl($covers[0]); // Rouge
        $this->assertEqualsWithDelta(0.0, $hsl['h'], 5.0, "La teinte du rouge doit être proche de 0°");
        $this->assertGreaterThan(0.9, $hsl['s'], "La saturation du rouge pur doit être proche de 1");

        // 2. Test tri Rainbow
        $rainbowSorted = PlaylistFormService::sortImagesByColorHarmony($covers, 'rainbow', 3);
        $this->assertCount(9, $rainbowSorted);

        // 3. Test tri Luminosité
        $brightSorted = PlaylistFormService::sortImagesByColorHarmony($covers, 'brightness', 3);
        $this->assertCount(9, $brightSorted);

        // 4. Test génération mosaïque 3x3 (9) avec tri arc-en-ciel
        $mosaicFile = PlaylistFormService::generateMosaic($covers, 'Test Mosaïque 3x3', 888, 9, 'rainbow');
        $this->assertNotEmpty($mosaicFile);
        $this->assertStringStartsWith('test-mosaique-3x3_888_', $mosaicFile);

        $playlistsDir = dirname(__DIR__) . '/src/public/data/playlists';
        $fullPath = $playlistsDir . '/' . $mosaicFile;
        $this->assertFileExists($fullPath);

        // Vérifier les dimensions du canevas généré (600x600 pour 3x3)
        $sizeInfo = getimagesize($fullPath);
        $this->assertEquals(600, $sizeInfo[0]);
        $this->assertEquals(600, $sizeInfo[1]);

        // Nettoyage
        @unlink($fullPath);
        foreach ($colors as $idx => $rgb) {
            @unlink($chansonDir . "/tile_color_{$idx}.png");
        }
        @rmdir($chansonDir);
    }

    public function testMosaicPreviewAndApplyWorkflow(): void
    {
        $chansonDir = dirname(__DIR__) . '/src/public/data/chansons/' . $this->testSongId;
        if (!is_dir($chansonDir)) {
            mkdir($chansonDir, 0755, true);
        }

        $img = imagecreatetruecolor(50, 50);
        $path = $chansonDir . "/tile_test_preview.png";
        imagepng($img, $path);
        imagedestroy($img);
        $covers = ["/data/chansons/{$this->testSongId}/tile_test_preview.png"];

        // 1. Génération de l'aperçu (action: preview)
        $previewFile = PlaylistFormService::generateMosaic($covers, 'Preview Playlist', 777, 4, 'manual', true);
        $this->assertStringStartsWith('preview_preview-playlist_777_', $previewFile);

        $playlistsDir = dirname(__DIR__) . '/src/public/data/playlists';
        $previewFullPath = $playlistsDir . '/' . $previewFile;
        $this->assertFileExists($previewFullPath);

        // 2. Validation / officialisation de l'aperçu (action: apply)
        $finalFile = PlaylistFormService::applyMosaicPreview($previewFile, 'Preview Playlist', 777);
        $this->assertNotNull($finalFile);
        $this->assertStringStartsWith('preview-playlist_777_', $finalFile);
        $this->assertFalse(str_starts_with($finalFile, 'preview_preview-playlist'), "Le fichier définitif ne doit plus avoir le préfixe preview_");

        $finalFullPath = $playlistsDir . '/' . $finalFile;
        $this->assertFileExists($finalFullPath);
        $this->assertFileDoesNotExist($previewFullPath, "L'aperçu temporaire doit avoir été déplacé ou supprimé.");

        // Nettoyage
        @unlink($finalFullPath);
        @unlink($path);
        @rmdir($chansonDir);
    }

    public function testAddSongAndCheckOrderAndDisplay(): void
    {
        $db = $_SESSION['mysql'];
        $db->query("INSERT INTO playlist (nom, description, date_creation, id_utilisateur, type, criteres, image, hits) VALUES ('Test Pl Add', '', NOW(), 1, 0, '[]', '', 0)");
        $plId = (int)$db->insert_id;
        $this->testPlaylistId = $plId;

        $msg = PlaylistFormService::addSong($plId, $this->testSongId);
        $this->assertEquals("Morceau ajouté avec succès !", $msg);

        // 1. Vérifier dans la table lienchansonplaylist
        $check = $db->query("SELECT * FROM lienchansonplaylist WHERE id_playlist = $plId");
        $this->assertEquals(1, $check->num_rows, "La chanson doit être présente pour id_playlist = $plId");
        $row = $check->fetch_assoc();
        $this->assertEquals($this->testSongId, (int)$row['id_chanson'], "id_chanson doit correspondre");
        $this->assertEquals($plId, (int)$row['id_playlist'], "id_playlist doit correspondre");
        $this->assertEquals(1, (int)$row['ordre'], "ordre doit être 1");

        // 2. Vérifier avec chercheLiensChansonPlaylist
        $res = chercheLiensChansonPlaylist('id_playlist', $plId, "ordre", true);
        $this->assertEquals(1, $res->num_rows);
        $rowLien = $res->fetch_row();
        $this->assertEquals($this->testSongId, (int)$rowLien[1], "index 1 doit être id_chanson pour Chanson::load");

        // 3. Vérifier avec getMorceauxPlaylist
        $morceaux = getMorceauxPlaylist($plId, 'ordre');
        $this->assertEquals(1, $morceaux->num_rows);
    }

    public function testAutoRepairInvertedLinks(): void
    {
        $db = $_SESSION['mysql'];
        $db->query("INSERT INTO playlist (nom, description, date_creation, id_utilisateur, type, criteres, image, hits) VALUES ('Test Pl Inverted', '', NOW(), 1, 0, '[]', '', 0)");
        $plId = (int)$db->insert_id;

        // Simuler un lien inversé historique : id_playlist = chansonId, id_chanson = plId
        $db->query("INSERT INTO lienchansonplaylist (id_chanson, id_playlist, ordre) VALUES ($plId, {$this->testSongId}, 1)");
        $lienId = (int)$db->insert_id;

        // Appeler prepareData qui déclenche l'auto-réparation
        $data = PlaylistFormService::prepareData($plId);
        $this->assertEquals("MAJ", $data['mode']);

        // Vérifier que le lien a bien été réparé (id_chanson = testSongId, id_playlist = plId)
        $res = $db->query("SELECT * FROM lienchansonplaylist WHERE id = $lienId");
        $row = $res->fetch_assoc();
        $this->assertEquals($this->testSongId, (int)$row['id_chanson'], "Lien réparé : id_chanson doit être le morceau");
        $this->assertEquals($plId, (int)$row['id_playlist'], "Lien réparé : id_playlist doit être la playlist");

        // Nettoyage
        $db->query("DELETE FROM lienchansonplaylist WHERE id = $lienId");
        $db->query("DELETE FROM playlist WHERE id = $plId");
    }
}
