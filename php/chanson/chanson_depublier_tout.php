<?php
// On démarre la session au tout début
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../lib/configMysql.php";
require_once "../lib/utilssi.php";
require_once "../utilisateur/utilisateur.php"; // Nécessaire pour les constantes de privilèges
require_once "chanson.php";

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
