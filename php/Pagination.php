<?php /** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
//namespace Partoches;

// Fonctions de gestion d'une pagination


class Pagination
{

    private $_nombreDePages; // identifiant en BDD
    private $_nombreItemsTotal; // titre de la chanson , chaine de caractères
    private $_nombreItemsParPage; // titre de la chanson , chaine de caractères
    private $_pageEnCours; // interprete de reference de la chanson, chaîne de caractères


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
        if ($pageEnCours >= 1 && $pageEnCours <= $this->_nombreDePages)
            $this->_pageEnCours = $pageEnCours;
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
        $chaine = "<div class = nav> Pages :  ";
        if ($this->getPageEnCours() > 1)
            $chaine .= Ancre($_SERVER['PHP_SELF'] . "?page=1", "<< ");
        else
            $chaine .= "<< ";
        $pagePrecedente = $this->getPageEnCours() - 1;
        if ($pagePrecedente == 0)
            $pagePrecedente == 1;
        if ($this->getPageEnCours() > 1)
            $chaine .= Ancre($_SERVER['PHP_SELF'] . "?page=$pagePrecedente", " préc. ");
        else
            $chaine.= " préc. ";
        for ($compteur = 1; $compteur <= ($this->getNombreDePages()); $compteur++) {
            if ($compteur > 1)
                $chaine .= " - ";
            if ($compteur == $this->getPageEnCours())
                $chaine .= $compteur;
            else
                $chaine .= Ancre($_SERVER['PHP_SELF'] . "?page=" . $compteur, $compteur);
        }
        $pageSuivante = $this->getPageEnCours() + 1;
        if ($pageSuivante > $this->getNombreDePages())
            $pageSuivante = $this->getNombreDePages();
        if ($this->getPageEnCours() < $this->getNombreDePages()) {
            $chaine .= Ancre($_SERVER['PHP_SELF'] . "?page=$pageSuivante", " suiv. ");
            $chaine .= Ancre($_SERVER['PHP_SELF'] . "?page=" . $this->getNombreDePages(), " >>");
        }
        else {
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
        if ($this->_pageEnCours > $this->getNombreDePages())
            $this->_pageEnCours = $this->getNombreDePages();
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
        if ($this->getPageEnCours() < $this->getNombreDePages())
            return (($this->_pageEnCours) * $this->_nombreItemsParPage);
        else
            return $this->getNombreItemsTotal();
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

}
