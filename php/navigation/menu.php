<?php
require_once("../lib/utilssi.php");
require_once("../utilisateur/utilisateur.php");

$cheminVignettes = "../../vignettes/";

// Si l'utilisateur n'est pas logué
if (!isset ($_SESSION ['largeur-fenetre'])) {
    // On définit une largeur de fenetre, utile pour décider quel niveau de détail sera affiché
    $_SESSION ['largeur-fenetre'] = 800;
}

// Si l'utilisateur n'est pas logué
if (!isset ($_SESSION ['user'])) {
    // version précédente : on présente un formulaire de login
    //header('Location: ./login.php');

    // Nouveauté mars 2018 : On le connecte en tant qu'invite

    // Si oui, on crée une session avec user, idclub, nomClub
    $donnee = login_utilisateur("invite", "invite");
    if ($donnee) {
        // TODO : 5 lignes dupliquées de login.php
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

if (isset($_SESSION['login'])&&($_SESSION['login'] == "ko")){
    $infoLogin = "<p class='ko'> erreur de login/mot de passe...</p>";
    $_SESSION['login'] = "";
}

$contenu = envoieHead($_SESSION ['titreSite'], "../../css/index.css?v=25.3.28");
$contenu .= "<body>";
$contenu .= "<script> if (window.innerWidth !== " . $_SESSION['largeur-fenetre'] . ") {
    const donnees = 'largeur_fenetre=' + window.innerWidth;
        $.ajax({
                url: '../lib/ajaxappli.php',
                type: 'POST', // Le type de la requête HTTP, ici devenu POST
                data: donnees,
                dataType: 'html'
            });
}
</script>";
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
if ($_SESSION['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $contenu .= "<span class='icon-bar'></span>";
}

// Le lien paramétrage est limité aux admin et login parametrage
if ((($_SESSION ['user']) == $_SESSION ['loginParam']) || ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"])) {
    $contenu .= "<span class='icon-bar'></span>\n";
}
$contenu .= "		</button>\n
		<a class='navbar-brand' href='../media/listeMedias.php'>
            <img src='../../images/navigation/".$_SESSION['logoSite']."' width='42' class='logo'>
    " . $_SESSION['titreSite'] . "
</a>\n
	</div> <!--/.navbar-header -->\n
    <div id='main-menu' class='collapse navbar-collapse'>\n
          <ul class='nav navbar-nav'>\n
			<li class='divider' role='separator'></li>\n";
// Le lien utilisateur est limité aux admin et login parametrage
if ($_SESSION['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    $contenu .= "<li><a href='../songbook/songbook_liste.php'>Songbooks</a></li>\n";
}
else{
    $contenu .= "<li><a href='../songbook/songbook-portfolio.php'>Songbooks</a></li>\n";
}
$contenu .= "<li><a href='../chanson/chanson_liste.php'>Chansons</a></li>\n
            <li><a href='../strum/strum_liste.php'>Strums</a></li>\n
            <li><a href='../liens/lienurl_liste.php'>Liens</a></li>\n";
//            <li><a href='../php/playlist_liste.php'>Playlists</a></li>\n";
// Le lien utilisateur est limité aux admin et login parametrage
if ($_SESSION['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    $contenu .= "<li ><a href='../utilisateur/utilisateur_liste.php'>Utilisateurs</a></li>\n
            <li><a href='../document/documents_voir.php'>Documents</a></li>\n";
}
    $contenu .= "<li><a href='../../html/diagrammes/pageDiagrammes.htm' target='_blank'><img height='32' alt='' src='../../images/icones/diagramme.png'>Outils</a></li>
<li></li>";
// Le lien paramétrage est limité aux admin et login parametrage
if (($_SESSION['user'] == $_SESSION['loginParam'])
    || ($_SESSION['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"])) {
    $contenu .= "<li><a href='../navigation/paramsEdit.php'>parametrage</a></li>\n";
}

$contenu .= "
          </ul>\n
    </div><!--/.nav-collapse -->\n
</div><!--/.container -->\n
</nav>\n\n";

// Sous le menu
$contenu .= "<div class='container'>\n
			<div class='starter-template'>\n";

$contenu .= "<br><br><br> ".$_SESSION ['sousTitreSite'] . " <br>\n";

$contenu .= image("{$cheminVignettes}" . $_SESSION ['image'], 64, 64, $_SESSION['user']) . "\n";

$date = date("d/m/Y");
$heure = date("H:i");

if ($_SESSION ['user'] != "invite") {
    $msgLogin = "se déconnecter";
    $contenu .= ancre("../navigation/login.php?logoff=1", $msgLogin) . " | \n";
} else {
    $msgLogin = "se connecter";
    $contenu .= file_get_contents('../../html/menuLogin.html');
    $contenu .= "<a id='afficherPopup'>$msgLogin</a> <script src='../../js/utilsJquery.js'></script>";
}
if (isset($infoLogin)) {
    $contenu .= $infoLogin . "<br>\n";
}
$contenu .= "Bienvenue " . $_SESSION ['user'] . ", " . statut($_SESSION ['privilege']) . ", nous sommes le $date et il est $heure<br>\n";
$contenu .= " </div> <!--/.container --></div><!--/.starter-template -->";
if (!isset($pasDeMenu) || !$pasDeMenu) {
    echo $contenu . "\n\n";
}

