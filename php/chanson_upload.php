<?php
require ("lib/utilssi.php");
require ("document.php");
if (isset ( $_SESSION ['user'] )) {
	
	$autorisees = "pdf doc docx gif jpg png swf mp3 odt";
	$repertoire = "../data/chansons/" . $_POST ['id'] . "/";
	if (! file_exists ( $repertoire )) {
		mkdir ( $repertoire, 0755 );
		// echo " -=> Création du repertoire $repertoire réussi<br>";
	}
	// //////////////////////////////////////////////////////////////////////ADMIN
	// if ($email==$emailadmin)$_POST ['user']
	// {
	if (! isset ( $_FILES ['fichierUploade'] )) {
		
		echo "Pas de fichier joint";
		return (0);
	}
	
	// taille autorisées (min & max -- en octets)
	$file_min_size = 1;
	$file_max_size = 10000000;
	// On vérifie la présence d'un fichier à uploader
	if (($_FILES ['fichierUploade'] ['size'] < $file_min_size) || ($_FILES ['fichierUploade'] ['size'] > $file_max_size)) {
		echo "La taille du fichier doit être comprise entre 1 et $file_max_size octets ! ";
		return (0);
	}
	
	// dossier où sera déplacé le fichier
	$tmp_file = $_FILES ['fichierUploade'] ['tmp_name'];
	if (! is_uploaded_file ( $tmp_file )) {
		$errors ['fichierUploade'] = "le fichier est introuvable";
		echo $errors ['fichierUploade'];
		return 0;
	}
	
	$path = $_FILES ['fichierUploade'] ['name'];
	$ext = pathinfo ( $path, PATHINFO_EXTENSION ); // on récupère l'extension
	
	// on vérifie l'extension
	if (strstr ( $autorisees, $ext) == FALSE) 
	// reproduire cette syntaxe pour ajouter d'autre extension
	{
		$errors ['fichierUploade'] = "le fichier n'a pas une extension autorisée ($type_file) .";
		$errors ['fichierUploade'] .= "Extensions autorisées :  . $autorisees";
		echo $errors ['fichierUploade'];
		return 0;
	}
	
	// Si le formulaire est validé, on copie le fichier dans le dossier de destination
	if (empty ( $errors )) {
		
		creeModifieDocument ( $_FILES ['fichierUploade'] ['name'], $_FILES ['fichierUploade'] ['size'], "chanson", $_POST ['id'] );
		$doc = chercheDocumentNomTableId ( $_FILES ['fichierUploade'] ['name'],"chanson", $_POST ['id'] );
		
		$path = str_replace ( ".$ext" , "-v" . ($doc [4]), $path ) . ".$ext";
		echo "Path : $path;";
		$name_file = renommeFichier ( $path ); // on crée un nom compatible url
		$name_file = urlencode ( $name_file );
		
		if (! move_uploaded_file ( $tmp_file, $repertoire . $name_file )) {
			$errors ['fichierUploade'] = "Il y a des erreurs! Impossible de copier le fichier dans le dossier cible";
			echo $errors ['fichierUploade'];
			return 0;
		}
	}
	
	// On récupère l'url du fichier envoyé
	$get_the_file = "<a href=\"http://" . $_SERVER ['SERVER_NAME'] . dirname ( $_SERVER ['REQUEST_URI'] ) . "/" . $repertoire . $name_file . "\" target=\"_blank\">Accéder au fichier</a>";

}
header ( 'Location: ./chanson_voir.php?id=' . $_POST ['id'] );
// }
// echo "Vous �tes identifi� avec : " . $email . "<BR>";
// $texte = " Bonjour, un fichier ($toto_name) a �t� upload� sur http://medina.arnaud.free.fr/$repertoire, par l'ip $REMOTE_ADDR, identifi� avec le nom $email.";
// $texte = $texte . "\n" . date ( "D M j G:i:s T Y" );
// mail ( "medina.arnaud@free.fr", "Fichier upload� sur http://medina.arnaud.free.fr", $texte, "webmaster@medina.arnaud.free.fr" );
// echo "Ceci est un espace priv�, merci de le respecter.<BR>";
// echo " Votre adresse IP ($REMOTE_ADDR) a �t� transmise par mail au webmaster du site, tout abus pourra faie l'objet d'une plainte.<BR>";
// echo "Texte mail : $texte";
function renommeFichier($nomFichier) {
	// TODO : le remplacement n'est pas fait'
	$trans = array (
			"#" => "diese",
			"strm" => "strum" 
	);
	echo "renomme";
	$nomFichier = strtr ( $nomFichier, $trans );
	return strtr ( $nomFichier, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ ", "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn_" );
}
?>