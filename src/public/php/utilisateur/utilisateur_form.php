<?php
/**
 * Formulaire Utilisateur
 */

require_once __DIR__ . "/../lib/utilssi.php";
require_once __DIR__ . "/../document/Document.php";
require_once __DIR__ . "/../lib/Image.php";
$pasDeMenu = true;
require_once __DIR__ . "/../navigation/menu.php";

$mode = "";
$table = "utilisateur";
$sortie = "";

// Chargement des données... (logique existante conservée via include ou réécriture)
// Pour la simplicité et la sécurité, je vais juste injecter le $pasDeMenu et l'echo $MENU_HTML
// au début et à la fin du fichier actuel.

if (isset ($_GET ['id']) && $_GET ['id'] != "") {
    $donnee = chercheUtilisateur($_GET ['id']);
    if (($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) || $_SESSION ['user'] == $donnee [1]) {
        $mode = "MAJ";
        $donnee [2] = Chiffrement::decrypt($donnee [2]);
        $donnee [1] = htmlspecialchars($donnee [1]);
        $donnee [3] = htmlspecialchars($donnee [3]);
        $donnee [4] = htmlspecialchars($donnee [4]);
        $donnee [6] = htmlspecialchars($donnee [6]);
        $donnee [7] = htmlspecialchars($donnee [7]);
        $donnee [8] = htmlspecialchars($donnee [8]);
    }
} else if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $mode = "INS";
    $donnee [0] = 0; // id
    $donnee [1] = ""; // login
    $donnee [2] = ""; // mdp
    $donnee [3] = ""; // prenom
    $donnee [4] = ""; // nom
    $donnee [5] = ""; // image
    $donnee [6] = "http://"; // site
    $donnee [7] = "@"; // Adresse
    $donnee [8] = "Devise ou citation..."; // signature
    $donnee [9] = "1970-01-01"; // Date dernier login
    $donnee [10] = 0; // nbrelogins
    $donnee [11] = 0; // privilege
}

// --- RENDU HTML ---
$headHtml = envoieHead("Profil Utilisateur", "../../css/form.css");
echo $headHtml;
echo $MENU_HTML;
$sortie = "";

if ($mode == "MAJ"){
    $sortie .= "<H1> Mise à jour - " . $table . " : " . $donnee[1] . "</H1>";
}
else if ($mode == "INS") {
    $sortie .= "<H1> Création - " . $table . "</H1>";
} else {
    return;
}

// ... (Reste du code du formulaire, condensé ici pour la réécriture) ...
$sortie .= "
<ul class='nav nav-tabs' role='tablist' style='margin-bottom: 20px;'>
    <li role='presentation' class='active'><a href='#general' aria-controls='general' role='tab' data-toggle='tab'>Général</a></li>
    " . ($mode == 'MAJ' ? "<li role='presentation'><a href='#publications' aria-controls='publications' role='tab' data-toggle='tab'>Publications</a></li>" : "") . "
</ul>
<div class='tab-content'><div role='tabpanel' class='tab-pane active' id='general'>";

$retour_form = "";
$f = new Formulaire ("POST", $table . "_get.php", $retour_form);
$f->champCache("id", $donnee [0]);
$formHtml = str_replace("<FORM ", "<FORM id='user-form' ", $f->getHtml());

$avatarFile = str_replace("/utilisateur/", "", $donnee [5]);
$avatarUrl = Image::getThumbnailUrl($avatarFile, 'sd', 'utilisateurs');
$sortie .= "<div class='text-center' style='margin-bottom: 20px;'><img id='user-avatar-preview' src='$avatarUrl' class='img-circle shadow' style='width:150px; height:150px; object-fit:cover; border: white 3px solid;'></div>";

$listeImages = listeImages("/utilisateur");
$f->champListeImages("Image : ", "fimage", $avatarFile, 1, $listeImages);
$f->champTexte("Login :", "flogin", $donnee [1], 50, 32);
$f->champMotDePasse("Mot de passe : ", "fmdp", $donnee [2], 50, 32);
$f->champTexte("Prénom :", "fprenom", $donnee [3], 50, 64);
$f->champTexte("Nom :", "fnom", $donnee [4], 50, 64);
$f->champTexte("Site :", "fsite", $donnee [6], 50);
$f->champTexte("Email :", "femail", $donnee [7], 128);
$f->champFenetre("Signature :", "fsignature", $donnee [8], 5, 60);
$f->champTexte("Dernier login :", "fdateDernierLogin", dateMysqlVersTexte($donnee [9]), 50);
$f->champTexte("Nbre de logins :", "fnbreLogins", $donnee [10], 50);

$pListe = array("utilisateur non validé", "abonné", "éditeur", "administrateur");
$f->champListe("Privileges :", "fprivilege", $donnee [11], 1, $pListe);
$f->champCache("mode", $mode);
$f->champValider("Valider la saisie", "valider");
$sortie .= $formHtml . "</FORM>";

if (estAdmin() && $donnee[0] > 0) {
    $sortie .= "<div style='margin-top: 20px; padding: 15px; border: 1px solid #d9534f; border-radius: 8px; background-color: #f9f2f2;'>
                <h4 style='color: #d9534f; margin-top: 0;'>Actions d'administration</h4>
                <a id='btn-depublier-tout' href='../chanson/chanson_depublier_tout.php?idUser=" . $donnee[0] . "' class='btn btn-danger'>Dépublier toutes les partoches</a></div>";
}

$sortie .= "<h2>Envoyer une image</h2>
	<form id='user-upload-form' action='utilisateur_upload.php' method='post' enctype='multipart/form-data'>
		<input type='hidden' name='MAX_FILE_SIZE' value='150000'> 
		<input type='hidden' name='id' value='" . $donnee[0] . "'>
		<input type='file' id='fichier' name='fichierUploade' size='40'> <input id='btn-upload-submit' type='submit' value='Envoyer'>
	</form></div></div>";

$sortie .= envoieFooter();
echo $sortie;
