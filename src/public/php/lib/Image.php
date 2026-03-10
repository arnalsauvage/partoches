<?php

/**
 * Classe utilitaire pour la gestion des images via GD.
 * Gère le chargement, le redimensionnement et la conversion (notamment WebP).
 */
class Image {

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
