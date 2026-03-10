<?php
const FICHIER_UPLOADE = 'fichierUploade';
require("../document/Document.php");
require("../lib/utilssi.php");
require_once("../lib/Image.php");

global $_DOSSIER_CHANSONS;

// Fonction pour vérifier si l'utilisateur est connecté
function checkUserAuthentication()
{
    if (!isset($_SESSION['user'])) {
        echo "Vous devez vous authentifier !";
        return false;
    }
    return true;
}

// Fonction pour vérifier la présence d'un fichier joint
function checkFileUpload()
{
    if (!isset($_FILES[FICHIER_UPLOADE])) {
        echo "Pas de fichier joint";
        return false;
    }
    return true;
}

// Fonction pour créer un répertoire si nécessaire
function createDirectoryIfNotExists($repertoire)
{
    if (!file_exists($repertoire)) {
        mkdir($repertoire, 0755);
    }
}

// Fonction pour vérifier la taille du fichier
function checkFileSize($fileSize, $minSize, $maxSize)
{
    if ($fileSize < $minSize || $fileSize > $maxSize) {
        echo "La taille du fichier doit être comprise entre $minSize et $maxSize octets ! ";
        return false;
    }
    return true;
}

// Fonction pour vérifier l'extension du fichier
function checkFileExtension($fileName, $allowedExtensions)
{
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    if (!strstr($allowedExtensions, $ext)) {
        echo "Le fichier n'a pas une extension autorisée ($allowedExtensions).";
        return false;
    }
    return $ext;
}

// Fonction principale pour gérer l'upload
function handleFileUpload()
{
    global $_DOSSIER_CHANSONS;

    // Vérification de l'authentification
    if (!checkUserAuthentication()) {
        return 0;
    }

    // Vérification de la présence d'un fichier
    if (!checkFileUpload()) {
        return 0;
    }

    // Paramètres d'application
    $allowedExtensions = "pdf doc docx gif jpg png webp swf mp3 mp4 aac odt ppt pptx svg crd txt m4a ogg mscz mid xls xlsx";
    $repertoire = $_DOSSIER_CHANSONS . $_POST['id'] . "/";
    createDirectoryIfNotExists($repertoire);

    // Taille autorisée (min & max en octets)
    $file_min_size = 1;
    $file_max_size = 10000000;

    // Vérification de la taille du fichier
    if (!checkFileSize($_FILES[FICHIER_UPLOADE]['size'], $file_min_size, $file_max_size)) {
        return 0;
    }

    // Vérification de l'upload du fichier
    $tmp_file = $_FILES[FICHIER_UPLOADE]['tmp_name'];
    if (!is_uploaded_file($tmp_file)) {
        echo "Le fichier est introuvable";
        return 0;
    }

    // Vérification de l'extension
    $path = $_FILES[FICHIER_UPLOADE]['name'];
    $ext = strtolower(checkFileExtension($path, $allowedExtensions));
    if ($ext === false) {
        return 0;
    }

    // Gestion de la conversion WebP
    $canConvertToWebp = function_exists('imagewebp');
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    $finalExt = ($isImage && $canConvertToWebp) ? 'webp' : $ext;

    // Si le fichier est pour mise à jour, on recherche son nom dans la bdd d'après son id
    $oldFileId = $_POST['oldFile'] ?? 0;
    if ($oldFileId > 0) {
        $doc = chercheDocument($oldFileId);
        $name_file = $doc[1];
        // On force l'extension .webp si c'est une image et qu'on peut convertir
        if ($isImage && $canConvertToWebp) {
            $name_file = pathinfo($name_file, PATHINFO_FILENAME) . ".webp";
        }
    } else {
        // Simplification du nom de fichier
        $name_file = simplifieNomFichier($path);
        if ($isImage && $canConvertToWebp) {
            $name_file = pathinfo($name_file, PATHINFO_FILENAME) . ".webp";
        }
    }

    // --- TRAITEMENT IMAGE (REDIMENSIONNEMENT & WEBP) ---
    if ($isImage) {
        $quality = $_SESSION['qualiteWebp'] ?? 66;
        $maxW = $_SESSION['largeurMaxImageChanson'] ?? 400;
        $maxH = $_SESSION['hauteurMaxImageChanson'] ?? 400;
        
        $img = Image::load($tmp_file);
        
        if ($img) {
            // Redimensionnement selon les limites configurées
            $resized = Image::resizeToLimit($img, (int)$maxW, (int)$maxH);
            if ($resized) {
                imagedestroy($img);
                $img = $resized;
            }

            if ($canConvertToWebp) {
                $webp_tmp = $tmp_file . ".webp";
                if (Image::save($img, $webp_tmp, 'webp', $quality)) {
                    $tmp_file = $webp_tmp;
                }
            } else {
                // Si pas de WebP, on sauvegarde dans le format d'origine (ou on laisse le fichier tel quel)
                // Ici on va écraser le fichier temporaire avec la version redimensionnée
                Image::save($img, $tmp_file, $ext, 85);
            }
            
            imagedestroy($img);
            $_FILES[FICHIER_UPLOADE]['size'] = filesize($tmp_file);
        }
    }

    creeModifieDocument($name_file, $_FILES[FICHIER_UPLOADE]['size'], "chanson", $_POST['id']);
    $doc = chercheDocumentNomTableId($name_file, "chanson", $_POST['id']);

    $name_file_versioned = str_replace(".$finalExt", "-v" . ($doc[4]), $name_file) . ".$finalExt";

    // Déplacement du fichier final
    if (!rename($tmp_file, $repertoire . $name_file_versioned)) {
        echo "Il y a des erreurs! Impossible de copier le fichier $name_file_versioned dans le dossier cible $repertoire";
        return 0;
    }

    return 1; // Indique que l'upload a réussi
}

// Appel de la fonction principale
if (handleFileUpload()) {
    actualiseMedias();
}
$idChanson = $_GET['id'] ?? $_POST['id'] ?? 0;

if ($idChanson == 0) {
    echo "Pas de chanson spécifiée";
    exit();
}

header("Location: ./chanson_form.php?id=" . $idChanson);
