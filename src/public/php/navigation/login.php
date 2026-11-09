<?php
require_once file_exists(dirname(__DIR__, 2) . '/autoload.php') ? dirname(__DIR__, 2) . '/autoload.php' : dirname(__DIR__) . '/autoload.php';
require_once LIB_DIR . "/utilssi.php";
if (!class_exists('Utilisateur')) {
    require_once PHP_DIR . "/utilisateur/Utilisateur.php";
}
if (!class_exists('AuthTokenService')) {
    require_once PHP_DIR . "/utilisateur/AuthTokenService.php";
}

$donnee = null;
$_login = "";

// Si l'utilisateur a demandé la déconnexion, on efface les infos de la session et révoque le jeton
if (isset ($_GET ['logoff'])) {
    if (class_exists('AuthTokenService')) {
        AuthTokenService::revoquerToken();
        AuthTokenService::supprimerCookie();
    }
    unset ($_SESSION ['user']);
    unset ($_SESSION ['email']);
    unset ($_SESSION ['image']);
    unset ($_SESSION ['privilege']);
    unset ($_SESSION ['id']);
    $donnee = login_utilisateur("invite", "invite");
    $_login = "logout";
} else {
    // Traitement du formulaire si besoin
    if (isset ($_POST ['user'])) {
        if ($_POST ['user'] == "mdp") { // Ceci permet d'afficher le mot de passe en clair en saisissant "mdp" dans le champ user
            echo Chiffrement::decrypt($_POST ["pass"]);
            exit;
        }

        // Récupère les données user / password depuis le formulaire
        $user = $_SESSION ['mysql']->real_escape_string($_POST ["user"]);
        $pass = $_POST ["pass"];

        // Si oui, on crée une session avec user, id, email, image, privilege
        $donnee = login_utilisateur($user, $pass);
    }
}

if ($donnee) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    $_SESSION ['id'] = $donnee [0];
    $_SESSION ['user'] = $donnee [1];
    $_SESSION ['email'] = $donnee [7];
    $_SESSION ['image'] = $donnee [5];
    $_SESSION ['privilege'] = $donnee [11];
    if ($_login != "logout") {
        $_login = "ok";
    }

    // Gestion du jeton persistant "Se souvenir de moi" (14 jours)
    if (!empty($_POST['remember_me']) && class_exists('AuthTokenService')) {
        AuthTokenService::creerToken((int)$donnee[0]);
    }
} else {
    // Si on n'était pas en train de se déconnecter, alors c'est une vraie erreur de login
    if ($_login != "logout") {
        $_login = "ko";
    }
    // Par sécurité, on s'assure que la session contient au moins l'invité par défaut si le login échoue
    if (!isset($_SESSION['user'])) {
        $_SESSION['id'] = 1;
        $_SESSION['user'] = "invite";
        $_SESSION['privilege'] = 0;
    }
}

$_SESSION['login'] = $_login;
$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "../../index.php";
header('Location: ' . $url);
