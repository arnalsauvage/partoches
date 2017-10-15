<?php
include_once ("lib/utilssi.php");
include_once ("utilisateur.php");

// Si l'utilisateur n'est pas logué
if (! isset ( $_SESSION ['user'] )) {
	header('Location: ./login.php');
}

$contenu = envoieHead ( "Partoches", "../css/index.css" );
$contenu .= "<body>";

// Affichage du menu

$contenu .= "
<nav class='navbar navbar-inverse navbar-fixed-top'>
<div class='container'>
	<div class='navbar-header' >
		<button class='navbar-toggle collapsed' data-toggle='collapse'
			data-target='#main-menu' aria-expanded='true'>
			<span class='sr-only'>Menu</span>
			<span class='icon-bar'></span>
			<span class='icon-bar'></span>";
// Le lien paramétrage est limité aux admin et login parametrage
if ((($_SESSION ['user']) == $_SESSION ['loginParam']) || ($_SESSION ['privilege'] > 2))
	$contenu .= "<span class='icon-bar'></span>";
	$contenu .= "
			<span class='icon-bar'></span>
		</button>
		<a class='navbar-brand' href='../html/index.html'>Partoches</a>
	</div>
    <div id='main-menu' class='collapse navbar-collapse'>
          <ul class='nav navbar-nav'>
			<li class='divider' role='separator'></li>
            <li ><a href='../php/utilisateur_liste.php'>Utilisateurs</a></li>
            <li><a href='../php/chanson_liste.php'>Chansons</a></li>\n";
// Le lien paramétrage est limité aux admin et login parametrage
if (($_SESSION['user'] == $_SESSION['loginParam']) 
		|| ($_SESSION['privilege'] > 2))
	$contenu .= "<li><a href='../php/paramsEdit.php'>parametrage</a></li>\n";

$contenu .= "<li><a href='#contact'>Contact</a></li>
          </ul>
    </div><!--/.nav-collapse -->
</div>
</nav>   ";

// Sous menu
$contenu .= "<div class= 'container' class='row col-sm-4'>
			<div class='starter-template'>";
$contenu .= "<br><br><br>" . image ( "../images" . $_SESSION ['image'], 64 );

$date = date ( "d/m/Y" );
$heure = date ( "H:i" );

$contenu .= Ancre ( "login.php?logoff=1", "logoff" ) . " | ";
$contenu .= "Bienvenue " . $_SESSION ['user'] . ", " . statut ( $_SESSION ['privilege'] ) . ", nous sommes le $date et il est $heure<br>";
$contenu .= "</td></tr></table>";
$contenu .= " </div> </div>";

echo $contenu . "\n\n";
?>