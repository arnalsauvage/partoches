<?php
require_once "lib/utilssi.php";
require_once "lib/configMysql.php";

// Objet de gestion des strums

class Strum
{
    private $_id; // identifiant en BDD
    private $_strum; // chaîne de description du strum , chaine de caractères , ex : "B BH HBH"
    private $_unite; // annee de sortie de la version, entier entre 0 et 2100
    private $_longueur; // identifiant de l'utilisateur ayant propose la strum, entier
    private $_description; // description du strum , chaine de caractères


    // Fonction conseillée pour gérer plusieurs constructeurs
    function __construct()
    {
        // Strum::$_logger = init_logger();

        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = '__construct' . $i)) {
            call_user_func_array(array($this, $f), $a);
        }
    }

    // Constructeur par défaut
    public function __construct0()
    {
        $this->setId(0);
        $this->setStrum("");
        $this->setUnite(8);
        $this->setLongueur(8);
        $this->setDescription("");
    }

    /**
     * strum constructor.
     * @param $_strum
     * @param $_unite
     * @param $_longueur
     * @param $_description
     */
    public function __construct5($_id, $_strum, $_unite, $_longueur, $_description)
    {
        $this->setId($_id);
        $this->setStrum($_strum);
        $this->setUnite($_unite);
        $this->setLongueur($_longueur);
        $this->setDescription($_description);
    }

    /**
     * strum constructor.
     * @param $_strum
     * @param $_unite
     * @param $_longueur
     * @param $_description
     */
    public function __construct4($_strum, $_unite, $_longueur, $_description)
    {
        $this->setId(0);
        $this->setStrum($_strum);
        $this->setUnite($_unite);
        $this->setLongueur($_longueur);
        $this->setDescription($_description);
    }

    // Un constructeur qui charge directement depuis la BDD
    public function __construct1($_id)
    {
        $this->__construct0();
        $this->chercheStrum($_id);
    }

    /// Getters et Setters

    /**
     * @return mixed
     */
    public function getId() : int
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId(int $id)
    {
        $this->_id = $id;
    }

    /**
     * @return mixed
     */
    public function getStrum() : string
    {
        return $this->_strum;
    }

    /**
     * @param mixed $nom
     */
    public function setStrum(string $strum)
    {
        $this->_strum = $strum;
    }

    /**
     * @return mixed
     */
    public function getDescription() : string
    {
        return $this->_description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription(string $description)
    {
        $this->_description = $description;
    }

    /**
     * @return mixed
     */
    public function getUnite() : int
    {
        return $this->_unite;
    }

    /**
     * @param mixed $unite
     */
    public function setUnite(int $unite): void
    {
        $this->_unite = $unite;
    }

    /**
     * @return mixed
     */
    public function getLongueur() :int
    {
        return $this->_longueur;
    }

    /**
     * @param mixed $longueur
     */
    public function setLongueur(int $longueur): void
    {
        $this->_longueur = $longueur;
    }


    // Cherche une strum et la renvoie si elle existe
    public function chercheStrum($id)
    {
        $maRequete = sprintf("SELECT * FROM strum WHERE id = '%s'", $id);
        // pour debug : echo "requete : $maRequete";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheStrum #1 : " . $_SESSION ['mysql']->error);
        // renvoie la ligne sélectionnée : id,  unite longueur strum description
        if ($ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return (1);
        } else {
            return (0);
        }
    }

    // Charge une ligne mysql vers un objet
    private function mysqlRowVersObjet($ligne)
    {
        $this->_id = $ligne[0];
        $this->_unite = $ligne[1];
        $this->_longueur = $ligne[2];
        $this->_strum = $ligne[3];
        $this->_description = $ligne[4];
    }

    // Cherche un strum, la charge et renvoie vrai si elle existe
    public function chercheStrumParChaine($chaine)
    {
        $maRequete = sprintf("SELECT * FROM strum WHERE BINARY strum = '%s'", $chaine);
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchestrumParChaine #1 : " . $_SESSION ['mysql']->error);
        // renvoie la ligne sélectionnée : id,  unite longueur strum description
        if ($ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return (1);
        } else {
            return (0);
        }
    }

    // Créée un strum en BDD

    /**
     *      enregistre l'objet en BDD
     */
    public function creeModifiestrumBDD()
    {
        if ($this->_id == 0) {
            $this->creestrumBDD();
            $this->setId($_SESSION ['mysql']->insert_id);
            return ($this->getId());
        } else {
            $this->_strum = $_SESSION ['mysql']->real_escape_string($this->_strum);
            $this->_description = $_SESSION ['mysql']->real_escape_string($this->_description);
            $this->_unite = $_SESSION ['mysql']->real_escape_string($this->_unite);
            $this->_longueur = $_SESSION ['mysql']->real_escape_string($this->_longueur);
            $maRequete = sprintf("UPDATE  strum SET strum = '%s', description = '%s', unite = '%s',
            longueur = %s WHERE strum.id = %s",
                $this->_strum,
                $this->_description,
                $this->_unite,
                $this->_longueur,
                $this->_id);
            // strum::$_logger = init_logger();
            // strum::$_logger->info("Modification d'un strum $this->_unite - $this->_longueur");
            // strum::$_logger->debug($maRequete);
            $_SESSION ['mysql']->query($maRequete) or die ("Problème modif dans creeModifiestrum #1 : " . $_SESSION ['mysql']->error . " requete : " . $maRequete);
            return $this->_id;
        }
    }

    // Cree une strum et renvoie l'id de la strum créée
    public function creestrumBDD()
    {
        $this->_strum = $_SESSION ['mysql']->real_escape_string($this->_strum);
        $this->_description = $_SESSION ['mysql']->real_escape_string($this->_description);
        $this->_unite = $_SESSION ['mysql']->real_escape_string($this->_unite);
        $this->_longueur = $_SESSION ['mysql']->real_escape_string($this->_longueur);
        $maRequete = sprintf("INSERT INTO strum (id, strum, description, unite, longueur)
	        VALUES (NULL, '%s', '%s', '%s', '%s')",
            $this->_strum,
            $this->_description,
            $this->_unite,
            $this->_longueur);
        // strum::$_logger = init_logger();
        // strum::$_logger->debug($maRequete);
        // strum::$_logger->info("Création d'une strum $this->_unite - $this->_longueur");
        echo $maRequete;
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creestrumBDD#1 : " . $_SESSION ['mysql']->error);
        // On renseigne l'id de l'objet avec l'id créé en BDD
        $this->setId($_SESSION ['mysql']->insert_id);
        return ($this->getId());
    }

    // Supprime un strum si elle existe
    public function supprimestrumBDD()
    {
        // On supprime les enregistrements dans strum
        $maRequete = "DELETE FROM strum WHERE id='" . $this->getId() . "'";
        $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimestrum : " . $_SESSION ['mysql']->error);
        // strum::$_logger = init_logger();
        // strum::$_logger->debug($maRequete);
        // strum::$_logger->info("Suppression d'une strum $this->_unite - $this->_longueur");
        // On supprime ensuite tous les liens entre un strum et une chanson
        /* TODO
        $result = chercheDocumentsTableId("strum", $this->getId());
        while ($ligne = $result->fetch_row()) {
            $id = $ligne [0];
            supprimeDocument($id);
        }
        */
    }

// Renvoie une chaine de description de la strum
    public function infosstrum()
    {
        $retour = "Id : " . $this->_id . " Unité : " . $this->_unite . " Longueur : " . $this->_longueur . " Strum : " . $this->_strum;
        $retour .= " Description : " . $this->_description ;
        return $retour . "<BR>\n";
    }

    public static function chargeStrumsBdd()
    {
        $maRequete = sprintf("SELECT * FROM strum ");
        // pour debug : echo "requete : $maRequete";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chargeStrumsBdd #1 : " . $_SESSION ['mysql']->error);
        $listeStrum = [];


        // renvoie la ligne sélectionnée : id,  unite longueur strum description
        while ($ligne = $result->fetch_row()) {
            $strum = new Strum();
            $strum->mysqlRowVersObjet($ligne);
            array_push($listeStrum, $strum);
        }
        return $listeStrum;
    }

// Cherche la présence d'une strum dans des songbooks
//idSongbook	nomSongBook
//24            Madelon 2017
//28        	Les Face A
//32        	Vergers de l îlot
//33        	Fête de Printemps
//33         	Fête de Printemps

    public function chercheSongbooksDocuments()
    {
        $maRequete = "SELECT DISTINCT songbook.id, songbook.nom from songbook, liendocsongbook , document ,
        strum WHERE liendocsongbook.idDocument = document.id AND document.nomTable='strum' 
        AND document.idTable = strum.id AND strum.id = " . $this->_id . "  AND songbook.id = liendocsongbook.idSongbook";
        // strum::$_logger = init_logger();
        // strum::$_logger->debug($maRequete);
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheSongbooksDocuments #1 : " . $_SESSION ['mysql']->error);
        // strum::$_logger->warning(var_dump($result));
        return $result;
    }


// Cherche les liens associés à ce strum

    public function chercheLiensstrum()
    {
        $maRequete = sprintf("SELECT * from lienurl WHERE lienurl.nomtable = 'strum' AND lienurl.idtable = %s",
            $this->_id);
        // strum::$_logger = init_logger();
        // strum::$_logger->debug($maRequete);
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLiensStrum #1 : " . $_SESSION ['mysql']->error);
        // strum::$_logger->warning(var_dump($result));
        return $result;
    }

}
