<?php
include_once ("lib/utilssi.php");
include ("menu.php");
include ("songbook.php");

$songbookForm = "songbook_form.php";
$songbookGet = "songbook_get.php";
$songbookVoir = "songbook_voir.php";
$cheminImagesSongbook = "../images/songbook/";
$table = "songbook";
$retour = "";
$retour .= entreBalise ( "Songbooks", "H1" );
$retour .= TblDebut ( 0 );

// Gestion du paramètre de tri
if (isset ( $_GET ['tri'] ))
	$tri = $_GET ['tri'];
else
	$tri = "nom";

// Chargement de la liste des songbooks
$resultat = chercheSongbooks ( "nom", "%", $tri, true );
$numligne = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] > 2)
	$retour .= "<BR>" . Ancre ( "$songbookForm", Image ( $cheminImagesSongbook . $iconeCreer, 32, 32 ) . "Créer un nouvel songbook" );
// //////////////////////////////////////////////////////////////////////ADMIN

$retour .= Image ( $iconeAttention, "100%", 1, 1 );
$retour .= TblDebut ( 0 );
$retour .= TblDebutLigne () . TblCellule ( "Tri" ) . TblCellule ( Ancre ( "?tri=nom", "Nom" ) ) . TblCellule ( Ancre ( "?tri=description", "Description" ) ) . TblCellule ( Ancre ( "?tri=date", "Date" ) ) . TblCellule ( Ancre ( "?tri=hits", "Hits" ) ) . TblFinLigne ();

while ( $ligne = $resultat->fetch_row () ) {
	$numligne ++;
	$retour .= TblDebutLigne ();
	
	if ($ligne [4])
		// //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
		if ($_SESSION ['privilege'] > 2)
			$retour .= TblCellule ( Ancre ( $songbookForm . "?id=$ligne[0]", Image ( ($cheminImagesSongbook . $ligne [4]), 32, 32, "couverture" ) ) ); // image
			                                                                                                                                  // //////////////////////////////////////////////////////////////////////ADMIN
		else
			$retour .= TblCellule ( Image ( ($cheminImagesSongbook . $ligne [4]), 32, 32 ) ); // image
	else
		$retour .= TblCellule ( Ancre ( $songbookForm . "?id=$ligne[0]", "voir" ) );
	
		$retour .= TblCellule ( Ancre ( $songbookVoir . "?id=$ligne[0]", entreBalise ( $ligne [1], "H2" ) )); // Nom
	
	$retour .= TblCellule ( "  " .$ligne [2] ); // description
	$retour .= TblCellule ( " " . dateMysqlVersTexte ( $ligne [3], 0 ) ); // date
	$retour .= TblCellule ( "    " . $ligne [5] . " hit(s)" ); // hits
	                                                        
	// //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
	if ($_SESSION ['privilege'] > 2) {
		$retour .= TblCellule ( boutonSuppression ( $songbookGet . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImagesSongbook ) );
		// //////////////////////////////////////////////////////////////////////ADMIN
		
		$retour .= TblFinLigne ();
	}
}
$retour .= TblFin ();

$retour .= Image ( $iconeAttention, "100%", 1, 1 );
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] > 2)
	$retour .= "<BR>" . Ancre ( "?page=$songbookForm", Image ( $cheminImagesSongbook . $iconeCreer, 32, 32 ) . "Créer un nouvel songbook" );
// //////////////////////////////////////////////////////////////////////ADMIN
$retour .= envoieFooter ( "Bienvenue chez nous !" );
echo $retour;
?>