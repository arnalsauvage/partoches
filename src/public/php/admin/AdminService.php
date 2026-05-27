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

        $migrationDir = __DIR__ . '/../../../data/database/migrations/';
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
        $migrationDir = __DIR__ . '/../../../data/database/migrations/';
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
        // 1. Récupération des infos de connexion depuis la config du projet
        $host = $_ENV['DATABASE_HOST'] ?? 'db';
        $user = $_ENV['DATABASE_USER'] ?? 'root';
        $pass = $_ENV['DATABASE_PASSWORD'] ?? 'root';
        $dbname = $_ENV['DATABASE_NAME'] ?? 'dbPartoches';

        $tempDir = __DIR__ . '/../../../data/backups/';
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

        $filename = "backup_partoches_" . date('Y-m-d_H-i-s') . ".sql";
        $filePath = $tempDir . $filename;
        $gzPath = $filePath . ".gz";

        // 2. Commande mysqldump
        // Note: On utilise --column-statistics=0 pour la compatibilité avec certains serveurs
        $cmd = "mysqldump -h " . escapeshellarg($host) . " -u " . escapeshellarg($user) . " -p" . escapeshellarg($pass) . " " . escapeshellarg($dbname) . " --no-tablespaces > " . escapeshellarg($filePath);
        
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($filePath)) {
            return false;
        }

        // 3. Compression Gzip via PHP
        $fp = fopen($filePath, 'r');
        $gz = gzopen($gzPath, 'w9');
        while (!feof($fp)) {
            gzwrite($gz, fread($fp, 1024 * 512));
        }
        gzclose($gz);
        fclose($fp);

        // Nettoyage du fichier SQL non compressé
        unlink($filePath);

        return $gzPath;
    }
}
