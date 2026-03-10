<?php /** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
//namespace Partoches;

// Fonctions de gestion d'une pagination


class Pagination
{

    private $_nombreDePages; // nombre de pages pour contenir les items
    private $_nombreItemsTotal; // nombre d'items à gérer
    private $_nombreItemsParPage; // nomnbre d'items par page
    private $_pageEnCours; // numéro de la page en cours


    // Fonction conseillée pour gérer plusieurs constructeurs
    public function __construct($_nombreItemsTotal, $_nombreItemsParPage)
    {
        $this->setNombreItemsParPage($_nombreItemsParPage);
        $this->setNombreItemsTotal($_nombreItemsTotal);
        $this->setPageEnCours(1);
    }

    /**
     * @param mixed $pageEnCours
     */
    public function setPageEnCours($pageEnCours)
    {
        if ($pageEnCours >= 1 && $pageEnCours <= $this->_nombreDePages) {
            $this->_pageEnCours = $pageEnCours;
        }
    }


    public function goPagePrecedente()
    {
        $page = $this->getPageEnCours();
        if ($page > 1) {
            $this->setPageEnCours($page - 1);
        }
    }

    public function goPageSuivante()
    {
        $page = $this->getPageEnCours();
        if ($page < $this->getNombreDePages()) {
            $this->setPageEnCours($page + 1);
        }
    }

    public function barrePagination()
    {
        $_monUrlSansParamPage = $this->retirerParametreUrl("page");
        $total = $this->getNombreDePages();
        $actuelle = $this->getPageEnCours();
        
        if ($total <= 1) return ""; // Pas besoin de pagination pour une seule page

        $chaine = "<span class='pagination-container' style='margin-left: 15px; font-family: sans-serif;'>";
        $chaine .= "<span style='margin-right: 10px; font-weight: bold;'>Pages :</span> ";

        // --- BOUTON PRÉCÉDENT ---
        if ($actuelle > 1) {
            $prev = $actuelle - 1;
            $chaine .= ancre($this->urlAjouteParam($_monUrlSansParamPage, "page=1"), "&laquo; Prems", -1, -1, "Première page") . " ";
            $chaine .= ancre($this->urlAjouteParam($_monUrlSansParamPage, "page=$prev"), "&lsaquo; Préc.", -1, -1, "Page précédente") . " ";
        }

        // --- LOGIQUE FENÊTRE GLISSANTE ---
        $range = 2; // Nombre de pages à afficher autour de la page actuelle
        $show_dots_start = false;
        $show_dots_end = false;

        for ($i = 1; $i <= $total; $i++) {
            // Toujours afficher la première, la dernière et celles autour de l'actuelle
            if ($i == 1 || $i == $total || ($i >= $actuelle - $range && $i <= $actuelle + $range)) {
                if ($i == $actuelle) {
                    $chaine .= "<strong style='background: #337ab7; color: #fff; padding: 2px 8px; border-radius: 3px; margin: 0 2px;'>$i</strong>";
                } else {
                    $chaine .= ancre($this->urlAjouteParam($_monUrlSansParamPage, "page=$i"), " $i ", -1, -1, "Aller à la page $i") . " ";
                }
            }
            // Afficher des points de suspension si on saute des pages
            elseif ($i < $actuelle - $range && !$show_dots_start) {
                $chaine .= " ... ";
                $show_dots_start = true;
            } elseif ($i > $actuelle + $range && !$show_dots_end) {
                $chaine .= " ... ";
                $show_dots_end = true;
            }
        }

        // --- BOUTON SUIVANT ---
        if ($actuelle < $total) {
            $next = $actuelle + 1;
            $chaine .= " " . ancre($this->urlAjouteParam($_monUrlSansParamPage, "page=$next"), "Suiv. &rsaquo;", -1, -1, "Page suivante");
            $chaine .= " " . ancre($this->urlAjouteParam($_monUrlSansParamPage, "page=$total"), "Der. &raquo;", -1, -1, "Dernière page");
        }

        $chaine .= "</span>";
        return $chaine;
    }

    /**
     * @param mixed $nombreItemsTotal
     */
    public function setNombreItemsTotal($nombreItemsTotal)
    {
        $this->_nombreItemsTotal = $nombreItemsTotal;
        $this->calculeNombreDePages();
    }

    /**
     * @param mixed $nombreItemsParPage entier du nombre d'items par page
     */
    public function setNombreItemsParPage($nombreItemsParPage)
    {
        $this->_nombreItemsParPage = $nombreItemsParPage;
        $this->calculeNombreDePages();
    }

    private function calculeNombreDePages()
    {
        $this->_nombreDePages = ceil($this->_nombreItemsTotal / $this->_nombreItemsParPage);
        $this->limitePageEnCours();
    }

    private function limitePageEnCours()
    {
        if ($this->_pageEnCours > $this->getNombreDePages()) {
            $this->_pageEnCours = $this->getNombreDePages();
        }
    }

    //// GETTERS /////////////////////

    /**
     * @return mixed
     */
    public function getNombreItemsParPage()
    {
        return $this->_nombreItemsParPage;
    }

    /**
     * @return mixed
     */
    public function getNombreItemsTotal()
    {
        return $this->_nombreItemsTotal;
    }

    /**
     * @return mixed
     */
    public function getItemDebut()
    {
        return (1 + ($this->_pageEnCours - 1) * $this->_nombreItemsParPage);
    }

    /**
     * @return mixed
     */
    public function getItemFin()
    {
        if ($this->getPageEnCours() < $this->getNombreDePages()) {
            return (($this->_pageEnCours) * $this->_nombreItemsParPage);
        }
        else {
            return $this->getNombreItemsTotal();
        }
    }

    /**
     * @return mixed
     */
    public function getNombreDePages()
    {
        return $this->_nombreDePages;
    }

    /**
     * @return mixed
     */
    public function getPageEnCours()
    {
        return $this->_pageEnCours;
    }

    // ex on est dans la page http://mapage.pgp?id=12&tri=asc
    // on appelle retirerParametreUrl ("id")
    // on récupère http://mapage.pgp?tri=asc
    public function retirerParametreUrl($_paramAretirer)
    {
        $_monUrl = $_SERVER['REQUEST_URI'] ;
        // echo "mon url : " . $_monUrl . "\n";
        $parsed = parse_url($_monUrl);
        // echo "parsed : ";
        // var_dump($parsed);
        // On récupère la query (ex id=12&tri=ASC)
        if (isset($parsed['query'])) {
            $query = $parsed['query'];
        }
        else
        {
            $query = "";
        }
        // On la parse en un tableau de parametres
        parse_str($query, $params);
        // On supprime le parametre à retirer
        unset($params[$_paramAretirer]);
        // On reconstruit la nouvelle url
        $nouvelleUrl = $_SERVER['PHP_SELF']."?".http_build_query($params);
        return($nouvelleUrl);
    }

    public function urlAjouteParam($url, $_leparam){
        if (empty($url)) {
            $url = $_SERVER['REQUEST_URI'];
        }

        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $query = $parsedUrl['query'] ?? '';

        parse_str($query, $params);
        parse_str($_leparam, $newParams);

        // On fusionne les paramètres
        $mergedParams = array_merge($params, $newParams);

        // ON NETTOIE les paramètres de reset pour éviter les conflits
        unset($mergedParams['razFiltres']);
        unset($mergedParams['raz-recherche']);

        $newQuery = http_build_query($mergedParams);
        
        return $path . ($newQuery ? "?" . $newQuery : "");
    }
}
