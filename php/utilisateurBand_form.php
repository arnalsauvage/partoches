<?php
include_once("lib/utilssi.php");
include_once("menu.php");

$utilisateurForm = "utilisateur_form.php";
$utilisateurGet = "utilisateur_get.php";
$utilisateurVoir = "utilisateur_voir.php";

$table = "utilisateur";
$affichage = "";

if (isset($_POST['utilisateur'])) {
    $listeUtilisateurs = implode(",", $_POST['utilisateur']);
}

$affichage .= entreBalise("Utilisateurs", "H1");
$affichage .= "<form action=\"utilisateurBand_form.php\" method=\"post\"
          enctype=\"multipart/form-data\">";

    //if (($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) || $_SESSION ['user'] == $ligne [1]) {

// Chargement de la liste des utilisateurs et de leur nombre de votes

$marequete = "select u.id , u.prenom, u.nom ,
(select count(*) from noteutilisateur nu WHERE nu.idUtilisateur = u.id)  votes
from utilisateur u
order by votes DESC";
$resultat = $_SESSION ['mysql']->query($marequete);
if (!$resultat)
    die ("Problème utilisateursListe #1 : " . $_SESSION ['mysql']->error);
$numligne = 0;

// Affichage de la liste
while ($ligne = $resultat->fetch_row()) {
    $numligne++;
        $affichage .= "<div>
        <input type='checkbox' style = 'width: 20px;' id='". $ligne[0] ."' value='". $ligne[0] . "'";
        if (isset($_POST['utilisateur']) && in_array($ligne[0],$_POST['utilisateur']))
            $affichage .= " checked ";
        $affichage .= ">
        <label  style = 'width: 240px;'for='" . $ligne[0] ."'>";
        $affichage .= $ligne [1] . " " . $ligne [2] ." " . $ligne [3]  . " votes" ; // nom prenom
        $affichage .= "</label></div><br>\n";
}
$affichage .= " <input type=\"submit\" value=\"Envoyer\" /></form> <br>";

// Gestion de la form
if (isset($_POST['utilisateur'])) {
    /*    foreach ($_POST['utilisateur'] as $valeur) {
            echo "La checkbox utilisateur $valeur a été cochée<br>";
        }
     */

    $marequete = "select chanson.id , chanson.nom , SUM(noteUtilisateur.note) as score from chanson, noteUtilisateur
            where chanson.id = noteUtilisateur.idObjet and noteUtilisateur.nomObjet='chanson'
            AND noteUtilisateur.idUtilisateur in ($listeUtilisateurs)
            GROUP BY chanson.id ORDER BY score DESC";
    //$affichage .= "Requete :" . $marequete;
    $resultat = $_SESSION ['mysql']->query($marequete);

    if ($resultat) {
        $_compteur = 1;
        while ($ligne = $resultat->fetch_row()) {
            $affichage .= $_compteur++ . " - ";
            $affichage .= $ligne[1] . " ";
            $affichage .= $ligne[2] . "<br>";
        }
    }
}

$affichage .= envoieFooter();
echo $affichage;
