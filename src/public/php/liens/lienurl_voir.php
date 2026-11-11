<?php

/**
 * Affiche un lien associé à une chanson avec une mise en page harmonisée.
 * @param array $lien Données du lien (id, nomTable, idTable, url, type, description)
 * @return string HTML du lien
 */
function afficheLien($lien): string
{
    $rawUrl = $lien['url'] ?? $lien[3] ?? '';
    $rawType = $lien['type'] ?? $lien[4] ?? '';
    $rawDesc = $lien['description'] ?? $lien[5] ?? '';

    $url = htmlspecialchars($rawUrl, ENT_QUOTES);
    $type = htmlspecialchars($rawType, ENT_QUOTES);
    $description = htmlspecialchars($rawDesc, ENT_QUOTES);
    
    // Nettoyage du type pour détection vidéo
    $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
    $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
    $type_propre = strtolower(str_replace($search, $replace, $type));
    
    $is_video = ($type_propre === "video");
    $label_bouton = $is_video ? "Regarder la vidéo" : "Suivre le lien";
    $icone_bouton = $is_video ? "glyphicon-play" : "glyphicon-link";

    $contenuHtml = "<div class='lien-associe-carte'>";
    $contenuHtml .= "  <div class='well well-sm lien-associe-well'>";
    $contenuHtml .= "    <div class='row'>";

    if ($is_video) {
        // Layout VIDÉO : Vidéo à gauche, Texte à droite
        $url_embed = prepareYoutubeEmbedUrl($rawUrl);
        $contenuHtml .= "      <div class='col-sm-6 col-md-5'>";
        $contenuHtml .= "        <div class='embed-responsive embed-responsive-16by9'>";
        $contenuHtml .= "          <iframe class='embed-responsive-item' src='$url_embed' loading='lazy' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' referrerpolicy='strict-origin-when-cross-origin' allowfullscreen></iframe>";
        $contenuHtml .= "        </div>";
        $contenuHtml .= "      </div>";
        $contenuHtml .= "      <div class='col-sm-6 col-md-7'>";
        $contenuHtml .= "        <h3 class='lien-associe-titre'>$type</h3>";
        $contenuHtml .= "        <p class='text-muted lien-associe-desc'>$description</p>";
        $contenuHtml .= "        <a href='$url' target='_blank' rel='noopener noreferrer' class='btn btn-danger btn-sm'><i class='glyphicon $icone_bouton'></i> Voir sur YouTube</a>";
        $contenuHtml .= "      </div>";
    } else {
        // Layout LIEN CLASSIQUE : Texte et bouton
        $contenuHtml .= "      <div class='col-xs-12'>";
        $contenuHtml .= "        <h3 class='lien-associe-titre'>$type</h3>";
        $contenuHtml .= "        <p class='lien-associe-desc'>$description</p>";
        $contenuHtml .= "        <a href='$url' target='_blank' rel='noopener noreferrer' class='btn btn-info btn-sm'><i class='glyphicon $icone_bouton'></i> $label_bouton</a>";
        $contenuHtml .= "      </div>";
    }

    $contenuHtml .= "    </div>"; // Fin row
    $contenuHtml .= "  </div>"; // Fin well
    $contenuHtml .= "</div>";

    return $contenuHtml;
}

/**
 * Transforme une URL YouTube classique en URL Embed sans cookie publicitaire.
 * @param string $url URL d'origine
 * @return string URL formatée pour iframe YouTube No-Cookie
 */
function prepareYoutubeEmbedUrl(string $url): string
{
    // Extraction sécurisée de l'ID vidéo YouTube (11 caractères)
    if (preg_match('~(?:youtube(?:-nocookie)?\.com\/(?:watch\?.*v=|embed\/|shorts\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})~i', $url, $matches)) {
        return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
    }

    // Repli défensif pour URLs YouTube alternatives
    $url = explode("&", $url)[0];
    $url = str_replace(["youtu.be/", "watch?v="], ["youtube-nocookie.com/embed/", "embed/"], $url);
    if (strpos($url, "youtube-nocookie.com/embed/") === false && strpos($url, "youtube.com/embed/") === false) {
        $url = str_replace(["youtube.com/", "youtube-nocookie.com/"], "youtube-nocookie.com/embed/", $url);
    }
    $url = str_replace("youtube.com/embed/", "youtube-nocookie.com/embed/", $url);
    if (!preg_match('~^https?:\/\/~i', $url)) {
        $url = 'https://' . ltrim($url, '/');
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
