<?php
/**
 * Service de préparation du formulaire de Playlist.
 */

class PlaylistFormService
{
    /**
     * Prépare les données pour le formulaire.
     */
    public static function prepareData(int $id): array
    {
        $mode = ($id > 0) ? "MAJ" : "INS";

        if ($mode === "MAJ") {
            $donnee = chercheplaylist($id);
            $typePl = $donnee['type'] ?? 0;
            $criteres = json_decode($donnee['criteres'] ?? "[]", true);
        } else {
            $donnee = [
                'id' => 0, 
                'nom' => '', 
                'description' => '', 
                'date_creation' => date("Y-m-d"), 
                'image' => '', 
                'hits' => 0, 
                'type' => 0, 
                'criteres' => ''
            ];
            $typePl = 0;
            $criteres = [];
        }

        return [
            'id' => $id,
            'mode' => $mode,
            'playlist' => $donnee,
            'typePl' => $typePl,
            'criteres' => $criteres
        ];
    }

    /**
     * Gère les actions (up, down, del) sur les morceaux de la playlist.
     */
    public static function handleActions(int $id, array $get): void
    {
        if (isset($get['action'])) {
            if (isset($get['rang'])) {
                $rang = (int)$get['rang'];
                if ($get['action'] === "up") remonteTitrePlaylist($id, $rang, 1);
                if ($get['action'] === "down") descendTitrePlaylist($id, $rang, 1);
            }
            if ($get['action'] === "del" && isset($get['idLien'])) {
                supprimelienChansonPlaylist((int)$get['idLien']);
                ordonneLiensPlaylist($id);
            }
            redirection("playlist_form.php?id=$id&msg=OK_ACTION");
        }
    }

    /**
     * Ajoute un morceau à la playlist.
     */
    public static function addSong(int $id, int $chansonId): string
    {
        $db = $_SESSION['mysql'];
        $check = $db->query("SELECT id FROM lienchansonplaylist WHERE id_playlist = $id AND id_chanson = $chansonId");
        
        if ($check && $check->num_rows > 0) {
            return "Ce morceau est déjà dans la playlist.";
        }
        
        creelienChansonPlaylist($chansonId, $id);
        ordonneLiensPlaylist($id);
        return "Morceau ajouté avec succès !";
    }

    /**
     * Importe toutes les chansons d'un songbook dans la playlist.
     */
    public static function importFromSongbook(int $idPlaylist, int $idSongbook): string
    {
        $db = $_SESSION['mysql'];
        
        // 1. Récupérer les documents du songbook
        $resDocs = $db->query("SELECT idDocument FROM liendocsongbook WHERE idSongbook = $idSongbook");
        if (!$resDocs) return "Erreur lors de la récupération des documents.";

        $count = 0;
        $addedIds = [];

        while ($row = $resDocs->fetch_assoc()) {
            $idDoc = (int)$row['idDocument'];
            
            // 2. Trouver la chanson rattachée à ce document (via idTable/nomTable)
            $resCh = $db->query("SELECT idTable AS idChanson FROM document WHERE id = $idDoc AND nomTable = 'chanson'");
            if ($resCh && ($ch = $resCh->fetch_assoc())) {
                $idChanson = (int)$ch['idChanson'];
                
                // 3. Éviter les doublons dans la playlist
                $check = $db->query("SELECT id FROM lienchansonplaylist WHERE id_playlist = $idPlaylist AND id_chanson = $idChanson");
                if ($check && $check->num_rows == 0 && !in_array($idChanson, $addedIds)) {
                    creelienChansonPlaylist($idChanson, $idPlaylist);
                    $addedIds[] = $idChanson;
                    $count++;
                }
            }
        }

        if ($count > 0) {
            ordonneLiensPlaylist($idPlaylist);
            return "Succès : $count chanson(s) importée(s) du songbook !";
        }
        
        return "Aucune nouvelle chanson ajoutée (déjà présentes ou songbook vide).";
    }

    /**
     * Convertit une chaîne en slug kebab-case.
     */
    public static function slugify(string $text): string
    {
        // Remplacer les caractères accentués
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        // Tout en minuscules
        $text = strtolower($text);
        // Remplacer tout ce qui n'est pas alphanumérique par un tiret
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        // Supprimer les tirets au début et à la fin
        $text = trim($text, '-');
        // Remplacer les tirets multiples par un seul
        $text = preg_replace('/-+/', '-', $text);
        return empty($text) ? 'playlist' : $text;
    }

    /**
     * Récupère les pochettes des chansons d'une playlist (jusqu'à une certaine limite).
     */
    public static function getPlaylistContextualCovers(int $idPlaylist, int $limit = 4): array
    {
        $db = $_SESSION['mysql'];
        $covers = [];
        
        if (!function_exists('getMorceauxPlaylist')) {
            require_once __DIR__ . '/playlist.php';
        }
        if (!class_exists('Document')) {
            require_once dirname(__DIR__) . '/document/Document.php';
        }
        
        $res = getMorceauxPlaylist($idPlaylist, 'ordre');
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $coverUrl = "";
                
                // 1. Tenter la nouvelle colonne cover
                if (!empty($row['cover'])) {
                    $coverUrl = $row['cover'];
                    // Sécurisation : si la colonne cover a un chemin relatif, on le passe en absolu
                    if (str_starts_with($coverUrl, '../data/')) {
                        $coverUrl = str_replace('../data/', '/data/', $coverUrl);
                    }
                } else {
                    // 2. Tenter l'ancien système (table document)
                    $idChanson = (int)$row['id'];
                    $nomImage = Document::imageTableId("chanson", $idChanson);
                    if (!empty($nomImage)) {
                        // Construire le chemin absolu par rapport à la racine web
                        $coverUrl = "/data/chansons/" . $idChanson . "/" . $nomImage;
                    }
                }

                if (!empty($coverUrl) && !in_array($coverUrl, $covers)) {
                    $covers[] = $coverUrl;
                    if (count($covers) >= $limit) break;
                }
            }
        }
        
        return $covers;
    }

    /**
     * Retourne l'URL de la miniature d'une couverture en la générant à la volée en WebP si elle manque.
     */
    public static function getThumbnailUrlForCover(string $coverUrl): string
    {
        if (empty($coverUrl)) {
            return '/images/icones/vinyle.png';
        }

        // Si image externe (ex: Discogs / Spotify)
        if (str_starts_with($coverUrl, 'http://') || str_starts_with($coverUrl, 'https://')) {
            return $coverUrl;
        }

        // Si c'est déjà une miniature mini.webp
        if (str_contains($coverUrl, '-mini.webp')) {
            return $coverUrl;
        }

        require_once dirname(__DIR__) . '/lib/Image.php';

        // Extraction du sous-dossier data (ex: chansons, songbooks, playlists) et du chemin relatif
        if (preg_match('#(?:^|/)data/([a-zA-Z0-9_-]+)/(\d+)/(.+)#', $coverUrl, $matches)) {
            $subDir = $matches[1];
            $id = $matches[2];
            $imgName = $matches[3];

            $relPath = "$id/$imgName";
            // Image::getThumbnailUrl vérifie et génère automatiquement la vignette WebP si absente
            $genUrl = Image::getThumbnailUrl($relPath, 'mini', $subDir);
            if ($genUrl) {
                return preg_replace('#^(\.\./)+#', '/', $genUrl);
            }
        }

        return $coverUrl;
    }

    /**
     * Génère une mosaïque NxN à partir d'un tableau d'URL d'images et la sauvegarde.
     * Supporte les grilles carrées 2x2 (4), 3x3 (9), 4x4 (16), 5x5 (25) et l'harmonisation chromatique.
     *
     * @param array $imageUrls Tableau des URLs d'images (locales ou externes)
     * @param string $playlistName Nom de la playlist pour le slug
     * @param int $playlistId ID de la playlist (pour nommage et purge)
     * @param int $gridSize Nombre de cases souhaité (4, 9, 16, 25). Si 0, auto-déduit.
     * @param string $colorOrder Mode d'agencement ('manual', 'rainbow', 'brightness', 'warm_cold')
     * @return string Nom du fichier généré dans data/playlists/
     */
    public static function generateMosaic(
        array $imageUrls, 
        string $playlistName, 
        int $playlistId = 0, 
        int $gridSize = 0, 
        string $colorOrder = 'manual'
    ): string
    {
        $slug = self::slugify($playlistName);
        $uniqueSuffix = ($playlistId > 0) ? $playlistId . '_' . time() : 'mosaic_' . time();
        $fileName = $slug . '_' . $uniqueSuffix . ".jpg";
        $playlistsDir = defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR . '/playlists' : dirname(__DIR__, 2) . "/data/playlists";
        if (!is_dir($playlistsDir)) {
            @mkdir($playlistsDir, 0775, true);
        }

        // Nettoyage des anciennes mosaïques de cette playlist
        if ($playlistId > 0) {
            $oldFiles = glob($playlistsDir . '/' . $slug . '_' . $playlistId . '_*.jpg');
            if (!empty($oldFiles)) {
                foreach ($oldFiles as $oldFile) {
                    @unlink($oldFile);
                }
            }
        }

        $targetPath = $playlistsDir . "/" . $fileName;

        if (empty($imageUrls)) {
            // Pas d'images, on génère juste un fond avec des initiales
            $finalWidth = 400;
            $finalHeight = 400;
            $mosaic = imagecreatetruecolor($finalWidth, $finalHeight);
            $bgColor = imagecolorallocate($mosaic, 43, 29, 26);
            imagefill($mosaic, 0, 0, $bgColor);
            $textColor = imagecolorallocate($mosaic, 210, 180, 140);
            $text = strtoupper(substr($slug, 0, 2));
            imagestring($mosaic, 5, ($finalWidth / 2) - 10, ($finalHeight / 2) - 10, $text, $textColor);
            imagejpeg($mosaic, $targetPath, 85);
            imagedestroy($mosaic);
            return $fileName;
        }

        // Détermination de la dimension de grille N x N
        $count = count($imageUrls);
        if ($gridSize > 0) {
            $n = (int)round(sqrt($gridSize));
        } else {
            if ($count <= 1) $n = 1;
            elseif ($count <= 4) $n = 2;
            elseif ($count <= 9) $n = 3;
            elseif ($count <= 16) $n = 4;
            else $n = 5;
        }
        $n = max(1, min(5, $n));
        $totalSlots = $n * $n;

        // Limiter aux slots nécessaires
        $selectedUrls = array_values(array_slice($imageUrls, 0, $totalSlots));

        // Harmonisation chromatique si demandée
        if ($colorOrder !== 'manual' && count($selectedUrls) > 1) {
            $selectedUrls = self::sortImagesByColorHarmony($selectedUrls, $colorOrder, $n);
        }

        // Taille globale du canevas
        $finalSize = ($n >= 3) ? 600 : 400;
        $mosaic = imagecreatetruecolor($finalSize, $finalSize);
        $bgColor = imagecolorallocate($mosaic, 43, 29, 26);
        imagefill($mosaic, 0, 0, $bgColor);

        if ($n === 1) {
            self::stampImageToMosaic($mosaic, $selectedUrls[0], 0, 0, $finalSize, $finalSize);
        } else {
            $tileSize = (int)floor($finalSize / $n);
            for ($row = 0; $row < $n; $row++) {
                for ($col = 0; $col < $n; $col++) {
                    $idx = $row * $n + $col;
                    if (isset($selectedUrls[$idx])) {
                        $x = $col * $tileSize;
                        $y = $row * $tileSize;
                        self::stampImageToMosaic($mosaic, $selectedUrls[$idx], $x, $y, $tileSize, $tileSize);
                    }
                }
            }
        }

        imagejpeg($mosaic, $targetPath, 85);
        imagedestroy($mosaic);

        return $fileName;
    }

    /**
     * Trie les images selon une harmonie de couleur (Arc-en-ciel, Luminosité, Chaud-Froid).
     */
    public static function sortImagesByColorHarmony(array $urls, string $order, int $n = 2): array
    {
        $items = [];
        foreach ($urls as $url) {
            $hsl = self::getImageDominantHsl($url);
            $items[] = [
                'url' => $url,
                'h' => $hsl['h'],
                's' => $hsl['s'],
                'l' => $hsl['l']
            ];
        }

        switch ($order) {
            case 'rainbow':
                // Tri par Teinte (Hue 0° -> 360°)
                usort($items, fn($a, $b) => $a['h'] <=> $b['h']);
                $sortedUrls = array_column($items, 'url');
                return self::arrangeSnakePattern($sortedUrls, $n);

            case 'brightness':
                // Tri par Luminosité (Sombre -> Clair)
                usort($items, fn($a, $b) => $a['l'] <=> $b['l']);
                return array_column($items, 'url');

            case 'warm_cold':
                // Tri par proximité avec les teintes chaudes Canopée (orange ~ 30°)
                usort($items, function($a, $b) {
                    $distA = min(abs($a['h'] - 30), abs($a['h'] - 30 + 360), abs($a['h'] - 30 - 360));
                    $distB = min(abs($b['h'] - 30), abs($b['h'] - 30 + 360), abs($b['h'] - 30 - 360));
                    return $distA <=> $distB;
                });
                return array_column($items, 'url');

            default:
                return $urls;
        }
    }

    /**
     * Réordonne un tableau d'éléments en serpentin sur une grille N x N.
     */
    private static function arrangeSnakePattern(array $elements, int $n): array
    {
        if ($n <= 1) return $elements;
        $result = [];
        $chunks = array_chunk($elements, $n);
        foreach ($chunks as $rowIndex => $row) {
            if ($rowIndex % 2 === 1) {
                $row = array_reverse($row);
            }
            foreach ($row as $item) {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Calcule la teinte HSL dominante d'une image locale ou distante.
     */
    public static function getImageDominantHsl(string $url): array
    {
        $defaultHsl = ['h' => 0.0, 's' => 0.0, 'l' => 0.5];

        $filePath = $url;
        $publicDir = defined('PUBLIC_DIR') ? PUBLIC_DIR : dirname(__DIR__, 2);
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $filePath = $url;
        } elseif (str_starts_with($url, '../')) {
            $filePath = __DIR__ . "/" . $url;
        } elseif (str_starts_with($url, '/')) {
            $filePath = $publicDir . $url;
        }

        $content = @file_get_contents($filePath);
        if (!$content) return $defaultHsl;

        $img = @imagecreatefromstring($content);
        if (!$img) return $defaultHsl;

        // Réduire en 1x1 pour obtenir la moyenne RGB
        $thumb = imagecreatetruecolor(1, 1);
        $w = imagesx($img);
        $h = imagesy($img);
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, 1, 1, $w, $h);
        $rgb = imagecolorat($thumb, 0, 0);

        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        imagedestroy($thumb);
        imagedestroy($img);

        // Conversion RGB -> HSL
        $rNorm = $r / 255.0;
        $gNorm = $g / 255.0;
        $bNorm = $b / 255.0;

        $max = max($rNorm, $gNorm, $bNorm);
        $min = min($rNorm, $gNorm, $bNorm);
        $delta = $max - $min;

        $l = ($max + $min) / 2.0;
        if ($delta == 0) {
            $h = 0.0;
            $s = 0.0;
        } else {
            $s = ($l < 0.5) ? ($delta / ($max + $min)) : ($delta / (2.0 - $max - $min));
            if ($max == $rNorm) {
                $h = 60.0 * fmod((($gNorm - $bNorm) / $delta), 6);
            } elseif ($max == $gNorm) {
                $h = 60.0 * ((($bNorm - $rNorm) / $delta) + 2.0);
            } else {
                $h = 60.0 * ((($rNorm - $gNorm) / $delta) + 4.0);
            }
            if ($h < 0) $h += 360.0;
        }

        return ['h' => $h, 's' => $s, 'l' => $l];
    }
    
    private static function stampImageToMosaic($mosaic, string $url, int $dstX, int $dstY, int $dstW, int $dstH): void
    {
        // Gestion des URL relatives (partoches : souvent ../../data/chansons/...)
        // On les résout en local si elles commencent par .. ou /
        $filePath = $url;
        $publicDir = defined('PUBLIC_DIR') ? PUBLIC_DIR : dirname(__DIR__, 2);
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $filePath = $url;
        } elseif (str_starts_with($url, '../')) {
            $filePath = __DIR__ . "/" . $url;
        } elseif (str_starts_with($url, '/')) {
            $filePath = $publicDir . $url; 
        }
        
        // Tenter de charger l'image silencieusement
        $content = @file_get_contents($filePath);
        if ($content) {
            $img = @imagecreatefromstring($content);
            if ($img !== false) {
                $srcW = imagesx($img);
                $srcH = imagesy($img);
                // On recadre au centre (Crop)
                $minDim = min($srcW, $srcH);
                $srcX = ($srcW - $minDim) / 2;
                $srcY = ($srcH - $minDim) / 2;
                
                imagecopyresampled($mosaic, $img, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $minDim, $minDim);
                imagedestroy($img);
            }
        }
    }

    /**
     * Gère le téléversement d'une image d'illustration personnalisée pour une playlist.
     * Conforme aux règles de sécurité OWASP & Constitution (MIME réel, taille max 5Mo, renommage systématique).
     *
     * @param array $file Tableau $_FILES['fimage_file']
     * @param int $playlistId ID de la playlist
     * @param string $playlistNom Nom de la playlist pour le slug
     * @return string|null Nom du fichier sauvegardé ou null si échec/aucun fichier
     */
    public static function handleIllustrationUpload(array $file, int $playlistId, string $playlistNom): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $maxSize = 5 * 1024 * 1024;
        if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $maxSize) {
            return null;
        }

        $tmpPath = $file['tmp_name'] ?? '';
        if (empty($tmpPath) || !file_exists($tmpPath)) {
            return null;
        }

        $isRealUpload = is_uploaded_file($tmpPath) || (defined('PHPUNIT_RUNNING') && PHPUNIT_RUNNING);
        if (!$isRealUpload) {
            return null;
        }

        // Vérification du type MIME réel
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        ];

        if (!array_key_exists($mime, $allowedMimes)) {
            return null;
        }

        $extension = $allowedMimes[$mime];

        $playlistsDir = defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR . '/playlists' : dirname(__DIR__, 2) . '/data/playlists';
        if (!is_dir($playlistsDir)) {
            @mkdir($playlistsDir, 0775, true);
        }

        require_once dirname(__DIR__) . '/lib/html.php';
        $simplified = function_exists('simplifieNomFichier') ? simplifieNomFichier($playlistNom) : $playlistNom;
        $safeSlug = strtolower(pathinfo($simplified, PATHINFO_FILENAME));
        $safeSlug = preg_replace('/[^a-z0-9_]/i', '_', str_replace('-', '_', $safeSlug));
        $safeSlug = trim(preg_replace('/_+/', '_', $safeSlug), '_');
        if (empty($safeSlug)) {
            $safeSlug = 'playlist';
        }

        $uniqueSuffix = ($playlistId > 0) ? $playlistId . '_' . time() : 'custom_' . time() . '_' . bin2hex(random_bytes(4));
        $newFileName = $safeSlug . '_' . $uniqueSuffix . '.' . $extension;
        $destination = $playlistsDir . '/' . $newFileName;

        if (defined('PHPUNIT_RUNNING') && PHPUNIT_RUNNING && !is_uploaded_file($tmpPath)) {
            $copied = copy($tmpPath, $destination);
        } else {
            $copied = move_uploaded_file($tmpPath, $destination);
        }

        if ($copied) {
            return $newFileName;
        }

        return null;
    }
}
