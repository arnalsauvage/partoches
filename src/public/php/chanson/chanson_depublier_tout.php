<?php
require_once file_exists(dirname(__DIR__, 2) . '/autoload.php') ? dirname(__DIR__, 2) . '/autoload.php' : dirname(__DIR__) . '/autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once LIB_DIR . "/configMysql.php";
require_once LIB_DIR . "/utilssi.php";
if (!class_exists('Utilisateur')) {
    require_once PHP_DIR . "/utilisateur/Utilisateur.php";
}
require_once PHP_DIR . "/chanson/Chanson.php";

// Vérification des droits admin
if (!estAdmin()) {
    $idUser = (isset($_GET['idUser']) && is_numeric($_GET['idUser'])) ? (int)$_GET['idUser'] : 0;
    header("Location: ../utilisateur/utilisateur_form.php?id=$idUser&msg=error_rights");
    exit();
}

if (isset($_GET['idUser']) && is_numeric($_GET['idUser'])) {
    $idUser = (int)$_GET['idUser'];
    
    // On utilise la connexion mysqli directement depuis la variable ou la session
    $db = $_SESSION['mysql'];
    $maRequete = "UPDATE chanson SET publication = 0 WHERE idUser = $idUser";
    
    if ($db->query($maRequete)) {
        header("Location: ../utilisateur/utilisateur_form.php?id=$idUser&msg=depub_ok");
        exit();
    } else {
        header("Location: ../utilisateur/utilisateur_form.php?id=$idUser&msg=error_db");
        exit();
    }
} else {
    header("Location: ../chanson/chanson_liste.php");
    exit();
}
