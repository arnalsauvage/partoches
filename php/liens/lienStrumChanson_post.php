<?php
require("../chanson/chanson.php");
require_once("../lib/utilssi.php");
require("../utilisateur/utilisateur.php");
require_once("lienStrumChanson.php");
// Un non-admin non editeur ne peut modifier les liens

if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {

    // Suppression réservée aux admins
    if (isset($_GET['mode'] )&&$_GET['mode'] == "DEL" && $_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
        echo "supprimer post " . $_GET['id'];
        supprimelienStrumChanson($_GET['id']);
        echo " ok !";
        return;
    }

    $id = $_POST['idChanson'];
    $strum = $_POST['strum'];

    // Creation
    if ($_POST['mode'] == "NEW")
    {
        echo "NEW " . "post idChanson : " . $_POST['idChanson']. " strum : " . $strum;
        creelienStrumChanson($strum, $id);
        echo " ok !";
        return;
    }

}
//header('Location: ./chanson_form.php?id=' . $_POST['longueur']);
