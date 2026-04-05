<?php
require_once dirname(__DIR__, 3) . "/autoload.php";

/**
 * Gère l'affichage du menu supérieur et l'état de la session utilisateur.
 */
$contenu = "";

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

// Construction du Menu (sans le Head automatique)
$logoSite = !empty($_SESSION['logoSite']) ? $_SESSION['logoSite'] : 'logo_site.png';
$titreSite = $_SESSION['titreSite'] ?? 'Partoches Canopée';

// Correction encodage titre site si besoin
if (!empty($titreSite) && !mb_check_encoding($titreSite, 'UTF-8')) {
    $titreSite = mb_convert_encoding($titreSite, 'UTF-8', 'ISO-8859-1');
}

$privilege = $_SESSION['privilege'];
$user = $_SESSION['user'];

// --- CONSTRUCTION DU MENU (HEREDOC) ---

// 1. Médias (Tous)
$lienMedias = "<li><a href=\"../media/listeMedias.php\">Médias</a></li>";

// 2. Chansons (Tous)
$lienChansons = "<li><a href=\"../chanson/chanson_liste.php?razFiltres\">Chansons</a></li>";

// 3. Strums (Tous)
$lienStrums = "<li><a href=\"../strum/strum_liste.php\">Strums</a></li>";

// 4. Songbooks (Tous - destination selon privilège)
$liensSongbook = ($privilege > $GLOBALS["PRIVILEGE_MEMBRE"]) 
    ? "<li><a href=\"../songbook/songbook_liste.php\">Songbooks</a></li>"
    : "<li><a href=\"../songbook/songbook-portfolio.php\">Songbooks</a></li>";

// 5. Liens (Tous)
$lienLiens = "<li><a href=\"../liens/lienurl_liste.php\">Liens</a></li>";

// 6. Outils (Tous)
$lienOutils = <<<HTML
    <li>
        <a href="../../html/diagrammes/" target="_blank">
            <img height="24" alt="outils" src="../../images/icones/diagramme.png"> Outils
        </a>
    </li>
HTML;

// 7. Utilisateurs (Admin / Editeur)
$liensAdminUsers = "";
if ($privilege > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    $liensAdminUsers = "<li><a href=\"../playlist/playlist_liste.php\">Playlists</a></li>";
    $liensAdminUsers .= "<li><a href=\"../utilisateur/utilisateur_liste.php\">Utilisateurs</a></li>";
}

// 8. Paramétrage (Admin)
$lienParametrage = "";
if (($user == $_SESSION['loginParam']) || ($privilege > $GLOBALS["PRIVILEGE_EDITEUR"])) {
    $lienParametrage = "<li><a href=\"../admin/params.php\">Paramétrage</a></li>";
}

// ... GESTION DE L'AVATAR ... (inchangé)


// --- GESTION DE L'AVATAR ET DU ROLE ---
$imageUser = !empty($_SESSION['image']) ? $_SESSION['image'] : "defaut.png";
$statutTexte = Utilisateur::statut($privilege);

$roleIcon = match(true) {
    $privilege > $GLOBALS["PRIVILEGE_EDITEUR"] => "glyphicon-king",      // Admin
    $privilege > $GLOBALS["PRIVILEGE_MEMBRE"]  => "glyphicon-pencil",    // Editeur
    $privilege > $GLOBALS["PRIVILEGE_INVITE"]  => "glyphicon-user",      // Membre
    default                                    => "glyphicon-eye-open"   // Invite / Visiteur
};

// Utilisation de la vignette moderne via Image.php
$avatarUrl = Image::getThumbnailUrl($_SESSION['id'] . "/" . $imageUser, 'mini', 'utilisateurs');

if (str_contains($avatarUrl, 'icone_arnal.png')) {
    // Fallback : On utilise l'icône bonhomme si pas d'image
    $avatarHtml = "<span class='user-avatar-round' title='$user ($statutTexte)'><i class='glyphicon glyphicon-user'></i></span>";
} else {
    $avatarHtml = "<img src='$avatarUrl' class='user-avatar-round' alt='$user' title='$user'>";
}

// Lien connexion / déconnexion
$extraHtml = "";
if ($user != "invite") {
    $authLink = "<a href='../navigation/login.php?logoff=1' title='Se déconnecter' class='auth-btn'><i class='glyphicon glyphicon-off'></i></a>";
} else {
    $extraHtml = file_get_contents(__DIR__ . '/../../html/composants/menuLogin.html');
    $authLink = "<a id='afficherPopup' href='#' title='Se connecter' class='auth-btn auth-btn-pointer'><i class='glyphicon glyphicon-log-in'></i></a>";
    
    // Script de gestion de la popup
    $extraHtml .= <<<JS
    <script>
    $(function() {
        $('#afficherPopup').on('click', function(e) {
            e.preventDefault();
            $('.contenu_popup').fadeToggle(200);
            if ($('.contenu_popup').is(':visible')) {
                $('#login').focus();
            }
        });

        $('.btn-fermer-popup').on('click', function() {
            $('.contenu_popup').fadeOut(200);
        });

        // Empêcher la fermeture quand on clique à l'intérieur de la popup
        $('.contenu_popup').on('mouseup', function(e) {
            e.stopPropagation();
        });

        // Fermeture si clic en dehors
        $(document).on('mouseup', function(e) {
            if (!$('#afficherPopup').is(e.target) && $('#afficherPopup').has(e.target).length === 0) {
                $('.contenu_popup').fadeOut(200);
            }
        });

        // Echap pour fermer
        $(document).on('keyup', function(e) {
            if (e.key === "Escape") {
                $(".contenu_popup").fadeOut(200);
            }
        });
    });
    </script>
JS;
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
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-menu" aria-expanded="false">
                <span class="sr-only">Menu</span>
                <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
            <a class="navbar-brand navbar-brand-flex" href="../media/listeMedias.php">
                <span class="site-title-nav">$titreSite</span>
                <img src="../../images/navigation/$logoSite" class="logo-nav" alt="logo">
            </a>
        </div>
        <div id="main-menu" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="divider" aria-hidden="true"></li>
                $lienMedias
                $lienChansons
                $lienStrums
                $liensSongbook
                $lienLiens
                $lienOutils
                $liensAdminUsers
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
    <div class="starter-template msg-flash-container">
        $infoLogin
    </div>
</div>
HTML;
}
$MENU_HTML = $contenu . $extraHtml;

// On affiche le menu automatiquement par défaut pour assurer la compatibilité
// et restaurer les styles sur toutes les pages.
if (!isset($pasDeMenu) || !$pasDeMenu) {
    echo $MENU_HTML;
}
