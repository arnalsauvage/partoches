<?php
/**
 * SERVICE : UtilisateurInscriptionService
 * Traitement métier de l'inscription / création de compte utilisateur.
 */

require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
require_once PHP_DIR . "/utilisateur/Utilisateur.php";
require_once PHP_DIR . "/lib/utilssi.php";

class UtilisateurInscriptionService
{
    /**
     * Traite l'inscription d'un nouvel utilisateur (Création de compte publique)
     */
    public static function processInscription(array $postData, array $fileData): array
    {
        $login = trim($postData['flogin'] ?? '');
        $mdp = $postData['fmdp'] ?? '';
        $mdpConfirm = $postData['fmdp_confirm'] ?? '';
        $prenom = trim($postData['fprenom'] ?? '');
        $nom = trim($postData['fnom'] ?? '');
        $site = trim($postData['fsite'] ?? '');
        if ($site === 'http://' || $site === 'https://') {
            $site = '';
        }
        $email = trim($postData['femail'] ?? '');
        $signature = trim($postData['fsignature'] ?? '');
        $privilege = 1; // Toujours 1 (Membre) pour une inscription publique

        // Validation des champs obligatoires
        if (empty($login) || empty($mdp) || empty($prenom) || empty($nom) || empty($email)) {
            return ['success' => false, 'error' => 'Veuillez remplir tous les champs obligatoires (Login, Mot de passe, Prénom, Nom, Email).'];
        }

        // Vérification de la confirmation du mot de passe
        if ($mdp !== $mdpConfirm) {
            return ['success' => false, 'error' => 'La confirmation du mot de passe ne correspond pas.'];
        }

        // Format d'email valide
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Adresse email invalide.'];
        }

        // Vérification de l'unicité du login
        $existing = Utilisateur::chercheUtilisateurParLeLogin($login);
        if (!empty($existing) && is_array($existing)) {
            return ['success' => false, 'error' => "L'identifiant (Login) '$login' est déjà utilisé. Veuillez en choisir un autre."];
        }

        // Gestion de la photo (avatar optionnel)
        $fimage = "utilisateur/defaut.png";
        if (isset($fileData['fichierUploade']) && $fileData['fichierUploade']['error'] === UPLOAD_ERR_OK) {
            $dossier_cible = __DIR__ . "/../../data/utilisateurs/";
            if (!is_dir($dossier_cible)) {
                mkdir($dossier_cible, 0755, true);
            }

            $fileName = $fileData['fichierUploade']['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowedExts) && $fileData['fichierUploade']['size'] <= 2 * 1024 * 1024) {
                $cleanName = preg_replace('/[^a-zA-Z0-9\._-]/', '', $fileName);
                $destination = $dossier_cible . $cleanName;
                if (move_uploaded_file($fileData['fichierUploade']['tmp_name'], $destination)) {
                    $fimage = "/utilisateur/" . $cleanName;
                }
            }
        }

        // Génération du token d'activation
        $token = bin2hex(random_bytes(16));

        // Création de l'utilisateur en attente d'activation
        Utilisateur::creeUtilisateurEnAttente($login, $mdp, $prenom, $nom, $fimage, $site, $email, $signature, $token);

        // Construction du lien d'activation
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $activationLink = "$scheme://$host/php/utilisateur/utilisateur_activation.php?token=$token";

        // Envoi de l'email d'activation
        $sujet = "Activation de votre compte Partoches";
        $message = "Bonjour " . $prenom . ",\n\n"
                 . "Merci de vous être inscrit sur Partoches !\n"
                 . "Pour valider votre adresse e-mail et activer votre compte, veuillez cliquer sur le lien ci-dessous :\n\n"
                 . $activationLink . "\n\n"
                 . "Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail.\n\n"
                 . "À très bientôt sur Partoches !";
        $headers = "From: no-reply@" . parse_url("$scheme://$host", PHP_URL_HOST) . "\r\n"
                 . "Reply-To: no-reply@" . parse_url("$scheme://$host", PHP_URL_HOST) . "\r\n"
                 . "Content-Type: text/plain; charset=UTF-8\r\n";

        @mail($email, $sujet, $message, $headers);

        // Enregistrement dans les logs PHP pour faciliter les tests en local
        error_log("LIEN ACTIVATION [$email]: $activationLink");

        return [
            'success' => true,
            'requires_activation' => true,
            'email' => $email,
            'token' => $token,
            'user' => $login
        ];
    }
}
