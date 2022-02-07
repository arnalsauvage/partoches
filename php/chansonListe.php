<?php
include_once("lib/utilssi.php");
include_once "lib/configMysql.php";
include_once "document.php";
include_once 'chanson.php';

// Fonctions de gestion de la chanson
//init_logger();

class ChansonListe
{

    private $_tabChanson; // tableau de chansons

    // Fonction conseillée pour gérer plusieurs constructeurs
    function __construct()
    {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = '__construct' . $i)) {
            call_user_func_array(array($this, $f), $a);
        }
    }

    // Constructeur par défaut
    public function __construct0()
    {
        $this->_tabChanson = Array();

    }

    /**
     * Chanson constructor.
     * @param $_nom
     * @param $_interprete
     * @param $_annee
     * @param $_idUser
     * @param $_tempo
     * @param $_mesure
     * @param $_pulsation
     * @param $_hits
     * @param $_tonalite
     */


    /// Getters et Setters

    public function getNbChansons()
    {
        return (count($this->_tabChanson));
    }

    public function ajouteChanson($uneChanson)
    {
        // echo "on ajoute la chanson ";
        // var_dump($uneChanson);
        $this->_tabChanson[$this->getNbChansons()] = $uneChanson;
    }

    public function retireChanson($numero)
    {
        unset($this->_tabChanson[$numero]);
    }

    public function getChanson($rang)
    {
        $maChanson = $this->_tabChanson[$rang];
        return $maChanson;
    }

    public function recupereChanson ( $_idChanson)
    {
        $nbChansons = $this->getNbChansons();
        $parcours = 0;
        $_idChanson = intval($_idChanson);

        while ($parcours < $nbChansons)
        {
            $maChanson = new Chanson();
            $maChanson = $this->_tabChanson[$parcours];
            if (intval($maChanson->getId()) == $_idChanson)
            {
                return $maChanson;
            }
            // echo "id $parcours : " . intval($maChanson->getId() ) ;
//            var_dump($maChanson);
            $parcours++;
        }
        echo "Chanson $_idChanson non trouvée dans chansonListe.php parmi une liste de $nbChansons  !";
    }

    public function filtreChansons ( $critere, $valeur)
    {
        $nbChansons = $this->getNbChansons();
        $parcours = 0;

        while ($parcours < $nbChansons)
        {
            $maChanson = $this->_tabChanson[$parcours];
            if ($maChanson.$critere == $valeur)
            {
                unset($this->_tabChanson[$parcours]);
                $parcours--;
                $nbChansons--;
            }
            $parcours++;
        }
    }

    public function chargeListeChansons ()
    {
        // Lancer la requete
        // pour récupérer les id de toutes les chansons
        // Ajouter la chanson dans la liste
        $resultat = Chanson::chercheChansons( ""  );

        /** @noinspection PhpUndefinedMethodInspection */
        foreach ($resultat as $idChanson) {
            $_chanson = new Chanson();
            // echo " idchanson : $idChanson";
            $_chanson->chercheChanson(intval($idChanson));
            $this->ajouteChanson($_chanson);
        }
    }
}
