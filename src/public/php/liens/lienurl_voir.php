<?php

/**
 * Affiche un lien associé à une chanson avec une mise en page harmonisée.
 * @param array $lien Données du lien (id, nomTable, idTable, url, type, description)
 * @return string HTML du lien
 */
function afficheLien($lien): string
{
    $url = $lien[3];
    $type = $lien[4];
    $description = $lien[5];
    
    // Nettoyage du type pour détection vidéo
    $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
    $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
    $type_propre = strtolower(str_replace($search, $replace, $type));
    
    $is_video = ($type_propre === "video");
    $label_bouton = $is_video ? "Regarder la vidéo" : "Suivre le lien";
    $icone_bouton = $is_video ? "glyphicon-play" : "glyphicon-link";

    $contenuHtml = "<div class='col-xs-12' style='margin-bottom: 20px;'>";
    $contenuHtml .= "  <div class='well well-sm' style='overflow: hidden;'>";
    $contenuHtml .= "    <div class='row'>";

    if ($is_video) {
        // Layout VIDÉO : Vidéo à gauche, Texte à droite
        $url_embed = prepareYoutubeEmbedUrl($url);
        $contenuHtml .= "      <div class='col-sm-6 col-md-5'>";
        $contenuHtml .= "        <div class='embed-responsive embed-responsive-16by9'>";
        $contenuHtml .= "          <iframe class='embed-responsive-item' src='$url_embed' allowfullscreen></iframe>";
        $contenuHtml .= "        </div>";
        $contenuHtml .= "      </div>";
        $contenuHtml .= "      <div class='col-sm-6 col-md-7'>";
        $contenuHtml .= "        <h3 style='margin-top:0;'>$type</h3>";
        $contenuHtml .= "        <p class='text-muted' style='margin-bottom:15px;'>$description</p>";
        $contenuHtml .= "        <a href='$url' target='_blank' class='btn btn-danger btn-sm'><i class='glyphicon $icone_bouton'></i> Voir sur YouTube</a>";
        $contenuHtml .= "      </div>";
    } else {
        // Layout LIEN CLASSIQUE : Texte et bouton
        $contenuHtml .= "      <div class='col-xs-12'>";
        $contenuHtml .= "        <h3 style='margin-top:0;'>$type</h3>";
        $contenuHtml .= "        <p>$description</p>";
        $contenuHtml .= "        <a href='$url' target='_blank' class='btn btn-info btn-sm'><i class='glyphicon $icone_bouton'></i> $label_bouton</a>";
        $contenuHtml .= "      </div>";
    }

    $contenuHtml .= "    </div>"; // Fin row
    $contenuHtml .= "  </div>"; // Fin well
    $contenuHtml .= "</div>"; // Fin col-xs-12

    return $contenuHtml;
}

/**
 * Transforme une URL YouTube classique en URL Embed.
 * @param string $url URL d'origine
 * @return string URL formatée pour iframe
 */
function prepareYoutubeEmbedUrl(string $url): string
{
    // On vire les paramètres superflus (&t=..., etc.)
    $url = explode("&", $url)[0];
    // Gestion youtu.be vs youtube.com
    $url = str_replace("youtu.be/", "youtube.com/embed/", $url);
    $url = str_replace("watch?v=", "embed/", $url);
    // On s'assure d'avoir bien /embed/
    if (strpos($url, "youtube.com/embed/") === false) {
        $url = str_replace("youtube.com/", "youtube.com/embed/", $url);
    }
    return $url;
}

/**
 * @deprecated Cette fonction est intégrée dans afficheLien pour un meilleur layout.
 */
function afficheVignetteSiVideoYoutube(string $type, string $url): string
{
    return ""; // Plus besoin, géré dans afficheLien
}
