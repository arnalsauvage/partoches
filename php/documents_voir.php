<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("songbook.php");
include_once ("lienDocSongbook.php");
include_once ("document.php");
$table = "songbook";
$sortie = "";
$monImage = "";

$sortie .= "<h2>Documents publiés pour des chansons</h2> \n"; // Titre
                                                              
// Gestion du paramètre de tri
if (isset ( $_GET ['tri'] )) {
	$tri = $_GET ['tri'];
	$ordreAsc = true;
} else {
	if (isset ( $_GET ['triDesc'] )) {
		$tri = $_GET ['triDesc'];
		$ordreAsc = false;
	} else {
		$tri = "date";
		$ordreAsc = false;
	}
}

$lignes = chercheDocuments ( "nomTable", "chanson", $tri, $ordreAsc );

// On charge le tableau des utilisateurs
$tabUsers = portraitDesUtilisateurs ();

$sortie .= "<table> \n";
$sortie .= "<tr>";
$sortie .= titreColonne ( "Publicateur", "idUSer" );
$sortie .= "<td></td>\n";
$sortie .= "<td></td>\n";

$sortie .= titreColonne ( "Date", "date" );
$sortie .= titreColonne ( "Nbre vues", "hits" );
$sortie .= "</tr>\n";

$listeDocs = "";
$vignetteChanson = "";
while ( $ligneDoc = $lignes->fetch_row () ) {
	$sortie .= "<tr> \n";
	$fichierCourt = composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
	$fichier = "../data/chansons/" . $ligneDoc [6] . "/" . composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
	$extension = substr ( strrchr ( $ligneDoc [1], '.' ), 1 );
	$icone = Image ( "../images/icones/" . $extension . ".png", 32, 32, "icone" );
	
	if (! file_exists ( "../images/icones/" . $extension . ".png" ))
		$icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );
	$precedenteVignette = $vignetteChanson;
	$vignettePublicateur = Image ( "../images" . $tabUsers [$ligneDoc [7]] [1], 48, 48, $tabUsers [$ligneDoc [7]] [0] );
	$sortie .= "<td> $vignettePublicateur </td>\n";
	$vignetteChanson = Image ( "../data/chansons/" . $ligneDoc [6] . "/" . imageTableId ( "chanson", $ligneDoc [6] ), 128, 128, "chanson" );
	if ($precedenteVignette != $vignetteChanson) {
		$sortie .= "<td> $vignetteChanson </td>\n";
	} else
		$sortie .= "<td>  </td>\n ";
	$sortie .= "<td> $icone <a href= 'getdoc.php?doc=" . $ligneDoc [0] . "' target='_blank'> " . $fichierCourt . "</a></td> \n";
	$sortie .= "<td>" . intval ( $ligneDoc [2] / 1024 ) . " ko -  publié le " . dateMysqlVersTexte ( $ligneDoc [3] ) . " </td>";
	$sortie .= "<td>" . $ligneDoc [8] . "</td></tr>\n";
}

$sortie .= "</table>";
$sortie .= envoieFooter ( "Bienvenue chez nous !" );
echo $sortie;
function titreColonne($libelle, $nomRubrique) {
	$chaine = TblCellule ( Ancre ( "?tri=$nomRubrique", "<span class='glyphicon glyphicon-chevron-up'> " ) . "  $libelle   " . Ancre ( "?triDesc=$nomRubrique", "  <span class='glyphicon glyphicon-chevron-down'> " ) );
	return $chaine;
}
?>