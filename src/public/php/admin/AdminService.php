<?php
/**
 * SERVICE : AdminService
 * Gère la logique technique de l'administration (Logs, SQL, Migrations).
 */

class AdminService
{
    private $db;

    public function __construct($mysqli)
    {
        $this->db = $mysqli;
    }

    /**
     * Lit un fichier de log et retourne son contenu formaté.
     */
    public function getLogContent($filename)
    {
        $path = __DIR__ . "/../../../data/logs/" . basename($filename);
        if (!file_exists($path)) return "Fichier non trouvé.";

        $contenu = file_get_contents($path);
        if (!mb_check_encoding($contenu, 'UTF-8')) {
            $contenu = mb_convert_encoding($contenu, 'UTF-8', 'ISO-8859-1');
        }
        return $contenu;
    }

    /**
     * Exécute une requête SQL brute et retourne le résultat formaté (HTML).
     */
    public function executeRawSql($sql)
    {
        $res = $this->db->query($sql);
        if (!$res) return "<div class='alert alert-danger'>Erreur : " . $this->db->error . "</div>";
        if ($res === true) return "<div class='alert alert-success'>Requête exécutée avec succès (" . $this->db->affected_rows . " lignes affectées).</div>";

        $html = "<div class='table-responsive'><table class='table table-condensed table-striped table-bordered'><thead><tr class='info'>";
        while ($finfo = $res->fetch_field()) $html .= "<th>" . $finfo->name . "</th>";
        $html .= "</tr></thead><tbody>";
        while ($row = $res->fetch_assoc()) {
            $html .= "<tr>";
            foreach ($row as $val) $html .= "<td>" . htmlspecialchars($val ?? '') . "</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody></table></div>";
        return $html;
    }

    private function getMigrationDir(): string
    {
        if (defined('PUBLIC_DATA_DIR') && is_dir(PUBLIC_DATA_DIR . '/database/migrations/')) {
            return PUBLIC_DATA_DIR . '/database/migrations/';
        }
        if (defined('DATA_DIR') && is_dir(DATA_DIR . '/database/migrations/')) {
            return DATA_DIR . '/database/migrations/';
        }
        $dir1 = dirname(__DIR__, 2) . '/data/database/migrations/';
        if (is_dir($dir1)) return $dir1;
        $dir2 = dirname(__DIR__, 3) . '/data/database/migrations/';
        if (is_dir($dir2)) return $dir2;
        return $dir1;
    }

    /**
     * Retourne l'état des migrations SQL.
     */
    public function getMigrationsStatus()
    {
        $checkTable = $this->db->query("SHOW TABLES LIKE 'migrations'");
        $played = [];
        if ($checkTable->num_rows > 0) {
            $res = $this->db->query("SELECT version FROM migrations");
            while ($row = $res->fetch_row()) $played[] = $row[0];
        }

        $migrationDir = $this->getMigrationDir();
        $files = glob($migrationDir . "*.sql");
        $status = [];
        foreach ($files as $f) {
            $b = basename($f);
            $status[] = [
                'name' => $b,
                'played' => in_array($b, $played)
            ];
        }
        return $status;
    }

    /**
     * Exécute les migrations en attente.
     */
    public function runPendingMigrations()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `migrations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `version` varchar(255) NOT NULL,
            `date_execution` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $status = $this->getMigrationsStatus();
        $migrationDir = $this->getMigrationDir();
        $count = 0;

        foreach ($status as $mig) {
            if (!$mig['played']) {
                $sql = file_get_contents($migrationDir . $mig['name']);
                if ($this->db->multi_query($sql)) {
                    do {
                        if ($res = $this->db->store_result()) $res->free();
                    } while ($this->db->more_results() && $this->db->next_result());
                    
                    $this->db->query("INSERT INTO migrations (version) VALUES ('" . $this->db->real_escape_string($mig['name']) . "')");
                    $count++;
                } else {
                    throw new Exception("Erreur sur " . $mig['name'] . " : " . $this->db->error);
                }
            }
        }
        return $count;
    }

    /**
     * Exporte la base de données au format SQL compressé (GZ).
     * @return string|false Chemin vers le fichier généré ou false si erreur
     */
    public function exportDatabase()
    {
        // 1. Récupération des infos de connexion (Identique à configMysql.php pour la cohérence)
        $host = $_ENV['DATABASE_HOST'] ?? $_SERVER['DATABASE_HOST'] ?? null;
        $dbname = $_ENV['DATABASE_NAME'] ?? $_SERVER['DATABASE_NAME'] ?? null;
        $user = $_ENV['DATABASE_USER'] ?? $_SERVER['DATABASE_USER'] ?? null;
        $pass = $_ENV['DATABASE_PASSWORD'] ?? $_SERVER['DATABASE_PASSWORD'] ?? null;

        if (!$host) {
            $fichier = __DIR__ . "/../../../data/conf/params.ini";
            if (file_exists($fichier)) {
                $ini = new FichierIni();
                $ini->m_load_fichier($fichier);
                $host = $ini->m_valeur("monServeur", "mysql");
                $dbname = $ini->m_valeur("maBase", "mysql");
                $user = $ini->m_valeur("login", "mysql");
                $pass = $ini->m_valeur("motDePasse", "mysql");
            }
        }

        $host = $host ?: "localhost";
        $dbname = $dbname ?: "dbPartoches";
        $user = $user ?: "root";
        $pass = $pass ?: "";

        $tempDir = __DIR__ . '/../../../data/backups/';
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0777, true)) {
                error_log("AdminService::exportDatabase : Impossible de créer le dossier $tempDir");
                return false;
            }
        }

        $filename = "backup_partoches_" . date('Y-m-d_H-i-s') . ".sql";
        $filePath = $tempDir . $filename;
        $gzPath = $filePath . ".gz";

        // 2. Commande mysqldump avec capture d'erreurs
        $cmd = "mysqldump -h " . escapeshellarg($host) . " -u " . escapeshellarg($user) . " -p" . escapeshellarg($pass) . " " . escapeshellarg($dbname) . " --no-tablespaces 2>&1 > " . escapeshellarg($filePath);
        
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
            $errorMsg = implode("\n", $output);
            error_log("AdminService::exportDatabase failed (code $returnVar) : " . $errorMsg);
            if (file_exists($filePath)) unlink($filePath);
            return false;
        }

        // 3. Compression Gzip via PHP
        $fp = fopen($filePath, 'r');
        $gz = gzopen($gzPath, 'w9');
        if (!$gz) {
            error_log("AdminService::exportDatabase : Impossible de créer le fichier compressé $gzPath");
            fclose($fp);
            unlink($filePath);
            return false;
        }

        while (!feof($fp)) {
            gzwrite($gz, fread($fp, 1024 * 512));
        }
        gzclose($gz);
        fclose($fp);

        unlink($filePath);

        return $gzPath;
    }

    /**
     * Régénère en masse les miniatures manquantes pour toutes les chansons.
     */
    public function batchRegenerateThumbnails()
    {
        if (!class_exists('Document')) require_once dirname(__DIR__) . "/document/Document.php";
        if (!class_exists('Image')) require_once dirname(__DIR__) . "/lib/Image.php";

        $sql = "SELECT idTable, nom, version FROM document WHERE nomTable = 'chanson' AND (nom LIKE '%.jpg' OR nom LIKE '%.jpeg' OR nom LIKE '%.png' OR nom LIKE '%.webp')";
        $res = $this->db->query($sql);

        $countTotal = 0;
        $countGenerated = 0;
        while ($row = $res->fetch_assoc()) {
            $relPath = $row['idTable'] . "/" . Document::composeNomVersion($row['nom'], $row['version']);
            
            // On vérifie manuellement si les vignettes existent pour avoir un compte précis
            $pathInfo = pathinfo($relPath);
            $baseDir = dirname(__DIR__, 2) . "/data/chansons/";
            $thumbMini = $baseDir . $pathInfo['dirname'] . "/" . $pathInfo['filename'] . "-mini.webp";
            $thumbSd = $baseDir . $pathInfo['dirname'] . "/" . $pathInfo['filename'] . "-sd.webp";

            $needed = (!file_exists($thumbMini) || !file_exists($thumbSd));

            if ($needed) {
                Image::getThumbnailUrl($relPath, 'mini', 'chansons', false);
                Image::getThumbnailUrl($relPath, 'sd', 'chansons', false);
                $countGenerated++;
            }
            $countTotal++;
        }
        return ['total' => $countTotal, 'generated' => $countGenerated];
    }

    /**
     * Supprime tous les dossiers dans data/chansons dont l'ID n'existe plus en BDD.
     */
    public function deleteOrphanSongFolders()
    {
        global $_DOSSIER_CHANSONS;
        $db = $this->db;
        
        // 1. Liste des IDs existants
        $idsExistants = [];
        $res = $db->query("SELECT id FROM chanson");
        while ($row = $res->fetch_row()) $idsExistants[] = (int)$row[0];

        // 2. Scan du dossier data/chansons
        $count = 0;
        if (is_dir($_DOSSIER_CHANSONS)) {
            $folders = scandir($_DOSSIER_CHANSONS);
            foreach ($folders as $f) {
                if ($f === '.' || $f === '..' || !is_numeric($f)) continue;
                
                $idFolder = (int)$f;
                if (!in_array($idFolder, $idsExistants)) {
                    $path = $_DOSSIER_CHANSONS . $f;
                    if ($this->rrmdir($path)) {
                        $count++;
                    }
                }
            }
        }
        return $count;
    }

    /**
     * Supprime récursivement un dossier.
     */
    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                        $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            return rmdir($dir);
        }
        return false;
    }
}
