<?php
include_once ("lib/utilssi.php");
include_once "lib/configMysql.php";
include_once("lienDocSongbook.php");

// Fonctions de gestion de la document

// TODO : faire une fonction de contrôle des documents sur disque :
// Documents sur disque non vus en BDD & documents BDD non vus sur disque

// TODO : contrôler que les id / Tables fournis existent bien

// Cherche les documents correspondant à un critère
function chercheDocuments($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true) {
	$maRequete = "SELECT * FROM document WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
	if ($bTriAscendant == false)
		$maRequete .= " DESC";
	else
		$maRequete .= " ASC";
	// echo "ma requete : " . $maRequete;
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème cherchedocument #1 : " . $_SESSION ['mysql']->error );
	return $result;
}

// Cherche un document et le renvoie s'il existe
function chercheDocument($id) {
	$maRequete = "SELECT * FROM document WHERE document.id = '$id'";
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème cherchedocument #1 : " . $_SESSION ['mysql']->error );
	// renvoie la lisgne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
	if (($ligne = $result->fetch_row ()))
		return ($ligne);
	else
		return (0);
}

// Cherche un document et le renvoie s'il existe
function chercheDocumentNomTableId($nom, $table, $id) {
	$maRequete = "SELECT * FROM document WHERE document.nom = '$nom' AND document.idTable = '$id' AND document.nomTable = '$table'";
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème cherchedocument #1 : " . $_SESSION ['mysql']->error );
	// renvoie la lisgne sélectionnée : id, nom, interprète, année
	if (($ligne = $result->fetch_row ()))
		return ($ligne);
	else
		return (0);
}

// Cherche les documents d'une entree d'une table et les renvoie s'ils existent
function chercheDocumentsTableId($table, $id) {
	$maRequete = "SELECT * FROM document WHERE document.idTable = '$id' AND document.nomTable = '$table'";
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème chercheDocumentsTableId #1 : " . $_SESSION ['mysql']->error );
	return ($result);
}

function composeNomVersion($nom, $version) {
	// echo "Recherche $nom $version\n";
	
	// On cherche le rang du dernier point dans nom
	$ext = strrchr ( $nom, "." );
	$nomSec = str_replace ( $ext, "", $nom );
	$nouveauNom = $nomSec . "-v$version" . $ext;
	// echo $nouveauNom . "<br>";
	return $nouveauNom;
}

// Crée un document
function creeDocument($nom, $tailleKo, $nomTable, $idTable) {
	$date = date ( "d/m/y" );
	$date = convertitDateJJMMAAAA ( $date );
	$version = 1;

	$resultat = chercheDocumentNomTableId($nom, $nomTable, $idTable);
	if ($resultat != NULL)
		return false;
	$idUser = $_SESSION ['id'];
	$maRequete = "INSERT INTO document VALUES (NULL, '$nom', '$tailleKo', '$date', '$version', '$nomTable', '$idTable', '$idUser', '0')";
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème creedocument#1 : " . $_SESSION ['mysql']->error );
	return true;
}

// Modifie en base le document
function modifieDocument($nom, $tailleKo, $nomTable, $idTable)
{
	$date = date ( "d/m/y" );
	$date = convertitDateJJMMAAAA ( $date );
	$idUser = $_SESSION ['id'];

	$resultat = chercheDocumentNomTableId($nom, $nomTable, $idTable);
	if ($resultat == NULL)
		return false;
	else
		$version = $resultat [4] + 1;
	$maRequete = "UPDATE  document
	SET tailleKo = '$tailleKo', date = '$date', version = '$version', idUser = '$idUser'
	WHERE nom = '$nom'";
	$result = $_SESSION ['mysql']->query($maRequete) or die ("Problème modifiedocument #1 : " . $_SESSION ['mysql']->error . "<br>Requete : " . $maRequete);

	return true;
}

// Cette fonction supprime un document si il existe
function supprimeDocument($id) {
	// On supprime les enregistrements dans la table document
	$maRequete = "DELETE FROM document
	WHERE id='$id'";
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème #1 dans supprimedocument : " . $_SESSION ['mysql']->error );
	// On supprime également toutes les entrées Songbook lui correspondant
	supprimeliensDocSongbookDuDocument($id);
}

// Cette fonction modifie ou crée un document si besoin
function creeModifieDocument($nom, $tailleKo, $nomTable, $idTable) {
	$resultat = chercheDocumentNomTableId($nom, $nomTable, $idTable);
	if ($resultat == NULL)
		creeDocument ( $nom, $tailleKo, $nomTable, $idTable );
	else
		modifieDocument($nom, $tailleKo, $nomTable, $idTable);
	return;
}

function selectDocument($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
{
	$retour = "<select name='documentJoint'>\n";
	
	// Ajouter des options
	$lignes = chercheDocuments ( $critere, $valeur, $critereTri, $bTriAscendant );
	while ( $ligne = $lignes->fetch_row () ) {
		$retour .= "<option  value = '" . $ligne [0] . "'> " . htmlEntities ( $ligne [1] ) . "</option>\n";
	}
	$retour .= "</select>\n";
	return $retour;
}

// Cette fonction renvoie une chaine de description de la document pour test
function infosDocument($nom) {
	$resultat = chercheDocuments ( "nom", $nom );
	$resultat = $resultat->fetch_row ();
	if ($resultat != NULL) {
		$enr = $resultat;
		// id_journee id_joueur poste statut
		$retour = "id : " . $enr [0] . " nom : " . $enr [1] . " taille(ko) : " . $enr [2] . " Date : " . $enr [3];
		$retour .= " Version : " . $enr [4] . "nomTable : " . $enr [5] . " idTable : " . $enr [6] . " hits : " . $enr[8];
	} else
		$retour = "$nom pas trouvé...";
	return $retour . "<BR>\n";
}

// Fonction de test
function testeDocument() {
	// if (creeDocument ( "enfant.pdf", "128", "chanson", 2 ) == FALSE)
	// echo "erreur de création, le document existe déjà en base";
	// else {
	// echo "document enfant créé";
	// }
	// echo infosDocument ( "enfant.pdf" );
	
	// creeModifieDocument ( "GrilleSaladeDeFruits.pdf", "179124", "chanson", 25 );
	// creeModifieDocument ( "RiffSaladeDeFruits ukulele.pdf", "34900", "chanson", 25 );
	// creeModifieDocument ( "SaladeDeFruits.pdf", "475024", "chanson", 4 );
	// creeModifieDocument ( "SaladeDeFruits-BOURVIL.png.pdf", "599551", "chanson", 4 );
	
	// creeModifieDocument ( "parent.pdf", "256", "chanson", 5 );
	echo infosDocument ( "enfant.pdf" );
}

// testeDocument ();
// TODO ajouter des logs pur tracer l'activité du site
?>