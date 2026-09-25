<?php
/**
 * SERVICE : BackupService
 * Gère la génération du dump SQL de la base de données et l'exportation ZIP intégrale.
 */

require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';

class BackupService
{
    private mysqli $db;
    private string $rootDir;
    private string $dataDir;

    public function __construct(?mysqli $db = null, ?string $rootDir = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!$db && empty($_SESSION['mysql'])) {
            require_once dirname(__DIR__) . '/lib/configMysql.php';
        }
        $this->db = $db ?? $_SESSION['mysql'] ?? ($GLOBALS['conn'] ?? null);
        $this->rootDir = $rootDir ?? (defined('ROOT_DIR') ? ROOT_DIR : dirname(__DIR__, 2));
        $this->dataDir = $rootDir ? $rootDir . '/data' : (defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR : $this->rootDir . '/data');
    }

    /**
     * Génère le dump SQL complet de la base de données.
     */
    public function generateDatabaseDump(): string
    {
        $sql = "-- =====================================================\n";
        $sql .= "-- DUMP DE SAUVEGARDE COMPLET PARTOCHES (CANOPÉE)\n";
        $sql .= "-- Date : " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- =====================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        $tablesResult = $this->db->query("SHOW TABLES");
        if (!$tablesResult) return $sql;

        while ($tableRow = $tablesResult->fetch_row()) {
            $tableName = $tableRow[0];

            // 1. Structure de la table
            $sql .= "-- -----------------------------------------------------\n";
            $sql .= "-- Structure de la table `$tableName` \n";
            $sql .= "-- -----------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `$tableName`;\n";

            $createResult = $this->db->query("SHOW CREATE TABLE `$tableName`");
            if ($createResult && $createRow = $createResult->fetch_row()) {
                $sql .= $createRow[1] . ";\n\n";
            }

            // 2. Données de la table
            $dataResult = $this->db->query("SELECT * FROM `$tableName`");
            if ($dataResult && $dataResult->num_rows > 0) {
                $sql .= "-- Données de la table `$tableName` \n";

                while ($row = $dataResult->fetch_assoc()) {
                    $keys = array_map(fn($k) => "`$k`", array_keys($row));
                    $values = array_map(function ($val) {
                        if ($val === null) return "NULL";
                        return "'" . $this->db->real_escape_string((string)$val) . "'";
                    }, array_values($row));

                    $sql .= "INSERT INTO `$tableName` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        return $sql;
    }

    /**
     * Crée une archive ZIP complète contenant BDD SQL, data/ et conf/params.ini.
     * @return string Chemin physique du fichier ZIP temporaire créé.
     * @throws Exception Si ZipArchive manque ou échoue.
     */
    public function createBackupZip(?string $destZipPath = null): string
    {
        if (empty($destZipPath)) {
            $tempDir = sys_get_temp_dir();
            $destZipPath = $tempDir . '/partoches-backup-' . date('Y-m-d-His') . '.zip';
        }

        // Mode 1 : PHP ZipArchive extension
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($destZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                // 1. Ajout du dump SQL BDD
                $dumpSql = $this->generateDatabaseDump();
                $zip->addFromString('db_dump.sql', $dumpSql);

                // 2. Ajout du fichier de configuration (params.ini) s'il existe
                $iniPath1 = $this->rootDir . '/conf/params.ini';
                $iniPath2 = dirname($this->rootDir) . '/conf/params.ini';
                if (file_exists($iniPath1)) {
                    $zip->addFile($iniPath1, 'conf/params.ini');
                } elseif (file_exists($iniPath2)) {
                    $zip->addFile($iniPath2, 'conf/params.ini');
                }

                // 3. Ajout du répertoire data/
                if (is_dir($this->dataDir)) {
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($this->dataDir, RecursiveDirectoryIterator::SKIP_DOTS)
                    );

                    foreach ($iterator as $file) {
                        if ($file->isDir()) continue;

                        $fullPath = $file->getPathname();
                        $relPath = ltrim(str_replace('\\', '/', str_replace($this->dataDir, '', $fullPath)), '/');

                        // Ignorer fichiers système ou temporaires
                        if (str_contains($relPath, '/temp/')) continue;

                        $zip->addFile($fullPath, 'data/' . $relPath);
                    }
                }

                // 4. Manifeste JSON
                $manifest = [
                    'app' => 'Partoches Canopée',
                    'date' => date('Y-m-d H:i:s'),
                    'version' => '2.1',
                    'chansons_count' => $this->countTable('chanson'),
                    'documents_count' => $this->countTable('document'),
                    'medias_count' => $this->countTable('media')
                ];
                $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                $zip->close();
                return $destZipPath;
            }
        }

        // Mode 2 : Fallback via commande zip système (ex: /usr/bin/zip)
        if (function_exists('exec')) {
            $stagingDir = sys_get_temp_dir() . '/partoches_staging_' . uniqid();
            if (mkdir($stagingDir, 0777, true)) {
                // 1. Dump SQL
                file_put_contents($stagingDir . '/db_dump.sql', $this->generateDatabaseDump());

                // 2. Conf params.ini
                mkdir($stagingDir . '/conf', 0777, true);
                $iniPath = file_exists($this->rootDir . '/conf/params.ini') ? $this->rootDir . '/conf/params.ini' : dirname($this->rootDir) . '/conf/params.ini';
                if (file_exists($iniPath)) {
                    copy($iniPath, $stagingDir . '/conf/params.ini');
                }

                // 3. Manifest
                $manifest = [
                    'app' => 'Partoches Canopée',
                    'date' => date('Y-m-d H:i:s'),
                    'version' => '2.1',
                    'chansons_count' => $this->countTable('chanson'),
                    'documents_count' => $this->countTable('document'),
                    'medias_count' => $this->countTable('media')
                ];
                file_put_contents($stagingDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                // 4. Data
                if (is_dir($this->dataDir)) {
                    $targetDataDir = $stagingDir . '/data';
                    if (str_contains(PHP_OS_FAMILY, 'Windows')) {
                        exec("xcopy /E /I /Y " . escapeshellarg($this->dataDir) . " " . escapeshellarg($targetDataDir));
                    } else {
                        exec("cp -r " . escapeshellarg($this->dataDir) . " " . escapeshellarg($targetDataDir));
                    }
                }

                // Compresser via la commande système zip
                @unlink($destZipPath);
                $cmd = "cd " . escapeshellarg($stagingDir) . " && zip -r " . escapeshellarg($destZipPath) . " .";
                exec($cmd, $out, $retVal);

                // Nettoyage staging
                if (str_contains(PHP_OS_FAMILY, 'Windows')) {
                    exec("rmdir /S /Q " . escapeshellarg($stagingDir));
                } else {
                    exec("rm -rf " . escapeshellarg($stagingDir));
                }

                if (file_exists($destZipPath) && filesize($destZipPath) > 0) {
                    return $destZipPath;
                }
            }
        }

        throw new Exception("L'extension PHP ZipArchive n'est pas activée sur ce serveur.");
    }

    private function countTable(string $table): int
    {
        $res = $this->db->query("SELECT COUNT(*) FROM `$table`");
        if ($res && $row = $res->fetch_row()) {
            return (int)$row[0];
        }
        return 0;
    }
}
