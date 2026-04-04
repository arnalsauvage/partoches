<?php

require_once PHP_DIR . "/navigation/Footer.php";
require_once PHP_DIR . "/lib/Image.php";

if (!isset($FichierHtml)) {
    $FichierHtml = 1;

    function ancre($url, $libelle, $classe = -1, $nouvellefenetre = -1, $titre=-1)
    {
        $optionClasse = ($classe != -1) ? " class=\"$classe\"" : "";
        $optionTitre = ($titre != -1) ? " title=\"" . htmlspecialchars($titre) . "\"" : "";
        $optionFenetre = ($nouvellefenetre != -1) ? ' target="_blank"' : "";
        
        $urlPropre = str_replace('&amp;', '&', $url);
        $urlPropre = str_replace('&', '&amp;', $urlPropre);
        
        return "<a href=\"$urlPropre\"$optionFenetre$optionClasse$optionTitre>$libelle</a>";
    }

    function titre($texte, $niveau)
    {
        return "<h$niveau>$texte</h$niveau>";
    }

    function image($urlImage, $largeur = -1, $hauteur = -1, $alt = "image décorative", $class = "")
    {
        $attrLargeur = ($largeur != -1 && $largeur <> "100%") ? " width=\"$largeur\"" : "";
        $attrHauteur = ($hauteur != -1 && $hauteur <> "100%") ? " height=\"$hauteur\"" : "";
        return "<img src=\"$urlImage\"" . $attrLargeur . $attrHauteur . " alt=\"$alt\" class=\"$class\">\n";
    }

    function affichePochette($nomFichier, $id, $largeur = 48, $hauteur = 48)
    {
        if (empty($nomFichier) || empty($id)) return fallbackPochette($largeur, $hauteur);
        $tailleVignette = ($largeur > 100) ? 'sd' : 'mini';
        $relPath = $id . "/" . $nomFichier;
        $urlVignette = Image::getThumbnailUrl($relPath, $tailleVignette);
        if (str_contains($urlVignette, 'icone_musique.png')) return fallbackPochette($largeur, $hauteur);
        return "<img src=\"$urlVignette\" width=\"$largeur\" height=\"$hauteur\" alt=\"couverture\" class=\"img-thumbnail\" loading=\"lazy\" style=\"object-fit: cover;\">";
    }

    function fallbackPochette($largeur, $hauteur)
    {
        $fontSize = floor($largeur / 2);
        return "<div class=\"text-center img-thumbnail\" style=\"width:{$largeur}px; height:{$hauteur}px; display:flex; align-items:center; justify-content:center; background:#f9f9f9; border:1px solid #ddd;\">
                    <span class=\"glyphicon glyphicon-cd\" style=\"font-size:{$fontSize}px; color:#ccc;\" title=\"Pochette absente\"></span>
                </div>";
    }

    function champSelect($liste, $numero, $nom)
    {
        $champSelect = "<select name=$nom size=\"1\">";
        $choix = 0;
        while ($ligne = $liste->fetch_row()) {
            $choix++;
            $selected = ($numero == $choix) ? "selected " : "";
            $champSelect .= "<option $selected value=$choix>" . $ligne[1] . "</option>";
        }
        return $champSelect . "</select>";
    }

    function ecritHtml($texte)
    {
        return $texte;
    }

    function entreBalise($texte, $balise)
    {
        return "<" . $balise . "> " . htmlentities($texte) . "</" . $balise . ">";
    }

    function redirection($url)
    {
        if (headers_sent()) echo '<meta http-equiv="refresh" content="0;URL=' . $url . '">';
        else header("Location: $url");
        exit;
    }

    function lienCliquable($texte)
    {
        $texte = preg_replace('/https?:\/\/[\w\-\.!~#?&=+\*\'"(),\/]+/','<a href="$0">$0</a>', $texte);
        return str_replace("href=\"www.", "href=\"http://www.", $texte);
    }

    function transformerAdressesEnLiens(int $indice, array $tableau, $debug_fonc=false): array
    {
        if (!str_contains($tableau[$indice], "<")) {
            $chaine = $tableau[$indice];
            $tableau[$indice] = lienCliquable($tableau[$indice]);
        } else {
            $chaine = $tableau[$indice];
        }
        return array($chaine, $tableau);
    }

    function ajouteLiens($texte)
    {
        $indice = 0; $niveau = 0; $tableau = array("");
        $longueur = strlen($texte);
        for ($i = 0; $i < $longueur; $i++) {
            if ($texte[$i] == "<") {
                if (($i > 0) && ($niveau == 0)) { $indice++; $tableau[$indice] = ""; }
                $niveau++;
            }
            if ($texte[$i] == ">") {
                $niveau--; $tableau[$indice] .= $texte[$i];
                if ($niveau == 0) { $indice++; $tableau[$indice] = ""; }
            } else { $tableau[$indice] .= $texte[$i]; }
        }
        for ($j = 0; $j <= $indice; $j++) {
            list($unused, $tableau) = transformerAdressesEnLiens($j, $tableau);
        }
        return implode($tableau);
    }

    function envoieHead($titrePage, $feuilleCss)
    {
        $siteTitle = $_SESSION['titreSite'] ?? 'Partoches Canopée';
        $fullTitle = ($titrePage && !str_contains(strtolower($titrePage), strtolower($siteTitle))) ? "$siteTitle - $titrePage" : ($titrePage ?: $siteTitle);
        
        $relPath = (strpos($_SERVER['PHP_SELF'], '/php/') !== false) ? "../../" : "";
        $v = time();

        $head = <<<HTML
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{$relPath}favicon.ico" type="image/x-icon">
    <link href="{$relPath}css/bootstrap.min.css" rel="stylesheet">
    <link href="{$relPath}css/jquery-ui.1.12.1.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{$relPath}css/styles-communs.css?v=$v">
    <link rel="stylesheet" type="text/css" href="{$relPath}css/composants-canopee.css?v=$v">
    <link rel="stylesheet" type="text/css" href="{$relPath}css/params.css?v=$v">
    <link rel="stylesheet" media="screen" type="text/css" title="resolution" href="$feuilleCss" />
    
    <script src="{$relPath}js/jquery-1.12.4.min.js"></script>
    <script src="{$relPath}js/utilsJquery.js"></script>
    <script src="{$relPath}js/jquery-ui.1.12.1.min.js"></script>
    <script src="{$relPath}js/bootstrap.3.2.0.min.js"></script>
    <link href="{$relPath}css/toastr.min.css" rel="stylesheet" type="text/css">
    <script src="{$relPath}js/toastr.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <title>$fullTitle</title>
</head>
<body>
HTML;
        return $head;
    }

    function envoieFooter()
    {
        $footer = new Footer();
        $footerHtml = $footer->getHtml();

        if (!$footerHtml) {
            $footerHtml = <<<HTML
            Nom du club : 
            <a href="http://www.top5.re" target="_blank">Site web</a> | 
            <a href="https://padlet.com/top5asso/top5-atelier-d-butant-interm-diaire-wwiy3x9lz44a6vyx">padlet </a> | 
            <a href="http://partoches.canopee-musique.fr" target="_blank">partoches</a>
            <div class="footer-social-icons">
                <a href="https://www.youtube.com/channel/UCFKyqYcs5cnML-EgPgYmwdg" target="_blank" title="YouTube"><img src="../../images/icones/youtube.png" width="32" alt="YouTube"></a>
                <a href="https://twitter.com/top5ukeclub" target="_blank" title="Twitter"><img src="../../images/icones/socmark_twitter.png" width="32" alt="Twitter"></a>
            </div>
HTML;
        }

        $retour = <<<HTML
<footer class="site-footer">
    <div class="container">
        <div class="footer-content">$footerHtml</div>
        <div class="footer-legal">
            <a href="../../html/mentionsLegales.html" target="_blank">Mentions légales</a>
            <span class="separator">|</span>
            <a href="../../html/merci.html" target="_blank">Mercis</a>
            <span class="separator">|</span>
            <span>&copy; 2018 - 2026 Partoches Canopée</span>
        </div>
    </div>
</footer>

<div class="modal fade" id="modalConfirmation" tabindex="-1" role="dialog" aria-labelledby="modalConfirmationLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content modal-django-content">
      <div class="modal-header modal-django-header">
        <h2 class="modal-title modal-django-title" id="modalConfirmationLabel"><i class="glyphicon glyphicon-warning-sign"></i> Confirmation</h2>
      </div>
      <div class="modal-body text-center modal-django-body">
        <p id="modalConfirmationMessage" class="modal-django-text"></p>
      </div>
      <div class="modal-footer modal-django-footer">
        <button type="button" class="btn btn-default btn-sm btn-modal-django btn-modal-django-cancel" data-dismiss="modal">Annuler</button>
        <button type="button" id="btnConfirmAction" class="btn btn-danger btn-sm btn-modal-django btn-modal-django-confirm">Confirmer</button>
      </div>
    </div>
  </div>
</div>
<script src="../../js/precise-star-rating.js"></script>
</body>
</html>
HTML;
        return $retour;
    }

    function myUrlEncode($string)
    {
        $entities = array('%25', '%20', '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D');
        $replacements = array("%", ' ', '!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "#", "[", "]");
        return str_replace($replacements, $entities, $string);
    }

    function simplifieNomFichier($nomOriginal){
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r'
        );
        $_nomSimplifie = strtr($nomOriginal,$table) ;
        $_nomSimplifie = str_replace("#","diese", $_nomSimplifie);
        $_nomSimplifie = str_replace("'","", $_nomSimplifie);
        $_nomSimplifie = str_replace(" ","-", $_nomSimplifie);
        return $_nomSimplifie;
    }
}
