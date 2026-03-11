<?php

require_once PHP_DIR . "/navigation/Footer.php";
require_once PHP_DIR . "/lib/Image.php";
if (!isset ($FichierHtml)) {
    $FichierHtml = 1;
    // Fonction retournant le code HTML pour un lien hypertexte____________

    function ancre($url, $libelle, $classe = -1, $nouvellefenetre = -1, $titre=-1)
    {
        $optionClasse = "";
        $optionTitre = "";
        if ($nouvellefenetre == -1) {
            $nouvellefenetre = "";
        }
        else {
            $nouvellefenetre = 'target="_blank"';
        }
        if ($classe != -1) {
            $optionClasse = " class=\"$classe\"";
        }
        if ($titre != -1) {
            $titre = htmlspecialchars($titre);
            $optionTitre = " title=\"$titre\"";
        }
        
        // Protection W3C : on remplace les & par &amp; dans l'URL si ce n'est pas déjà fait
        $urlPropre = str_replace('&amp;', '&', $url);
        $urlPropre = str_replace('&', '&amp;', $urlPropre);
        
        return "<a href=\"$urlPropre\"" . " $nouvellefenetre$optionClasse$optionTitre>$libelle</a>";
    }

    function titre($texte, $niveau)
    {
        return "<h$niveau>$texte</h$niveau>";
    }

    function image($urlImage, $largeur = -1, $hauteur = -1, $alt = "image décorative", $class = "")
    {
        $attrLargeur = "";
        $attrHauteur = "";
        if (($largeur != -1) && ($largeur <> "100%")) {
            $attrLargeur = " width=\"$largeur\"";
        }
        if (($hauteur != -1) && ($hauteur <> "100%")) {
            $attrHauteur = " height=\"$hauteur\"";
        }
        return "<img src=\"$urlImage\"" . $attrLargeur . $attrHauteur . " alt=\"$alt\" class=\"$class\">\n";
    }

    function affichePochette($nomFichier, $id, $largeur = 48, $hauteur = 48)
    {
        if (empty($nomFichier) || empty($id)) {
            return fallbackPochette($largeur, $hauteur);
        }

        $tailleVignette = ($largeur > 100) ? 'sd' : 'mini';
        $relPath = $id . "/" . $nomFichier;
        $urlVignette = Image::getThumbnailUrl($relPath, $tailleVignette);

        if (str_contains($urlVignette, 'icone_musique.png')) {
            return fallbackPochette($largeur, $hauteur);
        }

        return "<img src=\"$urlVignette\" width=\"$largeur\" height=\"$hauteur\" alt=\"couverture\" class=\"img-thumbnail\" loading=\"lazy\" style=\"object-fit: cover;\">";
    }

    function fallbackPochette($largeur, $hauteur)
    {
        $fontSize = floor($largeur / 2);
        return "<div class=\"text-center img-thumbnail\" style=\"width:{$largeur}px; height:{$hauteur}px; display:flex; align-items:center; justify-content:center; background:#f9f9f9; border:1px solid #ddd;\">
                    <span class=\"glyphicon glyphicon-cd\" style=\"font-size:{$fontSize}px; color:#ccc;\" title=\"Pochette absente\"></span>
                </div>";
    }

    // Fin de la fonction Image____________________________________________

    // Fonction créant un champ SELECT
    // Liste contient toutes les valeurs duchamp select
    function champSelect($liste, $numero, $nom)
    {
        $champSelect = "";
        $champSelect .= "<select name = $nom size=\"1\">";
        $choix = 0;
        while ($ligne = $liste->fetch_row()) {
            $choix++;
            $champSelect .= "<option ";
            if ($numero == $choix) {
                $champSelect .= "selected ";
            }
            $champSelect .= "value=$choix>";
            $champSelect .= $ligne[1] . "</option>";
        }
        $champSelect .= " </select>";
        return $champSelect;
    }

    function ecritHtml($texte)
    {
        return $texte;
    }

    function entreBalise($texte, $balise)
    {
        return "<" . $balise . "> " . htmlentities($texte) . "</" . $balise . ">";
    }

    // Cette fonction donne l'instruction au navigateur de se rediriger
    // vers une autre adresse (aucun caractère n'a du être transmis,
    // pas m?me un espace ou  un retour de ligne
    function redirection($url)
    {
        if (headers_sent()) {
            print '<meta http-equiv="refresh" content="0;URL=' . $url . '">';
        }
        else {
            header("Location: $url");
        }
        exit;
    }

    // Cette fonction remplace une adresse url dans un texte par un lien cliquable
    function lienCliquable($texte)
    {
        $texte = preg_replace('/https?:\/\/[\w\-\.!~#?&=+\*\'"(),\/]+/','<a href="$0">$0</a>', $texte);

        //because you want the url to be an external link the href needs to start with 'http://'
        //simply replace any occurance of 'href="www.' into 'href="http://www."

        $texte = str_replace("href=\"www.", "href=\"http://www.", $texte);
        return $texte;
    }

    // Cette fonction remplacera dans le $texte les éléments de type http://www.bidule.com/machin en lien html
    function ajouteLiens($texte)
    {
        // On place d'abord le texte en tableaux où l'on sépare le texte pur du texte formaté html
        // parcours la chaine caractère par caractère
        // Quand la balise < est rencontrée, on augmente le niveau : il peut y a voir des < imbriqués
        // L'indice indique l'élément du tableau dans lequel le bout sera rangé

        $indice = 0;
        $niveau = 0;
        $tableau = array();
        $tableau[0] = "";
        $longueur = strlen($texte);
        for ($i = 0; $i < $longueur; $i++) {
            // si un nouvel ouvrant est découvert, on augmente l'indice et le niveau
            if ($texte[$i] == "<") {
                if (($i > 0) && ($niveau == 0)) {
                    $indice++;
                    $tableau[$indice] = "";
                }
                // Si l'ouvrant est un ouvrant imbriqué, on ajoute 1 à la variable niveau
                $niveau++;
            }
            // si un fermant est découvert, on diminue le niveau, et on le copie
            if ($texte[$i] == ">") {
                $niveau--;
                $tableau[$indice] .= $texte[$i];
                // Si le niveau est à nouveau à zéro, on est sorti du code, on peut créer une nouvelle ligne dans tableau
                if ($niveau == 0) {
                    $indice++;
                    $tableau[$indice] = "";
                }
            } else {// on copie le caractère dans le tableau[indice]
                $tableau[$indice] .= $texte[$i];
            }
        }
        // Pour chaque élément du tableau non HTML, on applique une expression régulière
        // transformant les adresses en liens
        $indice_max = $indice;
        $indice = 0;
        for ($indice = 0; $indice <= $indice_max; $indice++) {
            list($chaine, $tableau) = transformerAdressesEnLiens( $indice, $tableau);
        }
        $chaine = implode($tableau);
        return $chaine;
    }

    /**
     * @param $debug_fonc
     * @param int $indice
     * @param array $tableau
     * @return array
     */
    function transformerAdressesEnLiens( int $indice, array $tableau, $debug_fonc=false): array
    {
        if ($debug_fonc) {
            echo "tableau[$indice] : $tableau[$indice]\n";
        }

        if (!str_contains($tableau[$indice], "<")) {
            $chaine = $tableau[$indice];
            $tableau[$indice] = lienCliquable($tableau[$indice]);
            if ($debug_fonc) {
                echo "<br>chaine  remplacée : $chaine <br>\n";
            }
            if ($debug_fonc) {
                echo "<br>chaine  de remplacement : $tableau[$indice] <br>\n";
            }
        }
        return array($chaine, $tableau);
    }

    // Fin de la function ajouteLiens($texte)

    function envoieHead($titrePage, $feuilleCss)
    {
        $cssPath = PHP_DIR . "/../css/styles-communs.css";
        $v = file_exists($cssPath) ? filemtime($cssPath) : "";
        
        $siteTitle = $_SESSION['titreSite'] ?? 'Partoches Canopée';
        
        // On évite le bégaiement : si $titrePage contient déjà $siteTitle, on ne le rajoute pas
        if ($titrePage && str_contains(strtolower($titrePage), strtolower($siteTitle))) {
            $fullTitle = $titrePage;
        } else {
            $fullTitle = $titrePage ? "$siteTitle - $titrePage" : $siteTitle;
        }

        $head = <<<HTML
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../favicon.ico" type="image/x-icon">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/jquery-ui.1.12.1.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../css/styles-communs.css?v=$v">
    <link rel="stylesheet" media="screen" type="text/css" title="resolution" href="$feuilleCss" />
    
    <script src="../../js/jquery-1.12.4.min.js"></script>
    <script src="../../js/utilsJquery.js"></script>
    <script src="../../js/jquery-ui.1.12.1.min.js"></script>
    <script src="../../js/bootstrap.3.2.0.min.js"></script>
    
    <link href="../../css/toastr.min.css" rel="stylesheet" type="text/css">
    <script src="../../js/toastr.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <title>$fullTitle</title>
</head>
HTML;
        return $head;
    }

    function envoieFooter()
    {
        // On crée l'objet Footer
        $footer = new Footer();

        // On récupère le contenu HTML du footer personnalisé par l'admin
        $footerHtml = $footer->getHtml();

        // Si aucun HTML n'est défini, on met un contenu par défaut
        if (!$footerHtml) {
            $footerHtml = <<<HTML
            Nom du club : 
            <a href="http://www.top5.re" target="_blank">Site web</a> | 
            <a href="https://padlet.com/top5asso/top5-atelier-d-butant-interm-diaire-wwiy3x9lz44a6vyx">padlet </a> | 
            <a href="http://partoches.canopee-musique.fr" target="_blank">partoches</a>
            <div class="footer-social-icons" style="margin-top: 15px;">
                <a href="https://www.youtube.com/channel/UCFKyqYcs5cnML-EgPgYmwdg" target="_blank" title="YouTube">
                    <img src="../../images/icones/youtube.png" width="32" alt="YouTube">
                </a>
                <a href="https://twitter.com/top5ukeclub" target="_blank" title="Twitter">
                    <img src="../../images/icones/socmark_twitter.png" width="32" alt="Twitter">
                </a>
            </div>
HTML;
        }

        // Construction du footer complet (Le style est maintenant dans styles-communs.css)
        $retour = <<<HTML
<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            $footerHtml
        </div>
        
        <div class="footer-legal">
            <a href="../../html/mentionsLegales.html" target="_blank">Mentions légales</a>
            <span class="separator">|</span>
            <a href="../../html/merci.html" target="_blank">Mercis</a>
            <span class="separator">|</span>
            <span>&copy; 2018 - 2026 Partoches Canopée</span>
        </div>
    </div>
</footer>

<!-- Modale de Confirmation Globale (Django Style) -->
<div class="modal fade" id="modalConfirmation" tabindex="-1" role="dialog" aria-labelledby="modalConfirmationLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
      <div class="modal-header" style="background: #2b1d1a; color: white; border-radius: 12px 12px 0 0; padding: 10px 15px;">
        <h4 class="modal-title" id="modalConfirmationLabel" style="font-weight: bold; font-size: 16px;">
            <i class="glyphicon glyphicon-warning-sign"></i> Confirmation
        </h4>
      </div>
      <div class="modal-body text-center" style="padding: 20px 15px;">
        <p id="modalConfirmationMessage" style="font-size: 14px; color: #333; margin: 0;"></p>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #eee; padding: 10px; display: flex; justify-content: space-between;">
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="border-radius: 20px; font-weight: bold; flex-grow: 1; margin-right: 5px;">Annuler</button>
        <button type="button" id="btnConfirmAction" class="btn btn-danger btn-sm" style="border-radius: 20px; font-weight: bold; flex-grow: 1; margin-left: 5px;">Confirmer</button>
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
