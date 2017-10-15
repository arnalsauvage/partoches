<?php
require ("lib/utilssi.php");
$autorisees = "pdf doc docx gif jpg png swf mp3";
$repertoire = "../data/chansons/" . $_POST ['id'] . "/";
if (! file_exists ( $repertoire )) {
	mkdir ( $repertoire, 0755 );
	// echo " -=> Création du repertoire $repertoire réussi<br>";
}
// //////////////////////////////////////////////////////////////////////ADMIN
// if ($email==$emailadmin)$_POST ['user']
// {
if (isset ( $_FILES ['fichierUploade'] )) {
	// taille autorisées (min & max -- en octets)
	$file_min_size = 0;
	$file_max_size = 10000000;
	// On vérifie la présence d'un fichier à uploader
	if (($_FILES ['fichierUploade'] ['size'] > $file_min_size) && ($_FILES ['fichierUploade'] ['size'] < $file_max_size)) :
		// dossier où sera déplacé le fichier
		$tmp_file = $_FILES ['fichierUploade'] ['tmp_name'];
		if (! is_uploaded_file ( $tmp_file )) {
			$errors ['fichierUploade'] = "le fichier est introuvable";
		}
		
		// on vérifie l'extension
		$type_file = $_FILES ['fichierUploade'] ['type'];
		if (! strstr ( $type_file, 'jpg' ) && ! strstr ( $type_file, 'jpeg' ) && ! strstr ( $type_file, 'png' ) && ! strstr ( $type_file, 'gif' ) && ! strstr ( $type_file, 'txt' ) && ! strstr ( $type_file, 'rar' ) && ! strstr ( $type_file, 'zip' ) && ! strstr ( $type_file, 'pdf' ) && ! strstr ( $type_file, 'doc' ) && ! strstr ( $type_file, 'docx' )) 
		// reproduire cette syntaxe pour ajouter d'autre extension
		{
			$errors ['fichierUploade'] = "le fichier n'a pas une extension autorisée";
		}
		// Si le formulaire est validé, on copie le fichier dans le dossier de destination
		if (empty ( $errors )) {
			$path = $_FILES ['fichierUploade'] ['name'];
			$ext = pathinfo ( $path, PATHINFO_EXTENSION ); // on récupère l'extension
			$name_file = renommeFichier($_FILES ['fichierUploade'] ['name']); // on crée un nom compatible url
			while (file_exists( $repertoire . $name_file ))
			{
				$carsPossible = "012346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
				$char = substr($carsPossible, mt_rand(0, strlen($carsPossible)-1), 1);
				$name_file = $char."-".$name_file;
			}
			if (! move_uploaded_file ( $tmp_file, $repertoire . $name_file )) {
				$errors ['fichierUploade'] = "Il y a des erreurs! Impossible de copier le fichier dans le dossier cible";
			}
		}
		// Si le formulaire contient des erreurs, on annule le transfert du fichier
		
		// On récupère l'url du fichier envoyé
		$get_the_file = "<a href=\"http://" . $_SERVER ['SERVER_NAME'] . dirname ( $_SERVER ['REQUEST_URI'] ) . "/" . $repertoire . $name_file . "\" target=\"_blank\">Accéder au fichier</a>";
	 elseif ($_FILES ['fichierUploade'] ['size'] > $file_max_size) :
		$errors ['fichierUploade'] = "le fichier dépasse la limite autorisée";
		$get_the_file = "Pas de fichier joint";
	else :
		$get_the_file = "Pas de fichier joint";
	endif;
	
	// FIN DU SYSTEME D'UPLOAD
	// {
	// // On r�cup�re l'extension de fichiers sur 3 caract�res
	// $extension = substr($toto_name,-3) ;
	// // On va v�rifier que le fichier est un jpg, un png, un gif, un swf ou un mp3
	
	// if(stristr($autorisees,$extension))
	// {
	// copy($toto, "$repertoire/$toto_name");
	// echo "Le fichier $toto_name a �t� enregistr� dans le r�pertoire $repertoire.<BR>" ;
	// // Si le fichier est une image, on l'int�gre � la Base de donn�es
	// // et on calcule sa vignette pour la stocker dans le r�pertoire vignette
	// if ($extension=="jpg" or $extension=="gif" or $extension=="png")
	// {
	// $connexion = Connexion($LOGIN,$MOTDEPASSE,$mabase,$monserveur);
	// ajoute_fichier_BD("$repertoire/$toto_name",$connexion);
	// creation_vignette ($toto_name);
	// }
	// }
	// else
	// {
	// echo " Ce type de fichier (extension $extension) n'est pas pris en compte.<BR>" ;
	// echo "Les extensions autoris�es sont : " . $autorisees . "<BR>" ;
	// }
} else {
	echo "Le fichier n'a pas été reçu !!!<BR>";
}
header('Location: ./chanson_voir.php?id='.$_POST ['id']);
// }
// echo "Vous �tes identifi� avec : " . $email . "<BR>";
// $texte = " Bonjour, un fichier ($toto_name) a �t� upload� sur http://medina.arnaud.free.fr/$repertoire, par l'ip $REMOTE_ADDR, identifi� avec le nom $email.";
// $texte = $texte . "\n" . date ( "D M j G:i:s T Y" );
// mail ( "medina.arnaud@free.fr", "Fichier upload� sur http://medina.arnaud.free.fr", $texte, "webmaster@medina.arnaud.free.fr" );
// echo "Ceci est un espace priv�, merci de le respecter.<BR>";
// echo " Votre adresse  IP ($REMOTE_ADDR) a �t� transmise par mail au webmaster du site, tout abus pourra faie l'objet d'une plainte.<BR>";
// echo "Texte mail : $texte";
function renommeFichier($nomFichier)
{
//	TODO : le remplacement n'est pas fait'
	$trans = array("#" => "diese", "strm" => "strum");
	echo "renomme";
	$nomFichier = strtr($nomFichier, $trans);
	return strtr($nomFichier,
			"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ ",
			"aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn_"
			);
}
?>