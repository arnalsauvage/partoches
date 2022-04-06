<?php
const FICHIER_UPLOADE = 'fichierUploade';
require("../document/document.php");
require("../lib/utilssi.php");

global $_DOSSIER_CHANSONS;

// On vérifie que l'utilisateur est connecté
if (!isset ($_SESSION ['user'])) {
    echo "Vous devez vous authentifier !";
    return (0);
}

// On vérifie qu'on a un fichier joint
if (!isset ($_FILES [FICHIER_UPLOADE])) {
    echo "Pas de fichier joint";
    return (0);
}

// TODO : créer un paramètre d'application modifiable par l'admin
$autorisees = "pdf doc docx gif jpg png swf mp3 odt ppt pptx svg crd txt m4a ogg mscz mid";

$repertoire = $_DOSSIER_CHANSONS . $_POST ['id'] . "/";
// echo "répertoire de destination : $repertoire";
if (!file_exists($repertoire)) {
    mkdir($repertoire, 0755);
    // echo " -=> Création du repertoire $repertoire réussi<br>";
}

// taille autorisée (min & max -- en octets)
$file_min_size = 1;
$file_max_size = 10000000;
// On vérifie la présence d'un fichier à uploader
if (($_FILES [FICHIER_UPLOADE] ['size'] < $file_min_size) || ($_FILES [FICHIER_UPLOADE] ['size'] > $file_max_size)) {
    echo "La taille du fichier doit être comprise entre 1 et $file_max_size octets ! ";
    return (0);
}

// dossier où sera déplacé le fichier
$tmp_file = $_FILES [FICHIER_UPLOADE] ['tmp_name'];
if (!is_uploaded_file($tmp_file)) {
    $errors [FICHIER_UPLOADE] = "le fichier est introuvable";
    echo $errors [FICHIER_UPLOADE];
    return 0;
}

// on vérifie l'extension
$path = $_FILES [FICHIER_UPLOADE] ['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION); // on récupère l'extension

if (!strstr($autorisees, $ext)) {
    $errors [FICHIER_UPLOADE] = "le fichier n'a pas une extension autorisée ($autorisees) .";
    $errors [FICHIER_UPLOADE] .= "Extensions autorisées :  . $autorisees";
    echo $errors [FICHIER_UPLOADE];
    return 0;
}

// On met le contenuFiltrer au propre pour éviter les pb de caractères accentués
$name_file = simplifieNomFichier($path); // on crée un contenuFiltrer compatible url
//$name_file = urlencode($name_file);

// On enregistre notre contenuFiltrer de fichier en BDD, on récupère un n°de version
creeModifieDocument($name_file, $_FILES ['fichierUploade'] ['size'], "chanson", $_POST ['id']);
$doc = chercheDocumentNomTableId($name_file, "chanson", $_POST ['id']);
$name_file = str_replace(".$ext", "-v" . ($doc [4]), $name_file) . ".$ext";

// Si le formulaire est validé, on copie le fichier dans le dossier de destination
if (!move_uploaded_file($tmp_file, $repertoire . $name_file)) {
    $errors [FICHIER_UPLOADE] = "Il y a des erreurs! Impossible de copier le fichier dans le dossier cible";
    echo $errors [FICHIER_UPLOADE];
    // TODO : on supprime    le fichier en bdd
    supprimeDocument($doc[0]);
    return 0;
}

// On récupère l'url du fichier envoyé
$get_the_file = "<a href=\"http://" . $_SERVER ['SERVER_NAME'] . dirname($_SERVER ['REQUEST_URI']) . "/" . $repertoire . $name_file . "\" target=\"_blank\">Accéder au fichier</a>";

// On redirige vers la liste des songbooks
header('Location: ./chanson_form.php?id=' . $_POST ['id']);
// }
// echo "Vous �tes identifié avec : " . $email . "<BR>";
// $texte = " Bonjour, un fichier ($toto_name) a �t� upload� sur http://medina.arnaud.free.fr/$repertoire, par l'ip $REMOTE_ADDR, identifi� avec le contenuFiltrer $email.";
// $texte = $texte . "\n" . date ( "D M j G:i:s T Y" );
// mail ( "medina.arnaud@free.fr", "Fichier uploadé sur http://medina.arnaud.free.fr", $texte, "webmaster@medina.arnaud.free.fr" );
// echo "Ceci est un espace privé, merci de le respecter.<BR>";
// echo " Votre adresse IP ($REMOTE_ADDR) a �t� transmise par mail au webmaster du site, tout abus pourra faie l'objet d'une plainte.<BR>";
// echo "Texte mail : $texte";