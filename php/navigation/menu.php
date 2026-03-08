<?php
require_once "../lib/utilssi.php";
require_once "../utilisateur/utilisateur.php";

/**
 * Gère l'affichage du menu supérieur et l'état de la session utilisateur.
 */

$cheminVignettes = "../../vignettes/";

// Initialisation de la largeur de fenêtre si absente
if (!isset($_SESSION['largeur-fenetre'])) {
    $_SESSION['largeur-fenetre'] = 800;
}

// Gestion automatique du compte invité si non connecté
if (!isset($_SESSION['user'])) {
    $donnee = login_utilisateur("invite", "invite");
    if ($donnee) {
        $_SESSION['id'] = $donnee[0];
        $_SESSION['user'] = $donnee[1];
        $_SESSION['email'] = $donnee[7];
        $_SESSION['image'] = $donnee[5];
        $_SESSION['privilege'] = $donnee[11];
    } else {
        $_SESSION['id'] = 1;
        $_SESSION['user'] = "invite";
        $_SESSION['email'] = "test@test.mail";
        $_SESSION['image'] = "";
        $_SESSION['privilege'] = 0;
    }
}

// Messages flash de connexion/déconnexion
$infoLogin = "";
if (isset($_SESSION['login'])) {
    if ($_SESSION['login'] === "ok") {
        $infoLogin = "<p class='ok'>Vous vous êtes bien connecté.e</p>";
    } elseif ($_SESSION['login'] === "logout") {
        $infoLogin = "<p class='info'>Vous vous êtes bien déconnecté.e</p>";
    }
    
    if ($_SESSION['login'] === "ko") {
        $contenuExtra = "<script>$(function() { toastr.error('Erreur de login ou mot de passe !'); });</script>";
    }
    $_SESSION['login'] = "";
}

// Construction du Head
$contenu = envoieHead($_SESSION['titreSite'], "../../css/index.css?v=26.03.08");
if (isset($contenuExtra)) $contenu .= $contenuExtra;

$logoSite = $_SESSION['logoSite'];
$titreSite = $_SESSION['titreSite'];
$privilege = $_SESSION['privilege'];
$user = $_SESSION['user'];

// --- CONSTRUCTION DU MENU (HEREDOC) ---

$liensSongbook = ($privilege > $GLOBALS["PRIVILEGE_MEMBRE"]) 
    ? "<li><a href='../songbook/songbook_liste.php'>Songbooks</a></li>"
    : "<li><a href='../songbook/songbook-portfolio.php'>Songbooks</a></li>";

$liensAdmin = "";
if ($privilege > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    $liensAdmin = <<<HTML
        <li><a href='../utilisateur/utilisateur_liste.php'>Utilisateurs</a></li>
        <li><a href='../document/documents_voir.php'>Documents</a></li>
HTML;
}

$lienParametrage = "";
if (($user == $_SESSION['loginParam']) || ($privilege > $GLOBALS["PRIVILEGE_EDITEUR"])) {
    $lienParametrage = "<li><a href='../navigation/paramsEdit.php'>Paramétrage</a></li>";
}

// --- GESTION DE L'AVATAR ET DU ROLE ---
$imageUser = !empty($_SESSION['image']) ? $_SESSION['image'] : "utilisateur/defaut.png";
$avatarPath = $cheminVignettes . $imageUser;
$statutTexte = statut($privilege);

$roleIcon = match(true) {
    $privilege > $GLOBALS["PRIVILEGE_EDITEUR"] => "glyphicon-king",      // Admin
    $privilege > $GLOBALS["PRIVILEGE_MEMBRE"]  => "glyphicon-pencil",    // Editeur
    $privilege > $GLOBALS["PRIVILEGE_INVITE"]  => "glyphicon-user",      // Membre
    default                                    => "glyphicon-eye-open"   // Invite / Visiteur
};

// Vérification de l'existence du fichier sur le serveur
$avatarFileSystemPath = $_SERVER['DOCUMENT_ROOT'] . "/vignettes/" . $imageUser;
if (file_exists($avatarFileSystemPath) && is_file($avatarFileSystemPath)) {
    $avatarHtml = "<img src='$avatarPath' class='user-avatar-round' alt='$user' title='$user'>";
} else {
    // Fallback : On utilise l'icône bonhomme
    $avatarHtml = "<span class='user-avatar-round' title='$user ($statutTexte)'><i class='glyphicon glyphicon-user'></i></span>";
}

// Lien connexion / déconnexion
$extraHtml = "";
if ($user != "invite") {
    $authLink = "<a href='../navigation/login.php?logoff=1' title='Se déconnecter' class='auth-btn'><i class='glyphicon glyphicon-off'></i></a>";
} else {
    $extraHtml = file_get_contents('../../html/menuLogin.html');
    $authLink = "<a id='afficherPopup' title='Se connecter' class='auth-btn' style='cursor:pointer;'><i class='glyphicon glyphicon-log-in'></i></a>";
}

$userNav = <<<HTML
    <ul class="nav navbar-nav navbar-right">
        <li class="navbar-user-info">
            <span class="glyphicon $roleIcon role-icon" title="$user ($statutTexte)"></span>
            $avatarHtml
            $authLink
        </li>
    </ul>
HTML;

$contenu .= <<<HTML
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-menu" aria-expanded="false">
                <span class="sr-only">Menu</span>
                <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="../media/listeMedias.php">
                <img src="../../images/navigation/$logoSite" width="42" class="logo" alt="logo">
                $titreSite
            </a>
        </div>
        <div id="main-menu" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="divider" role="separator"></li>
                $liensSongbook
                <li><a href="../chanson/chanson_liste.php?razFiltres">Chansons</a></li>
                <li><a href="../strum/strum_liste.php">Strums</a></li>
                <li><a href="../liens/lienurl_liste.php">Liens</a></li>
                $liensAdmin
                <li>
                    <a href="../../html/diagrammes/" target="_blank">
                        <img height="24" alt="outils" src="../../images/icones/diagramme.png"> Outils
                    </a>
                </li>
                $lienParametrage
            </ul>
            $userNav
        </div>
    </div>
</nav>
HTML;

// --- ZONE MESSAGES (SOUS LE MENU) ---
if (!empty($infoLogin)) {
    $contenu .= <<<HTML
<div class="container">
    <div class="starter-template" style="padding-top: 10px; padding-bottom: 0;">
        $infoLogin
    </div>
</div>
HTML;
}
$contenu .= $extraHtml;

if (!isset($pasDeMenu) || !$pasDeMenu) {
    echo $contenu;
}
