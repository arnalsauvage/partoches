<?php
include_once ("lib/utilssi.php");
include ("menu.php");

$utilisateurForm = "utilisateur_form.php";
$utilisateurGet = "utilisateur_get.php";
$utilisateurVoir = "utilisateur_voir.php";

$table = "utilisateur";
$retour = "";
$retour .= entreBalise ( "Utilisateurs", "H1" );
$retour .= TblDebut ( 0 );

// Chargement de la liste des utilisateurs
$marequete = "select * from $table ORDER BY dateDernierLogin DESC";
$resultat = $_SESSION ['mysql']->query ( $marequete );
if (! $resultat)
	die ( "Problème utilisateursListe #1 : " . $_SESSION ['mysql']->error );
$numligne = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] > 2)
	$retour .= "<BR>" . Ancre ( "$utilisateurForm", Image ( $cheminImages . $iconeCreer, 32, 32 ) . "Créer un nouvel utilisateur" );
// //////////////////////////////////////////////////////////////////////ADMIN

$retour .= Image ( $iconeAttention, "100%", 1, 1 );

while ( $ligne = $resultat->fetch_row () ) {
	$numligne ++;
	$retour .= TblDebutLigne ();
	
	if ($ligne [5])
		// //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
		if (($_SESSION ['privilege'] > 2) || $_SESSION ['user'] == $ligne [1])
			$retour .= TblCellule ( Ancre ( $utilisateurForm . "?id=$ligne[0]", Image ( ($cheminImages . $ligne [5]), 32, 32 ) ) ); // image
			                                                                                                                        // //////////////////////////////////////////////////////////////////////ADMIN
		else
			$retour .= TblCellule ( Image ( ($cheminImages . $ligne [5]), 32, 32 ) ); // image
	else
		$retour .= TblCellule ( Ancre ( $utilisateurForm . "?id=$ligne[0]", "voir" ) );
	
	$retour .= TblCellule ( entreBalise ( $ligne [1], "H2" ) ); // Login
	
	$retour .= TblCellule ( $ligne [3] . " " . $ligne [4] ); // nom prenom
	$retour .= TblCellule ( statut ( $ligne [11] ) );
	$retour .= TblCellule ( dateMysqlVersTexte ( $ligne [9], 0 ) );
	$retour .= TblCellule ( " &nbsp &nbsp &nbsp &nbsp" ); // un petit blanc
	$retour .= TblCellule ( " " . $ligne [10] . " logins" ); // nbreLogins
	                                                         
	// //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
	if ($_SESSION ['privilege'] > 2) {
		$retour .= TblCellule ( boutonSuppression ( $utilisateurGet . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages ) );
		// //////////////////////////////////////////////////////////////////////ADMIN
		
		$retour .= TblFinLigne ();
	}
}
$retour .= TblFin ();

$retour .= Image ( $iconeAttention, "100%", 1, 1 );
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] > 2)
	$retour .= "<BR>" . Ancre ( "?page=$utilisateurForm", Image ( $cheminImages . $iconeCreer, 32, 32 ) . "Créer un nouvel utilisateur" );
// //////////////////////////////////////////////////////////////////////ADMIN
$retour .= envoieFooter ( "Bienvenue chez nous !" );
echo $retour;
?>