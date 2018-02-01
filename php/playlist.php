<?php
include_once ("lib/utilssi.php");
include_once "lib/configMysql.php";
//include_once ("lienDocplaylist.php");

$playlistForm = "playlist_form.php";
$playlistGet = "playlist_get.php";
$playlistVoir = "playlist_voir.php";
$playlistListe = "playlist_liste.php";
$cheminImagesplaylist = "../data/playlists/";

// Fonctions de gestion du playlist

// Cherche les playlists correspondant à un critère
function cherchePlaylists($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true) {
	$maRequete = "SELECT * FROM playlist WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
	if ($bTriAscendant == false)
		$maRequete .= " DESC";
		else
			$maRequete .= " ASC";
			// echo "ma requete : " . $maRequete;
			$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème cherchePlaylist #1 : " . $_SESSION ['mysql']->error  );
			return $result;
}

// Cherche une playlist et le renvoie s'il existe
function cherchePlaylist($id) {
	$maRequete = "SELECT * FROM playlist WHERE playlist.id = '$id'";
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème chercheplaylist #1 : " . $_SESSION ['mysql']->error  );
	// renvoie la ligne sélectionnée : id, nom, description, date , image, hits
	if (($ligne = $result->fetch_row()))
		return ($ligne);
		else
			return (0);
}

// Cherche une playlist et la renvoie si elle existe
function cherchePlaylistParLeNom($nom) {
	$maRequete = "SELECT * FROM playlist WHERE playlist.nom = '$nom'";
	$result =  $_SESSION ['mysql']->query ( $maRequete )or die ( "Problème cherchePlaylistParLeNom #1 : " . $_SESSION ['mysql']->error  );
	// renvoie la lisgne sélectionnée : id, nom, description, date , image, hits
	if (($ligne = $result->fetch_row()))
		return ($ligne);
		else
			return (0);
}

// Crée une playlist
function creePlaylist($nom, $description, $date, $image, $hits) {
	$date = convertitDateJJMMAAAA($date);
	$idUSer = $_SESSION ['id'];
	$maRequete = "INSERT INTO playlist VALUES (NULL, '$nom', '$description', '$date', '$image', '$hits', '$idUSer')";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème creePlaylist#1 : " . $_SESSION ['mysql']->error  );
}

// Modifie en base la playlist
function modifiePlaylist($id, $nom, $description, $date, $image, $hits){
	$date = convertitDateJJMMAAAA($date);
	$maRequete = "UPDATE  playlist
	SET nom = '$nom', description = '$description', date = '$date' , image = '$image', hits = '$hits'
	WHERE id='$id'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème modifiePlaylist #1 : " . $_SESSION ['mysql']->error  );
}

// Cette fonction supprime une playlist si il existe
function supprimePlaylist($idplaylist) {
	// On supprime les enregistrements dans playlist
	$maRequete = "DELETE FROM playlist
	WHERE id='$idplaylist'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème #1 dans supprimePlaylist : " . $_SESSION ['mysql']->error  );
	supprimeliensDocplaylistDuplaylist($idplaylist);
}

// Cette fonction modifie ou crée une playlist si besoin
function creemodifiePlaylist($id, $nom, $description, $date, $image, $hits){
	if (chercheplaylist ( $id ))
		modifiePlaylist ( $id, $nom, $description, $date, $image, $hits);
		else
			creePlaylist ( $nom, $description, $date, $image, $hits);
}

// Cette fonction renvoie l'image vignette d'une playlist
function imagePlaylist($idplaylist)
{

	$maRequete = "SELECT * FROM document WHERE document.idTable = '$idplaylist' AND document.nomTable='playlist' ";
	$maRequete .= " AND ( document.nom LIKE '%.png' OR document.nom LIKE '%.jpg')";
	$result = $_SESSION ['mysql']->query($maRequete) or die ("Problème imagePlaylist #1 : " . $_SESSION ['mysql']->error);
	if (empty($result)) {
		return ("");
	}

	// Choisit une vignette au hasard parmi les images
	// renvoie la ligne sélectionnée : id, nom, description, date , image, hits
	if (($ligne = $result->fetch_row())) {
		$nom = composeNomVersion($ligne[1], $ligne[4]);
		return ($nom);
	} else
		return ("");
}
// Cette fonction renvoie une chaine de description de la playlist
function infosPlaylist($id) {
	$enr = chercheplaylist ( $id );
	// id_journee id_joueur poste statut
	$retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Description : " . $enr [2] . " Date : " . $enr [3] . " image : " . $enr [4] . " Hits : " . $enr [5];
	return $retour . "<BR>\n";
}

// Cette fonction renvoie la liste des fichiers attachés à la playlist
function fichiersPlaylist($id) {
	$enr = chercheplaylist ( $id );
	$retour = []; // repertoire, nom, extension
	$repertoire = "../data/playlists/";
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
function testePlaylist() {
	creePlaylist ( "playlist #1", "Chansons d été", "31/07/2017","cover.jpg", 0 );
	$id = cherchePlaylistParLeNom ( "playlist #1" );
	$id = $id [0];
	echo infosPlaylist ( $id );
	
	$enr = chercheplaylist ( $id );
	$id = $id [0];
	echo infosPlaylist ( $id );
	
	creePlaylist ( "playlist #2", "Chansons d automne", "30/11/2017","cover.jpg", 0 );
	$id = cherchePlaylistParLeNom ( "playlist #2" );
	$id = $id [0];
	echo infosPlaylist ( $id );
	
	creemodifiePlaylist ( $id , "playlist #2", "Chansons d automne !", "28/11/2017","cover.jpg", 0 );
	$id = cherchePlaylistParLeNom ( "playlist #2" );
	$id = $id [0];
	echo infosPlaylist ( $id );
	
	$id = cherchePlaylistParLeNom ( "playlist #2" );
	$id = $id [0];
	// supprimePlaylist($id);
	echo infosPlaylist ( $id );
	
	$id = cherchePlaylistParLeNom ( "playlist #1" );
	supprimePlaylist($id[0]);
	$id = cherchePlaylistParLeNom ( "playlist #2" );
	supprimePlaylist($id[0]);
	
}

// testePlaylist ();
// TODO ajouter des logs pur tracer l'activité du site
?>