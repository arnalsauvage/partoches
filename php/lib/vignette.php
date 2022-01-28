<?php

// function afficheVignette($image)
// function creation_vignette($image,$largeur="",$hauteur="",$source="",$destination="",$prefixe = "")
// function ajoute_fichier_BD($nomFichier,$connexion)
// function supprime_fichier_BD($nomFichier,$connexion)
// function actualise_fichier_BD($nomFichier,$connexion)
const LOGS_FICHIERLOG_HTM = "../logs/fichierlog.htm";
function afficheVignette($image, $cheminImages, $cheminVignettes, $alt='vignette')
{
    global $largeur_max_vignette;
    global $hauteur_max_vignette;

    // Si la vignette n'existe pas
    if (!file_exists($cheminVignettes . $image)) {
        // On crée une vignette
        creation_vignette($image, $largeur_max_vignette, $hauteur_max_vignette, $cheminImages, $cheminVignettes);
    }
    // On retourne le code de la vignette
    $alt = echappeGuillemetSimple($alt);
    return ("<img class = 'vignette' loading='lazy' alt='$alt' src='$cheminVignettes$image'>");
}

function echappeGuillemetSimple($chaine)
{
    return str_replace("'", "&#39;", $chaine);
}

/**
 * ************************************************************************
 */
/* Script modifié par Mars@neomars.com */
/* D'après "Creation de vignette par huginnus" huginnus@croquisse.com */
/* Ajout de l'extension PNG et WBMP ainsi que la vérification de l'extension */
/* ------------------------------------------------------------------------- */
/* Attention à la version de PHP (PHP 4 >= 4.0.1, PHP 5) */
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
/* $largeur : largeur max de la vignette */
/* $hauteur : hauteur max de la vignette */
/* $source: Chemin relatif du répertoire de l'image originale */
/* $destination: Chemin relatif du répertoire de l'image réduite */
/* $prefixe : Prefixe de la vignette */
/**
 * ***********************************
 */
function creation_vignette($image, $largeur = "", $hauteur = "", $source = "", $destination = "", $prefixe = "")
{
    global $cheminVignettes, $largeur_max_vignette, $hauteur_max_vignette, $cheminImages;

    if ($destination == "") {
        $destination = $cheminVignettes;
    }
    if ($largeur == "") {
        $largeur = $largeur_max_vignette;
    }
    if ($hauteur == "") {
        $hauteur = $hauteur_max_vignette;
    }
    if ($source == "") {
        $source = $cheminImages;
    }

    if (!file_exists($source . $image)) {
        $log = "vignette.php : function creation_vignette : Le fichier source $source$image n'a pas été trouvé.";
        // ecritFichierLog(LOGS_FICHIERLOG_HTM, $log);
        // echo $log;
        return false;
    }
    // On verifie que l'extention du fichier est bien une image jpg,jpeg ou gif
    $ext = strtolower(strrchr($image, '.'));
    if ($ext == ".jpg" || $ext == ".jpeg" || $ext == ".gif" || $ext == ".png") {
        $size = getimagesize($source . $image);
        $largeur_src = $size [0];
        $hauteur_src = $size [1];

        // 2ieme verification -> on verifie que le type du fichier est un jpg,jpeg,gif ou bmp
        // rappel
        // retourne un tableau de 4 éléments.
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
                $image_src = imagecreatefromgif($source . $image);
            }
            if ($size [2] == 2) {
                // format jpg ou jpeg
                $image_src = imagecreatefromjpeg($source . $image);
                // echo 'image : $source / $image';
            }
            if ($size [2] == 3) {
                // format png
                $image_src = imagecreatefrompng($source . $image);
            }

            // on verifie que l'image source ne soit pas plus petite que l'image de destination
            if ($hauteur_max_vignette=="") {
                $ratio = $largeur_src / $largeur_max_vignette;
                $hauteur = round ( $hauteur_src / $ratio);
                $largeur = $largeur_max_vignette;
            }
            if ($largeur_max_vignette=="") {
                $ratio = $hauteur_src / $hauteur_max_vignette;
                $largeur = round( $largeur_src/$ratio);
                $hauteur = $hauteur_max_vignette;
            }
            $image_dest = imagecreatetruecolor($largeur, $hauteur);
            imagecopyresized($image_dest, $image_src, 0, 0, 0, 0, $largeur, $hauteur, $largeur_src, $hauteur_src);

            $log = "vignette.php Image : $image, largeur : " . round($largeur_src * $ratio) . ", $hauteur : " . round($hauteur_src * $ratio) . ", source : $source, destination : $destination";
            // ecritFichierLog(LOGS_FICHIERLOG_HTM, $log);
            if (!imagejpeg($image_dest, $destination . $prefixe . $image)) {
                $log = "la création de la vignette a echoué pour l'image $destination$prefixe$image";
                // ecritFichierLog(LOGS_FICHIERLOG_HTM, $log);
                return false;
            }
        } // fin du size
    } // fin de l'extension
    return true;
}