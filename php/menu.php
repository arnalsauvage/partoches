<?php
require_once("lib/utilssi.php");
require_once("utilisateur.php");

// TODO : ajouter notion de liens : 
// id nom, url, type (video, soundcloud, mp3, image)
// nomTable, idTable

// Si l'utilisateur n'est pas logué
if (!isset ($_SESSION ['user'])) {
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
    }
    else {
        $infoLogin = "<p class='ko'> Compte invité défaillant...</p>";
        $_SESSION ['id'] = 1;
        $_SESSION ['user'] = "invite";
        $_SESSION ['email'] = "test@test.mail";
        $_SESSION ['image'] = "";
        $_SESSION ['privilege'] = 0;
    }
}

if (isset($_SESSION['login'])&&($_SESSION['login'] == "ok")){
    $infoLogin = "<p class='ok'>Vous vous êtes bien connecté.e</p>";
    $_SESSION['login'] = "";
}

if (isset($_SESSION['login'])&&($_SESSION['login'] == "logout")){
    $infoLogin = "<p class='info'>Vous vous êtes bien déconnecté.e</p>";
    $_SESSION['login'] = "";
}

if ($_SESSION['login'] == "ko"){
    $infoLogin = "<p class='ko'> erreur de login/mot de passe...</p>";
    $_SESSION['login'] = "";
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
		<a class='navbar-brand' href='./songbook-portfolio.php'>Top 5 Partoches</a>\n
	</div> <!--/.navbar-header -->\n
    <div id='main-menu' class='collapse navbar-collapse'>\n
          <ul class='nav navbar-nav'>\n
			<li class='divider' role='separator'></li>\n
			<li><a href='../php/songbook_liste.php'>Songbooks</a></li>\n
            <li><a href='../php/chanson_liste.php'>Chansons</a></li>\n
            <li><a href='../php/documents_voir.php'>Documents</a></li>\n";
//            <li><a href='../php/playlist_liste.php'>Playlists</a></li>\n";
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

$contenu .= "<br><br><br> sur Top 5 partoches, les amis de Top5 partagent leurs partoches (venues du club ou d'ailleurs...) pour le plaisir de gratter l'ukulélé <br>\n";

$contenu .= image("../vignettes/" . $_SESSION ['image'], 64, 64, $_SESSION['user']) . "\n";

$date = date("d/m/Y");
$heure = date("H:i");

if ($_SESSION ['user'] != "invite") {
    $msgLogin = "se déconnecter";
    $contenu .= Ancre("login.php?logoff=1", $msgLogin) . " | \n";
} else {
    $msgLogin = "se connecter";
    $contenu .= file_get_contents('../html/menuLogin.html');
    $contenu .= "<a id='afficherPopup'>$msgLogin</a> <script src='../js/utilsJquery.js'></script>";
}
if (isset($infoLogin))
    $contenu .= $infoLogin . "<br>\n";
$contenu .= "Bienvenue " . $_SESSION ['user'] . ", " . statut($_SESSION ['privilege']) . ", nous sommes le $date et il est $heure<br>\n";
$contenu .= " </div> <!--/.container --></div><!--/.starter-template -->";
if (!isset($pasDeMenu) || false == $pasDeMenu)
    echo $contenu . "\n\n";