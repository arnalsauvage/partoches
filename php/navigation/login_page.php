<?php
require_once "../lib/utilssi.php";
require_once "../utilisateur/utilisateur.php";

// On force le titre de la page
$_SESSION['titreSite'] = $_SESSION['titreSite'] ?? "Partoches";

$contenu = envoieHead("Connexion - " . $_SESSION['titreSite'], "../../css/index.css?v=26.3.05");
$contenu .= "<body class='login-page'>";
$contenu .= "<div class='container' style='margin-top: 100px;'>";
$contenu .= "    <div class='row justify-content-center'>";
$contenu .= "        <div class='col-md-6' style='margin: 0 auto; float: none;'>";
$contenu .= "            <div class='panel panel-default' style='border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);'>";
$contenu .= "                <div class='panel-heading' style='background-color: #1F75CC; color: white; text-align: center; padding: 20px;'>";
$contenu .= "                    <h2 style='margin: 0;'>Connexion</h2>";
$contenu .= "                </div>";
$contenu .= "                <div class='panel-body' style='padding: 40px;'>";

if (isset($_SESSION['login']) && $_SESSION['login'] == "ko") {
    $contenu .= "<div class='alert alert-danger text-center'>Erreur de login ou mot de passe !</div>";
    $_SESSION['login'] = "";
}

$contenu .= "                    <form action='../navigation/login.php' method='post' style='background: none; border: none; width: 100%; padding: 0;'>";
$contenu .= "                        <div class='form-group'>";
$contenu .= "                            <label for='login' style='float: none; width: 100%;'>Nom d'utilisateur :</label>";
$contenu .= "                            <input type='text' id='login' name='user' class='form-control' placeholder='Votre login' required autofocus style='width: 100%; height: 45px; margin-bottom: 20px;'>";
$contenu .= "                        </div>";
$contenu .= "                        <div class='form-group'>";
$contenu .= "                            <label for='pass' style='float: none; width: 100%;'>Mot de passe :</label>";
$contenu .= "                            <input type='password' id='pass' name='pass' class='form-control' placeholder='Votre mot de passe' required style='width: 100%; height: 45px; margin-bottom: 20px;'>";
$contenu .= "                        </div>";
$contenu .= "                        <button type='submit' class='btn btn-primary btn-block' style='height: 45px; font-weight: bold; background-color: #1F75CC;'>Se connecter</button>";
$contenu .= "                    </form>";
$contenu .= "                    <hr>";
$contenu .= "                    <div class='text-center'>";
$contenu .= "                        <a href='../utilisateur/oubliMotDePasse.php'>Mot de passe oublié ?</a> | ";
$contenu .= "                        <a href='../media/listeMedias.php'>Retour à l'accueil</a>";
$contenu .= "                    </div>";
$contenu .= "                </div>";
$contenu .= "            </div>";
$contenu .= "        </div>";
$contenu .= "    </div>";
$contenu .= "</div>";
$contenu .= "</body></html>";

echo $contenu;
