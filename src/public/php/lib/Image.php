<?php

/**
 * Classe utilitaire pour la gestion des images via GD.
 * Gère le chargement, le redimensionnement et la conversion (notamment WebP).
 */
class Image {

    const SIZE_MINI = 100;
    const SIZE_SD = 300;
    const QUALITY_WEBP = 75;

    /**
     * Retourne l'URL d'une vignette, en la générant à la volée si nécessaire.
     * @param string $relPath Chemin relatif (ex: "354/couverture.jpg" ou "1/avatar.png")
     * @param string $size 'mini' (100px) ou 'sd' (300px)
     * @param string $subDir Sous-dossier dans data/ (chansons, utilisateurs...)
     * @param bool $force Si vrai, régénère la vignette même si elle existe
     * @return string URL de la vignette
     */
    public static function getThumbnailUrl(string $relPath, string $size = 'mini', string $subDir = 'chansons', bool $force = false): string {
        $fallback = ($subDir === 'utilisateurs') ? "../../images/icones/icone_arnal.png" : "../../images/icones/vinyle.png";
        
        if (empty($relPath)) return $fallback;

        $baseDir = dirname(__DIR__, 2) . "/data/$subDir/";
        $sourcePath = $baseDir . $relPath; // Pas de realpath ici, trop lent en boucle
        
        $width = ($size === 'sd') ? self::SIZE_SD : self::SIZE_MINI;
        $suffix = "-" . $size . ".webp";
        $pathInfo = pathinfo($sourcePath);
        $thumbPath = $pathInfo['dirname'] . "/" . $pathInfo['filename'] . $suffix;
        $thumbUrl = "../../data/$subDir/" . dirname($relPath) . "/" . $pathInfo['filename'] . $suffix;

        // --- OPTIMISATION ÉCLAIR ---
        // Si le WebP existe déjà, on le renvoie direct (sauf si on force)
        if (!$force && file_exists($thumbPath)) {
            return $thumbUrl;
        }

        // Si on arrive ici, c'est que la vignette manque. On fait les checks lourds.
        if (!file_exists($sourcePath)) return $fallback;

        $ext = strtolower($pathInfo['extension'] ?? '');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) return $fallback;

        if (self::generateThumb($sourcePath, $thumbPath, $width)) {
            return $thumbUrl;
        }

        return "../../data/$subDir/" . $relPath;
    }

    /**
     * S'assure que le fichier est compatible avec FPDF (JPG/PNG).
     * Si c'est un WebP, génère une version JPG temporaire.
     * @param string $relPath
     * @return string|false Chemin physique vers un fichier compatible
     */
    public static function getCompatiblePathForPdf(string $relPath) {
        $baseDir = dirname(__DIR__, 2) . "/data/chansons/";
        $sourcePath = realpath($baseDir . $relPath);
        
        if (!$sourcePath || !file_exists($sourcePath)) return false;

        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        
        // FPDF supporte JPG et PNG
        if ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png') {
            return $sourcePath;
        }

        // Si WebP, on génère un JPG pour le PDF
        if ($ext === 'webp') {
            $pdfJpgPath = dirname($sourcePath) . "/" . pathinfo($sourcePath, PATHINFO_FILENAME) . "-pdf.jpg";
            if (!file_exists($pdfJpgPath) || filemtime($sourcePath) > filemtime($pdfJpgPath)) {
                $img = self::load($sourcePath);
                self::save($img, $pdfJpgPath, 'jpg', 90);
                imagedestroy($img);
            }
            return $pdfJpgPath;
        }

        return false;
    }

    private static function generateThumb($srcPath, $destPath, $maxWidth) {
        // Tentative d'augmentation de la mémoire pour les grosses images
        @ini_set('memory_limit', '256M');

        if (!function_exists('imagewebp')) {
            error_log("IMAGE_LIB ERROR: GD support WebP manquant sur ce serveur.");
            return false;
        }

        $srcImg = self::load($srcPath);
        if (!$srcImg) {
            error_log("IMAGE_LIB ERROR: Impossible de charger la source : $srcPath (Fichier corrompu ou trop gros ?)");
            return false;
        }

        $thumbImg = self::resizeToLimit($srcImg, $maxWidth, $maxWidth);
        if (!$thumbImg) {
            error_log("IMAGE_LIB ERROR: Echec du redimensionnement ($maxWidth px) : $srcPath");
            imagedestroy($srcImg);
            return false;
        }

        // On s'assure que le dossier existe
        $destDir = dirname($destPath);
        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Suppression de l'éventuel fichier de 0 octets qui bloquerait
        if (file_exists($destPath) && filesize($destPath) === 0) {
            unlink($destPath);
        }

        $res = self::save($thumbImg, $destPath, 'webp', self::QUALITY_WEBP);
        if (!$res) {
            error_log("IMAGE_LIB ERROR: Impossible d'ecrire le fichier (droits ou disque plein ?) : $destPath");
        } else {
            // Check final : si le fichier fait 0 octets, c'est un echec de imagewebp
            if (filesize($destPath) === 0) {
                error_log("IMAGE_LIB ERROR: imagewebp a genere un fichier vide : $destPath");
                unlink($destPath);
                $res = false;
            }
        }
        
        imagedestroy($srcImg);
        imagedestroy($thumbImg);
        return $res;
    }

    /**
     * Charge une image GD depuis un fichier source selon son extension.
     * @param string $filePath Chemin du fichier
     * @return GdImage|false Ressource image GD ou false en cas d'échec
     */
    public static function load(string $filePath) {
        if (!file_exists($filePath)) return false;

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Si l'extension n'est pas fiable (ex: fichier temporaire d'upload), on peut tenter d'utiliser getimagesize
        $imageInfo = getimagesize($filePath);
        if ($imageInfo) {
            $mime = $imageInfo['mime'];
        } else {
            $mime = 'image/' . $ext;
        }

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg': return imagecreatefromjpeg($filePath);
            case 'image/png':  return imagecreatefrompng($filePath);
            case 'image/gif':  return imagecreatefromgif($filePath);
            case 'image/webp': 
                return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($filePath) : false;
            default: return false;
        }
    }

    /**
     * Sauvegarde une image GD dans le format spécifié.
     * @param GdImage $image Ressource GD
     * @param string $destination Chemin de destination
     * @param string $format 'jpg', 'png', 'webp', 'gif'
     * @param int $quality Qualité (0-100) pour JPG et WebP
     * @return bool Succès ou échec
     */
    public static function save($image, string $destination, string $format, int $quality = 80): bool {
        if (!$image) return false;

        $format = strtolower($format);
        switch ($format) {
            case 'jpg':
            case 'jpeg': return imagejpeg($image, $destination, $quality);
            case 'png':  return imagepng($image, $destination);
            case 'gif':  return imagegif($image, $destination);
            case 'webp': 
                if (function_exists('imagewebp')) {
                    return imagewebp($image, $destination, $quality);
                }
                return false;
            default: return false;
        }
    }

    /**
     * Redimensionne une image.
     * @param GdImage $srcImage Ressource GD source
     * @param int $newWidth Largeur cible
     * @param int $newHeight Hauteur cible
     * @param bool $preserveAlpha Préserver la transparence (PNG/WebP)
     * @return GdImage|false Nouvelle ressource GD ou false
     */
    public static function resize($srcImage, int $newWidth, int $newHeight, bool $preserveAlpha = true) {
        if (!$srcImage) return false;

        $width = imagesx($srcImage);
        $height = imagesy($srcImage);

        $dstImage = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($preserveAlpha) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
        }

        if (imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)) {
            return $dstImage;
        }
        
        imagedestroy($dstImage);
        return false;
    }

    /**
     * Redimensionne une image si elle dépasse les limites fixées, tout en gardant les proportions.
     * @param GdImage $srcImage Ressource GD source
     * @param int $maxWidth Largeur max
     * @param int $maxHeight Hauteur max
     * @param bool $preserveAlpha Préserver la transparence
     * @return GdImage|false Nouvelle ressource GD ou false
     */
    public static function resizeToLimit($srcImage, int $maxWidth, int $maxHeight, bool $preserveAlpha = true) {
        if (!$srcImage) return false;

        $width = imagesx($srcImage);
        $height = imagesy($srcImage);

        if ($width <= $maxWidth && $height <= $maxHeight) {
            // Déjà dans les clous, on crée juste une copie pour rester cohérent avec le retour d'une nouvelle ressource
            $dstImage = imagecreatetruecolor($width, $height);
            if ($preserveAlpha) {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
            }
            imagecopy($dstImage, $srcImage, 0, 0, 0, 0, $width, $height);
            return $dstImage;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        return self::resize($srcImage, $newWidth, $newHeight, $preserveAlpha);
    }

    /**
     * Vérifie si le support WebP est disponible (lecture/écriture).
     * @return bool
     */
    public static function hasWebpSupport(): bool {
        return function_exists('imagewebp') && function_exists('imagecreatefromwebp');
    }

    /**
     * Tente de convertir une image en WebP si possible.
     * @param string $sourcePath
     * @param string $destPath
     * @param int $quality
     * @return bool Succès ou échec (si échec, le fichier source n'est pas touché)
     */
    public static function convertToWebp(string $sourcePath, string $destPath, int $quality = 66): bool {
        if (!self::hasWebpSupport()) return false;

        $img = self::load($sourcePath);
        if (!$img) return false;

        $result = self::save($img, $destPath, 'webp', $quality);
        imagedestroy($img);
        
        return $result;
    }
}
