<?php
include_once ("lib/utilssi.php");
include_once "lib/configMysql.php";
$songbookForm = "songbook_form.php";
$songbookGet = "songbook_get.php";
$songbookVoir = "songbook_voir.php";
$songbookListe = "songbook_liste.php";
$cheminImagesSongbook = "../data/songbooks/";

// Fonctions de gestion du songbook

// Cherche les songbooks correspondant à un critère
function chercheSongbooks($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true) {
	$maRequete = "SELECT * FROM songbook WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
	if ($bTriAscendant == false)
		$maRequete .= " DESC";
		else
			$maRequete .= " ASC";
			// echo "ma requete : " . $maRequete;
			$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème cherchesongbook #1 : " . $_SESSION ['mysql']->error  );
			return $result;
}

// Cherche un songbook et le renvoie s'il existe
function cherchesongbook($id) {
	$maRequete = "SELECT * FROM songbook WHERE songbook.id = '$id'";
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème cherchesongbook #1 : " . $_SESSION ['mysql']->error  );
	// renvoie la ligne sélectionnée : id, nom, description, date , image, hits
	if (($ligne = $result->fetch_row()))
		return ($ligne);
		else
			return (0);
}

// Cherche un songbook et la renvoie si elle existe
function cherchesongbookParLeNom($nom) {
	$maRequete = "SELECT * FROM songbook WHERE songbook.nom = '$nom'";
	$result =  $_SESSION ['mysql']->query ( $maRequete )or die ( "Problème cherchesongbookParLeNom #1 : " . $_SESSION ['mysql']->error  );
	// renvoie la lisgne sélectionnée : id, nom, description, date , image, hits
	if (($ligne = $result->fetch_row()))
		return ($ligne);
		else
			return (0);
}

// Crée un songbook
function creesongbook($nom, $description, $date, $image, $hits) {
	$date = convertitDateJJMMAAAA($date);
	$idUSer = $_SESSION ['id'];
	$maRequete = "INSERT INTO songbook VALUES (NULL, '$nom', '$description', '$date', '$image', '$hits', '$idUSer')";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème creesongbook#1 : " . $_SESSION ['mysql']->error  );
}

// Modifie en base la songbook
function modifiesongbook($id, $nom, $description, $date, $image, $hits){
	$date = convertitDateJJMMAAAA($date);
	$maRequete = "UPDATE  songbook
	SET nom = '$nom', description = '$description', date = '$date' , image = '$image', hits = '$hits'
	WHERE id='$id'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème modifiesongbook #1 : " . $_SESSION ['mysql']->error  );
}

// Cette fonction supprime un songbook si elle existe
function supprimesongbook($idsongbook) {
	// On supprime les enregistrements dans songbook
	$maRequete = "DELETE FROM songbook
	WHERE id='$idsongbook'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème #1 dans supprimesongbook : " . $_SESSION ['mysql']->error  );
}

// Cette fonction modifie ou crée un songbook si besoin
function creeModifiesongbook($id, $nom, $description, $date, $image, $hits){
	if (cherchesongbook ( $id ))
		modifiesongbook ( $id, $nom, $description, $date, $image, $hits);
		else
			creesongbook ( $nom, $description, $date, $image, $hits);
}

// Cette fonction renvoie une chaine de description de la songbook
function infossongbook($id) {
	$enr = cherchesongbook ( $id );
	// id_journee id_joueur poste statut
	$retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Description : " . $enr [2] . " Date : " . $enr [3] . " image : " . $enr [4] . " Hits : " . $enr [5];
	return $retour . "<BR>\n";
}

// Cette fonction renvoie la liste des fichiers attachés à la songbook
function fichiersSongbook($id) {
	$enr = cherchesongbook ( $id );
	$retour = []; // repertoire, nom, extension
	$repertoire = "../data/songbooks/$id/";
	if (is_dir ( $repertoire )) {
		foreach ( new DirectoryIterator ( $repertoire ) as $fileInfo ) {
			if ($fileInfo->isDot ()|| strpos($fileInfo->getFilename (),".")==0)
				continue;
				array_push($retour, [$repertoire , $fileInfo->getFilename (),$fileInfo->getextension()]);
		}
	}
	return $retour;
}
// Fonction de test
function testeSongbook() {
	creesongbook ( "Songbook #1", "Chansons d été", "31/07/2017","cover.jpg", 0 );
	$id = cherchesongbookParLeNom ( "Songbook #1" );
	$id = $id [0];
	echo infossongbook ( $id );
	
	$enr = cherchesongbook ( $id );
	$id = $id [0];
	echo infossongbook ( $id );
	
	creesongbook ( "Songbook #2", "Chansons d automne", "30/11/2017","cover.jpg", 0 );
	$id = cherchesongbookParLeNom ( "Songbook #2" );
	$id = $id [0];
	echo infossongbook ( $id );
	
	creeModifiesongbook ( $id , "Songbook #2", "Chansons d automne !", "28/11/2017","cover.jpg", 0 );
	$id = cherchesongbookParLeNom ( "Songbook #2" );
	$id = $id [0];
	echo infossongbook ( $id );
	
	$id = cherchesongbookParLeNom ( "Songbook #2" );
	$id = $id [0];
	// supprimesongbook($id);
	echo infossongbook ( $id );
	
	$id = cherchesongbookParLeNom ( "Songbook #1" );
	supprimesongbook($id[0]);
	$id = cherchesongbookParLeNom ( "Songbook #2" );
	supprimesongbook($id[0]);
	
}

// testesongbook ();
// TODO ajouter des logs pur tracer l'activité du site
?>