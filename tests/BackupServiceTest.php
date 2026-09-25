<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (session_status() === PHP_SESSION_NONE) session_start();
$_SERVER['DOCUMENT_ROOT'] = "../";
require_once __DIR__ . "/../src/autoload.php";
require_once PHP_DIR . "/admin/BackupService.php";

class BackupServiceTest extends TestCase
{
    private BackupService $service;
    private array $tempFiles = [];

    protected function setUp(): void
    {
        $db = $_SESSION['mysql'];
        $rootDir = sys_get_temp_dir() . '/partoches_test_backup_' . uniqid();
        @mkdir($rootDir . '/data/conf', 0777, true);
        @file_put_contents($rootDir . '/data/conf/params.ini', "[general]\nversion=2.1\n");
        $this->tempFiles[] = $rootDir;
        $this->service = new BackupService($db, $rootDir);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                if (is_dir($file)) {
                    $it = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($it as $sub) {
                        $sub->isDir() ? rmdir($sub->getPathname()) : unlink($sub->getPathname());
                    }
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }
        }
    }

    public function testGenerateDatabaseDump()
    {
        $dump = $this->service->generateDatabaseDump();
        $this->assertNotEmpty($dump);
        $this->assertStringContainsString('DUMP DE SAUVEGARDE COMPLET PARTOCHES', $dump);
        $this->assertStringContainsString('CREATE TABLE', $dump);
    }

    public function testCreateBackupZip()
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive non disponible.');
        }

        $zipPath = sys_get_temp_dir() . '/test_backup_' . time() . '.zip';
        $this->tempFiles[] = $zipPath;

        $createdZip = $this->service->createBackupZip($zipPath);
        $this->assertFileExists($createdZip);

        $zip = new ZipArchive();
        $res = $zip->open($createdZip);
        $this->assertTrue($res);

        $this->assertNotFalse($zip->locateName('db_dump.sql'));
        $this->assertNotFalse($zip->locateName('manifest.json'));

        $manifestContent = $zip->getFromName('manifest.json');
        $this->assertNotEmpty($manifestContent);

        $json = json_decode($manifestContent, true);
        $this->assertEquals('Partoches Canopée', $json['app']);
        $this->assertEquals('2.1', $json['version']);

        $zip->close();
    }
}
