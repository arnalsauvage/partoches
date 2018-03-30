<?php
include_once("lib/utilssi.php");
include_once("menu.php");
include_once("chanson.php");
include_once("document.php");
// DONE : ajouter un bouton "ajouter un doc pour cette chanson"
// DONE : ajouter la date de publication et le tri par date de pub
$chansonForm = "chanson_form.php";
$chansonPost = "chanson_post.php";
$chansonVoir = "chanson_voir.php";
$table = "chanson";
$contenuHtml = "<div class='container'>
  <div class='starter-template'> \n";

$contenuHtml .= entreBalise("Chansons", "H1");

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
$nbreChansons = $_SESSION ['mysql']->affected_rows;
$numligne = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] > 1)
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'></span> Ajouter une chanson</a>\n";
// //////////////////////////////////////////////////////////////////////ADMIN

$contenuHtml .= Image($iconeAttention, "100%", 1, 1);

$contenuHtml .= TblDebut(0);
$contenuHtml .= TblEnteteDebut(). TblDebutLigne();
$contenuHtml .= TblEntete("  -  ")  ;
$contenuHtml .= TblEntete("  Pochette  ");
$contenuHtml .= titreColonne("Nom", "nom");
$contenuHtml .= titreColonne("Interprète", "interprete");
$contenuHtml .= titreColonne("Année", "annee");
$contenuHtml .= titreColonne("Tempo", "tempo");
$contenuHtml .= titreColonne("Mesure", "mesure");
$contenuHtml .= titreColonne("Pulsation", "pulsation");
$contenuHtml .= titreColonne("Tonalité", "tonalite");
$contenuHtml .= titreColonne("Date pub.", "datePub");
$contenuHtml .= titreColonne("Publié par", "idUser");
$contenuHtml .= titreColonne("Vues", "hits");
$contenuHtml .= TblFinLigne() . TblEnteteFin();
$contenuHtml .= TblCorpsDebut();

$cheminImagesChanson = "../data/chansons/";

while ($ligne = $resultat->fetch_row()) {
    $numligne++;
    $contenuHtml .= TblDebutLigne();

    // //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
    if ($_SESSION ['privilege'] > 1)
        $contenuHtml .= TblCellule(Ancre("$chansonForm?id=$ligne[0]", Image($cheminImages . $iconeEdit, 32, 32))); // Nom));
    else
        $contenuHtml .= TblCellule(" "); // Nom));

    $contenuHtml .= TblCellule(Image(($cheminImagesChanson . $ligne[0] . "/" . imageTableId("chanson", $ligne[0])), 48, 48, "couverture"));
    $contenuHtml .= TblCellule(Ancre("$chansonVoir?id=$ligne[0]", entreBalise(limiteLongueur($ligne[1],18), "EM"))); // Nom
    $contenuHtml .= TblCellule(limiteLongueur($ligne [2],18)); // interprete
    $contenuHtml .= TblCellule($ligne [3],1,1,"centrer"); // annee
    $contenuHtml .= TblCellule($ligne [4],1,1,"alignerAdroite"); // tempo
    $contenuHtml .= TblCellule($ligne [5],1,1,"centrer"); // mesure
    $contenuHtml .= TblCellule($ligne [6],1,1,"centrer"); // pulsation
    $contenuHtml .= TblCellule($ligne [10],1,1,"centrer"); // tonalité
    $contenuHtml .= TblCellule(dateMysqlVersTexte($ligne[7])); // Date Pub
    $nomAuteur = chercheUtilisateur($ligne [8]);
    $nomAuteur = $nomAuteur[3];
    $contenuHtml .= TblCellule($nomAuteur,1,1,"centrer"); // auteur
    $contenuHtml .= TblCellule($ligne [9],1,1,"alignerAdroite"); // hits

    // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
    if ($_SESSION ['privilege'] > 1) {
        $contenuHtml .= TblCellule(boutonSuppression($chansonPost . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages));
        // //////////////////////////////////////////////////////////////////////ADMIN
    }
    $contenuHtml .= TblFinLigne();

}
$contenuHtml .= TblCorpsFin();
$contenuHtml .= TblFin();
$contenuHtml .= $nbreChansons . " chanson(s) dans la liste.<br>\n";
$contenuHtml .= Image($iconeAttention, "100%", 1, 1);
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] > 1) {
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'></span> Ajouter une chanson</a>\n";
}
// //////////////////////////////////////////////////////////////////////ADMIN
$contenuHtml .= "</div>\n
</div><!-- /.container -->\n";
$contenuHtml .= envoieFooter();
echo $contenuHtml;

function titreColonne($libelle, $nomRubrique)
{
    $lienCroissant = Ancre("?tri=$nomRubrique", "<span class='glyphicon glyphicon-chevron-up'> ");
    $lienDecroissant = Ancre("?triDesc=$nomRubrique", "  <span class='glyphicon glyphicon-chevron-down'> ");
    $chaine = TblEntete( $lienCroissant . "  $libelle " .  $lienDecroissant);
    return $chaine;
}

function limiteLongueur($chaine, $tailleMax)
{
    if (strlen($chaine) > $tailleMax)
        return (substr($chaine, 0, $tailleMax) . "...");
    else
        return $chaine;
}

?>