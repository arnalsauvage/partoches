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
                } else {
                    // 2. Tenter l'ancien système (table document)
                    $idChanson = (int)$row['id'];
                    $nomImage = Document::imageTableId("chanson", $idChanson);
                    if (!empty($nomImage)) {
                        // Construire le chemin relatif
                        $coverUrl = "../data/chansons/" . $idChanson . "/" . $nomImage;
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
     * Génère une mosaïque 2x2 à partir d'un tableau d'URL d'images et la sauvegarde.
     */
    public static function generateMosaic(array $imageUrls, string $playlistName): string
    {
        $slug = self::slugify($playlistName);
        $fileName = $slug . ".jpg";
        $targetPath = dirname(__DIR__, 3) . "/data/playlists/" . $fileName;
        
        // Taille cible pour l'image finale
        $finalWidth = 400;
        $finalHeight = 400;
        $mosaic = imagecreatetruecolor($finalWidth, $finalHeight);
        
        // Fond marron très foncé par défaut
        $bgColor = imagecolorallocate($mosaic, 43, 29, 26);
        imagefill($mosaic, 0, 0, $bgColor);
        
        if (empty($imageUrls)) {
            // Pas d'images, on génère juste un fond avec des initiales
            $textColor = imagecolorallocate($mosaic, 210, 180, 140); // Beige/Marron clair Canopée
            // On écrit les initiales ou un texte par défaut (rudimentaire sans font ttf)
            $text = strtoupper(substr($slug, 0, 2));
            imagestring($mosaic, 5, ($finalWidth / 2) - 10, ($finalHeight / 2) - 10, $text, $textColor);
            imagejpeg($mosaic, $targetPath, 85);
            imagedestroy($mosaic);
            return $fileName;
        }
        
        // On complète le tableau avec la première image si on en a moins de 4, ou on laisse vide.
        // Option choisie : Si 1 image, elle prend tout. Si 2 ou 3 images, on les place dans les cases 200x200.
        
        if (count($imageUrls) === 1) {
            // Une seule image, elle prend tout l'espace
            self::stampImageToMosaic($mosaic, $imageUrls[0], 0, 0, $finalWidth, $finalHeight);
        } else {
            // Mosaïque 2x2
            $tileW = 200;
            $tileH = 200;
            
            // Tile 1 : Top Left
            if (isset($imageUrls[0])) self::stampImageToMosaic($mosaic, $imageUrls[0], 0, 0, $tileW, $tileH);
            // Tile 2 : Top Right
            if (isset($imageUrls[1])) self::stampImageToMosaic($mosaic, $imageUrls[1], $tileW, 0, $tileW, $tileH);
            // Tile 3 : Bottom Left
            if (isset($imageUrls[2])) self::stampImageToMosaic($mosaic, $imageUrls[2], 0, $tileH, $tileW, $tileH);
            // Tile 4 : Bottom Right
            if (isset($imageUrls[3])) self::stampImageToMosaic($mosaic, $imageUrls[3], $tileW, $tileH, $tileW, $tileH);
        }
        
        imagejpeg($mosaic, $targetPath, 85);
        imagedestroy($mosaic);
        
        return $fileName;
    }
    
    private static function stampImageToMosaic($mosaic, string $url, int $dstX, int $dstY, int $dstW, int $dstH): void
    {
        // Gestion des URL relatives (partoches : souvent ../../data/chansons/...)
        // On les résout en local si elles commencent par .. ou /
        $filePath = $url;
        if (str_starts_with($url, 'http')) {
            $filePath = $url;
        } elseif (str_starts_with($url, '../')) {
            // On remonte depuis public/php/playlist
            $filePath = dirname(__DIR__, 3) . "/" . str_replace("../", "", $url);
        } elseif (str_starts_with($url, '/')) {
            $filePath = dirname(__DIR__, 4) . $url; // /var/www/html/src/public/data... etc
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
}
