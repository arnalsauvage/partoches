<?php
include_once("lib/utilssi.php");
include_once("menu.php");
include_once "utilisateur.php";

$nomTable = "utilisateur";
$utilisateurListe = "utilisateur_liste.php";

// include_once ("params.php");
$sortie = envoieHead("Menu", "../css/index.css");
$table = "utilisateur";
entreBalise("Utilisateurs", "H1");

//var_dump( $_POST);
$mode = $_POST ['mode'];

$fimage = "/utilisateur/" . $_POST ['fimage'];
// Un non-admin ne peut changer ses privilèges
if ($_SESSION ['privilege'] < 3) {
    $fprivilege = $_SESSION ['privilege'];
    // ne peut changer son nombre de connexions, il faut donc charger la valeur, elle n'est pas passée par le formulaire
    $fnbreLogins = chercheUtilisateur($_SESSION ['id']);
    $fnbreLogins = $fnbreLogins[11];
}
else
{
    $fprivilege = $_POST ['fprivilege'];
    $fnbreLogins = $_POST ['fnbreLogins'];
}

// On gère 3 cas : création d'utilisateur, modif ou suppressions
if ($mode == "MAJ") {
    modifieUtilisateur($_POST ['id'], $_POST ['flogin'], $_POST ['fmdp'], $_POST ['fprenom'], $_POST ['fnom'],
        $_POST ['fimage'], $_POST ['fsite'], $_POST ['femail'], $_POST ['fsignature'], $fnbreLogins,
        $fprivilege);
}

// Gestion de la demande de suppression
if ($_POST ['$id'] && $mode == "SUPPR") {
    if ($_SESSION ['privilege'] > 2) {
        supprimeUtilisateur($_POST ['id']);
    }
}
if ($mode == "INS") {
    // Entrer une nouvelle fiche utilisateur
    creeUtilisateur($_POST ['flogin'], $_POST ['fmdp'], $_POST ['fprenom'], $_POST ['fnom'],
        $_POST ['fimage'], $_POST ['fsite'], $_POST ['femail'], $_POST ['fsignature'], $_POST ['fprivilege']);
}
redirection($utilisateurListe);
