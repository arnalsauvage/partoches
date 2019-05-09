<?php
include_once("lib/utilssi.php");
include_once("menu.php");
include_once("chanson.php");
include_once("document.php");
include_once("chanson_comp_cherche.php");
include_once("Pagination.php");

// DONE : ajouter un bouton "ajouter un doc pour cette chanson"
// DONE : ajouter la date de publication et le tri par date de pub
$chansonForm = "chanson_form.php";
$chansonPost = "chanson_post.php";
$chansonVoir = "chanson_voir.php";
$table = "chanson";
$nombreChansonsParPage = 20;

$contenuHtml = "<div class='container'> \n
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

// Gestion parametre de recherche
if (isset ($_GET ['cherche'])) {
    $cherche = "%" . $_GET ['cherche'] . "%";
} else {
    $cherche = "%";
}

$critere = "nom";
// Gestion parametre de recherche
if (isset ($_POST ['chercheT']) && (strlen($_POST['chercheT']) > 0)) {
    $cherche = "%" . $_POST ['chercheT'] . "%";
}
if (isset ($_POST ['chercheI']) && strlen($_POST['chercheI']) > 0) {

    $critere = "interprete";
    $cherche = "%" . $_POST ['chercheI'] . "%";
}


// Chargement de la liste des chansons
$resultat = Chanson::chercheChansons($critere, $cherche, $tri, $ordreAsc);
$nbreChansons = $_SESSION ['mysql']->affected_rows;
$numligne = 0;

$pagination = new Pagination ($nbreChansons, $nombreChansonsParPage);
if (isset ($_GET['page']))
    $page = $_GET['page'];
else
    $page = 1;
$pagination->setPageEnCours($page);

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] > 1)
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'></span> Ajouter une chanson</a>\n";
// //////////////////////////////////////////////////////////////////////ADMIN

$contenuHtml .= Image($iconeAttention, "100%", 1, 1);

$contenuHtml .= TblDebut(0);
$contenuHtml .= TblEnteteDebut() . TblDebutLigne();
$contenuHtml .= TblEntete("  -  ");
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
// //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
if ($_SESSION ['privilege'] > 1) {
    $contenuHtml .= TblCellule(" ");
}
// //////////////////////////////////////////////////////////////////////ADMIN
$contenuHtml .= TblFinLigne() . TblEnteteFin();
$contenuHtml .= TblCorpsDebut();

$cheminImagesChanson = "../data/chansons/";
$_chanson = new Chanson();

/** @noinspection PhpUndefinedMethodInspection */
while ($ligne = $resultat->fetch_row()) {
    $numligne++;
    if (($numligne < $pagination->getItemDebut()) || $numligne > $pagination->getItemFin())
        continue;
    $contenuHtml .= TblDebutLigne();

    $_chanson->chercheChanson($ligne[0]);
    $_id = $_chanson->getId();

    // //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
    if ($_SESSION ['privilege'] > 1)
        $contenuHtml .= TblCellule(Ancre("$chansonForm?id=" . $_id, Image($cheminImages . $iconeEdit, 32, 32)));
    else
        $contenuHtml .= TblCellule(" ");
    $imagePochette = Image(($cheminImagesChanson . $_id . "/" . rawurlencode(imageTableId("chanson", $_id))), 48, 48, "couverture");
    $contenuHtml .= TblCellule(Ancre("$chansonVoir?id=$_id", $imagePochette));
    $contenuHtml .= TblCellule(Ancre("$chansonVoir?id=$_id", entreBalise(limiteLongueur($_chanson->getNom(), 21), "EM"))); // Nom
    $contenuHtml .= TblCellule(limiteLongueur($_chanson->getInterprete(), 21)); // interprete
    $contenuHtml .= TblCellule($_chanson->getAnnee(), 1, 1, "centrer"); // annee
    $contenuHtml .= TblCellule($_chanson->getTempo(), 1, 1, "alignerAdroite"); // tempo
    $contenuHtml .= TblCellule($_chanson->getMesure(), 1, 1, "centrer"); // mesure
    $contenuHtml .= TblCellule($_chanson->getPulsation(), 1, 1, "centrer"); // pulsation
    $contenuHtml .= TblCellule($_chanson->getTonalite(), 1, 1, "centrer"); // tonalité
    $contenuHtml .= TblCellule(dateMysqlVersTexte($_chanson->getDatePub())); // Date Pub
    $nomAuteur = chercheUtilisateur($_chanson->getIdUser());
    $nomAuteur = $nomAuteur[3];
    $contenuHtml .= TblCellule($nomAuteur, 1, 1, "centrer"); // auteur
    $contenuHtml .= TblCellule($_chanson->getHits(), 1, 1, "alignerAdroite"); // hits

    // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
    if ($_SESSION ['privilege'] > 1) {
        $contenuHtml .= TblCellule(boutonSuppression($chansonPost . "?id=$_id&mode=SUPPR", $iconePoubelle, $cheminImages));
        // //////////////////////////////////////////////////////////////////////ADMIN
    }
    $contenuHtml .= TblFinLigne();
}
$contenuHtml .= TblCorpsFin();
$contenuHtml .= TblFin();
$contenuHtml .= $pagination->barrePagination() . "   ";
$contenuHtml .= $nbreChansons . " chanson(s) dans la liste.<br>\n";
$contenuHtml .= Image($iconeAttention, "100%", 1, 1);
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] > 1) {
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'> </span> Ajouter une chanson</a>\n";
}
// //////////////////////////////////////////////////////////////////////ADMIN


// Affichage de la recherche
$contenuHtml .= $contenuHtmlCompCherche;
$contenuHtml .= "
</div>\n
</div><!-- /.container -->\n";
$contenuHtml .= envoieFooter();
echo $contenuHtml;

function titreColonne($libelle, $nomRubrique)
{
    $lienCroissant = Ancre("?tri=$nomRubrique", "<span class='glyphicon glyphicon-chevron-up'> </span>");
    $lienDecroissant = Ancre("?triDesc=$nomRubrique", "  <span class='glyphicon glyphicon-chevron-down'> </span>");
    $chaine = TblEntete($lienCroissant . "  $libelle " . $lienDecroissant);
    return $chaine;
}

?>