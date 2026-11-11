<?php
include_once __DIR__ . "/../lib/utilssi.php";
include_once __DIR__ . "/../navigation/menu.php";

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
            $affichage .= "<li>" . ($utilisateur['login'] ?? $utilisateur[1]) . "</li>";
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
        while ($ligne = $resultat->fetch_assoc()) {
            $score = (float)($ligne['score'] ?? $ligne[2]);
            if (round($score / $nombreMembres, 2) < 3) {
                break;
            }
            $affichage .= $_compteur++ . " - ";
            $affichage .= ($ligne['nom'] ?? $ligne[1]) . " ";
            $affichage .= round($score / $nombreMembres, 1) . "<br>";
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
    $affichage .= "<form action=\"utilisateurBand_form.php\" method=\"post\" enctype=\"multipart/form-data\">";
    $numligne = 0;
    while ($ligne = $resultat->fetch_assoc()) {
        $numligne++;
        $uId = (int)($ligne['id'] ?? $ligne[0]);
        $uPrenom = $ligne['prenom'] ?? $ligne[1] ?? '';
        $uNom = $ligne['nom'] ?? $ligne[2] ?? '';
        $uVotes = $ligne['votes'] ?? $ligne[3] ?? 0;
        $affichage .= "
        <div>
        <input type='checkbox' style = 'width: 20px;' id='$uId' value='$uId' name='utilisateurs[]'";
        if (isset($_POST['utilisateurs']) && in_array($uId, $_POST['utilisateurs'])) {
            $affichage .= " checked ";
        }
        $affichage .= ">
        <label  style = 'width: 240px;' for='$uId'>";
        $affichage .= "$uPrenom $uNom $uVotes votes";
        $affichage .= "</label></div><br>\n";
    }
    $affichage .= " <input type=\"submit\" value=\"Envoyer\" /></form> <br>";
    return $affichage;
}