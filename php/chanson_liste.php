<?php
include_once("lib/utilssi.php");
include_once("menu.php");
include_once("chanson.php");
include_once("document.php");
include_once("Pagination.php");
include_once ("UtilisateurNote.php");

$chansonForm = "chanson_form.php";
$chansonPost = "chanson_post.php";
$chansonVoir = "chanson_voir.php";
$table = "chanson";
$nombreChansonsParPage = 20;

$contenuHtml = "<div class='container'> \n
  <div class='starter-template'> \n";

$contenuHtml .= entreBalise("Chansons", "H1");

// Gestion du paramètre de tri
// On prend en compte une demande de tri ascendant
if (isset ($_GET ['tri'])) {
    $_SESSION['tri'] = $_GET ['tri'];
    $_SESSION['ordreAsc'] = true;
    // echo "session tri = get tro = " . $_SESSION['tri'] = $_GET ['tri'];
}
// On prend en compte une demande de tri descendant
else {
    if (isset ($_GET ['triDesc'])) {
        $_SESSION['tri'] = $_GET ['triDesc'];
        $_SESSION['ordreAsc'] = false;
        // echo "session tri desc = get tro = " . $_SESSION['tri'] = $_GET ['triDesc'];
    }
    // Sinon, on installe le tri par date dégressif
    else {
        if (!isset ($_SESSION['tri'])) {
            $_SESSION['tri'] = "datePub";
            $_SESSION['ordreAsc'] = false;
            // echo "tri par défaut ";
        }
    }
}

// Gestion paramètres de recherche
if (isset ($_POST ['cherche'])) {

    $_SESSION['cherche'] = $_POST['cherche'];
}
else
    if (! isset($_SESSION['cherche']))
    $_SESSION['cherche'] = "";

if ($_SESSION['cherche'] != "")
    $critere_cherche = "%" . $_SESSION['cherche'] . "%";
else
    $critere_cherche = "%";

// Gestion razFiltres
if (isset ($_GET ['razFiltres'])) {
    $_SESSION['tri'] = "datePub";
    $_SESSION['ordreAsc'] = false;
    $_SESSION['cherche'] = "";
    $critere_cherche = "%";
}


// echo " Recherche = " . $critere_cherche;

// Chargement de la liste des chansons
$resultat = Chanson::chercheChansons( $critere_cherche , $_SESSION['tri'] , $_SESSION['ordreAsc'] );
$nbreChansons = count($resultat);
$numligne = 0;

// Gestion de la pagination
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
$contenuHtml .= titreColonne("  Votes  ", "votes");
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
foreach ($resultat as $ligne) {
    $numligne++;
    if (($numligne < $pagination->getItemDebut()) || $numligne > $pagination->getItemFin())
        continue;
    $contenuHtml .= TblDebutLigne();

    $_chanson->chercheChanson($ligne);
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
    if ($_SESSION ['privilege'] > 0)
        $contenuHtml .= TblCellule(  UtilisateurNote::starBarUtilisateur( "chanson", $_id, 5, 25), 1, 1, "centrer");
    else
        $contenuHtml .= TblCellule(  UtilisateurNote::starBar( "chanson", $_id, 5, 25), 1, 1, "centrer");

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
if ($nbreChansons==0)
    $contenuHtml .= "Pas de résultat ... <BR><a href='?razFiltres' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'> </span> Supprimer les filtres et tris</a>\n";
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] > 1) {
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'> </span> Ajouter une chanson</a>\n";
}
// //////////////////////////////////////////////////////////////////////ADMIN

// Affichage de la recherche
include_once("chanson_comp_cherche.php");
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