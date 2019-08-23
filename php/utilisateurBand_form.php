<?php
include_once("lib/utilssi.php");
include_once("menu.php");

$utilisateurForm = "utilisateur_form.php";
$utilisateurGet = "utilisateur_get.php";
$utilisateurVoir = "utilisateur_voir.php";

$table = "utilisateur";
$affichage = "";

// Gestion de la form
if (isset($_POST['utilisateur'])) {
    $listeUtilisateurs = "";
/*    foreach ($_POST['utilisateur'] as $valeur) {
        echo "La checkbox utilisateur $valeur a été cochée<br>";
    }
 */
    $listeUtilisateurs .= implode(",", $_POST['utilisateur']);

    $marequete = "select chanson.nom , SUM(noteUtilisateur.note) as score from chanson, noteUtilisateur
            where chanson.id = noteUtilisateur.idObjet and noteUtilisateur.nomObjet='chanson'
            AND noteUtilisateur.idUtilisateur in ($listeUtilisateurs)
            GROUP BY chanson.nom ORDER BY score DESC";
    $resultat = $_SESSION ['mysql']->query($marequete);

    while ($ligne = $resultat->fetch_row()) {
        $affichage .= $ligne[0] . " ";
        $affichage .= $ligne[1] . "<br>";
    }
}


$affichage .= entreBalise("Utilisateurs", "H1");
$affichage .= "<form action=\"utilisateurBand_form.php\" method=\"post\"
          enctype=\"multipart/form-data\">";

    //if (($_SESSION ['privilege'] > 1) || $_SESSION ['user'] == $ligne [1]) {
// Chargement de la liste des utilisateurs
$marequete = "select * from $table ORDER BY dateDernierLogin DESC";
$resultat = $_SESSION ['mysql']->query($marequete);
if (!$resultat)
    die ("Problème utilisateursListe #1 : " . $_SESSION ['mysql']->error);
$numligne = 0;

// Affichage de la liste

while ($ligne = $resultat->fetch_row()) {
    $numligne++;
        $affichage .= TblDebutLigne();

        $affichage .= "<div>
        <input type='checkbox' id='". $ligne[0] ."' value='". $ligne[0] ."' name='utilisateur[]'
         >
        <label for='". $ligne[0] ."'>";
        $affichage .= TblCellule($ligne [3] . " " . $ligne [4]); // nom prenom
        $affichage .= "</label></div><br>\n";


}
$affichage .= " <input type=\"submit\" value=\"Envoyer\" /></form>";

$affichage .= envoieFooter();
echo $affichage;
