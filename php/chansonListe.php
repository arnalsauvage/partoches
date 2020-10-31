<?php
include_once("lib/utilssi.php");
include_once "lib/configMysql.php";
include_once "document.php";

// Fonctions de gestion de la chanson
init_logger();

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
    public function __construct9($_nom, $_interprete, $_annee, $_idUser, $_tempo, $_mesure, $_pulsation, $_hits, $_tonalite)
    {
        $this->setId(0);
        $this->setNom($_nom);
        $this->setInterprete($_interprete);
        $this->setAnnee($_annee);
        $this->setIdUser($_idUser);
        $this->setTempo($_tempo);
        $this->setMesure($_mesure);
        $this->setPulsation($_pulsation);
        $this->setDatePub(date("d/m/Y"));
        $this->setHits($_hits);
        $this->setTonalite($_tonalite);
    }


    /// Getters et Setters

    public function getNbChansons()
    {
        return (count($this->_tabChanson));
    }


    public function ajouteChanson($uneChanson)
    {
        $this->_tabChanson[$this->getNbChansons] = $uneChanson;
    }

    public function retireChanson($numero)
    {
        unset($this->_tabChanson[$numero]);
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
        // pour toutes les chansons
        // Ajouter la chansson dans la liste
        $resultat = Chanson::chercheChansons( ""  );
        $_chanson = new Chanson();

        /** @noinspection PhpUndefinedMethodInspection */
        foreach ($resultat as $ligne) {
            $_chanson->chercheChanson($ligne);
            $this->ajouteChanson($_chanson);
        }
    }

}
