<?php
/**
 * SERVICE : DataAuditService
 * Gère l'analyse des fichiers orphelins et des doublons dans le répertoire data/.
 */

require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
require_once PHP_DIR . "/document/Document.php";

class DataAuditService
{
    private mysqli $db;
    private string $dataDir;

    public function __construct(?mysqli $db = null, ?string $dataDir = null)
    {
        $this->db = $db ?? $_SESSION['mysql'];
        $this->dataDir = $dataDir ?? (defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR : dirname(__DIR__, 2) . '/data');
    }

    /**
     * Récupère la liste de tous les nomVersion ou noms de fichiers référencés en BDD.
     */
    public function getReferencedFilesFromDb(): array
    {
        $referenced = [];

        // 1. Table document (nom + version -> nom-v1.ext)
        $resDoc = $this->db->query("SELECT id, nom, version, nomTable, idTable FROM document");
        if ($resDoc) {
            while ($row = $resDoc->fetch_assoc()) {
                $nomVersion = Document::composeNomVersion($row['nom'], $row['version']);
                $nomTable = strtolower(trim($row['nomTable']));
                $idTable = (int)$row['idTable'];

                // Formats de stockage possibles
                $referenced[strtolower($nomVersion)] = true;
                $referenced[strtolower($row['nom'])] = true;
                if ($nomTable && $idTable) {
                    $referenced[strtolower("{$nomTable}s/{$idTable}/{$nomVersion}")] = true;
                    $referenced[strtolower("{$nomTable}s/{$idTable}/{$row['nom']}")] = true;
                }
            }
        }

        // 2. Table media (image, lien si local)
        $resMedia = $this->db->query("SELECT image, lien FROM media");
        if ($resMedia) {
            while ($row = $resMedia->fetch_assoc()) {
                if (!empty($row['image'])) {
                    $img = basename(ltrim($row['image'], './data/'));
                    $referenced[strtolower($img)] = true;
                }
                if (!empty($row['lien']) && str_contains($row['lien'], 'data/')) {
                    $referenced[strtolower(basename($row['lien']))] = true;
                }
            }
        }

        // 3. Table utilisateur (image / photo)
        $resUser = $this->db->query("SELECT image FROM utilisateur");
        if ($resUser) {
            while ($row = $resUser->fetch_assoc()) {
                if (!empty($row['image'])) {
                    $referenced[strtolower(basename($row['image']))] = true;
                }
            }
        }

        return $referenced;
    }

    /**
     * Recherche les fichiers orphelins dans le dossier data/.
     */
    public function findOrphanFiles(): array
    {
        if (!is_dir($this->dataDir)) return [];

        $referencedMap = $this->getReferencedFilesFromDb();
        $orphans = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->dataDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) continue;

            $fullPath = $file->getPathname();
            $filename = $file->getFilename();
            $relPath = ltrim(str_replace('\\', '/', str_replace($this->dataDir, '', $fullPath)), '/');
            $ext = strtolower($file->getExtension());

            // Ignorer fichiers système ou temporaires
            if (in_array($filename, ['.htaccess', 'index.html', 'index.php', '.DS_Store', 'desktop.ini'])) continue;
            if (str_contains($relPath, '/temp/') || str_contains($relPath, 'database/migrations')) continue;

            $lowerFilename = strtolower($filename);
            $lowerRelPath = strtolower($relPath);

            // Vérification si le fichier est référencé
            $isReferenced = isset($referencedMap[$lowerFilename]) || isset($referencedMap[$lowerRelPath]);

            // Pour les vignettes auto-générées WebP (-mini, -sd)
            if (!$isReferenced && (str_contains($lowerFilename, '-mini.webp') || str_contains($lowerFilename, '-sd.webp') || str_contains($lowerFilename, '-pdf.jpg'))) {
                $baseNameWithoutSuffix = preg_replace('/-(mini|sd)\.webp$/', '', $lowerFilename);
                $baseNameWithoutSuffix = preg_replace('/-pdf\.jpg$/', '', $baseNameWithoutSuffix);
                
                // Si l'une des extensions d'origine existe ou est référencée, la vignette n'est pas orpheline
                foreach (['.jpg', '.jpeg', '.png', '.webp', '.pdf', ''] as $tryExt) {
                    if (isset($referencedMap[$baseNameWithoutSuffix . $tryExt])) {
                        $isReferenced = true;
                        break;
                    }
                }
            }

            if (!$isReferenced) {
                $orphans[] = [
                    'filename' => $filename,
                    'relativePath' => $relPath,
                    'fullPath' => $fullPath,
                    'size' => $file->getSize(),
                    'mtime' => $file->getMTime(),
                    'formattedSize' => $this->formatBytes($file->getSize()),
                    'formattedDate' => date('Y-m-d H:i:s', $file->getMTime())
                ];
            }
        }

        return $orphans;
    }

    /**
     * Recherche les fichiers doublons dans le dossier data/ par empreinte MD5.
     * Utilise un cache persistant JSON basé sur (relPath, mtime, size) pour accélérer le scan.
     */
    public function findDuplicateFiles(): array
    {
        if (!is_dir($this->dataDir)) return [];

        $hashMap = [];
        $duplicates = [];
        $md5Cache = $this->loadMd5Cache();
        $cacheUpdated = false;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->dataDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) continue;

            $fullPath = $file->getPathname();
            $filename = $file->getFilename();
            $relPath = ltrim(str_replace('\\', '/', str_replace($this->dataDir, '', $fullPath)), '/');

            if (in_array($filename, ['.htaccess', 'index.html', 'index.php', '.DS_Store', 'desktop.ini'])) continue;
            if (str_contains($relPath, '/temp/') || str_contains($relPath, 'database/migrations')) continue;
            if ($file->getSize() < 100) continue; // Ignorer fichiers vides/micro

            $mtime = $file->getMTime();
            $size = $file->getSize();

            // 1. Vérification dans le cache MD5
            if (isset($md5Cache[$relPath]) && $md5Cache[$relPath]['mtime'] === $mtime && $md5Cache[$relPath]['size'] === $size) {
                $hash = $md5Cache[$relPath]['hash'];
            } else {
                $hash = md5_file($fullPath);
                if (!$hash) continue;
                $md5Cache[$relPath] = ['mtime' => $mtime, 'size' => $size, 'hash' => $hash];
                $cacheUpdated = true;
            }

            $fileInfo = [
                'filename' => $filename,
                'relativePath' => $relPath,
                'fullPath' => $fullPath,
                'size' => $size,
                'mtime' => $mtime,
                'formattedSize' => $this->formatBytes($size),
                'formattedDate' => date('Y-m-d H:i:s', $mtime),
                'reference' => $this->findFileReference($filename, $relPath)
            ];

            $hashMap[$hash][] = $fileInfo;
        }

        if ($cacheUpdated) {
            $this->saveMd5Cache($md5Cache);
        }

        foreach ($hashMap as $hash => $group) {
            if (count($group) > 1) {
                $duplicates[] = [
                    'hash' => $hash,
                    'count' => count($group),
                    'totalWastedSize' => ($group[0]['size'] * (count($group) - 1)),
                    'formattedWastedSize' => $this->formatBytes($group[0]['size'] * (count($group) - 1)),
                    'files' => $group
                ];
            }
        }

        // Trier par taille gaspillée décroissante
        usort($duplicates, fn($a, $b) => $b['totalWastedSize'] <=> $a['totalWastedSize']);

        return $duplicates;
    }

    /**
     * Supprime une liste de fichiers orphelins (par chemins relatifs).
     */
    public function deleteOrphanFiles(array $relativePaths): int
    {
        $deleted = 0;
        foreach ($relativePaths as $relPath) {
            $safeRel = ltrim(str_replace('\\', '/', $relPath), '/');
            if (str_contains($safeRel, '..')) continue;

            $fullPath = $this->dataDir . '/' . $safeRel;
            if (file_exists($fullPath) && is_file($fullPath)) {
                if (@unlink($fullPath)) {
                    $deleted++;
                }
            }
        }
        return $deleted;
    }

    /**
     * Tente d'associer un fichier à l'élément (chanson, songbook, etc.) qui y fait référence.
     */
    public function findFileReference(string $filename, string $relPath): ?array
    {
        $safeRel = str_replace('\\', '/', $relPath);

        // 1. Déduction par structure de répertoires
        if (preg_match('#^chansons/(\d+)/#i', $safeRel, $m)) {
            $id = (int)$m[1];
            return [
                'type' => 'chanson',
                'id' => $id,
                'label' => 'Chanson #' . $id,
                'url' => '../chanson/chanson_voir.php?id=' . $id
            ];
        }

        if (preg_match('#^songbooks/(\d+)/#i', $safeRel, $m)) {
            $id = (int)$m[1];
            return [
                'type' => 'songbook',
                'id' => $id,
                'label' => 'Songbook #' . $id,
                'url' => '../songbook/songbook_voir.php?id=' . $id
            ];
        }

        if (preg_match('#^utilisateur/(\d+)/#i', $safeRel, $m)) {
            $id = (int)$m[1];
            return [
                'type' => 'utilisateur',
                'id' => $id,
                'label' => 'Profil #' . $id,
                'url' => '../utilisateur/utilisateur_form.php?id=' . $id
            ];
        }

        // 2. Recherche par BDD table document
        $lowerName = strtolower($filename);
        $resDoc = $this->db->query("SELECT nomTable, idTable FROM document WHERE LOWER(nom) = '" . $this->db->real_escape_string($lowerName) . "' LIMIT 1");
        if ($resDoc && $row = $resDoc->fetch_assoc()) {
            $nomTable = strtolower(trim($row['nomTable']));
            $idTable = (int)$row['idTable'];

            if ($nomTable === 'chanson') {
                return [
                    'type' => 'chanson',
                    'id' => $idTable,
                    'label' => 'Chanson #' . $idTable,
                    'url' => '../chanson/chanson_voir.php?id=' . $idTable
                ];
            }
            if ($nomTable === 'songbook') {
                return [
                    'type' => 'songbook',
                    'id' => $idTable,
                    'label' => 'Songbook #' . $idTable,
                    'url' => '../songbook/songbook_voir.php?id=' . $idTable
                ];
            }
        }

        return null;
    }

    private function getCacheFilePath(): string
    {
        $tempDir = $this->dataDir . '/temp';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }
        return $tempDir . '/md5_cache.json';
    }

    private function loadMd5Cache(): array
    {
        $cacheFile = $this->getCacheFilePath();
        if (file_exists($cacheFile)) {
            $content = @file_get_contents($cacheFile);
            if ($content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }
        return [];
    }

    private function saveMd5Cache(array $cache): void
    {
        $cacheFile = $this->getCacheFilePath();
        @file_put_contents($cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
    }

    /**
     * Recherche les dossiers orphelins dans data/ (chansons/X, songbooks/X, etc.) dont l'ID n'existe plus en BDD.
     */
    public function findOrphanDirectories(): array
    {
        $orphanDirs = [];
        $domainConfig = [
            'chansons' => ['table' => 'chanson', 'pk' => 'id', 'urlPrefix' => '../chanson/chanson_form.php?id='],
            'songbooks' => ['table' => 'songbook', 'pk' => 'id', 'urlPrefix' => '../songbook/songbook_form.php?id='],
            'playlists' => ['table' => 'playlist', 'pk' => 'id', 'urlPrefix' => '../playlist/playlist_form.php?id='],
            'utilisateur' => ['table' => 'utilisateur', 'pk' => 'id', 'urlPrefix' => '../utilisateur/utilisateur_form.php?id='],
            'strum' => ['table' => 'strum', 'pk' => 'id', 'urlPrefix' => '../strum/strum_form.php?id=']
        ];

        foreach ($domainConfig as $folderName => $config) {
            $targetDir = $this->dataDir . '/' . $folderName;
            if (!is_dir($targetDir)) continue;

            // Récupérer tous les IDs existants en BDD pour cette table
            $existingIds = [];
            $res = $this->db->query("SELECT `{$config['pk']}` FROM `{$config['table']}`");
            if ($res) {
                while ($row = $res->fetch_row()) {
                    $existingIds[(int)$row[0]] = true;
                }
            }

            // Scanner le répertoire
            $subDirs = scandir($targetDir);
            foreach ($subDirs as $item) {
                if ($item === '.' || $item === '..') continue;
                $fullPath = $targetDir . '/' . $item;

                if (is_dir($fullPath) && is_numeric($item)) {
                    $id = (int)$item;
                    if (!isset($existingIds[$id])) {
                        // Dossier orphelin !
                        $stats = $this->getDirStats($fullPath);
                        $relPath = $folderName . '/' . $item;

                        $orphanDirs[] = [
                            'domain' => $folderName,
                            'id' => $id,
                            'relativePath' => $relPath,
                            'fullPath' => $fullPath,
                            'fileCount' => $stats['fileCount'],
                            'totalSize' => $stats['totalSize'],
                            'formattedSize' => $this->formatBytes($stats['totalSize'])
                        ];
                    }
                }
            }
        }

        return $orphanDirs;
    }

    /**
     * Supprime de façon sécurisée un dossier orphelin et son contenu.
     */
    public function deleteOrphanDirectory(string $relDir): bool
    {
        $safeRel = ltrim(str_replace('\\', '/', $relDir), '/');
        if (str_contains($safeRel, '..')) return false;

        $allowedPrefixes = ['chansons/', 'songbooks/', 'playlists/', 'utilisateur/', 'strum/'];
        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($safeRel, $prefix)) {
                $isAllowed = true;
                break;
            }
        }
        if (!$isAllowed) return false;

        $fullPath = $this->dataDir . '/' . $safeRel;
        if (is_dir($fullPath)) {
            $this->removeRecursiveDir($fullPath);
            return !file_exists($fullPath);
        }
        return false;
    }

    /**
     * Recherche les fichiers référencés en BDD mais physiquement manquants sur le disque.
     */
    public function findMissingFilesFromDb(): array
    {
        $missing = [];

        // 1. Table document
        $resDoc = $this->db->query("SELECT id, nom, version, nomTable, idTable FROM document");
        if ($resDoc) {
            while ($row = $resDoc->fetch_assoc()) {
                $nomVersion = Document::composeNomVersion($row['nom'], $row['version']);
                $nomTable = strtolower(trim($row['nomTable']));
                $idTable = (int)$row['idTable'];

                $relPath = ($nomTable && $idTable) ? "{$nomTable}s/{$idTable}/{$nomVersion}" : $nomVersion;
                $fullPath = $this->dataDir . '/' . $relPath;
                $fallbackPath = $this->dataDir . '/' . $row['nom'];

                if (!file_exists($fullPath) && !file_exists($fallbackPath)) {
                    $missing[] = [
                        'source' => 'document',
                        'id' => (int)$row['id'],
                        'filename' => $nomVersion,
                        'relativePath' => $relPath,
                        'entityLabel' => ucfirst($nomTable) . " #{$idTable}",
                        'editUrl' => ($nomTable === 'chanson') ? "../chanson/chanson_voir.php?id={$idTable}" : "../songbook/songbook_voir.php?id={$idTable}"
                    ];
                }
            }
        }

        // 2. Table media (liens locaux)
        $resMedia = $this->db->query("SELECT id, titre, image, lien FROM media");
        if ($resMedia) {
            while ($row = $resMedia->fetch_assoc()) {
                if (!empty($row['image']) && $row['image'] !== 'defaut.png' && !str_starts_with($row['image'], 'http')) {
                    $imgPath = $this->dataDir . '/' . ltrim(str_replace('./data/', '', $row['image']), '/');
                    if (!file_exists($imgPath)) {
                        $missing[] = [
                            'source' => 'media (image)',
                            'id' => (int)$row['id'],
                            'filename' => basename($row['image']),
                            'relativePath' => ltrim(str_replace('./data/', '', $row['image']), '/'),
                            'entityLabel' => "Média #" . $row['id'] . " (" . $row['titre'] . ")",
                            'editUrl' => "../media/listeMedias.php"
                        ];
                    }
                }
            }
        }

        return $missing;
    }

    /**
     * Recherche les tables de liaison SQL contenant des références orphelines.
     */
    public function findOrphanDbRelations(): array
    {
        $relations = [];

        // 1. liendocsongbook
        $resSb = $this->db->query("SELECT l.id, l.idSongbook, l.idDocument 
                                   FROM liendocsongbook l 
                                   LEFT JOIN songbook s ON l.idSongbook = s.id 
                                   LEFT JOIN document d ON l.idDocument = d.id 
                                   WHERE s.id IS NULL OR d.id IS NULL");
        if ($resSb) {
            while ($row = $resSb->fetch_assoc()) {
                $relations[] = [
                    'table' => 'liendocsongbook',
                    'id' => (int)$row['id'],
                    'details' => "Lien Document #{$row['idDocument']} <-> Songbook #{$row['idSongbook']} (Élément manquant)"
                ];
            }
        }

        // 2. lienstrumchanson
        $resStrum = $this->db->query("SELECT l.id, l.idChanson, l.idStrum 
                                     FROM lienstrumchanson l 
                                     LEFT JOIN chanson c ON l.idChanson = c.id 
                                     LEFT JOIN strum s ON l.idStrum = s.id 
                                     WHERE c.id IS NULL OR s.id IS NULL");
        if ($resStrum) {
            while ($row = $resStrum->fetch_assoc()) {
                $relations[] = [
                    'table' => 'lienstrumchanson',
                    'id' => (int)$row['id'],
                    'details' => "Lien Strum #{$row['idStrum']} <-> Chanson #{$row['idChanson']} (Élément manquant)"
                ];
            }
        }

        // 3. lienchansonplaylist
        $resPl = $this->db->query("SELECT l.id, l.id_playlist, l.id_chanson 
                                  FROM lienchansonplaylist l 
                                  LEFT JOIN playlist p ON l.id_playlist = p.id 
                                  LEFT JOIN chanson c ON l.id_chanson = c.id 
                                  WHERE p.id IS NULL OR c.id IS NULL");
        if ($resPl) {
            while ($row = $resPl->fetch_assoc()) {
                $relations[] = [
                    'table' => 'lienchansonplaylist',
                    'id' => (int)$row['id'],
                    'details' => "Lien Playlist #{$row['id_playlist']} <-> Chanson #{$row['id_chanson']} (Élément manquant)"
                ];
            }
        }

        // 4. document (fichiers pointant vers une entité supprimée)
        $resDocChan = $this->db->query("SELECT d.id, d.nom, d.idTable FROM document d LEFT JOIN chanson c ON d.idTable = c.id WHERE LOWER(d.nomTable) = 'chanson' AND d.idTable > 0 AND c.id IS NULL");
        if ($resDocChan) {
            while ($row = $resDocChan->fetch_assoc()) {
                $relations[] = [
                    'table' => 'document',
                    'id' => (int)$row['id'],
                    'details' => "Document #{$row['id']} ({$row['nom']}) -> Chanson #{$row['idTable']} (Chanson inexistante)"
                ];
            }
        }

        $resDocSb = $this->db->query("SELECT d.id, d.nom, d.idTable FROM document d LEFT JOIN songbook s ON d.idTable = s.id WHERE LOWER(d.nomTable) = 'songbook' AND d.idTable > 0 AND s.id IS NULL");
        if ($resDocSb) {
            while ($row = $resDocSb->fetch_assoc()) {
                $relations[] = [
                    'table' => 'document',
                    'id' => (int)$row['id'],
                    'details' => "Document #{$row['id']} ({$row['nom']}) -> Songbook #{$row['idTable']} (Songbook inexistant)"
                ];
            }
        }

        // 5. lienurl (liens externes pointant vers une entité supprimée)
        $resUrlChan = $this->db->query("SELECT l.id, l.url, l.idTable FROM lienurl l LEFT JOIN chanson c ON l.idTable = c.id WHERE LOWER(l.nomTable) = 'chanson' AND l.idTable > 0 AND c.id IS NULL");
        if ($resUrlChan) {
            while ($row = $resUrlChan->fetch_assoc()) {
                $relations[] = [
                    'table' => 'lienurl',
                    'id' => (int)$row['id'],
                    'details' => "LienUrl #{$row['id']} ({$row['url']}) -> Chanson #{$row['idTable']} (Chanson inexistante)"
                ];
            }
        }

        $resUrlSb = $this->db->query("SELECT l.id, l.url, l.idTable FROM lienurl l LEFT JOIN songbook s ON l.idTable = s.id WHERE LOWER(l.nomTable) = 'songbook' AND l.idTable > 0 AND s.id IS NULL");
        if ($resUrlSb) {
            while ($row = $resUrlSb->fetch_assoc()) {
                $relations[] = [
                    'table' => 'lienurl',
                    'id' => (int)$row['id'],
                    'details' => "LienUrl #{$row['id']} ({$row['url']}) -> Songbook #{$row['idTable']} (Songbook inexistant)"
                ];
            }
        }

        $resUrlPl = $this->db->query("SELECT l.id, l.url, l.idTable FROM lienurl l LEFT JOIN playlist p ON l.idTable = p.id WHERE LOWER(l.nomTable) = 'playlist' AND l.idTable > 0 AND p.id IS NULL");
        if ($resUrlPl) {
            while ($row = $resUrlPl->fetch_assoc()) {
                $relations[] = [
                    'table' => 'lienurl',
                    'id' => (int)$row['id'],
                    'details' => "LienUrl #{$row['id']} ({$row['url']}) -> Playlist #{$row['idTable']} (Playlist inexistante)"
                ];
            }
        }

        // 6. noteUtilisateur (notes d'utilisateurs rattachées à des éléments supprimés)
        $resNoteChan = $this->db->query("SELECT n.id, n.nomObjet, n.idObjet FROM noteUtilisateur n LEFT JOIN chanson c ON n.idObjet = c.id WHERE LOWER(n.nomObjet) = 'chanson' AND n.idObjet > 0 AND c.id IS NULL");
        if ($resNoteChan) {
            while ($row = $resNoteChan->fetch_assoc()) {
                $relations[] = [
                    'table' => 'noteUtilisateur',
                    'id' => (int)$row['id'],
                    'details' => "Note #{$row['id']} -> Chanson #{$row['idObjet']} (Chanson inexistante)"
                ];
            }
        }

        $resNoteSb = $this->db->query("SELECT n.id, n.nomObjet, n.idObjet FROM noteUtilisateur n LEFT JOIN songbook s ON n.idObjet = s.id WHERE LOWER(n.nomObjet) = 'songbook' AND n.idObjet > 0 AND s.id IS NULL");
        if ($resNoteSb) {
            while ($row = $resNoteSb->fetch_assoc()) {
                $relations[] = [
                    'table' => 'noteUtilisateur',
                    'id' => (int)$row['id'],
                    'details' => "Note #{$row['id']} -> Songbook #{$row['idObjet']} (Songbook inexistant)"
                ];
            }
        }

        $resNotePl = $this->db->query("SELECT n.id, n.nomObjet, n.idObjet FROM noteUtilisateur n LEFT JOIN playlist p ON n.idObjet = p.id WHERE LOWER(n.nomObjet) = 'playlist' AND n.idObjet > 0 AND p.id IS NULL");
        if ($resNotePl) {
            while ($row = $resNotePl->fetch_assoc()) {
                $relations[] = [
                    'table' => 'noteUtilisateur',
                    'id' => (int)$row['id'],
                    'details' => "Note #{$row['id']} -> Playlist #{$row['idObjet']} (Playlist inexistante)"
                ];
            }
        }

        $resNoteUser = $this->db->query("SELECT n.id, n.idUtilisateur FROM noteUtilisateur n LEFT JOIN utilisateur u ON n.idUtilisateur = u.id WHERE n.idUtilisateur > 0 AND u.id IS NULL");
        if ($resNoteUser) {
            while ($row = $resNoteUser->fetch_assoc()) {
                $relations[] = [
                    'table' => 'noteUtilisateur',
                    'id' => (int)$row['id'],
                    'details' => "Note #{$row['id']} -> Utilisateur #{$row['idUtilisateur']} (Utilisateur inexistant)"
                ];
            }
        }

        return $relations;
    }

    /**
     * Supprime automatiquement les entrées SQL orphelines dans les tables de liaison et référentiels.
     */
    public function cleanOrphanDbRelations(): int
    {
        $cleaned = 0;

        $q1 = $this->db->query("DELETE l FROM liendocsongbook l LEFT JOIN songbook s ON l.idSongbook = s.id LEFT JOIN document d ON l.idDocument = d.id WHERE s.id IS NULL OR d.id IS NULL");
        if ($q1) $cleaned += $this->db->affected_rows;

        $q2 = $this->db->query("DELETE l FROM lienstrumchanson l LEFT JOIN chanson c ON l.idChanson = c.id LEFT JOIN strum s ON l.idStrum = s.id WHERE c.id IS NULL OR s.id IS NULL");
        if ($q2) $cleaned += $this->db->affected_rows;

        $q3 = $this->db->query("DELETE l FROM lienchansonplaylist l LEFT JOIN playlist p ON l.id_playlist = p.id LEFT JOIN chanson c ON l.id_chanson = c.id WHERE p.id IS NULL OR c.id IS NULL");
        if ($q3) $cleaned += $this->db->affected_rows;

        // 4. document orphelins
        $q4 = $this->db->query("DELETE d FROM document d LEFT JOIN chanson c ON d.idTable = c.id WHERE LOWER(d.nomTable) = 'chanson' AND d.idTable > 0 AND c.id IS NULL");
        if ($q4) $cleaned += $this->db->affected_rows;

        $q5 = $this->db->query("DELETE d FROM document d LEFT JOIN songbook s ON d.idTable = s.id WHERE LOWER(d.nomTable) = 'songbook' AND d.idTable > 0 AND s.id IS NULL");
        if ($q5) $cleaned += $this->db->affected_rows;

        // 5. lienurl orphelins
        $q6 = $this->db->query("DELETE l FROM lienurl l LEFT JOIN chanson c ON l.idTable = c.id WHERE LOWER(l.nomTable) = 'chanson' AND l.idTable > 0 AND c.id IS NULL");
        if ($q6) $cleaned += $this->db->affected_rows;

        $q7 = $this->db->query("DELETE l FROM lienurl l LEFT JOIN songbook s ON l.idTable = s.id WHERE LOWER(l.nomTable) = 'songbook' AND l.idTable > 0 AND s.id IS NULL");
        if ($q7) $cleaned += $this->db->affected_rows;

        $q8 = $this->db->query("DELETE l FROM lienurl l LEFT JOIN playlist p ON l.idTable = p.id WHERE LOWER(l.nomTable) = 'playlist' AND l.idTable > 0 AND p.id IS NULL");
        if ($q8) $cleaned += $this->db->affected_rows;

        // 6. noteUtilisateur orphelines
        $q9 = $this->db->query("DELETE n FROM noteUtilisateur n LEFT JOIN chanson c ON n.idObjet = c.id WHERE LOWER(n.nomObjet) = 'chanson' AND n.idObjet > 0 AND c.id IS NULL");
        if ($q9) $cleaned += $this->db->affected_rows;

        $q10 = $this->db->query("DELETE n FROM noteUtilisateur n LEFT JOIN songbook s ON n.idObjet = s.id WHERE LOWER(n.nomObjet) = 'songbook' AND n.idObjet > 0 AND s.id IS NULL");
        if ($q10) $cleaned += $this->db->affected_rows;

        $q11 = $this->db->query("DELETE n FROM noteUtilisateur n LEFT JOIN playlist p ON n.idObjet = p.id WHERE LOWER(n.nomObjet) = 'playlist' AND n.idObjet > 0 AND p.id IS NULL");
        if ($q11) $cleaned += $this->db->affected_rows;

        $q12 = $this->db->query("DELETE n FROM noteUtilisateur n LEFT JOIN utilisateur u ON n.idUtilisateur = u.id WHERE n.idUtilisateur > 0 AND u.id IS NULL");
        if ($q12) $cleaned += $this->db->affected_rows;

        return $cleaned;
    }

    private function getDirStats(string $dirPath): array
    {
        $fileCount = 0;
        $totalSize = 0;
        if (is_dir($dirPath)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $fileCount++;
                    $totalSize += $file->getSize();
                }
            }
        }
        return ['fileCount' => $fileCount, 'totalSize' => $totalSize];
    }

    private function removeRecursiveDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeRecursiveDir($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

