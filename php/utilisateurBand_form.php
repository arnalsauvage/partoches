<?php
include_once("lib/utilssi.php");
include_once("menu.php");

$utilisateurForm = "utilisateur_form.php";
$utilisateurGet = "utilisateur_get.php";
$utilisateurVoir = "utilisateur_voir.php";

$table = "utilisateur";
$affichage = "";

if (isset($_POST['utilisateurs'])) {
    $listeUtilisateurs = implode(",", $_POST['utilisateurs']);
}

$affichage .= entreBalise("Utilisateurs", "H1");
$affichage .= "<form action=\"utilisateurBand_form.php\" method=\"post\"
          enctype=\"multipart/form-data\">";

    //if (($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) || $_SESSION ['user'] == $ligne [1]) {

// Chargement de la liste des utilisateurs et de leur nombre de votes

$marequete = "select u.id , u.prenom, u.nom ,
(select count(*) from noteUtilisateur nu WHERE nu.idUtilisateur = u.id)  votes
from utilisateur u
order by votes DESC";
$resultat = $_SESSION ['mysql']->query($marequete);
if (!$resultat){
    echo "ma requete : $marequete";
    die ("Problème utilisateursBand #1 : " . $_SESSION ['mysql']->error);
}

// Affichage de la liste
$numligne = 0;
while ($ligne = $resultat->fetch_row()) {
    $numligne++;
        $affichage .= "
        <div>
        <input type='checkbox' style = 'width: 20px;' id='". $ligne[0] ."' value='". $ligne[0] . "' ' name='utilisateurs[]'";
        if (isset($_POST['utilisateurs']) && in_array($ligne[0],$_POST['utilisateurs'])) {
            $affichage .= " checked ";
        }
    $affichage .= ">
        <label  style = 'width: 240px;' for='" . $ligne[0] ."'>";
        $affichage .= $ligne [1] . " " . $ligne [2] ." " . $ligne [3]  . " votes" ; // nom prenom
        $affichage .= "</label></div><br>\n";
}
$affichage .= " <input type=\"submit\" value=\"Envoyer\" /></form> <br>";

// Gestion de la form
if (isset($_POST['utilisateurs'])) {
        if (true==false) {
            foreach ($_POST['utilisateurs'] as $valeur) {
                echo "La checkbox utilisateur $valeur a été cochée<br>";
            }
        }
    $nombreMembres = count($_POST['utilisateurs']);

    $marequete = "select chanson.id , chanson.nom , SUM(noteUtilisateur.note) as score from chanson, noteUtilisateur
            where chanson.id = noteUtilisateur.idObjet and noteUtilisateur.nomObjet='chanson'
            AND noteUtilisateur.idUtilisateur in ($listeUtilisateurs)
            GROUP BY chanson.id ORDER BY score DESC";
    //$affichage .= "Requete :" . $marequete;
    $resultat = $_SESSION ['mysql']->query($marequete);
    if (!$resultat){
        echo "ma requete : $marequete";
        die ("Problème utilisateursBand #2 : " . $_SESSION ['mysql']->error);
    }
    if ($resultat) {
        $_compteur = 1;
        while ($ligne = $resultat->fetch_row()) {
            if (round($ligne[2]/$nombreMembres,2)<3)
            {
                break;
            }
            $affichage .= $_compteur++ . " - ";
            $affichage .= $ligne[1] . " ";
            $affichage .= round($ligne[2]/$nombreMembres,1) . "<br>";
        }
    }
}

$affichage .= envoieFooter();
echo $affichage;
