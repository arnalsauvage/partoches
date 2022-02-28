<?php
require_once("lib/utilssi.php");
require("lienurl.php");
require("utilisateur.php");
// Un non-admin non editeur ne peut modifier les liens
if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    // Suppression
    if ($_POST['mode'] == "DEL") {
        supprimeLienurl($_POST['id']);
    }

    $id = $_SESSION ['mysql']->real_escape_string($_POST['id']);
    $url = $_SESSION ['mysql']->real_escape_string($_POST['url']);
    $type = $_SESSION ['mysql']->real_escape_string($_POST['type']);
    $description = $_SESSION ['mysql']->real_escape_string($_POST['description']);
    $nomtable = $_SESSION ['mysql']->real_escape_string($_POST['nomtable']);
    $idtable = $_SESSION ['mysql']->real_escape_string($_POST['idtable']);
    $date = $_SESSION ['mysql']->real_escape_string($_POST['date']);
    $idUser = $_SESSION ['mysql']->real_escape_string($_POST['idUser']);
    $hits = $_SESSION ['mysql']->real_escape_string($_POST['hits']);

    // Creation
    if ($_POST['mode'] == "NEW")
    {
        //echo "NEW";
        creeLienurl($url, $type, $description, $nomtable, $idtable, $date, $idUser, $hits);
    }
    // Modification
    if (($_POST['mode'] == "UPDATE") && (is_numeric($id ))) {
        //echo "Update";
        modifieLienurl($id, $url, $type, $description, $nomtable, $idtable, $date, $idUser, $hits);
    }
}
//header('Location: ./chanson_form.php?id=' . $_POST['idTable']);
