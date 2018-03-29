<?php
include_once ("lib/utilssi.php");
include_once ("utilisateur.php");

// TODO : ajouter notion de liens : 
// id nom, url, type (video, soundcloud, mp3, image)
// nomTable, idTable

// Si l'utilisateur n'est pas logué
if (! isset ( $_SESSION ['user'] )) {
	// version précédente : on présente un formulaire de login
	//header('Location: ./login.php');

	// Nouveauté mars 2018 : On le connecte en tant qu'invite

	// Si oui, on crée une session avec user, idclub, nomClub
	$donnee = login_utilisateur("invite", "invite");
	if ($donnee) {
		$_SESSION ['id'] = $donnee [0];
		$_SESSION ['user'] = $donnee [1];
		$_SESSION ['email'] = $donnee [7];
		$_SESSION ['image'] = $donnee [5];
		$_SESSION ['privilege'] = $donnee [11];
	} else {
		$sortie .= "erreur de login/mot de passe...";
	}
}

$contenu = envoieHead("Top 5 Partoches", "../css/index.css");
$contenu .= "<body>";

// Affichage du menu

$contenu .= "
<nav class='navbar navbar-inverse navbar-fixed-top'>\n
<div class='container'>\n
	<div class='navbar-header' >\n
		<button class='navbar-toggle collapsed' data-toggle='collapse' \n
			data-target='#main-menu' aria-expanded='true'>\n
			<span class='sr-only'>Menu</span>\n
			<span class='icon-bar'></span>\n
			<span class='icon-bar'></span>\n
			<span class='icon-bar'></span>\n
			<span class='icon-bar'></span>\n";
// Le lien paramétrage est limité aux admin et login parametrage
if (($_SESSION['privilege'] > 1))
	$contenu .= "<span class='icon-bar'></span>";

// Le lien paramétrage est limité aux admin et login parametrage
if ((($_SESSION ['user']) == $_SESSION ['loginParam']) || ($_SESSION ['privilege'] > 2))
	$contenu .= "<span class='icon-bar'></span>\n";
$contenu .= "		</button>\n
		<a class='navbar-brand' href='./songbook_portfolio.php'>Top 5 Partoches</a>\n
	</div> <!--/.navbar-header -->\n
    <div id='main-menu' class='collapse navbar-collapse'>\n
          <ul class='nav navbar-nav'>\n
			<li class='divider' role='separator'></li>\n
			<li><a href='../php/songbook_liste.php'>Songbooks</a></li>\n
            <li><a href='../php/chanson_liste.php'>Chansons</a></li>\n
            <li><a href='../php/documents_voir.php'>Documents</a></li>\n
            <li><a href='../php/playlist_liste.php'>Playlists</a></li>\n";
// Le lien utilisateur est limité aux admin et login parametrage
if (($_SESSION['privilege'] > 1))
	$contenu .= "<li ><a href='../php/utilisateur_liste.php'>Utilisateurs</a></li>\n";
// Le lien paramétrage est limité aux admin et login parametrage
if (($_SESSION['user'] == $_SESSION['loginParam']) 
		|| ($_SESSION['privilege'] > 2))
	$contenu .= "<li><a href='../php/paramsEdit.php'>parametrage</a></li>\n";

$contenu .= "
          </ul>\n
    </div><!--/.nav-collapse -->\n
</div><!--/.container -->\n
</nav>\n\n";

// Sous menu
$contenu .= "<div class='container'>\n
			<div class='starter-template'>\n";

$contenu .= "<br><br><br> sur Top 5 partoches, les amis de Top5 partagent leurs partoches pour jouer des morceaux, venues du club ou d'ailleurs... <br>\n";

$contenu .= image("../images" . $_SESSION ['image'], 64) . "\n";

$date = date ( "d/m/Y" );
$heure = date ( "H:i" );

$contenu .= Ancre("login.php?logoff=1", "logoff") . " | \n";
$contenu .= "Bienvenue " . $_SESSION ['user'] . ", " . statut ( $_SESSION ['privilege'] ) . ", nous sommes le $date et il est $heure<br>\n";
$contenu .= " </div> <!--/.container --></div><!--/.starter-template -->";
if (!isset($pasDeMenu) || false == $pasDeMenu)
	echo $contenu . "\n\n";
?>