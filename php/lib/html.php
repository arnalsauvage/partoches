<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/php/navigation/Footer.php";
if (!isset ($FichierHtml)) {
    $FichierHtml = 1;
    // Fonction retournant le code HTML pour un lien hypertexte____________

    function Ancre($url, $libelle, $classe = -1, $nouvellefenetre = -1, $titre=-1)
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
            $optionClasse = " class='$classe'";
        }
        if ($titre != -1) {
            $titre = htmlspecialchars($titre);
            $optionTitre = " title=\"$titre\"";
        }
        return "<a href='$url'" . "$nouvellefenetre $optionClasse $optionTitre>$libelle</A>";
    }

    // Fin de la fonction Ancre____________________________________________

    function titre($texte, $niveau)
    {
        return "<h$niveau>$texte</h$niveau>";
    }

    // Fonction retournant le code HTML pour une image ____________________
    function Image($urlImage, $largeur = -1, $hauteur = -1, $alt = "image décorative", $class = "")
    {
        $attrLargeur = "";
        $attrHauteur = "";
        if (($largeur != -1) && ($largeur <> "100%")) {
            $attrLargeur = " width = '$largeur' ";
        }
        if (($hauteur != -1) && ($hauteur <> "100%")) {
            $attrHauteur = " height = '$hauteur' ";
        }
        return "<img src='".$urlImage."' " . $attrLargeur . $attrHauteur . " alt='$alt' class ='$class'>\n";
    }

    // Fin de la fonction Image____________________________________________

    // Fonction créant un champ SELECT
    // Liste contient toutes les valeurs duchamp select
    function ChampSelect($liste, $numero, $nom)
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
        return ($texte);
    }

    function entreBalise($texte, $balise)
    {
        return ("<" . $balise . "> " . htmlentities($texte) . "</" . $balise . ">");
    }

    // Cette fonction donne l'instruction au navigateur de se rediriger
    // vers une autre adresse (aucun caractère n'a du être transmis,
    // pas m?me un espace ou  un retour de ligne
    function redirection($url)
    {
        if (headers_sent()) {
            print('<meta http-equiv="refresh" content="0;URL=' . $url . '">');
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

        if (strstr($tableau[$indice], "<") == FALSE) {
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
        $retour =
            "<!doctype html>
		<html lang='fr'>
		<head>
		<meta charset='UTF-8' >";

        // Pour BootStrap
        $retour .= "
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
    	<link href='../../css/bootstrap.min.css' rel='stylesheet'>
        <!-- source : http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->
    	 <link href=\"../../css/jquery-ui.1.12.1.css\" rel='stylesheet'>
        <link rel='stylesheet' type='text/css' href='../../css/styles.0.3.css'>
    	 ";

        $retour .= "
        
        <link rel='stylesheet' media='screen' type='text/css' title='resolution' href='$feuilleCss' />
		<script src='..//lib/javascript.js'></script>
		<!-- Jquery --- source : https://code.jquery.com/jquery-1.12.4.js -->
		<script src=\"../../js/jquery-1.12.4.min.js\"></script>
        <!-- jquery-ui --- source : https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
		<script src='../../js/jquery-ui.1.12.1.min.js'></script>
        
		<!-- Pour bootstrap         -->
        <script src='../../js/bootstrap.3.2.0.min.js'></script>
		
		<!-- Pour Toaster, les petites infos instantanées         -->
		<link href='../../css/toastr.min.css' rel='stylesheet' type='text/css'>
		
		<!-- Pour Select2, le combo amélioré         -->
		<link href='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' rel='stylesheet' />
        <script src='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'></script>
		
		<title>$titrePage</title>
		</head>";
        return $retour;
    }

    function envoieFooter()
    {
        // On crée l'objet Footer
        $footer = new Footer();

        // On récupère le contenu HTML du footer
        $footerHtml = $footer->getHtml();

        // Si aucun HTML n'est défini, on met un contenu par défaut
        if (!$footerHtml) {
            $footerHtml = <<<HTML
            Nom du club :
            <a href="http://www.top5.re" target="_blank">Site web</a> |
            <a href="https://padlet.com/top5asso/top5-atelier-d-butant-interm-diaire-wwiy3x9lz44a6vyx">padlet </a>   |
            <a href="http://partoches.top5.re" target="_blank">partoches</a>
            <a href="https://www.youtube.com/channel/UCFKyqYcs5cnML-EgPgYmwdg" target="_blank" title="youtube">
                &nbsp;
                <img src="https://cdn3.iconfinder.com/data/icons/peelicons-vol-1/50/YouTube-128.png" width="30" height="30" alt="youtube">
            </a>
            &nbsp;
            <a href="https://twitter.com/top5ukeclub" target="_blank" title="twitter">
                <img src="https://cdn3.iconfinder.com/data/icons/peelicons-vol-1/50/Twitter-128.png" width="30" height="30" alt="twitter">
            </a>

HTML;
        }

        // Construction du footer complet
        $retour = <<<HTML
<footer>
    <div class='container'>
        <div class='starter-template'>
            <br>
            $footerHtml
            <br>
            <a href='../../html/mentionsLegales.html' target='_blank' class='lienMentionsLegales'>Mentions légales</a>
            <script src='https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js'></script>
            <script src='../../js/precise-star-rating.js'></script>
        </div>
    </div>
</footer>
HTML;

        $retour .= "\n</html>";
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
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
        );

        $_nomSimplifie = strtr($nomOriginal,$table) ;
        $_nomSimplifie = str_replace("#","diese", $_nomSimplifie);
        $_nomSimplifie = str_replace("'","", $_nomSimplifie);
        $_nomSimplifie = str_replace(" ","-", $_nomSimplifie);
        return $_nomSimplifie;
    }
}
