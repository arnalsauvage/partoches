<?php

use JetBrains\PhpStorm\Pure;

const DOC_TRI = 'docTri';
const DOC_ORDRE_ASC = 'docOrdreAsc';
const CHANSON = "chanson";
const DOC_PAGE = 'docPage';
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("songbook.php");
require_once("lienDocSongbook.php");
require_once("document.php");
require_once("Pagination.php");
$table = "songbook";
$sortie = "";
$monImage = "";

global $_DOSSIER_CHANSONS;

$nombreDocumentsParPage = 50;
if (!isset ($_SESSION['user']) || $_SESSION ['privilege'] < 1) {
    // Affichage du formulaire de login
    echo "pas de contenu...";
    exit();
}
$sortie .= "<h2>Documents publiés pour des chansons</h2> \n"; // Titre

// Gestion du paramètre de tri
if (isset ($_POST ['tri'])) {
    $_SESSION[DOC_TRI] = $_GET ['tri'];
    $_SESSION[DOC_ORDRE_ASC] = true;
} else {
    if (isset ($_GET ['triDesc'])) {
        $_SESSION[DOC_TRI] = $_GET ['triDesc'];
        $_SESSION[DOC_ORDRE_ASC] = false;
    } else {
        if (!isset($_SESSION[DOC_TRI])) {
            $_SESSION[DOC_TRI] = "date";
            $_SESSION[DOC_ORDRE_ASC] = false;
        }
    }
}

$lignes = chercheDocuments("nomTable", CHANSON, $_SESSION[DOC_TRI], $_SESSION[DOC_ORDRE_ASC]);

// Gestion de la pagination
$pagination = new Pagination ($lignes->num_rows, $nombreDocumentsParPage);
if (isset ($_GET['page']) && is_numeric($_GET['page'])) {
    $_SESSION[DOC_PAGE] = $_GET['page'];
} else
    if (!isset ($_SESSION[DOC_PAGE])) {
        $_SESSION[DOC_PAGE] = 1;
    }
$pagination->setPageEnCours($_SESSION[DOC_PAGE]);

// On charge le tableau des utilisateurs
$tabUsers = portraitDesUtilisateurs();

if (isset ($_POST['filtre'])) {
    $contenuFiltrer = $_POST['filtre'];
    $contenuFiltrer = htmlspecialchars($contenuFiltrer, ENT_QUOTES);
    //echo "filtre :" . $contenuFiltrer;
} else {
    $contenuFiltrer = "";
}

$sortie .= "
<form  METHOD='POST' ACTION='documents_voir.php' NAME='formfiltre'>
<input type=text list=typesFichier value='$contenuFiltrer' name='filtre'>
<datalist id=typesFichier >
   <option value='*'> tous</option>
   <option value='doc'> document </option>
   <option value='son'> son</option>p
   <option value='img'> image</option>
</datalist>
<label class='inline'> </label><INPUT TYPE='submit' NAME='filtrer' VALUE=' filtrer ' >
</form>";

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
    if (($numligne < $pagination->getItemDebut()) || $numligne > $pagination->getItemFin()) {
        continue;
    }

    $sortie .= "<tr> \n";
    $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $fichier = "../". $_DOSSIER_CHANSONS . $ligneDoc [6] . "/" . composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $extension = substr(strrchr($ligneDoc [1], '.'), 1);
    //echo "extension " . $extension . " et filtre : " . $contenuFiltrer . " - " ;

    if ($contenuFiltrer) {
        if (($contenuFiltrer == "son") && ($extension <> "mp3")) {
            continue;
        }
        if (($contenuFiltrer == "pdf") && ($extension <> "pdf")) {
            continue;
        }
        if (($contenuFiltrer == "doc") && ($extension <> "doc")) {
            continue;
        }
    }

    $icone = Image("../images/icones/" . $extension . ".png", 32, 32, "icone");

    if (!file_exists("../images/icones/" . $extension . ".png")) {
        $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
    }
        $precedenteVignette = $vignetteChanson;
        $vignettePublicateur = Image("../images/utilisateur/" . urlencode($tabUsers [$ligneDoc [7]] [1]), 48, 48, $tabUsers [$ligneDoc [7]] [0]);
        $sortie .= "<td> $vignettePublicateur </td>\n";
        $vignetteChanson = Image("../".$_DOSSIER_CHANSONS . $ligneDoc [6] . "/" . rawurlencode(imageTableId(CHANSON, $ligneDoc [6])), 128, 128, CHANSON);
        if ($precedenteVignette != $vignetteChanson) {
            $sortie .= "<td> $vignetteChanson </td>\n";
        } else {
            $sortie .= "<td>  </td>\n ";
        }
        $sortie .= "<td> " . Ancre("getdoc.php?doc=" . $ligneDoc [0], $icone, "", true) . "<a href= '" . urlencode($fichier) . "' target='_blank'> " . $fichierCourt . "</a> \n";
        $sortie .= "<td>" . intval($ligneDoc [2] / 1024) . " ko  </td>";
        $sortie .= "<td>" . " -  publié le " . dateMysqlVersTexte($ligneDoc [3]) . " </td>";
        $sortie .= "<td> &nbsp; - " . $ligneDoc [8] . " vues </td></tr>\n";
}
$sortie .= "</table>";
$sortie .= $pagination->barrePagination() . "   ";
$sortie .= envoieFooter();
echo $sortie;

#[Pure] function titreColonne($libelle, $nomRubrique): string
{
    return TblCellule(Ancre("?tri=$nomRubrique", "<span class='glyphicon glyphicon-chevron-up'> </span>")
        . "  $libelle   " . Ancre("?triDesc=$nomRubrique", "  <span class='glyphicon glyphicon-chevron-down'></span> "));
}
