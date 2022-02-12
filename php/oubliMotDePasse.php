<?php
include_once("lib/utilssi.php");
include_once("utilisateur.php");

$sortie = envoieHead("Partoches", "../css/index.css");
$sortie .= "<body>
    <h1>Top 5 Partoches - oubli de mot de passe (étape 1/4)</h1>
    <p> Vous pouvez demander un nouveau mot de passe ici :</p>
	<form action='oubliMotDePasse.php' class='login' method='post'>
		<label class='email' for='email'>Adresse mail :</label>
			<input size='16' id='email' name='email' value='' placeholder='adresse@email.fr'>
		<input type='submit' value='Ok'>
	</form>

";

// Traitement du formulaire si besoin
function envoieMailRecup($_email)
{

    $resultat = chercheUtilisateurParEmail($_email);
    if ($resultat == null) {
        //echo "Pas de résultat...";
        return 0;
    }
    $prenom = $resultat[3];
    $nom = $resultat[4];
// Envoie un email de récupération de mot de passe

    $emailCrypte = Chiffrement::crypt($_POST ['email']);
    $dateDuJour = Chiffrement::crypt(date("d/m/Y"));
    $url = $_SERVER ['HTTP_REFERER'] . "?compte=" . urlencode($emailCrypte) . "&date=" . urlencode($dateDuJour);

    $sujet = "Oubli de mot de passe sur Partoches Top 5 - étape 2/4";

    $contenu = "Bonjour, vous avez certainement demandé la régénération de votre mot de passe sur partoche.
        Voici un lien actif aujourd'hui pour le réinitialiser : 
        
        <a href='" . $url . "'>cliquez ici</a>";

    $contenu .= "
        Si ce n'est pas vous, du coup, c'est plutôt bizarre...
        
        Cordialement,
        Arnaud
        
        ";

    // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';

    // En-têtes additionnels
    $headers[] = 'To: $prenom $nom<' . $_POST['email'] . '>';
    $headers[] = 'From: Top 5 partoches <hello@top5.re>';

    // Envoi
    mail($_POST['email'], $sujet, $contenu, implode("\r\n", $headers));
    echo "sujet : " . $sujet . "<br>";
    echo "Contenu : " . $contenu . "<br>";
    return true;
}

if (isset ($_POST ['email'])) {
    if (!filter_var($_POST ['email'], FILTER_VALIDATE_EMAIL)) {
        echo "Email address '" . $_POST ['email'] . "' is considered invalid.\n";
        return;
    }
    $reponse = "<h1>Top 5 Partoches - oubli de mot de passe (étape 2/4) </h1>";
    if (envoieMailRecup($_POST ['email'])) {
        $reponse .= "Un email de récupération de mot de passe vient de vous être adressé !";
    } else {
        $reponse .= "Si votre email est valide, votre compte est réactualisé...";
    }
    echo $reponse;
    return;
}

if (isset ($_GET ['date']) && isset ($_GET ['compte'])) {
    $reponse = "";
    $date = Chiffrement::decrypt($_GET ['date']);
    $compte = Chiffrement::decrypt($_GET ['compte']);

    if ($date <> date("d/m/Y"))
        $reponse = "Votre jeton n'est pas valide, désolé !";
    //else
    //    $reponse = "date reçue = " . $date;

    // echo "email cherché : " . $compte;
    // Si on ne trouve pas le compte en base, on informe de l'erreur
    $resultat = chercheUtilisateurParEmail($compte);
    if (!$resultat)
        $reponse .= "Email non trouvé dans la base...";
    else {
        $_SESSION['id'] = $resultat[0];
        $reponse .= "Bienvenue, " . $resultat[3] . " ! <br>";
        $reponse .= "Il est temps de choisir un nouveau mot de passe !";
        $reponse .= "<body>
    <h1>Top 5 Partoches - oubli de mot de passe (étape 3/4)</h1>
    <p> Vous pouvez demander un nouveau mot de passe ici :</p>
	<FORM action='oubliMotDePasse.php' class='login' method='post'>
		<label for='nouveauMdp'>Nouveau mdp :</label>
			<input size='16' id='nouveauMdp' name='nouveauMdp' type='password' value='' placeholder='un mot de passe de qualité'>
		<input type='submit' value='Ok'>
	</FORM>";
    }

    echo $reponse;
    return;
}

if (isset ($_POST ['nouveauMdp'])) {
    $url = $_SERVER ['SERVER_ADDR'] . "/chanson_liste.php";
    $url = "./chanson_liste.php";
    $reponse = "<body>
    <h1>Top 5 Partoches - oubli de mot de passe (étape 4/4)</h1>
    <p> Votre mot de passe a bien été changé : vous pouvez vous reconnecter !</p>
    <a href='$url'>par ici !</a>";
    modifieMdpUtilisateur($_SESSION['id'], $_POST ['nouveauMdp']);
    echo $reponse;
    return;
}

echo $sortie;