<?php
require __DIR__ . "/../lib/utilssi.php";
require __DIR__ . "/../document/Document.php";

/**
 * Upload d'avatar utilisateur (Django Style)
 */

// On vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: ../navigation/login.php?msg=AUTH_REQUIRED");
    exit();
}

$id = (int)$_SESSION['id'];
$dossier_cible = __DIR__ . "/../../data/utilisateurs/";

// On vérifie qu'on a un fichier joint
if (!isset($_FILES['fichierUploade']) || $_FILES['fichierUploade']['error'] == 4) {
    header("Location: utilisateur_form.php?id=$id&msg=ERR_NO_FILE");
    exit();
}

// Sécurité : dossier cible
if (!is_dir($dossier_cible)) {
    mkdir($dossier_cible, 0755, true);
}

// Taille autorisée : 2 Mo max
$file_max_size = 2000000; 
if ($_FILES['fichierUploade']['size'] > $file_max_size) {
    header("Location: utilisateur_form.php?id=$id&msg=ERR_SIZE");
    exit();
}

// Extensions autorisées
$path = $_FILES['fichierUploade']['name'];
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$autorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($ext, $autorisees)) {
    header("Location: utilisateur_form.php?id=$id&msg=ERR_EXT");
    exit();
}

// Nettoyage du nom
$name_file = renommeFichierChanson($path);
$destination = $dossier_cible . $name_file;

// Déplacement du fichier
if (move_uploaded_file($_FILES['fichierUploade']['tmp_name'], $destination)) {
    // Succès ! Mise à jour BDD
    require_once __DIR__ . "/Utilisateur.php";
    $userObj = new Utilisateur($id);
    $userObj->setImage($name_file);
    $userObj->enregistreUtilisateurBDD();
    
    $_SESSION['image'] = $name_file;
    header("Location: utilisateur_form.php?id=$id&msg=OK_UPLOAD");
    exit();
} else {
    header("Location: utilisateur_form.php?id=$id&msg=ERR_COPY");
    exit();
}

/**
 * Nettoie le nom du fichier
 */
function renommeFichierChanson($nomFichier)
{
    $nomFichier = str_replace(
        array('à', 'â', 'ä', 'á', 'ã', 'å', 'î', 'ï', 'ì', 'í', 'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 'ù', 'û', 'ü', 'ú', 'é', 'è', 'ê', 'ë', 'ç', 'ÿ', 'ñ', '#', ' '),
        array('a', 'a', 'a', 'a', 'a', 'a', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'e', 'e', 'e', 'e', 'c', 'y', 'n', "diese", "-"),
        $nomFichier
    );
    return $nomFichier;
}
