<?php
include_once ("lib/utilssi.php");
include_once ("utilisateur.php");

$sortie = envoieHead ( "Partoches", "../css/index.css" );
$sortie .= "<body>";
// Si l'utilisateur a demandé la déconnexion, on efface les infos de la session
if (isset ( $_GET ['logoff'] )) {
	unset ( $_SESSION ['user'] );
	unset ( $_SESSION ['email'] );
	unset ( $_SESSION ['image'] );
	unset ( $_SESSION ['privilege'] );
}

// Traitement du formulaire si besoin
if (isset ( $_POST ['user'] )) {
	// Récupère les données user / password depuis le formulaire
	$user = addslashes ( $_POST ["user"] );
	$pass = addslashes ( $_POST ["pass"] );
	
	// Si oui, on crée une session avec user, idclub, nomClub
	$donnee = login_utilisateur ( $user, $pass );
	if ($donnee) {
		$_SESSION ['user'] = $donnee [1];
		$_SESSION ['email'] = $donnee [7];
		$_SESSION ['image'] = $donnee [5];
		$_SESSION ['privilege'] = $donnee [11];
	} else {
		$sortie .= "erreur de login/mot de passe...";
	}
}

// Si l'utilisateur est logué
if (isset ( $_SESSION ['user'] ))
	header ( 'Location: ./chanson_liste.php' );
else {
	echo $sortie;
	include "../html/menuLogin.html";
}
?>