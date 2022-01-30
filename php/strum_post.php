<?php
require_once("lib/utilssi.php");
require("strum.php");
require("utilisateur.php");
// Un non-admin non editeur ne peut modifier les liens

if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {

    // Suppression réservée aux admins
    if ($_POST['mode'] == "DEL" && $_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
        echo "supprimer post " + $_POST['id'];
        supprimestrum($_POST['id']);
        echo " ok !";
        return;
    }

    $id = $_SESSION ['mysql']->real_escape_string($_POST['id']);
    $strum = $_SESSION ['mysql']->real_escape_string($_POST['strum']);
    $description = $_SESSION ['mysql']->real_escape_string($_POST['description']);
    $unite = intval($_SESSION ['mysql']->real_escape_string($_POST['unite']));
    $longueur = intval($_SESSION ['mysql']->real_escape_string($_POST['longueur']));

    // Creation
    if ($_POST['mode'] == "NEW")
    {
        echo "NEW " . "post id : " . $_POST['id']. " strum : " . $strum;
        $strum = new Strum (  0, $strum, $unite, $longueur, $description);
        $strum->creestrumBDD();
        echo " ok !";
        return;
    }
    // Modification
    if (($_POST['mode'] == "UPDATE") && (is_numeric($id ))) {
        echo "UPDATE " . "post id : " . $_POST['id']. " strum : " . $strum;
        $strum = new Strum ($id, $strum, $unite, $longueur, $description);
        $strum->creeModifiestrumBDD();
        echo " ok !";
        return;
    }
}
//header('Location: ./chanson_form.php?id=' . $_POST['longueur']);
