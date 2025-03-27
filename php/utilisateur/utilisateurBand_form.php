<?php
include_once("../lib/utilssi.php");
include_once("../navigation/menu.php");

$utilisateurForm = "utilisateur_form.php";
$utilisateurGet = "utilisateur_get.php";
$utilisateurVoir = "utilisateur_voir.php";

$table = "utilisateur";
$affichage = "";

if ($_SESSION ['privilege'] >= $GLOBALS["PRIVILEGE_MEMBRE"]) {
    $affichage .= entreBalise("Utilisateurs", "H1");
    if (!isset($_POST['utilisateurs'])) {
        $affichage .= afficheSelectionUtilisateurs();
    }
// Gestion de la form
    if (isset($_POST['utilisateurs'])) {
        $listeUtilisateurs = implode(",", $_POST['utilisateurs']);
        $nombreMembres = count($_POST['utilisateurs']);
        $affichage .= "Pour le groupe composé par : <br> <ul>";
        foreach ($_POST['utilisateurs'] as $valeur) {
            $utilisateur = chercheUtilisateur($valeur);
            $affichage .= "<li>" . $utilisateur[1] . "</li>";
        }
        $affichage .= "</ul> <br> Un bon choix de morceaux serait : <br>";
        $marequete = "select chanson.id , chanson.nom , SUM(noteUtilisateur.note) as score from chanson, noteUtilisateur
            where chanson.id = noteUtilisateur.idObjet and noteUtilisateur.nomObjet='chanson'
            AND noteUtilisateur.idUtilisateur in ($listeUtilisateurs)
            GROUP BY chanson.id ORDER BY score DESC";
        // Pour debug $affichage .= "Requete :" . $marequete;
        $resultat = $_SESSION ['mysql']->query($marequete);
        if (!$resultat) {
            echo "ma requete : $marequete";
            die ("Problème utilisateursBand #2 : " . $_SESSION ['mysql']->error);
        }
        $_compteur = 1;
        while ($ligne = $resultat->fetch_row()) {
            if (round($ligne[2] / $nombreMembres, 2) < 3) {
                break;
            }
            $affichage .= $_compteur++ . " - ";
            $affichage .= $ligne[1] . " ";
            $affichage .= round($ligne[2] / $nombreMembres, 1) . "<br>";
        }
        $affichage .= "<a href=''>Retour</a>";
    }
    $affichage .= envoieFooter();
    echo $affichage;
}

function afficheSelectionUtilisateurs(): string
{
// Chargement de la liste des utilisateurs et de leur nombre de votes
    $affichage = "";
    $marequete = "select u.id , u.prenom, u.nom ,
(select count(*) from noteUtilisateur nu WHERE nu.idUtilisateur = u.id)  votes
from utilisateur u
order by votes DESC";
    $resultat = $_SESSION ['mysql']->query($marequete);
    if (!$resultat) {
        echo "ma requete : $marequete";
        die ("Problème utilisateursBand #1 : " . $_SESSION ['mysql']->error);
    }
// Affichage de la liste de selection des utilisateurs
    $affichage .= ">";
    $numligne = 0;
    while ($ligne = $resultat->fetch_row()) {
        $numligne++;
        $affichage .= "
        <div>
        <input type='checkbox' style = 'width: 20px;' id='" . $ligne[0] . "' value='" . $ligne[0] . "' ' name='utilisateurs[]'";
        if (isset($_POST['utilisateurs']) && in_array($ligne[0], $_POST['utilisateurs'])) {
            $affichage .= " checked ";
        }
        $affichage .= ">
        <label  style = 'width: 240px;' for='" . $ligne[0] . "'>";
        $affichage .= $ligne [1] . " " . $ligne [2] . " " . $ligne [3] . " votes"; // nom prenom
        $affichage .= "</label></div><br>\n";
    }
    $affichage .= " <input type=\"submit\" value=\"Envoyer\" /></form> <br>";
    return $affichage;
}