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
        // echo "url sans page : " . $_monUrlSansParamPage;
        $chaine = "<div class = nav> Pages :  ";
        if ($this->getPageEnCours() > 1) {
            $chaine .= Ancre($this->urlAjouteParam($_monUrlSansParamPage, "page=1"), "<< ");
        }
        else {
            $chaine .= " &lt;&lt; ";
        }
        $pagePrecedente = $this->getPageEnCours() - 1;
        if ($pagePrecedente == 0) {
            $pagePrecedente = 1;
        }
        $chaine = $this->getPageEnCours() > 1 ? $chaine . Ancre($this->urlAjouteParam($_monUrlSansParamPage, "page=$pagePrecedente"), " préc. ") : $chaine . " préc. ";
        for ($compteur = 1; $compteur <= ($this->getNombreDePages()); $compteur++) {
            if ($compteur > 1) {
                $chaine .= " - ";
            }
            if ($compteur == $this->getPageEnCours()) {
                $chaine .= $compteur;
            }
            else {
                $chaine .= Ancre($this->urlAjouteParam($_monUrlSansParamPage, "page=" . $compteur), $compteur);
            }
        }
        $pageSuivante = $this->getPageEnCours() + 1;
        if ($pageSuivante > $this->getNombreDePages()) {
            $pageSuivante = $this->getNombreDePages();
        }
        if ($this->getPageEnCours() < $this->getNombreDePages()) {
            $chaine .= Ancre($this->urlAjouteParam($_monUrlSansParamPage, "page=$pageSuivante"), " suiv. ");
            $chaine .= Ancre($this->urlAjouteParam($_monUrlSansParamPage,  "page=".$this->getNombreDePages()), " >>");
        } else {
            $chaine .= " suiv. >>";
        }
        $chaine .= "</div>";
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

        if (!strstr($url,"?")){
            $url .= "?";
        }
        else {
            $url .= "&";
        }
        $url.= $_leparam;
        return ($url);
    }
}