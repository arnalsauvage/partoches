<?php
/**
 * Upload d'avatar utilisateur (Django Style)
 */
require_once __DIR__ . "/../lib/utilssi.php";
require_once __DIR__ . "/Utilisateur.php";

// On vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: ../navigation/login.php?msg=AUTH_REQUIRED");
    exit();
}

$id = (int)$_SESSION['id'];
// Le dossier des images utilisateur est dans src/public/images/utilisateur/
// Depuis src/public/php/utilisateur/, c'est ../../images/utilisateur/
$dossier_cible = __DIR__ . "/../../images/utilisateur/";

// 1. Gestion des erreurs d'upload natives de PHP
if (!isset($_FILES['fichierUploade'])) {
    header("Location: utilisateur_form.php?id=$id&msg=ERR_NO_FILE");
    exit();
}

$uploadError = $_FILES['fichierUploade']['error'];
if ($uploadError !== UPLOAD_ERR_OK) {
    $msg = "ERR_UPLOAD";
    if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
        $msg = "ERR_SIZE";
    } elseif ($uploadError === UPLOAD_ERR_NO_FILE) {
        $msg = "ERR_NO_FILE";
    }
    header("Location: utilisateur_form.php?id=$id&msg=$msg");
    exit();
}

// 2. Sécurité : création du dossier cible s'il n'existe pas
if (!is_dir($dossier_cible)) {
    if (!mkdir($dossier_cible, 0755, true)) {
        // Si le mkdir échoue, on loggue l'erreur pour le debug mais on affiche un toast générique
        header("Location: utilisateur_form.php?id=$id&msg=ERR_COPY");
        exit();
    }
}

// 3. Taille autorisée : 2 Mo max (en plus du check PHP)
$file_max_size = 2 * 1024 * 1024; 
if ($_FILES['fichierUploade']['size'] > $file_max_size) {
    header("Location: utilisateur_form.php?id=$id&msg=ERR_SIZE");
    exit();
}

// 4. Extensions autorisées
$path = $_FILES['fichierUploade']['name'];
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$autorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($ext, $autorisees)) {
    header("Location: utilisateur_form.php?id=$id&msg=ERR_EXT");
    exit();
}

// 5. Nettoyage du nom et destination
// On utilise une fonction de nettoyage simple ou celle du projet
$name_file = preg_replace('/[^a-zA-Z0-9\._-]/', '', $path);
$destination = $dossier_cible . $name_file;

// 6. Déplacement du fichier
if (move_uploaded_file($_FILES['fichierUploade']['tmp_name'], $destination)) {
    // Succès ! Mise à jour BDD
    $u = Utilisateur::chercheUtilisateur($id);
    if ($u) {
        // La fonction modifieUtilisateur attend tous les paramètres
        // On récupère les données actuelles pour ne pas les écraser
        $mdp_decrypte = Chiffrement::decrypt($u[2]);
        modifieUtilisateur(
            $id, 
            $u[1], // login
            $mdp_decrypte, 
            $u[3], // prenom
            $u[4], // nom
            "/utilisateur/" . $name_file, // nouvelle image
            $u[6], // site
            $u[7], // email
            $u[8], // signature
            $u[10], // nbreLogins
            $u[11]  // privilege
        );
        
        $_SESSION['image'] = "/utilisateur/" . $name_file;
        header("Location: utilisateur_form.php?id=$id&msg=OK_UPLOAD");
        exit();
    }
} else {
    header("Location: utilisateur_form.php?id=$id&msg=ERR_COPY");
    exit();
}
