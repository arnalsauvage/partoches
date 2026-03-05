<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../lib/configMysql.php";
require_once "../lib/utilssi.php";
require_once "../utilisateur/utilisateur.php";

// Vérification des droits (Membre minimum pour modifier ses propres chansons, Admin pour tout)
if (!aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
    die(json_encode(['status' => 'error', 'message' => 'Droits insuffisants']));
}

if (isset($_POST['id']) && isset($_POST['publication'])) {
    $idChanson = (int)$_POST['id'];
    $publication = (int)$_POST['publication'];
    $db = $_SESSION['mysql'];

    // Vérification de sécurité supplémentaire : si pas admin, on vérifie qu'on est l'auteur
    if (!estAdmin()) {
        $checkReq = "SELECT idUser FROM chanson WHERE id = $idChanson";
        $res = $db->query($checkReq);
        $row = $res->fetch_assoc();
        if (!$row || $row['idUser'] != $_SESSION['id']) {
            die(json_encode(['status' => 'error', 'message' => 'Ce n\'est pas votre chanson !']));
        }
    }

    $maRequete = "UPDATE chanson SET publication = $publication WHERE id = $idChanson";
    
    if ($db->query($maRequete)) {
        echo json_encode(['status' => 'success', 'publication' => $publication]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $db->error]);
    }
}
