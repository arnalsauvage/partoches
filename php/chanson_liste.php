<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("chanson.php");
// TODO : ajouter un bouton "ajouter un doc pour cette chanson"
// TODO : ajouter la date de publication et le tri par date de pub
$chansonForm = "chanson_form.php";
$chansonPost = "chanson_post.php";
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
$retour .= TblDebutLigne () . TblCellule ( "  Tri  " ) . TblCellule ( Ancre ( "?tri=nom", "  Nom  " ) ) . TblCellule ( Ancre ( "?tri=interprete", "  Interprète  " ) ) . TblCellule ( Ancre ( "?tri=annee", "  Année  " ) );
$retour .= TblCellule ( Ancre ( "?tri=tempo", "  Tempo  " ) ) . TblCellule ( Ancre ( "?tri=mesuree", "  Mesure  " ) ) . TblCellule ( Ancre ( "?tri=pulsation", "  Pulsation  " ) );
$retour .= TblCellule ( Ancre ( "?tri=tonalite", "  Tonalité  " ) ) . TblCellule ( Ancre ( "?tri=date_publication", "  Date Pub.  " ) ) . TblCellule ( Ancre ( "?tri=idauteur", "  Publié par  " ) ). TblCellule ( Ancre ( "?tri=hits", "  Vues  " ) ). TblFinLigne ();
while ( $ligne = $resultat->fetch_row()) {
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
	$retour .= TblCellule ( $ligne [4] ); // tempo
	$retour .= TblCellule ( $ligne [5] ); // mesure
	$retour .= TblCellule ( $ligne [6] ); // pulsation
	$retour .= TblCellule ( $ligne [10] ); // tonalité
	$retour .= TblCellule (  dateMysqlVersTexte ( $ligne[7])); // Date Pub
	$nomAuteur = chercheUtilisateur($ligne [8]);
	$nomAuteur = $nomAuteur[3];
	$retour .= TblCellule ( $nomAuteur); // auteur
	$retour .= TblCellule ( $ligne [9] ); // hits
	                                      
	// //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
	if ($_SESSION ['privilege'] > 1) {
		$retour .= TblCellule ( boutonSuppression ( $chansonPost . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages ) );
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