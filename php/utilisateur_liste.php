<?php
require_once("lib/utilssi.php");
require_once("menu.php");

global $largeur_max_vignette;
global $iconeAttention;
global $cheminImages;
global $iconePoubelle;
global $iconeCreer;
global $cheminVignettes;

$utilisateurForm = "utilisateur_form.php";
$utilisateurGet = "utilisateur_get.php";
$utilisateurVoir = "utilisateur_voir.php";
$largeur_max_vignette = 64;
$table = "utilisateur";
$fichiersDuSongbook = "";
$fichiersDuSongbook .= entreBalise("Utilisateurs", "H1");

// Chargement de la liste des utilisateurs
$marequete = "select * from $table ORDER BY 'dateDernierLogin' DESC";
$resultat = $_SESSION ['mysql']->query($marequete);
if (!$resultat) {
    die ("Problème utilisateursListe #1 : " . $_SESSION ['mysql']->error);
}
$numligne = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $fichiersDuSongbook .= "<BR>" . Ancre("$utilisateurForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouvel utilisateur");
}
// //////////////////////////////////////////////////////////////////////ADMIN

// On affiche pour l'admin le lien de la page pour les meilleurs résultats pour un band
if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]){
    $fichiersDuSongbook .= "<a href='utilisateurBand_form.php'>Morceaux pour un band</a>";
}
    $fichiersDuSongbook .= Image($iconeAttention, "100%", 1, 1);

$fichiersDuSongbook .= TblDebut();

while ($ligne = $resultat->fetch_row()) {
    $numligne++;
    if (($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) || $_SESSION ['user'] == $ligne [1]) {
        $fichiersDuSongbook .= TblDebutLigne();

        if ($ligne [5]) {
            $ligne [5] = str_replace("/utilisateur", "/", $ligne[5]);
            afficheVignette($ligne [5], $cheminImages . "utilisateur", $cheminVignettes, "vignette utilisateur");
            // //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
            if (($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) || $_SESSION ['user'] == $ligne [1]) {
                $fichiersDuSongbook .= TblCellule(Ancre($utilisateurForm . "?id=$ligne[0]", Image(($cheminVignettes . $ligne [5]), 32, 32)));
            } // image
            // //////////////////////////////////////////////////////////////////////ADMIN
            else {
                $fichiersDuSongbook .= TblCellule(Image(($cheminVignettes . $ligne [5]), 32, 32));
            } // image
        } else {
            $fichiersDuSongbook .= TblCellule(Ancre($utilisateurForm . "?id=$ligne[0]", "voir"));
        }

        $fichiersDuSongbook .= TblCellule(entreBalise($ligne [1], "H2")); // Login

        $fichiersDuSongbook .= TblCellule($ligne [3] . " " . $ligne [4]); // nom prenom
        $fichiersDuSongbook .= TblCellule(statut($ligne [11]));
        $fichiersDuSongbook .= TblCellule(dateMysqlVersTexte($ligne [9]));
        $fichiersDuSongbook .= TblCellule(" &nbsp; &nbsp; &nbsp; "); // un petit blanc
        $fichiersDuSongbook .= TblCellule(" " . $ligne [10] . " logins"); // nbreLogins

        // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
        if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
            $fichiersDuSongbook .= TblCellule(boutonSuppression($utilisateurGet . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages));
            // //////////////////////////////////////////////////////////////////////ADMIN
            $fichiersDuSongbook .= TblFinLigne();
        }
    }
}
$fichiersDuSongbook .= TblFin();

$fichiersDuSongbook .= Image($iconeAttention, "100%", 1, 1);
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $fichiersDuSongbook .= "<BR>" . Ancre("?page=$utilisateurForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouvel utilisateur");
}
// //////////////////////////////////////////////////////////////////////ADMIN
$fichiersDuSongbook .= envoieFooter();
echo $fichiersDuSongbook;
