<?php

include_once "lib/configMysql.php";

// Classe chiffrement, prise sur http://www.finalclap.com/tuto/php-cryptage-aes-chiffrement-85/
class Chiffrement{
	private static $cipher  = MCRYPT_RIJNDAEL_128;          // Algorithme utilisé pour le cryptage des blocs
	private static $key     = 'La blanquette est bonne';    // Clé de cryptage
	private static $mode    = 'cbc';                        // Mode opératoire (traitement des blocs)
 
	public static function crypt($data){
		$keyHash = md5(self::$key);
		$key = substr($keyHash, 0,   mcrypt_get_key_size(self::$cipher, self::$mode) );
		$iv  = substr($keyHash, 0, mcrypt_get_block_size(self::$cipher, self::$mode) );
 
		$data = mcrypt_encrypt(self::$cipher, $key, $data, self::$mode, $iv);
		return base64_encode($data);
	}
 
	public static function decrypt($data){
		$keyHash = md5(self::$key);
		$key = substr($keyHash, 0,   mcrypt_get_key_size(self::$cipher, self::$mode) );
		$iv  = substr($keyHash, 0, mcrypt_get_block_size(self::$cipher, self::$mode) );
 
		$data = base64_decode($data);
		$data = mcrypt_decrypt(self::$cipher, $key, $data, self::$mode, $iv);
		return rtrim($data);
	}
}

// Fonctions de gestion de l'utilisateur

// Cherche un utilisateur et le renvoie s'il existe
function chercheUtilisateur($id){
	$maRequete = "SELECT * FROM utilisateur WHERE utilisateur.id = '$id'";
	$result = mysql_query ( $maRequete ) or die ( "Problème chercheutilisateur #1 : " . mysql_error () );
	// renvoie la lisgne sélectionnée : id, nom, taille, date
	if(($ligne = mysql_fetch_array ( $result )))
	return ($ligne);
	else
	return (0);
}

// Cherche un utilisateur et le renvoie s'il existe
function chercheUtilisateurParLeLogin($login){
	$maRequete = "SELECT * FROM utilisateur WHERE utilisateur.login = '$login'";
	$result = mysql_query ( $maRequete ) or die ( "Problème chercheutilisateurParLeNom #1 : " . mysql_error () );
	// renvoie la lisgne sélectionnée : id, nom, taille, date
	if(($ligne = mysql_fetch_array ( $result )))
	return ($ligne);
	else
	return (0);
}

// Crée un utilisateur
function creeUtilisateur($login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $dateDernierLogin, $nbreLogins ){
	// $date = date du jour
	$date = convertitDateJJMMAAAA( $dateDernierLogin );
	// Crypter le mdp
	$crypt   = Chiffrement::crypt($mdp);
	// vérifier l'email
	$maRequete = "INSERT INTO  utilisateur VALUES (NULL, '$login', '$crypt', '$prenom', '$nom', '$image', '$site', '$email', '$signature', '$date', 0)";
	$result = mysql_query ( $maRequete ) or die ( "Problème creeUtilisateur#1 : " . mysql_error () );
}

// Modifie en base le utilisateur
function modifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $dateDernierLogin, $nbreLogins ){
	// On convertit la date au format mysql
	$date = convertitDateJJMMAAAA ( $dateDernierLogin );
	// Crypter le mdp
	$crypt   = Chiffrement::crypt($mdp);
	
	$maRequete = "UPDATE  utilisateur
	SET id = '$id', login = '$login', mdp = '$crypt',
	prenom = '$prenom' ,nom = '$nom', image = '$image', site = '$site', email = '$email',
	signature = '$signature', date = '$date', nbreLogins = '$nbreLogins'
	WHERE id='$id'";
	$result = mysql_query ( $maRequete ) or die ( "Problème modifieutilisateur #1 : " . mysql_error () );
}

// Cette fonction supprime un utilisateur si il existe
function supprimeUtilisateur($idUtilisateur){
	
	// On supprime les enregistrements dans utilisateur
	$maRequete = "DELETE FROM  utilisateur
	WHERE id='$idUtilisateur'";
	$result = mysql_query ( $maRequete ) or die ( "Problème #1 dans supprimeUtilisateur : " . mysql_error () );
}

// Cette fonction modifie ou crée un utilisateur si besoin
function creeModifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $dateDernierLogin, $nbreLogins ){
	if(chercheutilisateur ( $id ))
	modifieUtilisateur ( $id, $id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $dateDernierLogin, $nbreLogins );
	else
	creeUtilisateur ( $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $dateDernierLogin, $nbreLogins );
}

// Cette fonction renvoie une chaine de description de l'utilisateur
function infos($id){
	$enr = chercheUtilisateur ( $id );
	// id_journee id_joueur poste statut
	$retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Desc : " . $enr [2] . " date : " . $enr [3] . " image : " . $enr [4] . " hits : " . $enr [5];
	return $retour;
}

// Fonction de test
function testeUtilisateur(){
	creeUtilisateur ("test", "kazoo", "Alain", "Minc", "test.png", "http://samere", "truc@bidule.com", "bla bla bla", "25/10/2017", 5 );
	$id = chercheUtilisateurParLeLogin ( "utilisateur 1" );
	$id = $id [0];
	$enr = chercheUtilisateur ( $id );
	echo infos ( $id );
	creeUtilisateur ("test2", "kazoo2", "Alain", "Minc", "test.png", "http://samere", "truc@bidule.com", "bla bla bla", "25/10/2017", 5 );
	creeModifieUtilisateur ($id, "test3", "kazoo3", "Alainx", "Mincx", "tesxt.png", "http://samere.org", "truc@bidule.com", "bla bla bla", "25/10/2017", 5 );
	
	$id = chercheUtilisateurParLeLogin ( "test" );
	supprimeUtilisateur($id[0]);
	
	$id = chercheUtilisateurParLeLogin ( "test2" );
	supprimeUtilisateur($id[0]);
	$id = chercheUtilisateurParLeLogin ( "test3" );
	//	supprimeUtilisateur($id[0]);
}

function login_utilisateur($login, $mdp, $idconnect){
	$donnee = chercheUtilisateurParLeLogin ( $login );
	$crypt   = Chiffrement::crypt($mdp);

	if($crypt==$donnee[2]){
		$date = dateDuJourMysql();
		$nbLogins = $donnee[10] + 1;
		$maRequete = "UPDATE  utilisateur 
		SET  dateDernierLogin = '$date', nbreLogins = '$nbLogins' 
		WHERE id='".$donnee[0]."'";
		$resultat = ExecRequete ( $maRequete, $idconnect);
	
		return true;
	}
	else
	return false;
}

function statut($privilege){
	switch($privilege){
		case 0 : return "invité";
		case 1 : return "membre";
		case 2 : return "éditeur";
		case 3 : return "administrateur";
	}
}
//testeUtilisateur ();

?>
