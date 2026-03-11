<?php
require __DIR__ . "/../lib/utilssi.php";
require __DIR__ . "/../document/Document.php";
require_once __DIR__ . "/../lib/vignette.php";

// On vérifie que l'utilisateur est connecté
if (!isset ($_SESSION ['user'])) {
    echo "Vous devez vous authentifier !";
    return (0);
}

// On vérifie qu'on a un fichier joint
if (!isset ($_FILES ['fichierUploade'])) {
    echo "Pas de fichier joint !";
    return (0);
}

$autorisees = "gif jpg png jpeg";
$repertoire = "../images/utilisateur/";
if (!file_exists($repertoire)) {
    mkdir($repertoire, 0755);
    // echo " -=> Création du repertoire $repertoire réussi<br>";
}

// taille autorisées (min & max -- en octets)
$file_min_size = 500;
$file_max_size = 150000;
// On vérifie la présence d'un fichier à uploader
if (($_FILES ['fichierUploade'] ['size'] < $file_min_size) || ($_FILES ['fichierUploade'] ['size'] > $file_max_size)) {
    echo "La taille du fichier doit être comprise entre 1 et $file_max_size octets ! ";
    return (0);
}

// dossier où sera déplacé le fichier
$tmp_file = $_FILES ['fichierUploade'] ['tmp_name'];
if (!is_uploaded_file($tmp_file)) {
    $errors ['fichierUploade'] = "le fichier est introuvable";
    echo $errors ['fichierUploade'];
    return 0;
}

// on vérifie l'extension
$path = $_FILES ['fichierUploade'] ['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION); // on récupère l'extension

if (strstr($autorisees, $ext) == FALSE) {
    $errors ['fichierUploade'] = "le fichier n'a pas une extension autorisée ($ext) .";
    $errors ['fichierUploade'] .= "Extensions autorisées :  . $autorisees";
    echo $errors ['fichierUploade'];
    return 0;
}

// On met le nom au propre pour éviter les pb de caractères accentués
$name_file = renommeFichierChanson($path); // on crée un nom compatible url
//$name_file = urlencode($name_file);

//// On enregistre notre nom de fichier en BDD, on récupère un n°de version
//creeModifieDocument($name_file, $_FILES ['fichierUploade'] ['size'], "songbook", $_POST ['id']);
//$doc = chercheDocumentNomTableId($name_file, "songbook", $_POST ['id']);
//$name_file = str_replace(".$ext", "-v" . ($doc [4]), $path) . ".$ext";

// Si le formulaire est validé, on copie le fichier dans le dossier de destination
if (!move_uploaded_file($tmp_file, $repertoire . $name_file)) {
    $errors ['fichierUploade'] = "Il y a des erreurs! Impossible de copier le fichier dans le dossier cible";
    echo $errors ['fichierUploade'];
    return 0;
}

// On redirige vers la liste des songbooks
header('Location: ./utilisateur_form.php?id=' . $_POST ['id']);
// }
// echo "Vous �tes identifi� avec : " . $email . "<BR>";
// $texte = " Bonjour, un fichier ($toto_name) a �t� upload� sur http://medina.arnaud.free.fr/$repertoire, par l'ip $REMOTE_ADDR, identifi� avec le nom $email.";
// $texte = $texte . "\n" . date ( "D M j G:i:s T Y" );
// mail ( "medina.arnaud@free.fr", "Fichier upload� sur http://medina.arnaud.free.fr", $texte, "webmaster@medina.arnaud.free.fr" );
// echo "Ceci est un espace privé, merci de le respecter.<BR>";
// echo " Votre adresse IP ($REMOTE_ADDR) a �t� transmise par mail au webmaster du site, tout abus pourra faie l'objet d'une plainte.<BR>";
// echo "Texte mail : $texte";

function renommeFichierChanson($nomFichier)
{
    /*
    $trans = array(
        "#" => "diese",
        "strm" => "strum");
    */
    $nomFichier = str_replace(
        array(
            'à', 'â', 'ä', 'á', 'ã', 'å',
            'î', 'ï', 'ì', 'í',
            'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
            'ù', 'û', 'ü', 'ú',
            'é', 'è', 'ê', 'ë',
            'ç', 'ÿ', 'ñ', '#'
        ),
        array(
            'a', 'a', 'a', 'a', 'a', 'a',
            'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u',
            'e', 'e', 'e', 'e',
            'c', 'y', 'n', "Diese"
        ),
        $nomFichier
    );

    // 	$nomFichier = strtr_unicode( $nomFichier, $trans );
    // 	$nomFichier = strtr_unicode( $nomFichier, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
    // 								 		"aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn" );
    return $nomFichier;
}
