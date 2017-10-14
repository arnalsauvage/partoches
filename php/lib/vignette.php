<?php

// function afficheVignette($image)
// function creation_vignette($image,$largeur="",$hauteur="",$source="",$destination="",$prefixe = "")
// function ajoute_fichier_BD($nomFichier,$connexion)
// function supprime_fichier_BD($nomFichier,$connexion)
// function actualise_fichier_BD($nomFichier,$connexion)
function afficheVignette($image, $cheminImages, $cheminVignettes) {
	global $largeur_max;
	global $hauteur_max;
	
	// Si la vignette n'existe pas
	if (! file_exists ( $cheminVignettes . $image ))
		// On crée une vignette
		creation_vignette ( $image, $largeur_max, $hauteur_max, $cheminImages, $cheminVignettes, "" );
	// On retourne le code de la vignette
	return ("<IMG class = 'vignette' SRC='$cheminVignettes$image'>");
}
/**
 * ************************************************************************
 */
/* Script modifi� par Mars@neomars.com */
/* D'apr�s "Creation de vignette par huginnus" huginnus@croquisse.com */
/* Ajout de l'extension PNG et WBMP ainsi que la v�rification de l'extension */
/* ------------------------------------------------------------------------- */
/* Attention � la version de PHP (PHP 4 >= 4.0.1, PHP 5) */
/**
 * ************************************************************************
 */
/**
 * ************************************************************************
 */

/**
 * ************************************
 */
/* CREATION DE VIGNETTE */
/* ------------------------------------- */
/* $image: Nom de l'image originale */
/* $largeur_max : largeur max de la vignette */
/* $hauteur_max : hauteur max de la vignette */
/* $source: Chemin relatif du r�pertoire de l'image originale */
/* $destination: Chemin relatif du r�pertoire de l'image r�duite */
/* $prefixe : Prefixe de la vignette */
/**
 * ***********************************
 */
function creation_vignette($image, $largeur = "", $hauteur = "", $source = "", $destination = "", $prefixe = "") {
	global $cheminVignettes, $largeur_max, $hauteur_max, $cheminImages;
	
	if ($destination == "")
		$destination = $cheminVignettes;
	if ($largeur == "")
		$largeur = $largeur_max;
	if ($hauteur == "")
		$hauteur = $hauteur_max;
	if ($source == "")
		$source = $cheminImages;
	
	if (! file_exists ( $source . $image )) {
		$log = "vignette.php : function creation_vignette : Le fichier source $source$image n'a pas �t� trouv�.";
		ecritFichierLog ( "../logs/fichierlog.htm", $log );
		return false;
	}
	// On verifie que l'extention du fichier est bien une image jpg,jpeg ou gif
	$ext = strtolower ( strrchr ( $image, '.' ) );
	if ($ext == ".jpg" || $ext == ".jpeg" || $ext == ".gif" || $ext == ".png") {
		$size = getimagesize ( $source . $image );
		$largeur_src = $size [0];
		$hauteur_src = $size [1];
		
		// 2ieme verification -> on verifie que le type du fichier est un jpg,jpeg,gif ou bmp
		// rappel
		// retourne un tableau de 4 �l�ments.
		// L'index 0 contient la largeur.
		// L'index 1 contient la longueur.
		// L'index 2 contient le type de l'image :
		// 1 = GIF ,
		// 2 = JPG ,
		// 3 = PNG ,
		// 4 = SWF ,
		// 5 = PSD ,
		// 6 = BMP ,
		// 7 = TIFF (Ordre des octets Intel),
		// 8 = TIFF (Ordre des octets Motorola),
		// 9 = JPC ,
		// 10 = JP2 ,
		// 11 = JPX ,
		// 12 = JB2 ,
		// 13 = SWC ,
		// 14 = IFF .
		// Ces valeurs correspondent aux constantes IMAGETYPE qui ont �t� ajout�es en PHP 4.3
		
		// $size[2] -> type de l'image : 1 = GIF , 2 = JPG,JPEG
		if ($size [2] == 1 || $size [2] != 2 || $size [2] != 3 || $size [2] != 6) {
			if ($size [2] == 1) {
				// format gif
				$image_src = imagecreatefromgif ( $source . $image );
			}
			if ($size [2] == 2) {
				// format jpg ou jpeg
				$image_src = imagecreatefromjpeg ( $source . $image );
			}
			if ($size [2] == 3) {
				// format png
				$image_src = imagecreatefrompng ( $source . $image );
			}
			if ($size [2] == 6) {
				// format bmp
				$image_src = imagecreatefromwbmp ( $source . $image );
			}
			
			// on verifie que l'image source ne soit pas plus petite que l'image de destination
			if ($largeur_src > $largeur_max or $hauteur_src > $hauteur_max) {
				// si la largeur est plus grande que la hauteur
				if ($hauteur_src <= $largeur_src) {
					$ratio = $largeur / $largeur_src;
				} else {
					$ratio = $hauteur / $hauteur_src;
				}
			} else {
				$ratio = 1; // l'image cr�ee sera identique � l'originale
			}
			
			$image_dest = imagecreatetruecolor ( round ( $largeur_src * $ratio ), round ( $hauteur_src * $ratio ) );
			imagecopyresized ( $image_dest, $image_src, 0, 0, 0, 0, round ( $largeur_src * $ratio ), round ( $hauteur_src * $ratio ), $largeur_src, $hauteur_src );
			
			$log = "vignette.php Image : $image, largeur : " . round ( $largeur_src * $ratio ) . ", $hauteur : " . round ( $hauteur_src * $ratio ) . ", source : $source, destination : $destination";
			ecritFichierLog ( "../logs/fichierlog.htm", $log );
			if (! imagejpeg ( $image_dest, $destination . $prefixe . $image )) {
				$log = "la cr�ation de la vignette a echoué pour l'image $destination$prefixe$image";
				ecritFichierLog ( "../logs/fichierlog.htm", $log );
				return false;
			}
		} // fin du size
	} // fin de l'extension
}

/* --------------------------------------------------------------------------------------------- */

// Cette fonction ajoute un fichier � la table images de la base de donnees
function ajoute_fichier_BD($nomFichier, $connexion) {
	$tabInfos = getimagesize ( $nomFichier );
	$largeur = $tabInfos [0];
	$hauteur = $tabInfos [1];
	$poids = filesize ( $nomFichier );
	$fichier = substr ( strrchr ( $nomFichier, '/' ), 1 );
	$repertoire = substr ( $nomFichier, 0, strlen ( $nomFichier ) - strlen ( $fichier ) );
	$marequete = "INSERT INTO image (nomFichier, repertoire, largeur, hauteur, poids, tags, hits, occurences) VALUES ('$fichier','$repertoire', $largeur, $hauteur, $poids,'',0,1)";
	$resultat = ExecRequete ( $marequete, $connexion );
}
function supprime_fichier_BD($nomFichier, $connexion) {
	$marequete = "DELETE FROM image WHERE nomFichier = '$nomFichier'";
	$resultat = ExecRequete ( $marequete, $connexion );
}
function actualise_fichier_BD($nomFichier, $connexion) {
	$tabInfos = getimagesize ( $nomFichier );
	$largeur = $tabInfos [0];
	$hauteur = $tabInfos [1];
	$poids = filesize ( $nomFichier );
	$fichier = substr ( strrchr ( $nomFichier, '/' ), 1 );
	$repertoire = substr ( $nomFichier, 0, strlen ( $nomFichier ) - strlen ( $fichier ) );
	$marequete = "UPDATE image SET nomFichier='$fichier', largeur='$largeur', hauteur='$hauteur', repertoire='$repertoire', poids = $poids WHERE nomFichier='$fichier'";
	$resultat = ExecRequete ( $marequete, $connexion );
}

?>