<?php
include "lib/configMysql.php";
// Fonctions de gestion du songbook

// Cherche un songbook et le renvoie s'il existe
function chercheSongbook($id) {
	$maRequete = "SELECT * FROM songbook WHERE songbook.id = '$id'";
	$result = mysql_query ( $maRequete ) or die ( "Problème chercheSongbook #1 : " . mysql_error () );
	// renvoie la lisgne sélectionnée : id, nom, taille, date
	if (($ligne = mysql_fetch_array ( $result )))
		return ($ligne);
	else
		return (0);
}

// Cherche un songbook par le nom et le renvoie s'il existe
function chercheSongbookParLeNom($nom) {
	$maRequete = "SELECT * FROM songbook WHERE songbook.nom = '$nom'";
	$result = mysql_query ( $maRequete ) or die ( "Problème chercheSongbookParLeNom #1 : " . mysql_error () );
	// renvoie la lisgne sélectionnée : id, nom, taille, date
	if (($ligne = mysql_fetch_array ( $result )))
		return ($ligne);
	else
		return (0);
}

// Crée un songbook
function creeSongbook($nom, $description, $date, $image, $hits) {
	$date = convertitDateJJMMAAAA ( $date );
	$maRequete = "INSERT INTO  songbook VALUES (NULL, '$nom', '$description', '$date', '$image', '$hits')";
	$result = mysql_query ( $maRequete ) or die ( "Problème creeSongbook#1 : " . mysql_error () );
}

// Modifie en base le songbook
function modifieSongbook($id, $nom, $description, $date, $image, $hits) {
	// On convertit la date au format mysql
	$date = convertitDateJJMMAAAA ( $date );
	
	$maRequete = "UPDATE  songbook
     SET id = '$id', nom = '$nom', description = '$description', date = '$date', image = '$image', hits = '$hits'
     WHERE id='$id'";
	$result = mysql_query ( $maRequete ) or die ( "Problème modifieSongbook #1 : " . mysql_error () );
}

// Cette fonction supprime un songbook si il existe
function supprimeSongbook($idSongbook) {
	
	// On supprime les enregistrements dans Songbook
	$maRequete = "DELETE FROM  songbook
     WHERE id='$idSongbook'";
	$result = mysql_query ( $maRequete ) or die ( "Problème #1 dans supprimeSongbook : " . mysql_error () );
	// il faut également supprimer les enregistrements dans la table de liens avec les documents
	$maRequete = "DELETE FROM  lienDocSongbook
    WHERE idSongbook='$idSongbook'";
	$result = mysql_query ( $maRequete ) or die ( "Problème #2 dans supprimeSongbook : " . mysql_error () );
}

// Cette fonction modifie ou cr饠un songbook si besoin
function creeModifieSongbook($id, $nom, $description, $date, $image, $hits) {
	if (chercheSongbook ( $id ))
		modifieSongbook ( $id, $nom, $description, $date, $image, $hits );
	else
		creeSongbook ( $nom, $description, $date, $image, $hits );
}

// Cette fonction renvoie une chaine de description du Songbook
function infos($id) {
	$enr = chercheSongbook ( $id );
	// id_journee id_joueur poste statut
	$retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Desc : " . $enr [2] . " date : " . $enr [3] . " image : " . $enr [4] . " hits : " . $enr [5];
	return $retour . "<BR>\n";
}

// Fonction de test
function testeSongbook() {
	
	$ligne = chercheSongbookParLeNom ( "Songbook 1" );
	$id = $ligne [0];
	echo infos ($id);
	supprimeSongbook($id);

	$ligne = chercheSongbookParLeNom ( "Songbook 2" );
	$id = $ligne [0];
	echo infos ($id);
	supprimeSongbook($id);
	
	creeSongbook ("Songbook 1", "les chansons retro", "10/04/2017", "songbook1.png", 112 );
	echo infos ($id);
	$id = 0;
	creeModifieSongbook ( $id, "Songbook 2", "les chansons swing", "11/04/2017", "songbook2.png", 51 );
	$ligne = chercheSongbookParLeNom ( "Songbook 2" );
	$id = $ligne [0];
	echo infos ($id);
}

testeSongbook ();

?>
