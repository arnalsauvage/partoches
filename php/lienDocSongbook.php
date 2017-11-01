<?php
include_once ("lib/utilssi.php");
include_once "lib/configMysql.php";

// Fonctions de gestion de la lienDocSongbook

// Cherche les lienDocSongbooks correspondant à un critère
function chercheLiensDocSongbook($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true) {
	$maRequete = "SELECT * FROM liendocsongbook WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
	if ($bTriAscendant == false)
		$maRequete .= " DESC";
	else
		$maRequete .= " ASC";
	// echo "ma requete : " . $maRequete;
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème cherchelienDocSongbook #1 : " .  $_SESSION ['mysql']->error  );
	return $result;
}

// Cherche un lienDocSongbook et le renvoie s'il existe
function chercheLienDocSongbook($id) {
	$maRequete = "SELECT * FROM liendocsongbook WHERE id = '$id'";
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème cherchelienDocSongbook #1 : " .  $_SESSION ['mysql']->error  );
	// renvoie la lisgne sélectionnée : id, nom, interprète, année
	if (($ligne = $result->fetch_row()))
		return ($ligne);
	else
		return (0);
}

// Crée un lienDocSongbook
function creelienDocSongbook($idDocument, $idSongbook) {
	$resultats = chercheLiensDocSongbook ("idSongbook",$idSongbook, "id");
	$nb = $_SESSION ['mysql']->affected_rows + 1;
	$maRequete = "INSERT INTO liendocsongbook VALUES (NULL, '$idDocument', '$idSongbook', '$nb')";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème creelienDocSongbook#1 : " .  $_SESSION ['mysql']->error  );
}

// Modifie en base la lienDocSongbook
function modifielienDocSongbook($id, $idDocument, $idSongbook, $ordre) {
	$maRequete = "UPDATE  liendocsongbook
	SET idDocument = '$idDocument', idSongbook = '$idSongbook', ordre = '$ordre'
	WHERE id='$id'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème modifielienDocSongbook #1 : " .  $_SESSION ['mysql']->error  );
}

// Cette fonction supprime un lienDocSongbook si elle existe
function supprimelienDocSongbook($idlienDocSongbook) {
	// On supprime les enregistrements dans lienDocSongbook
	$maRequete = "DELETE FROM liendocsongbook
	WHERE id='$idlienDocSongbook'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème #1 dans supprimelienDocSongbook : " .  $_SESSION ['mysql']->error  );
}

// Cette fonction supprime un lienDocSongbook si elle existe
function supprimelienIdDocIdSongbook($idDoc, $idSongbook) {
	// On supprime les enregistrements dans lienDocSongbook
	$maRequete = "DELETE FROM liendocsongbook
	WHERE idDocument = '$idDoc' AND idSongBook = '$idSongbook'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème #1 dans supprimelienDocSongbook : " .  $_SESSION ['mysql']->error  );
}


// TODO faire l'équivalent pour la suppression de SongBook
// Cette fonction supprime un lienDocSongbook si elle existe
function supprimeliensDocSongbookDuDocument($idDocument) {
	// On supprime les enregistrements dans lienDocSongbook
	$maRequete = "DELETE FROM liendocsongbook
	WHERE idDocument ='$idDocument'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème #1 dans supprimeliensDocSongbookDuDocument : " .  $_SESSION ['mysql']->error  );
}


// Cette fonction modifie ou crée un lienDocSongbook si besoin
function creeModifielienDocSongbook($id, $idDocument, $idSongbook, $ordre = 0) {
	if (chercheLienDocSongbook ( $id ))
		modifielienDocSongbook ( $id, $idDocument, $idSongbook, $ordre);
	else
		creelienDocSongbook ( $idDocument, $idSongbook);
}

// Cette fonction renvoie une chaine de description de la lienDocSongbook
function infoslienDocSongbook($id) {
	$enr = chercheLienDocSongbook ( $id );
	// id_journee id_joueur poste statut
	$retour = "Id : " . $enr [0] . " idDocument : " . $enr [1] . " IdSongbook : " . $enr [2] . " Ordre : " . $enr [3];
	return $retour . "<BR>\n";
}


// Fonction de test
function testelienDocSongbook() {
	creelienDocSongbook ( 1, 1 );
	creelienDocSongbook ( 2, 1 );
//	creeModifielienDocSongbook ( 2, 1,1,3 );
	creelienDocSongbook ( 3, 1);
//	echo infoslienDocSongbook ( $id );
}

 // testelienDocSongbook ();
// TODO ajouter des logs pur tracer l'activité du site
?>