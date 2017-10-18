<?php
include_once "lib/utilssi.php";

// Classe chiffrement, prise sur http://www.finalclap.com/tuto/php-cryptage-aes-chiffrement-85/
class Chiffrement {
	private static $cipher = MCRYPT_RIJNDAEL_128; // Algorithme utilisé pour le cryptage des blocs
	private static $key = 'Top5, Club Ukulele Fontenay-Sous-Bois'; // Clé de cryptage
	private static $mode = 'cbc'; // Mode opératoire (traitement des blocs)
	public static function crypt($data) {
		$keyHash = md5 ( self::$key );
		$key = substr ( $keyHash, 0, mcrypt_get_key_size ( self::$cipher, self::$mode ) );
		$iv = substr ( $keyHash, 0, mcrypt_get_block_size ( self::$cipher, self::$mode ) );
		
		$data = mcrypt_encrypt ( self::$cipher, $key, $data, self::$mode, $iv );
		return base64_encode ( $data );
	}
	public static function decrypt($data) {
		$keyHash = md5 ( self::$key );
		$key = substr ( $keyHash, 0, mcrypt_get_key_size ( self::$cipher, self::$mode ) );
		$iv = substr ( $keyHash, 0, mcrypt_get_block_size ( self::$cipher, self::$mode ) );
		
		$data = base64_decode ( $data );
		$data = mcrypt_decrypt ( self::$cipher, $key, $data, self::$mode, $iv );
		return rtrim ( $data );
	}
}

// Fonctions de gestion de l'utilisateur

// Cherche un utilisateur par son identifiant et le renvoie s'il existe
function chercheUtilisateur($id) {
	$maRequete = "SELECT * FROM utilisateur WHERE utilisateur.id = '$id'";
	$result = $_SESSION ['mysql']->query ( $maRequete );
	if (! $result)
		die ( "Problème chercheutilisateur #1 : " . $_SESSION ['mysql']->error );
	// renvoie la lisgne sélectionnée : id, nom, taille, date
		if (($ligne = $result->fetch_row()))
		return ($ligne);
	else
		return (0);
}

// Cherche un utilisateur par le login et le renvoie s'il existe
function chercheUtilisateurParLeLogin($login) {
	$maRequete = "SELECT * FROM utilisateur WHERE utilisateur.login = '$login'";
	$result = $_SESSION ['mysql']->query ( $maRequete );
	if (! $result)
		die ( "Problème chercheutilisateurParLeNom #1 : " . $_SESSION ['mysql']->error );
	// renvoie la lisgne sélectionnée : id, nom, taille, date
	$ligne = $result->fetch_row ();
	if ($ligne)
		return ($ligne);
	else
		return (0);
}

// Crée un utilisateur
function creeUtilisateur($login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $privilege) {
	// Crypter le mdp
	$crypt = Chiffrement::crypt ( $mdp );
	// vérifier l'email
	$date = convertitDateJJMMAAAA ( "01/01/1970" );
	$login = $_SESSION ['mysql']->escape_string (  $login );
	$prenom = $_SESSION ['mysql']->escape_string (  $prenom );
	$nom = $_SESSION ['mysql']->escape_string (  $nom );
	$image = $_SESSION ['mysql']->escape_string (  $image );
	$site = $_SESSION ['mysql']->escape_string (  $site );
	$email = $_SESSION ['mysql']->escape_string (  $email );
	$signature = $_SESSION ['mysql']->escape_string (  $signature );
	$privilege = $_SESSION ['mysql']->escape_string (  $privilege );
	
	$maRequete = "INSERT INTO  utilisateur VALUES (NULL, '$login', '$crypt', '$prenom', '$nom', '$image', '$site', '$email', '$signature', '$date', '0', '$priviliege')";
	$result = $_SESSION ['mysql']->query ( $maRequete );
	if (! $result)
		die ( "Problème creeUtilisateur#1 : " . $_SESSION ['mysql']->error );
}

// Formate les chaînes pour enregistrement en base

// Modifie en base le utilisateur
function modifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $dateDernierLogin, $nbreLogins, $privilege) {
	// On convertit la date au format mysql
	$date = convertitDateJJMMAAAA ( $dateDernierLogin );
	// Crypter le mdp
	$crypt = Chiffrement::crypt ( $mdp );
	
	$login = $_SESSION ['mysql']->real_escape_string ($login );
	$prenom = $_SESSION ['mysql']->real_escape_string ($prenom );
	$nom = $_SESSION ['mysql']->real_escape_string (  $nom );
	$image = $_SESSION ['mysql']->real_escape_string (  $image );
	$site = $_SESSION ['mysql']->real_escape_string (  $site );
	$email =$_SESSION ['mysql']->real_escape_string (  $email );
	$signature = $_SESSION ['mysql']->real_escape_string ( $signature );
	
	$maRequete = "UPDATE  utilisateur
	SET id = '$id', login = '$login', mdp = '$crypt',
	prenom = '$prenom' ,nom = '$nom', image = '$image', site = '$site', email = '$email',
	signature = '$signature', dateDernierLogin = '$date', nbreLogins = '$nbreLogins', privilege = '$privilege'
	WHERE id='$id'";
	$result = $_SESSION ['mysql']->query ( $maRequete );
	if (! $result)
		die ( "Problème modifieUtilisateur#1 : " . $_SESSION ['mysql']->error);
}

// Cette fonction supprime un utilisateur si il existe
function supprimeUtilisateur($idUtilisateur) {
	
	// On supprime les enregistrements dans utilisateur
	$maRequete = "DELETE FROM  utilisateur
	WHERE id='$idUtilisateur'";
	$result = $_SESSION ['mysql']->query ( $maRequete );
	if (! $result)
		die ( "Problème supprimeUtilisateur#1 : " . $_SESSION ['mysql']->error );
}

// Cette fonction modifie ou crée un utilisateur si besoin
function creeModifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $dateDernierLogin, $nbreLogins, $privilege) {
	if (chercheutilisateur ( $id ))
		modifieUtilisateur ( $id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $dateDernierLogin, $nbreLogins, $privilege );
	else
		creeUtilisateur ( $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $dateDernierLogin, $nbreLogins, $privilege );
}

// Cette fonction tente de loguer un utilisateur avec le mot de passe
function login_utilisateur($login, $mdp) {
	$donnee = chercheUtilisateurParLeLogin ( $login );
	$crypt = Chiffrement::crypt ( $mdp );
	
	if ($crypt == $donnee [2]) {
		$donnee [9] = date ("d/m/y");
		$donnee [10] = $donnee [10] + 1;
		echo "login ok";
		modifieUtilisateur ( $donnee [0], $donnee [1], $mdp, $donnee [3], $donnee [4], $donnee [5], $donnee [6], $donnee [7], $donnee [8], $donnee [9], $donnee [10], $donnee [11] );
		return $donnee;
	} else
		echo "Erreur de mot de passe : $crypt";
		return false;
}
function statut($privilege) {
	switch ($privilege) {
		case 0 :
			return "invité";
		case 1 :
			return "membre";
		case 2 :
			return "éditeur";
		case 3 :
			return "administrateur";
	}
}

// Pour le test
// Cette fonction renvoie une chaine de description de l'utilisateur
function infos($id) {
	$enr = chercheUtilisateur ( $id );
	// id_journee id_joueur poste statut
	$retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Desc : " . $enr [2] . " date : " . $enr [3] . " image : " . $enr [4] . " hits : " . $enr [5];
	return $retour;
}

// Fonction de test
function testeUtilisateur() {
	creeUtilisateur ( "test", "kazoo", "Alain", "Minc", "test.png", "http://samere", "truc@bidule.com", "bla bla bla", "25/10/2017", 5, 3 );
	$id = chercheUtilisateurParLeLogin ( "utilisateur 1" );
	$id = $id [0];
	$enr = chercheUtilisateur ( $id );
	echo infos ( $id );
	creeUtilisateur ( "test2", "kazoo2", "Alain", "Minc", "test.png", "http://samere", "truc@bidule.com", "bla bla bla", "25/10/2017", 5, 2 );
	creeModifieUtilisateur ( $id, "test3", "kazoo3", "Alainx", "Mincx", "tesxt.png", "http://samere.org", "truc@bidule.com", "bla bla bla", "25/10/2017", 5 );
	
	$id = chercheUtilisateurParLeLogin ( "test" );
	supprimeUtilisateur ( $id [0] );
	
	$id = chercheUtilisateurParLeLogin ( "test2" );
	supprimeUtilisateur ( $id [0] );
	$id = chercheUtilisateurParLeLogin ( "test3" );
	// supprimeUtilisateur($id[0]);
}

//testeUtilisateur ();

?>