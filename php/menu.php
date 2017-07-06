<?php
include_once("lib/utilssi.php");
include_once("utilisateur.php");

$sortie = envoieHead("Menu", "../css/index.css");

// Si l'utilisateur a demandé la déconnexion, on efface les infos de la session
if(isset($_GET['logoff']))
unset($_SESSION['user']);

// Traitement du formulaire si besoin
if(isset ($_POST['user'])){
	// Récupère les données user / password depuis le formulaire
	$user = addslashes($_POST["user"]);
	$pass = addslashes($_POST["pass"]);

	$crypt   = Chiffrement::crypt($pass);
	// On regarde si le logon/mdp existent dans la BDD
	$maRequete = "SELECT * FROM utilisateur WHERE login = '$user' AND mdp = '$crypt'";
	$result = mysql_query($maRequete) or die ("Problème #1 dans menu.php " .mysql_error());
	$ligne = mysql_fetch_array ( $result );
	
	// Si oui, on crée une session avec user, idclub, nomClub
	if(login_utilisateur($user, $pass, $idconnect)){
		$_SESSION['user'] = $user;
		$_SESSION['email'] = $ligne[7];    
		$_SESSION['image'] = $ligne[5];
		$_SESSION['privilege'] = $ligne[11];
	}
	else{
		$sortie .= "erreur de login/mot de passe...";
	}
}
echo $sortie;


// Si l'utilisateur n'est pas logué
if(!isset ($_SESSION['user']) ){
	// Affichage du formulaire de login

	include "../html/menuLogin.html";
	//	echo afficheComposClubs($idChampionnat);
	exit();
}

echo "<table align='center'><tr><td>";
//echo image ($_SESSION['image'], 64);
echo Ancre("../html/index.html","index")." | ";
echo Ancre("../php/utilisateur_liste.php","utilisateurs")." | ";
// echo Ancre("page_compos_actives.php","compositions actives")." | ";

if((($_SESSION['user'])==$_SESSION['loginParam'])||	($_SESSION['privilege']>2))
	echo Ancre("../php/paramsEdit.php","parametrage")." | ";

$date = date("d/m/Y");
$heure = date("H:i");  
    
echo Ancre("menu.php?logoff=1","logoff") . " | ";
echo "Bienvenue ".$_SESSION['user'].", ". statut($_SESSION['privilege']) .", nous sommes le $date et il est $heure<br>";
echo "</td></tr></table>";
	
?>