<?php
include_once ("lib/utilssi.php");
include ("menu.php");
include ("chanson.php");

$chansonForm = "chanson_form.php";
$chansonGet = "chanson_get.php";
$chansonVoir = "chanson_voir.php";
$table = "chanson";
$retour = "";

$retour .= entreBalise ( "Chansons", "H1" );

// Gestion du paramètre de tri
if (isset ( $_GET ['tri'] ))
	$tri = $_GET ['tri'];
else
	$tri = "nom";

// Chargement de la liste des chansons
$resultat = chercheChansons ( "nom", "%", $tri, true );
$numligne = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] > 1)
	$retour .= "<BR>" . Ancre ( "$chansonForm", Image ( $cheminImages . $iconeCreer, 32, 32 ) . "Ajouter une chanson" );
// //////////////////////////////////////////////////////////////////////ADMIN

$retour .= Image ( $iconeAttention, "100%", 1, 1 );

$retour .= TblDebut ( 0 );
$retour .= TblDebutLigne () . TblCellule ( "Tri" ) . TblCellule ( Ancre ( "?tri=nom", "Nom" ) ) . TblCellule ( Ancre ( "?tri=interprete", "Interprète" ) ) . TblCellule ( Ancre ( "?tri=annee", "Année" ) ) . TblFinLigne ();
while ( $ligne = lignesuivante ( $resultat ) ) {
	$numligne ++;
	$retour .= TblDebutLigne ();
	
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
		$retour .= TblCellule ( Ancre ( "$chansonForm?id=$ligne[0]", Image ( $cheminImages . $iconeEdit, 32, 32 ) ) ); // Nom));
		                                                                                                               // //////////////////////////////////////////////////////////////////////ADMIN
	
	$retour .= TblCellule ( Ancre ( "$chansonVoir?id=$ligne[0]", entreBalise ( $ligne [1], "H3" ) ) ); // Nom
	$retour .= TblCellule ( $ligne [2] ); // interprete
	$retour .= TblCellule ( $ligne [3] ); // annee
	                                      
	// //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
	if ($_SESSION ['privilege'] > 1) {
		$retour .= TblCellule ( boutonSuppression ( $chansonGet . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages ) );
		// //////////////////////////////////////////////////////////////////////ADMIN
		$retour .= TblFinLigne ();
	}
}
$retour .= TblFin ();

$retour .= Image ( $iconeAttention, "100%", 1, 1 );
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] > 1)
	$retour .= "<BR>" . Ancre ( "$chansonForm", Image ( $cheminImages . $iconeCreer, 32, 32 ) . "Ajouter une chanson" );
// //////////////////////////////////////////////////////////////////////ADMIN
$retour .= envoieFooter ( "Bienvenue chez nous !" );
echo $retour;
?>