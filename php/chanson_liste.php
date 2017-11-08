<?php
include_once("lib/utilssi.php");
include_once("menu.php");
include_once("chanson.php");
// DONE : ajouter un bouton "ajouter un doc pour cette chanson"
// DONE : ajouter la date de publication et le tri par date de pub
$chansonForm = "chanson_form.php";
$chansonPost = "chanson_post.php";
$chansonVoir = "chanson_voir.php";
$table = "chanson";
$fichiersDuSongbook = "";

$fichiersDuSongbook .= entreBalise("Chansons", "H1");

// Gestion du paramètre de tri
if (isset ($_GET ['tri'])) {
    $tri = $_GET ['tri'];
    $ordreAsc = true;
} else {
    if (isset ($_GET ['triDesc'])) {
        $tri = $_GET ['triDesc'];
        $ordreAsc = false;
    } else {
        $tri = "datePub";
        $ordreAsc = false;
    }
}
// Chargement de la liste des chansons
$resultat = chercheChansons("nom", "%", $tri, $ordreAsc);
$numligne = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] > 1)
    $fichiersDuSongbook .= "<BR>" . Ancre("$chansonForm", Image($cheminImages . $iconeCreer, 32, 32) . "Ajouter une chanson");
// //////////////////////////////////////////////////////////////////////ADMIN

$fichiersDuSongbook .= Image($iconeAttention, "100%", 1, 1);

$fichiersDuSongbook .= TblDebut(0);
$fichiersDuSongbook .= TblDebutLigne() . TblCellule("  Tri  ") . TblCellule(Ancre("?tri=nom", "  Nom  ^ ") . Ancre("?triDesc=nom", "  v ")) . TblCellule(Ancre("?tri=interprete", "  Interprète ^ ") . Ancre("?triDesc=interprete", "  v  ")) . TblCellule(Ancre("?tri=annee", "  Année ^ ") . Ancre("?triDesc=annee", "  v  "));
$fichiersDuSongbook .= TblCellule(Ancre("?tri=tempo", "  Tempo  ")) . TblCellule(Ancre("?tri=mesuree", "  Mesure  ")) . TblCellule(Ancre("?tri=pulsation", "  Pulsation  "));
$fichiersDuSongbook .= TblCellule(Ancre("?tri=tonalite", "  Tonalité  ")) . TblCellule(Ancre("?tri=datePub", "  Date Pub.  ")) . TblCellule(Ancre("?tri=idUser", "  Publié par  ")) . TblCellule(Ancre("?tri=hits", "  Vues  ")) . TblFinLigne();
while ($ligne = $resultat->fetch_row()) {
    $numligne++;
    $fichiersDuSongbook .= TblDebutLigne();

    /*
     * TODO Gestion d'une image pour une chanson'
     * if($ligne[5])
     * TblCellule(Ancre($chansonForm."?id=$ligne[0]",afficheVignette(($ligne[5]),$cheminImages,$cheminVignettes))); // image
     * else
     *
     * TblCellule(Ancre($_SESSION['urlSite']."/index.php?id=$ligne[0]","voir"));
     */

    // //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
    if ($_SESSION ['privilege'] > 1)
        $fichiersDuSongbook .= TblCellule(Ancre("$chansonForm?id=$ligne[0]", Image($cheminImages . $iconeEdit, 32, 32))); // Nom));
    else
        $fichiersDuSongbook .= TblCellule(" "); // Nom));

    $fichiersDuSongbook .= TblCellule(Ancre("$chansonVoir?id=$ligne[0]", entreBalise($ligne [1], "H3"))); // Nom
    $fichiersDuSongbook .= TblCellule($ligne [2]); // interprete
    $fichiersDuSongbook .= TblCellule($ligne [3]); // annee
    $fichiersDuSongbook .= TblCellule($ligne [4]); // tempo
    $fichiersDuSongbook .= TblCellule($ligne [5]); // mesure
    $fichiersDuSongbook .= TblCellule($ligne [6]); // pulsation
    $fichiersDuSongbook .= TblCellule($ligne [10]); // tonalité
    $fichiersDuSongbook .= TblCellule(dateMysqlVersTexte($ligne[7])); // Date Pub
    $nomAuteur = chercheUtilisateur($ligne [8]);
    $nomAuteur = $nomAuteur[3];
    $fichiersDuSongbook .= TblCellule($nomAuteur); // auteur
    $fichiersDuSongbook .= TblCellule($ligne [9]); // hits

    // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
    if ($_SESSION ['privilege'] > 1) {
        $fichiersDuSongbook .= TblCellule(boutonSuppression($chansonPost . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages));
        // //////////////////////////////////////////////////////////////////////ADMIN
        $fichiersDuSongbook .= TblFinLigne();
    }
}
$fichiersDuSongbook .= TblFin();

$fichiersDuSongbook .= Image($iconeAttention, "100%", 1, 1);
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] > 1)
    $fichiersDuSongbook .= "<BR>" . Ancre("$chansonForm", Image($cheminImages . $iconeCreer, 32, 32) . "Ajouter une chanson");
// //////////////////////////////////////////////////////////////////////ADMIN
$fichiersDuSongbook .= envoieFooter("Bienvenue chez nous !");
echo $fichiersDuSongbook;
?>