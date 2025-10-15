<?php
include_once "../lib/utilssi.php";
include_once "utilisateur.php";

// Initialisation
$nomEmail = $_SESSION['nomEmailOubliMotDePasse'] ?? '';
echo envoieHead("Partoches", "../../css/index.css?v=25.3.28");

// Routage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // Traite l'envoi d'email (étape 2/4)
        handleEmailSubmission($_POST['email']);
    } elseif (isset($_POST['nouveauMdp'])) {
        // Traite la soumission du nouveau mot de passe (étape 4/4)
        handleNewPasswordSubmission($_POST['nouveauMdp']);
    }
} elseif (isset($_GET['date'], $_GET['compte'])) {
    // Gère la vérification du lien (étape 3/4)
    handleTokenVerification($_GET['date'], $_GET['compte']);
} else {
    // Affiche le formulaire initial (étape 1/4)
    showInitialForm($nomEmail);
}

// Affiche le formulaire initial (étape 1/4)
function showInitialForm($nomEmail)
{
    echo <<<HTML
    <body>
        <h1>$nomEmail - oubli de mot de passe (étape 1/4)</h1>
        <p>Vous pouvez demander un nouveau mot de passe ici :</p>
        <form action='oubliMotDePasse.php' class='login' method='post'>
            <label class='email' for='email'>Adresse mail :</label>
            <input size='16' id='email' name='email' value='' placeholder='adresse@email.fr'>
            <input type='submit' value='Ok'>
        </form>
    </body>
HTML;
}

// Traite l'envoi d'email (étape 2/4)
function handleEmailSubmission($email)
{
    global $nomEmail;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Adresse email invalide : '$email'.";
        return;
    }

    echo "<h1>$nomEmail - oubli de mot de passe (étape 2/4)</h1>";

    if (envoieMailRecup($email)) {
        echo "Un email de récupération de mot de passe vient de vous être adressé !<br>Rendez-vous dans votre boîte mail pour poursuivre.";
    } else {
        echo "Si votre email est valide, votre compte est réactualisé...";
    }
}

// Gère la vérification du lien (étape 3/4)
function handleTokenVerification($dateCryptee, $compteCrypte)
{
    global $nomEmail;

    $date = Chiffrement::decrypt($dateCryptee);
    $email = Chiffrement::decrypt($compteCrypte);

    if ($date !== date("d/m/Y")) {
        echo "Votre jeton n'est pas valide, désolé !";
        return;
    }

    $utilisateur = chercheUtilisateurParEmail($email);
    if (!$utilisateur) {
        echo "Email non trouvé dans la base...";
        return;
    }

    $_SESSION['id'] = $utilisateur[0];

    $prenomNom = htmlspecialchars($utilisateur[3], ENT_QUOTES);

    echo <<<HTML
    <body>
        <h1>$nomEmail - oubli de mot de passe (étape 3/4)</h1>
        <p>Bienvenue, $prenomNom ! Il est temps de choisir un nouveau mot de passe :</p>
        <form action='oubliMotDePasse.php' class='login' method='post'>
            <label for='nouveauMdp'>Nouveau mot de passe :</label>
            <input size='16' id='nouveauMdp' name='nouveauMdp' type='password' placeholder='un mot de passe de qualité'>
            <input type='submit' value='Ok'>
        </form>
    </body>
HTML;
}

// Traite la soumission du nouveau mot de passe (étape 4/4)
function handleNewPasswordSubmission($nouveauMdp)
{
    global $nomEmail;

    if (!isset($_SESSION['id'])) {
        echo "Session invalide. Veuillez recommencer le processus.";
        return;
    }

    modifieMdpUtilisateur($_SESSION['id'], $nouveauMdp);

    $url = "../chanson/chanson_liste.php";

    echo <<<HTML
    <body>
        <h1>$nomEmail - oubli de mot de passe (étape 4/4)</h1>
        <p>Votre mot de passe a bien été changé. Vous pouvez vous reconnecter :</p>
        <a href='$url'>par ici !</a>
    </body>
HTML;
}

// Fonction d'envoi du mail
function envoieMailRecup($email)
{
    $utilisateur = chercheUtilisateurParEmail($email);
    if (!$utilisateur) {
        return false;
    }

    $emailCrypte = Chiffrement::crypt($email);
    $dateCryptee = Chiffrement::crypt(date("d/m/Y"));
    $referer = $_SERVER['HTTP_REFERER'] ?? 'http://localhost';
    $url = "$referer?compte=" . urlencode($emailCrypte) . "&date=" . urlencode($dateCryptee);

    $sujet = "Oubli de mot de passe sur ". $_SESSION ['titreSite'] ." - étape 2/4";
    $contenu = <<<HTML
    Bonjour, vous avez certainement demandé la régénération de votre mot de passe sur un site partoches.<br><br>
    Voici un lien actif aujourd'hui pour le réinitialiser :<br>
    <a href="$url">Cliquez ici pour entrer un nouveau mot de passe</a><br><br>
    Si ce n'est pas vous, du coup, c'est plutôt bizarre...<br><br>
    Cordialement,<br>Arnaud
HTML;

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $headers .= "From: " . ($_SESSION['mailOubliMotDePasse'] ?? 'no-reply@partoches.local') . "\r\n";

    return mail($email, $sujet, $contenu, $headers);
}
