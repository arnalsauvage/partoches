<?php
/**
 * PAGE : utilisateur_activation.php
 * Contrôleur d'activation de compte utilisateur via jeton d'email.
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
require_once PHP_DIR . "/utilisateur/Utilisateur.php";

$token = trim($_GET['token'] ?? '');
$success = false;
$errorMsg = "";
$userLogin = "";

if (!empty($token)) {
    $row = Utilisateur::activeCompteParToken($token);
    if ($row && is_array($row)) {
        $success = true;
        $userLogin = $row[1];

        // Connexion automatique après activation
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['id'] = (int)$row[0];
        $_SESSION['user'] = $row[1];
        $_SESSION['email'] = $row[7];
        $_SESSION['image'] = $row[5];
        $_SESSION['privilege'] = (int)$row[11];
        $_SESSION['login'] = "ok";
    } else {
        $errorMsg = "Lien d'activation invalide, expiré ou le compte a déjà été activé.";
    }
} else {
    $errorMsg = "Aucun jeton d'activation fourni.";
}

// Rendu de la page (Head + Menu + Vue + Footer)
$html = envoieHead("Activation de compte - Partoches Canopée", "../../css/form.css");
$pasDeMenu = true;
require_once PHP_DIR . "/navigation/menu.php";
$html .= $MENU_HTML;

ob_start();
?>
<div class="container mt-20">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="card-shadow-django bg-white p-30 border-radius-lg text-center">
                <?php if ($success): ?>
                    <i class="glyphicon glyphicon-ok-circle text-success font-size-48 mb-15"></i>
                    <h1 class="mt-0 text-success">Compte activé avec succès !</h1>
                    <p class="font-size-16 lead mb-20">
                        Bienvenue <strong><?= htmlspecialchars($userLogin) ?></strong> ! Votre adresse e-mail a été confirmée et votre compte est maintenant actif.
                    </p>
                    <p class="text-muted mb-30">
                        Vous êtes désormais connecté avec le statut <strong>Membre</strong>.
                    </p>
                    <div>
                        <a href="../chanson/chanson_liste.php" class="btn btn-success btn-lg">
                            <i class="glyphicon glyphicon-music"></i> Accéder au catalogue de chansons
                        </a>
                    </div>
                <?php else: ?>
                    <i class="glyphicon glyphicon-remove-circle text-danger font-size-48 mb-15"></i>
                    <h1 class="mt-0 text-danger">Échec de l'activation</h1>
                    <div class="alert alert-danger mt-20 mb-20">
                        <i class="glyphicon glyphicon-exclamation-sign"></i> <?= htmlspecialchars($errorMsg) ?>
                    </div>
                    <div class="mt-30">
                        <a href="../chanson/chanson_liste.php" class="btn btn-default btn-lg">
                            <i class="glyphicon glyphicon-home"></i> Retour à l'accueil
                        </a>
                        <a href="utilisateur_inscription.php" class="btn btn-primary btn-lg ml-10">
                            <i class="glyphicon glyphicon-user"></i> S'inscrire à nouveau
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$html .= ob_get_clean();
$html .= envoieFooter();
echo $html;
