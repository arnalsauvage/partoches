<?php

include_once("menu.php");
include_once("songbook.php");
include_once("document.php");
include_once("lib/utilssi.php");

global $cheminImages;
global $iconePoubelle;
global $songbookForm;
global $iconeCreer;
global $cheminImagesSongbook;
global $songbookGet;
global $songbookVoir;

$table = "songbook";
$fichiersDuSongbook = "";
$fichiersDuSongbook .= entreBalise("Songbooks", "H1");

// Gestion du paramètre de tri
if (isset ($_GET ['tri'])) {
    $tri = $_GET ['tri'];
    $ordreAsc = true;
} else {
    if (isset ($_GET ['triDesc'])) {
        $tri = $_GET ['triDesc'];
        $ordreAsc = false;
    } else {
        $tri = "date";
        $ordreAsc = false;
    }
}

// Chargement de la liste des songbooks
$resultat = chercheSongbooks("nom", "%", $tri, $ordreAsc);
$numligne = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] >= 2) {
    $fichiersDuSongbook .= "<BR>" . Ancre("$songbookForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouveau songbook");
}
// //////////////////////////////////////////////////////////////////////ADMIN

$fichiersDuSongbook .= TblDebut();
$fichiersDuSongbook .= TblDebutLigne() . TblCellule("  Tri  ");
$fichiersDuSongbook .= titreColonne("Nom", "nom");
$fichiersDuSongbook .= titreColonne("Description", "description");
$fichiersDuSongbook .= titreColonne("Date", "date");
$fichiersDuSongbook .= titreColonne("Vues", "hits");
$fichiersDuSongbook .= titreColonne("", "");
$fichiersDuSongbook .= titreColonne("", "");
$fichiersDuSongbook .= TblFinLigne();

while ($ligne = $resultat->fetch_row()) {
    $numligne++;
    $fichiersDuSongbook .= TblDebutLigne();
    // Songbook : 	[0]id 	[1]nom 	[2]description 	[3]date  	[4]image 	[5]hits 	[6]idUser
    if ($ligne [4])
        // //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
    {
        if ($_SESSION ['privilege'] >= 2) {
            $fichiersDuSongbook .= TblCellule(Ancre($songbookForm . "?id=$ligne[0]", Image(($cheminImagesSongbook . $ligne[0] . "/" . urlencode(imageTableId("songbook", $ligne[0]))), 32, 32, "couverture")));
        } // image
        else {
            $fichiersDuSongbook .= TblCellule(Image(($cheminImagesSongbook . $ligne[0]) . "/" . urlencode(imageTableId("songbook", $ligne[0])), 32, 32));
        }
    } // image
    else {
        $fichiersDuSongbook .= TblCellule(Ancre($songbookForm . "?id=$ligne[0]", "voir"));
    }

    $fichiersDuSongbook .= TblCellule(Ancre($songbookVoir . "?id=$ligne[0]", entreBalise($ligne [1], "H2"))); // Nom

    $fichiersDuSongbook .= TblCellule("  " . $ligne [2]); // description
    $fichiersDuSongbook .= TblCellule(" " . dateMysqlVersTexte($ligne [3])); // date
    $fichiersDuSongbook .= TblCellule("  -  " . $ligne [5] . " hit(s)"); // hits

    // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
    if ($_SESSION ['privilege'] >= 2) {
        $fichiersDuSongbook .= TblCellule(boutonSuppression($songbookGet . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages));
        $fichiersDuSongbook .= TblCellule(Ancre($songbookGet . "?DUP=$ligne[0]", "dupliquer"));
        // //////////////////////////////////////////////////////////////////////ADMIN

        $fichiersDuSongbook .= TblFinLigne();
    }
}
$fichiersDuSongbook .= TblFin();

// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] >= 2) {
    $fichiersDuSongbook .= "<BR>" . Ancre("?page=$songbookForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouvel songbook");
}
// //////////////////////////////////////////////////////////////////////ADMIN
$fichiersDuSongbook .= envoieFooter();
echo $fichiersDuSongbook;

function titreColonne($libelle, $nomRubrique): string
{
    return TblCellule(Ancre("?tri=$nomRubrique", "<span class='glyphicon glyphicon-chevron-up'> </span>")
        . "  $libelle   " . Ancre("?triDesc=$nomRubrique", "  <span class='glyphicon glyphicon-chevron-down'></span> "));
}
