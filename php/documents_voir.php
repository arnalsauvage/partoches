<?php
include_once("lib/utilssi.php");
include_once("menu.php");
include_once("songbook.php");
include_once("lienDocSongbook.php");
include_once("document.php");
include_once("Pagination.php");
$table = "songbook";
$sortie = "";
$monImage = "";

$nombreDocumentsParPage = 50;

$sortie .= "<h2>Documents publiés pour des chansons</h2> \n"; // Titre

// Gestion du paramètre de tri
if (isset ($_GET ['tri'])) {
    $_SESSION['docTri'] = $_GET ['tri'];
    $_SESSION['docOrdreAsc'] = true;
} else {
    if (isset ($_GET ['triDesc'])) {
        $_SESSION['docTri'] = $_GET ['triDesc'];
        $_SESSION['docOrdreAsc'] = false;
    } else {
        if (!isset($_SESSION['docTri'])) {
            $_SESSION['docTri'] = "date";
            $_SESSION['docOrdreAsc'] = false;
        }
    }
}

$lignes = chercheDocuments("nomTable", "chanson", $_SESSION['docTri'], $_SESSION['docOrdreAsc']);

// Gestion de la pagination
$pagination = new Pagination ($lignes->num_rows, $nombreDocumentsParPage);
if (isset ($_GET['page']))
    $_SESSION['docPage'] = $_GET['page'];
else
    if (!isset ($_SESSION['docPage']))
        $_SESSION['docPage'] = 1;
$pagination->setPageEnCours($_SESSION['docPage']);

// On charge le tableau des utilisateurs
$tabUsers = portraitDesUtilisateurs();

$sortie .= "<input type=text list=typesFichier >
<datalist id=typesFichier >
   <option> tous</option>
   <option> document </option>
   <option> son</option>p
   <option> image</option>
</datalist>";

// Entête de la table
$sortie .= "<table> \n";
$sortie .= "<tr>";
$sortie .= titreColonne("Publicateur", "idUSer");
$sortie .= "<td></td>\n";
$sortie .= "<td></td>\n";

$sortie .= titreColonne("Taille", "tailleKo");
$sortie .= titreColonne("Date", "date");
$sortie .= titreColonne("Nbre vues", "hits");
$sortie .= "</tr>\n";

$listeDocs = "";
$vignetteChanson = "";
$numligne = 0;

while ($ligneDoc = $lignes->fetch_row()) {
    $numligne++;
    if (($numligne < $pagination->getItemDebut()) || $numligne > $pagination->getItemFin())
        continue;

    $sortie .= "<tr> \n";
    $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $fichier = "../data/chansons/" . $ligneDoc [6] . "/" . composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $extension = substr(strrchr($ligneDoc [1], '.'), 1);
    $icone = Image("../images/icones/" . $extension . ".png", 32, 32, "icone");

    if (!file_exists("../images/icones/" . $extension . ".png"))
        $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
    $precedenteVignette = $vignetteChanson;
    $vignettePublicateur = Image("../images" . $tabUsers [$ligneDoc [7]] [1], 48, 48, $tabUsers [$ligneDoc [7]] [0]);
    $sortie .= "<td> $vignettePublicateur </td>\n";
    $vignetteChanson = Image("../data/chansons/" . $ligneDoc [6] . "/" . imageTableId("chanson", $ligneDoc [6]), 128, 128, "chanson");
    if ($precedenteVignette != $vignetteChanson) {
        $sortie .= "<td> $vignetteChanson </td>\n";
    } else
        $sortie .= "<td>  </td>\n ";
    $sortie .= "<td> " . Ancre("getdoc.php?doc=" . $ligneDoc [0], $icone, "", true) . "<a href= '" . $fichier . "' target='_blank'> " . $fichierCourt . "</a> \n";
    $sortie .= "<td>" . intval($ligneDoc [2] / 1024) . " ko  </td>";
    $sortie .= "<td>" . " -  publié le " . dateMysqlVersTexte($ligneDoc [3]) . " </td>";
    $sortie .= "<td> &nbsp - " . $ligneDoc [8] . " vues </td></tr>\n";
}

$sortie .= "</table>";
$sortie .= $pagination->barrePagination() . "   ";
$sortie .= envoieFooter();
echo $sortie;
function titreColonne($libelle, $nomRubrique)
{
    $chaine = TblCellule(Ancre("?tri=$nomRubrique", "<span class='glyphicon glyphicon-chevron-up'> ") . "  $libelle   " . Ancre("?triDesc=$nomRubrique", "  <span class='glyphicon glyphicon-chevron-down'> "));
    return $chaine;
}
