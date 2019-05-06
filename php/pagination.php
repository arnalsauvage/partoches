<?php
include_once("./lib/utilssi.php");
include_once "lib/configMysql.php";

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
        $this->setNombreDePages(round($_nombreItemsTotal / $_nombreItemsParPage, 0, PHP_ROUND_HALF_UP));
        $this->setPagesEnCours(1);

    }

    public function pageSuivante()
    {
        $page = $this->getPageEnCours();
        if ($page < $this->getNombreDePages()) {
            $this->setPageEnCours($page + 1);
        }
    }

    /**
     * @return mixed
     */
    public function getPageEnCours()
    {
        return $this->_pageEnCours;
    }

    /**
     * @param mixed $pageEnCours
     */
    public function setPageEnCours($pageEnCours)
    {
        $this->_pageEnCours = $pageEnCours;
    }

    /**
     * @return mixed
     */
    public function getNombreDePages()
    {
        return $this->_nombreDePages;
    }

    /**
     * @param mixed $nombreDePages
     */
    public function setNombreDePages($nombreDePages)
    {
        $this->_nombreDePages = $nombreDePages;
    }

    public function pagePrecedente()
    {
        $page = $this->getPageEnCours();
        if ($page > 1) {
            $this->setPageEnCours($page - 1);
        }
    }

    public function barrePAgination()
    {
        $chaine = "";
        for ($compteur = 1, $compteur < ($this->getNombreDePages()), $compteur++) {
            if ($compteur == $this->getPageEnCours())
                $chaine .= $compteur;
            else
                $chaine .= Ancre($_SERVER['REQUEST_URI'] . "?page=" . $compteur, $compteur);
        }
    }

    /**
     * @return mixed
     */
    public function getNombreItemsTotal()
    {
        return $this->_nombreItemsTotal;
    }

    /**
     * @param mixed $nombreItemsTotal
     */
    public function setNombreItemsTotal($nombreItemsTotal)
    {
        $this->_nombreItemsTotal = $nombreItemsTotal;
    }

    /**
     * @return mixed
     */
    public function getNombreItemsParPage()
    {
        return $this->_nombreItemsParPage;
    }

    /**
     * @param mixed $nombreItemsParPage
     */
    public function setNombreItemsParPage($nombreItemsParPage)
    {
        $this->_nombreItemsParPage = $nombreItemsParPage;
    }


}

// TODO ajouter des logs pour tracer l'activité du site
