<?php

/**
 * @param $lien
 * @param string $contenuHtml
 * @return string
 */
function afficheLien($lien): string
{
    $url = $lien[3];
    $type = $lien[4];
    $description = $lien[5];
    $contenuHtml = "
        <div class=\"col-xs-4 col-sm-3 col-md-2 centrer\">
            <h3> 
                <a href = '$url' target='_blank'>
                    $type
                </a>
            </h3>
        <p>  $description</p>
        </div>";

    $contenuHtml .= afficheVignetteSiVideoYoutube($type, $url);
    return $contenuHtml;
}

/**
 * @param mixed $type
 * @param mixed $url
 * @param string $contenuHtml
 * @return string
 */
function afficheVignetteSiVideoYoutube(string $type, string $url): string
{
    $contenuHtml = "";
    $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
    $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
    $type_sans_accent = str_replace($search, $replace, $type);
    if (strtolower($type_sans_accent) == "video") {
        // On vire les paramètres
        $url = explode("&", $url);
        $url = $url[0];
        // On remplace youtu.be par youtube.com
        $url = str_replace(".be/", "be.com/", $url);
        // On ajoute, si manquant, le mot clé embed
        $urlEmbedded = str_replace(".com/", ".com/embed/", $url);
        $urlEmbedded = str_replace("watch?v=", "", $urlEmbedded);
        $contenuHtml = "
        <div class=\"col-xs-8 col-sm-6 col-md-4 \">
        
            <iframe width='280' height='200' src='$urlEmbedded' title='YouTube video player' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>
        </div >
        ";
    }
    return $contenuHtml;
}