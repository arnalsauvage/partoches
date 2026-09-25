<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (session_status() === PHP_SESSION_NONE) session_start();
$_SERVER['DOCUMENT_ROOT'] = "../";
require_once __DIR__ . "/../src/autoload.php";
require_once PHP_DIR . "/admin/DataAuditService.php";

class DataAuditServiceTest extends TestCase
{
    private string $tempDataDir;
    private DataAuditService $service;

    protected function setUp(): void
    {
        $this->tempDataDir = sys_get_temp_dir() . '/partoches_test_audit_' . rand(1000, 9999);
        if (!is_dir($this->tempDataDir)) {
            mkdir($this->tempDataDir, 0777, true);
        }

        $db = $_SESSION['mysql'];
        $this->service = new DataAuditService($db, $this->tempDataDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDataDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testGetReferencedFilesFromDb()
    {
        $referenced = $this->service->getReferencedFilesFromDb();
        $this->assertIsArray($referenced);
    }

    public function testFindOrphanFilesAndDeletion()
    {
        // 1. Créer un fichier orphelin
        $orphanFile = $this->tempDataDir . '/test_orphan_file.txt';
        file_put_contents($orphanFile, "Contenu de test orphelin " . rand(1000, 9999));

        // 2. Détecter l'orphelin
        $orphans = $this->service->findOrphanFiles();
        $this->assertNotEmpty($orphans);

        $foundRelPath = null;
        foreach ($orphans as $o) {
            if ($o['filename'] === 'test_orphan_file.txt') {
                $foundRelPath = $o['relativePath'];
                break;
            }
        }
        $this->assertNotNull($foundRelPath);

        // 3. Supprimer le fichier orphelin
        $deletedCount = $this->service->deleteOrphanFiles([$foundRelPath]);
        $this->assertEquals(1, $deletedCount);
        $this->assertFileDoesNotExist($orphanFile);
    }

    public function testFindDuplicateFiles()
    {
        $content = "Contenu identique pour tester les doublons MD5 avec au moins 100 octets de texte pour dépasser le filtre minimal de taille.";
        $file1 = $this->tempDataDir . '/dup1.txt';
        $file2 = $this->tempDataDir . '/dup2.txt';

        file_put_contents($file1, $content);
        file_put_contents($file2, $content);

        $duplicates = $this->service->findDuplicateFiles();
        $this->assertNotEmpty($duplicates);

        $foundDupGroup = null;
        foreach ($duplicates as $d) {
            if ($d['hash'] === md5($content)) {
                $foundDupGroup = $d;
                break;
            }
        }

        $this->assertNotNull($foundDupGroup);
        $this->assertEquals(2, $foundDupGroup['count']);
    }

    public function testFindDuplicateFilesWithCache()
    {
        $content = "Contenu identique pour tester le cache persistant des empreintes MD5 avec plus de 100 octets de texte.";
        $file1 = $this->tempDataDir . '/dup1.txt';
        $file2 = $this->tempDataDir . '/dup2.txt';

        file_put_contents($file1, $content);
        file_put_contents($file2, $content);

        // Scan 1 : alimente le cache
        $dup1 = $this->service->findDuplicateFiles();
        $this->assertNotEmpty($dup1);

        $cacheFile = $this->tempDataDir . '/temp/md5_cache.json';
        $this->assertFileExists($cacheFile);

        // Scan 2 : réutilise le cache
        $dup2 = $this->service->findDuplicateFiles();
        $this->assertNotEmpty($dup2);
        $this->assertEquals(count($dup1), count($dup2));
    }

    public function testFindAndDeleteOrphanDirectories()
    {
        // 1. Créer un dossier orphelin fictif chansons/999999
        $orphanDir = $this->tempDataDir . '/chansons/999999';
        mkdir($orphanDir, 0777, true);
        file_put_contents($orphanDir . '/sample.pdf', 'pdf content');

        // 2. Détecter le dossier orphelin
        $orphanDirs = $this->service->findOrphanDirectories();
        $this->assertNotEmpty($orphanDirs);

        $found = false;
        foreach ($orphanDirs as $dir) {
            if ($dir['relativePath'] === 'chansons/999999') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // 3. Supprimer le dossier orphelin
        $res = $this->service->deleteOrphanDirectory('chansons/999999');
        $this->assertTrue($res);
        $this->assertDirectoryDoesNotExist($orphanDir);
    }

    public function testFindAndCleanOrphanDbRelations()
    {
        $db = $_SESSION['mysql'];

        // 1. Insérer un document rattaché à une chanson fictive #999999
        $db->query("INSERT INTO document (nom, nomTable, idTable) VALUES ('test_orphan_doc.pdf', 'chanson', 999999)");
        $docId = $db->insert_id;

        // 2. Insérer un lienurl rattaché à une chanson fictive #999999
        $db->query("INSERT INTO lienurl (url, nomTable, idTable) VALUES ('https://test.com/orphan', 'chanson', 999999)");
        $urlId = $db->insert_id;

        // 3. Détecter les relations orphelines
        $relations = $this->service->findOrphanDbRelations();
        $this->assertNotEmpty($relations);

        $foundDoc = false;
        $foundUrl = false;
        foreach ($relations as $rel) {
            if ($rel['table'] === 'document' && $rel['id'] === (int)$docId) $foundDoc = true;
            if ($rel['table'] === 'lienurl' && $rel['id'] === (int)$urlId) $foundUrl = true;
        }

        $this->assertTrue($foundDoc, "Le document orphelin doit être détecté");
        $this->assertTrue($foundUrl, "Le lien URL orphelin doit être détecté");

        // 4. Nettoyer les relations orphelines
        $cleanedCount = $this->service->cleanOrphanDbRelations();
        $this->assertGreaterThanOrEqual(2, $cleanedCount);

        // 5. Vérifier la suppression en BDD
        $resDoc = $db->query("SELECT id FROM document WHERE id = $docId");
        $this->assertEquals(0, $resDoc->num_rows);

        $resUrl = $db->query("SELECT id FROM lienurl WHERE id = $urlId");
        $this->assertEquals(0, $resUrl->num_rows);
    }
}
