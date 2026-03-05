<?php
require_once("../lib/utilssi.php");
require_once("utilisateur.php"); // Pour les constantes de privilèges
require_once("../navigation/menu.php");

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

// Vérification session
$userSession = $_SESSION['user'] ?? '';
$privilegeSession = $_SESSION['privilege'] ?? 0;

// Chargement de la liste des utilisateurs
$marequete = "select * from $table ORDER BY dateDernierLogin DESC";
$resultat = $_SESSION ['mysql']->query($marequete);
if (!$resultat) {
    die ("Problème utilisateursListe #1 : " . $_SESSION ['mysql']->error);
}
$numligne = 0;

// On affiche pour l'admin le lien de la page pour les meilleurs résultats pour un band
if ($privilegeSession > ($GLOBALS["PRIVILEGE_MEMBRE"] ?? 1)){
    $fichiersDuSongbook .= "<a href='utilisateurBand_form.php' title='Utilise les votes pour proposer la meilleure setlist d un groupe donné'>Morceaux pour un band</a> <br>";
}
// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($privilegeSession > ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
    $fichiersDuSongbook .= "<BR>" . ancre("$utilisateurForm", image(($cheminImages ?? '') . ($iconeCreer ?? ''), 32, 32) . "Créer un nouvel utilisateur");
}
// //////////////////////////////////////////////////////////////////////ADMIN

if (isset($iconeAttention)) {
    $fichiersDuSongbook .= image($iconeAttention, "100%", 1, 1);
}

$fichiersDuSongbook .= TblDebut();

while ($ligne = $resultat->fetch_row()) {
    $numligne++;
    if (($privilegeSession > ($GLOBALS["PRIVILEGE_MEMBRE"] ?? 1)) || $userSession == $ligne [1]) {
        $fichiersDuSongbook .= TblDebutLigne();

        if (isset($ligne [5]) && $ligne [5] != "") {
            $ligne [5] = str_replace("/utilisateur", "/", $ligne[5]);
            afficheVignette($ligne [5], ($cheminImages ?? '') . "utilisateur", $cheminVignettes ?? '', "vignette utilisateur");
            // //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
            if (($privilegeSession > ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) || $userSession == $ligne [1]) {
                $fichiersDuSongbook .= TblCellule(ancre($utilisateurForm . "?id=$ligne[0]", image((($cheminVignettes ?? '') . $ligne [5]), 32, 32)));
            } // image
            // //////////////////////////////////////////////////////////////////////ADMIN
            else {
                $fichiersDuSongbook .= TblCellule(image((($cheminVignettes ?? '') . $ligne [5]), 32, 32));
            } // image
        } else {
            $fichiersDuSongbook .= TblCellule(ancre($utilisateurForm . "?id=$ligne[0]", "voir"));
        }

        $fichiersDuSongbook .= TblCellule(entreBalise($ligne [1], "H2")); // Login

        $fichiersDuSongbook .= TblCellule(($ligne [3] ?? '') . " " . ($ligne [4] ?? '')); // nom prenom
        $fichiersDuSongbook .= TblCellule(statut($ligne [11] ?? 0));
        $fichiersDuSongbook .= TblCellule(dateMysqlVersTexte($ligne [9] ?? ''));
        $fichiersDuSongbook .= TblCellule(" &nbsp; &nbsp; &nbsp; "); // un petit blanc
        $fichiersDuSongbook .= TblCellule(" " . ($ligne [10] ?? 0) . " logins"); // nbreLogins

        // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
        if ($privilegeSession > ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
            $fichiersDuSongbook .= TblCellule(boutonSuppression($utilisateurGet . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle ?? '', $cheminImages ?? ''));
            // //////////////////////////////////////////////////////////////////////ADMIN
            $fichiersDuSongbook .= TblFinLigne();
        }
    }
}
$fichiersDuSongbook .= TblFin();

if (isset($iconeAttention)) {
    $fichiersDuSongbook .= image($iconeAttention, "100%", 1, 1);
}
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($privilegeSession > ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
    $fichiersDuSongbook .= "<BR>" . ancre("?page=$utilisateurForm", image(($cheminImages ?? '') . ($iconeCreer ?? ''), 32, 32) . "Créer un nouvel utilisateur");
}
// //////////////////////////////////////////////////////////////////////ADMIN
$fichiersDuSongbook .= envoieFooter();
echo $fichiersDuSongbook;
