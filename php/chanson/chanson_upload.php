<?php
const FICHIER_UPLOADE = 'fichierUploade';
require("../document/document.php");
require("../lib/utilssi.php");

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
    $allowedExtensions = "pdf doc docx gif jpg png swf mp3 mp4 aac odt ppt pptx svg crd txt m4a ogg mscz mid";
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
    $ext = checkFileExtension($path, $allowedExtensions);
    if ($ext === false) {
        return 0;
    }

    // Si le fichier est pour mise à jour, on recherche son nom dans la bdd d'après son id
    if ($_POST['oldFile'] !== null && $_POST['oldFile'] !== 0) {
        $doc = chercheDocument($_POST['oldFile']);
        $name_file = $doc[1];
    } else {
        // Simplification du nom de fichier
        $name_file = simplifieNomFichier($path);
    }
    creeModifieDocument($name_file, $_FILES[FICHIER_UPLOADE]['size'], "chanson", $_POST['id']);
    $doc = chercheDocumentNomTableId($name_file, "chanson", $_POST['id']);

    $name_file = str_replace(".$ext", "-v" . ($doc[4]), $name_file) . ".$ext";

    // Déplacement du fichier
    if (!move_uploaded_file($tmp_file, $repertoire . $name_file)) {
        echo "Il y a des erreurs! Impossible de copier le fichier $name_file dans le dossier cible $repertoire";
        supprimeDocument($doc[0]);
        return 0;
    }

    return 1; // Indique que l'upload a réussi
}

// Appel de la fonction principale
handleFileUpload();
