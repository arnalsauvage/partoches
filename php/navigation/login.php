<?php
require_once("../lib/utilssi.php");
require_once("../utilisateur/utilisateur.php");

$sortie = envoieHead("Partoches", "../../css/index.css");
$sortie .= "<body>";
// Si l'utilisateur a demandé la déconnexion, on efface les infos de la session
if (isset ($_GET ['logoff'])) {
    unset ($_SESSION ['user']);
    unset ($_SESSION ['email']);
    unset ($_SESSION ['image']);
    unset ($_SESSION ['privilege']);
    unset ($_SESSION ['id']);
    $donnee = login_utilisateur("invite", "invite");
    $_login= "logout";
} else {
// Traitement du formulaire si besoin
    if (isset ($_POST ['user'])) {
        if ($_POST ['user'] == "mdp") { // Ceci permet d'afficher le mot de passe en clair en saisissant "mdp" dans le champ user
            echo Chiffrement::decrypt($_POST ["pass"]);
            // Le password saisi en crypté ds le champ mdp est alors affiché en clair... C'est mal !!!
            exit;
        }

        // Récupère les données user / password depuis le formulaire
        $user = $_SESSION ['mysql']->real_escape_string($_POST ["user"]);
        $pass = $_POST ["pass"];

        // echo "user = $user , mot de passe = $pass";

        // Si oui, on crée une session avec user, id, email, image, privilege
        $donnee = login_utilisateur($user, $pass);
    }
}
if ($donnee) {
    $_SESSION ['id'] = $donnee [0];
    $_SESSION ['user'] = $donnee [1];
    $_SESSION ['email'] = $donnee [7];
    $_SESSION ['image'] = $donnee [5];
    $_SESSION ['privilege'] = $donnee [11];
    if ($_login != "logout") {
        $_login = "ok";
    }
}
else {

    // erreur de login/mot de passe...
    $_login = "ko";
}

    $_SESSION['login'] = $_login;
    header('Location: ' . $_SERVER['HTTP_REFERER']);