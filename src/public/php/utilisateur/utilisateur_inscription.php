<?php
/**
 * PAGE : utilisateur_inscription.php
 * Contrôleur de création de compte (Inscription utilisateur).
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
require_once __DIR__ . "/UtilisateurInscriptionService.php";

$error = "";
$requiresActivation = false;
$registeredEmail = "";
$activationToken = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = UtilisateurInscriptionService::processInscription($_POST, $_FILES);
    if ($result['success']) {
        $requiresActivation = true;
        $registeredEmail = $result['email'];
        $activationToken = $result['token'] ?? '';
    } else {
        $error = $result['error'];
    }
}

// Données par défaut du formulaire
$formData = [
    'login' => htmlspecialchars($_POST['flogin'] ?? ''),
    'prenom' => htmlspecialchars($_POST['fprenom'] ?? ''),
    'nom' => htmlspecialchars($_POST['fnom'] ?? ''),
    'site' => htmlspecialchars($_POST['fsite'] ?? ''),
    'email' => htmlspecialchars($_POST['femail'] ?? ''),
    'signature' => htmlspecialchars($_POST['fsignature'] ?? ''),
    'error' => $error,
    'requires_activation' => $requiresActivation,
    'registered_email' => $registeredEmail,
    'activation_token' => $activationToken
];

// Rendu de la page (Head + Menu + Vue + Footer)
$html = envoieHead("Créer un compte - Partoches Canopée", "../../css/form.css");
$pasDeMenu = true;
require_once PHP_DIR . "/navigation/menu.php";
$html .= $MENU_HTML;

ob_start();
include __DIR__ . "/views/utilisateur_inscription_view.phtml";
$html .= ob_get_clean();

$html .= envoieFooter();
echo $html;
