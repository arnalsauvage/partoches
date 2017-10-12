<?php
include_once ("lib/utilssi.php");
include_once ("utilisateur.php");

$sortie = envoieHead ( "Partoches", "../css/index.css" );

// Si l'utilisateur a demandé la déconnexion, on efface les infos de la session
if (isset ( $_GET ['logoff'] ))
	unset ( $_SESSION ['user'] );

// Traitement du formulaire si besoin
if (isset ( $_POST ['user'] )) {
	// Récupère les données user / password depuis le formulaire
	$user = addslashes ( $_POST ["user"] );
	$pass = addslashes ( $_POST ["pass"] );
	
	// Si oui, on crée une session avec user, idclub, nomClub
	$donnee = login_utilisateur ( $user, $pass);
	if ($donnee) {
		$_SESSION ['user'] = $donnee[1];
		$_SESSION ['email'] = $donnee[7];
		$_SESSION ['image'] = $donnee[5];
		$_SESSION ['privilege'] = $donnee[11];
	} else {
		$sortie .= "erreur de login/mot de passe...";
	}
}
echo $sortie;

// Si l'utilisateur n'est pas logué
if (! isset ( $_SESSION ['user'] )) {
	// Affichage du formulaire de login
	
	include "../html/menuLogin.html";
	// echo afficheComposClubs($idChampionnat);
	exit ();
}
$contenu = "";
$contenu .= "
<nav class='navbar navbar-inverse navbar-fixed-top'>
<div class= 'container '>
	<div class= 'navbar-header ' >
		<button type= 'button ' class= 'navbar-toggle collapsed ' data-toggle= 'collapse ' data-target= '#navbar ' aria-expanded= 'false ' aria-controls= 'navbar '>
		<span class= 'sr-only '>Menu</span>
		<span class= 'icon-bar '></span>
		<span class= 'icon-bar '></span>";
// Le lien paramétrage est limité aux admin et login parametrage
if ((($_SESSION ['user']) == $_SESSION ['loginParam']) || ($_SESSION ['privilege'] > 2))
	$contenu .= "<span class= 'icon-bar '></span>";
$contenu .= "
		<span class= 'icon-bar '></span>
		</button>
		<a class='navbar-brand' href= '../html/index.html'>Partoches</a>
	</div>
    <div id= 'navbar ' class= 'collapse navbar-collapse '>
          <ul class= 'nav navbar-nav '>
            <li class= 'active '><a href= '../php/utilisateur_liste.php '>Utilisateurs</a></li>
            <li><a href= '../php/chanson_liste.php '>Chansons</a></li>";
// Le lien paramétrage est limité aux admin et login parametrage
if ((($_SESSION ['user']) == $_SESSION ['loginParam']) || ($_SESSION ['privilege'] > 2))
	$contenu .= "<li><a href= '../php/paramsEdit.php '>parametrage</a></li>";

$contenu .= "<li><a href= '#contact '>Contact</a></li>
          </ul>
    </div><!--/.nav-collapse -->
</div>
</nav>   ";

$contenu .= "<div class= 'container' class='row col-sm-4'>
			<div class='starter-template'>";
$contenu .= "<br><br><br>" . image ( "../images".$_SESSION['image'], 64 );

$date = date ( "d/m/Y" );
$heure = date ( "H:i" );

$contenu .= Ancre ( "menu.php?logoff=1", "logoff" ) . " | ";
$contenu .= "Bienvenue " . $_SESSION ['user'] . ", " . statut ( $_SESSION ['privilege'] ) . ", nous sommes le $date et il est $heure<br>";
$contenu .= "</td></tr></table>";
$contenu .= " </div> </div>";

echo $contenu;
?>