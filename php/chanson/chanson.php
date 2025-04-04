<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/document/document.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/utilssi.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/configMysql.php";


// Fonctions de gestion de la chanson

class Chanson
{
    const D_M_Y = "d/m/Y";
    const MYSQL = 'mysql';
    private int $_id; // identifiant en BDD
    private string $_nom; // titre de la chanson , chaine de caractères
    private string $_interprete; // interprete de reference de la chanson, chaîne de caractères
    private int $_annee; // annee de sortie de la version, entier entre 0 et 2100
    private int $_idUser; // identifiant de l'utilisateur ayant propose la chanson, entier
    private int $_tempo; // bpm principal de la chanson, entier entre 0 et 300 environ
    private string $_mesure; // chaine indiquant la mesure, le plus souvent "4/4" ou "3/4"
    private string $_pulsation; // chaine, indique si les temps se découpent en "binaire" ou "ternaire"
    private string $_datePub; // date de publication de la chanson en chaine de caractères JJ/MM/AAAA
    private int $_hits; // compteur de visites de la chanson, corresponds aux affichages de la page chanson
    private string $_tonalite;
    // static $_logger;

    // Fonction conseillée pour gérer plusieurs constructeurs
    function __construct()
    {
        // Chanson::$_logger = init_logger();

        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = '__construct' . $i)) {
            call_user_func_array(array($this, $f), $a);
        }
    }

    // Constructeur par défaut
    public function __construct0()
    {
        $this->_id = 0;
        $this->setNom("");
        $this->setInterprete("");
        $this->setAnnee(1975);
        $this->setIdUser(1);
        $this->setTempo(120);
        $this->setMesure("4/4");
        $this->setPulsation("binaire");
        $this->setDatePub(convertitDateJJMMAAAAversMySql(date(self::D_M_Y)));
        $this->setHits(0);
        $this->setTonalite("C");
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
        $this->setDatePub(date(self::D_M_Y));
        $this->setHits($_hits);
        $this->setTonalite($_tonalite);
    }

    public function __construct10($_id, $_nom, $_interprete, $_annee, $_idUser, $_tempo, $_mesure, $_pulsation, $_hits, $_tonalite)
    {
        $this->__construct9($_nom, $_interprete, $_annee, $_idUser, $_tempo, $_mesure, $_pulsation, $_hits, $_tonalite);
        $this->setId($_id);
    }

    public function __construct11($_id, $_nom, $_interprete, $_annee, $_idUser, $_tempo, $_mesure, $_pulsation, $_date, $_hits, $_tonalite)
    {
        $this->__construct10($_id, $_nom, $_interprete, $_annee, $_idUser, $_tempo, $_mesure, $_pulsation, $_hits, $_tonalite);
        $this->setDatePub($_date);
    }

    // Un constructeur qui charge directement depuis la BDD
    public function __construct1($_id)
    {
        $this->__construct0();
        $this->chercheChanson($_id);
    }

    /// Getters et Setters

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId(int $id): void
    {
        if ($id > 0) {
            $this->_id = $id;
        }
    }

    /**
     * @return mixed
     */
    public function getNom(): string
    {
        return $this->_nom;
    }

    /**
     * @param mixed $nom
     */
    public function setNom(string $nom): void
    {
        $this->_nom = $nom;
    }

    /**
     * @return mixed
     */
    public function getInterprete(): string
    {
        return $this->_interprete;
    }

    /**
     * @param mixed $interprete
     */
    public function setInterprete(string $interprete)
    {
        $this->_interprete = $interprete;
    }

    /**
     * @return mixed
     */
    public function getAnnee(): int
    {
        return $this->_annee;
    }

    /**
     * @param mixed $annee
     */
    public function setAnnee(int $annee): void
    {
        if ($annee > 0) {
            $this->_annee = $annee;
        }
    }

    /**
     * @return int
     */
    public function getIdUser(): int
    {
        return $this->_idUser;
    }

    /**
     * @param int $idUser
     */
    public function setIdUser(int $idUser)
    {
        if ($idUser > 0) {
            $this->_idUser = $idUser;
        }
    }

    /**
     * @return int
     */
    public function getTempo(): int
    {
        return $this->_tempo;
    }

    /**
     * @param int $tempo
     */
    public function setTempo(int $tempo): void
    {
        if ($tempo > 0) {
            $this->_tempo = $tempo;
        }
    }

    /**
     * @return string
     */
    public function getMesure(): string
    {
        return $this->_mesure;
    }

    /**
     * @param mixed $mesure
     */
    public function setMesure(string $mesure)
    {
        $this->_mesure = $mesure;
    }

    /**
     * @return string
     */
    public function getPulsation(): string
    {
        if (is_null($this->_pulsation)) {
            return "";
        } else {
            return $this->_pulsation;
        }
    }

    /**
     * @param mixed $pulsation
     */
    public function setPulsation(string $pulsation)
    {
        $this->_pulsation = $pulsation;
    }

    /**
     * @return mixed
     */
    public function getDatePub(): string
    {
        return $this->_datePub;
    }

    /**
     * @param mixed $datePub
     */
    public function setDatePub(string $datePub)
    {
        $this->_datePub = $datePub;
    }

    /**
     * @return mixed
     */
    public function getHits(): int
    {
        return $this->_hits;
    }

    /**
     * @param mixed $hits
     */
    public function setHits(int $hits)
    {
        if ($hits >= 0) {
            $this->_hits = $hits;
        }
    }

    /**
     * @return mixed
     */
    public function getTonalite(): string
    {
        return $this->_tonalite;
    }

    /**
     * @param mixed $tonalite
     */
    public function setTonalite(string $tonalite)
    {
        $this->_tonalite = $tonalite;
    } // Indique la tonalité de la chanson ex : "Am" , "C#m"


    // Cherche une chanson et la renvoie si elle existe
    public function chercheChanson($id): int
    {
        $maRequete = sprintf("SELECT * FROM chanson WHERE chanson.id = '%s'",
            $id);
        // pour debug : echo "requete : $maRequete";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheChanson #1 : " . $_SESSION ['mysql']->error);
        // renvoie la ligne sélectionnée : id, nom, interprète, année
        if ($ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return 1;
        } else {
            return 0;
        }
    }


    // Charge une ligne mysql vers un objet
    private function mysqlRowVersObjet($ligne)
    {
        $this->_id = $ligne[0];
        $this->_nom = $ligne[1];
        $this->_interprete = $ligne[2];
        $this->_annee = $ligne[3];
        $this->_tempo = $ligne[4];
        $this->_mesure = $ligne[5];
        $this->_pulsation = $ligne[6];
        $this->_datePub = $ligne[7];
        $this->_idUser = $ligne[8];
        $this->_hits = $ligne[9];
        $this->_tonalite = $ligne[10];
    }

    // Cherche un chanson, la charge et renvoie vrai si elle existe
    public function chercheChansonParLeNom($nom): int
    {
        $maRequete = sprintf("SELECT * FROM chanson WHERE chanson.nom = '%s'", $nom);
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheChansonParLeNom #1 : " . $_SESSION ['mysql']->error);
        // renvoie la ligne sélectionnée : id, nom, taille, date
        if ($ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return (1);
        } else {
            return (0);
        }
    }

    // Créée une chanson en BDD

    /**
     *      enregistre l'objet en BDD
     */
    public function creeModifieChansonBDD()
    {
        if ($this->_id == 0) {
            $this->creeChansonBDD();
            $this->setId($_SESSION [self::MYSQL]->insert_id);
            return $this->getId();
        } else {
            $this->_nom = $_SESSION [self::MYSQL]->real_escape_string($this->_nom);
            $this->_interprete = $_SESSION [self::MYSQL]->real_escape_string($this->_interprete);
            $this->_annee = $_SESSION [self::MYSQL]->real_escape_string($this->_annee);
            $maRequete = sprintf("UPDATE  chanson SET nom = '%s', interprete = '%s', annee = '%s',
            idUser = %s, tempo = '%s', mesure='%s', pulsation='%s', 
            hits='%s', tonalite='%s', datePub='%s' WHERE id='%s'",
                $this->_nom,
                $this->_interprete,
                $this->_annee,
                $this->_idUser,
                $this->_tempo,
                $this->_mesure,
                $this->_pulsation,
                $this->_hits,
                $this->_tonalite,
                $this->_datePub,
                $this->_id);
            // Chanson::$_logger = init_logger();
            // Chanson::$_logger->info("Modification d'une chanson $this->_nom - $this->_interprete");
            // Chanson::$_logger->debug($maRequete);
            $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème modif dans creeModifieChanson #1 : " . $_SESSION [self::MYSQL]->error . " requete : " . $maRequete);
            return $this->_id;
        }
    }

    // Cree une chanson et renvoie l'id de la chanson créée
    public function creeChansonBDD()
    {
        $this->_nom = $_SESSION [self::MYSQL]->real_escape_string($this->_nom);
        $this->_interprete = $_SESSION [self::MYSQL]->real_escape_string($this->_interprete);
        $this->_annee = $_SESSION [self::MYSQL]->real_escape_string($this->_annee);
        $this->_datePub = convertitDateJJMMAAAAversMySql(date(self::D_M_Y));
        $maRequete = sprintf("INSERT INTO chanson (id, nom, interprete, annee, idUSer, tempo, mesure, pulsation, datePub, hits, tonalite)
	        VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', 
	        '%s', '%s' ,  '%s', '%s')",
            $this->_nom,
            $this->_interprete,
            $this->_annee,
            $this->_idUser,
            $this->_tempo,
            $this->_mesure,
            $this->_pulsation,
            $this->_datePub,
            $this->_hits,
            $this->_tonalite);
        // Chanson::$_logger = init_logger();
        // Chanson::$_logger->debug($maRequete);
        // Chanson::$_logger->info("Création d'une chanson $this->_nom - $this->_interprete");
        $result = $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème creeChansonBDD#1 : " . $_SESSION [self::MYSQL]->error);
        // On renseigne l'id de l'objet avec l'id créé en BDD
        $this->setId($_SESSION [self::MYSQL]->insert_id);
        return ($this->getId());
    }

    // Supprime un chanson si elle existe
    public function supprimeChansonBddFile()
    {
        // On supprime les enregistrements dans chanson
        $maRequete = "DELETE FROM chanson WHERE id='" . $this->getId() . "'";
        $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème #1 dans supprimeChanson : " . $_SESSION [self::MYSQL]->error);
        // Chanson::$_logger = init_logger();
        // Chanson::$_logger->debug($maRequete);
        // Chanson::$_logger->info("Suppression d'une chanson $this->_nom - $this->_interprete");
        // On supprime ensuite tous les documents de la chanson
        $result = chercheDocumentsTableId("chanson", $this->getId());
        while ($ligne = $result->fetch_row()) {
            $id = $ligne [0];
            supprimeDocument($id);
        }
    }

// Renvoie une chaine de description de la chanson
    public function infosChanson(): string
    {
        $retour = "Id : " . $this->_id . " Nom : " . $this->_nom . " Interprète : " . $this->_interprete . " Année : " . $this->_annee;
        $retour .= " idUSer : " . $this->_idUser . " tempo : " . $this->_tempo . " mesure : " . $this->_mesure . " pulsation : " . $this->_pulsation;
        $retour .= " hits : " . $this->_hits . " tonalité : " . $this->_tonalite;
        return $retour . "<BR>\n";
    }

// Cette fonction renvoie la liste des fichiers dans le repertoire de la chanson ../".$_DOSSIER_CHANSONS/id/
    public function fichiersChanson($dossier): array
    {
        $retour = array();// repertoire, nom, extension
        $repertoire = "../" . $dossier . $this->_id;
        if (is_dir($repertoire)) {
            foreach (new DirectoryIterator ($repertoire) as $fileInfo) {
                if ($fileInfo->isDot() || strpos($fileInfo->getFilename(), ".") == 0) {
                    continue;
                }
                array_push($retour,
                    $repertoire,
                    $fileInfo->getFilename(),
                    $fileInfo->getextension()
                );
            }
        }
        return $retour;
    }

    public static function moteurRecherche($recherche): string
    {
        $rechercheNormalisee = self::normalize($recherche);
        $maRequete = "SELECT id, nom, interprete FROM chanson";
        $retour = "";
        $result = $_SESSION[self::MYSQL]->query($maRequete) or die("Problème chercheChanson #2 : " . $_SESSION[self::MYSQL]->error . " Requete : $maRequete");
        $matches = [];
        if ($result->num_rows > 0) {
            // Parcourir les résultats et calculer la distance de Levenshtein
            while ($row = $result->fetch_assoc()) {
                $normalized_titre = self::normalize($row["nom"]);
                $normalized_interprete = self::normalize($row["interprete"]);
                $distance_titre = levenshtein($rechercheNormalisee, $normalized_titre);
                $distance_interprete = levenshtein($rechercheNormalisee, $normalized_interprete);
                // Choisir la plus petite distance entre le titre et l'interprète
                $distance = min($distance_titre, $distance_interprete);
                // Stocker la distance avec le résultat
                $row['distance'] = $distance;
                $matches[] = $row;
            }
        }
        // Trier les résultats par distance
        usort($matches, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        // Garder seulement les 10 meilleurs résultats
        $top_matches = array_slice($matches, 0, 10);
        if (count($top_matches) > 0) {
            $retour .= "Matches : ";
            foreach ($top_matches as $row) {
                $retour .= "Titre: " . $row["nom"] . " - Interprète: " . $row["interprete"] . " - Distance: " . $row['distance'] . "<br>\n";
            }
            $retour = $top_matches[0]["nom"];
        } else {
            $retour .= "0 résultats";
        }
        return $retour;
    }

// Cherche les chansons sur le titre ou l'interprete, renvoie le tableau des identifiants
    public static function chercheChansons($critere, $critereTri = 'nom', $bTriAscendant = true, $champFiltre = "", $valfiltre = ""): array
    {
        $critere = $_SESSION [self::MYSQL]->real_escape_string($critere);

        $maRequete = "SELECT id FROM chanson";
        $_bool_where_defini = false;
        if ($critere != "" && $critere != "%") {
            $maRequete .= " WHERE ( nom LIKE '$critere' OR interprete LIKE '$critere' )";
            $_bool_where_defini = true;
        }
        if ($champFiltre <> "" && $valfiltre <> "") {
            if (!$_bool_where_defini) {
                $maRequete .= " WHERE ";
            } else {
                $maRequete .= " AND ";
            }
            if ($champFiltre == "contributeur") {
                $maRequete .= " iduser =  " . $_SESSION[self::MYSQL]->real_escape_string($valfiltre);
            }
            if ($champFiltre == 'tempo' || $champFiltre == 'mesure' || $champFiltre == 'tonalite' || $champFiltre == 'pulsation' || $champFiltre == 'annee' || $champFiltre == 'interprete') {
                $maRequete .= $champFiltre . " =  '" . $_SESSION[self::MYSQL]->real_escape_string($valfiltre) . "'";
            }
        }
        $maRequete .= " ORDER BY $critereTri";
        if ($critereTri == "votes") {
            if ($_SESSION['privilege'] == $GLOBALS["PRIVILEGE_INVITE"]) {
                $maRequete = "SELECT chanson.id  FROM chanson 
                RIGHT JOIN noteUtilisateur on noteUtilisateur.idObjet = chanson.id 
                WHERE noteUtilisateur.nomObjet = 'chanson' OR noteUtilisateur.nomObjet = NULL
                GROUP BY chanson.id ORDER BY COALESCE(AVG(noteUtilisateur.note),0) ";
            } else {
                $maRequete = "SELECT  noteUtilisateur.idObjet FROM noteUtilisateur 
                WHERE noteUtilisateur.nomObjet = 'chanson' AND noteUtilisateur.idUtilisateur = '" . $_SESSION['id'] . "'
                ORDER BY noteUtilisateur.note";
            }
        }
        if (!$bTriAscendant) {
            $maRequete .= " DESC";
        } else {
            $maRequete .= " ASC";
        }
        // Chanson::$_logger = init_logger();
        // Chanson::$_logger->debug($maRequete);
        // echo "debug : " . $maRequete;
        $result = $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème chercheChanson #2 : " . $_SESSION [self::MYSQL]->error . "Requete : $maRequete");
        $tableau = [];
        while ($idChanson = $result->fetch_row()) {
            array_push($tableau, $idChanson[0]);
        }
        return $tableau;
    }

// Cherche la présence d'une chanson dans des songbooks
//idSongbook	nomSongBook
//24            Madelon 2017
//28        	Les Face A
//32        	Vergers de l îlot
//33        	Fête de Printemps
//33         	Fête de Printemps
    public function chercheSongbooksDocuments()
    {
        $maRequete = "SELECT DISTINCT songbook.id, songbook.nom from songbook, liendocsongbook , document ,
        chanson WHERE liendocsongbook.idDocument = document.id AND document.nomTable='chanson' 
        AND document.idTable = chanson.id AND chanson.id = " . $this->_id . "  AND songbook.id = liendocsongbook.idSongbook";
        // Chanson::$_logger = init_logger();
        // Chanson::$_logger->debug($maRequete);
        $result = $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème chercheSongbooksDocuments #1 : " . $_SESSION [self::MYSQL]->error);
        // Chanson::$_logger->warning(var_dump($result));
        return $result;
    }
    
// Cherche les liens associés à cette chanson

    public function chercheLiensChanson()
    {
        $maRequete = sprintf("SELECT * from lienurl WHERE lienurl.nomtable = 'chanson' AND lienurl.idtable = %s",
            $this->_id);
        // Chanson::$_logger = init_logger();
        // Chanson::$_logger->debug($maRequete);
        $result = $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème chercheLiensChanson #1 : " . $_SESSION [self::MYSQL]->error);
        // Chanson::$_logger->warning(var_dump($result));
        return $result;
    }

// Fonction pour normaliser les chaînes de caractères
    public
    static function normalize($string)
    {
        echo "Normalize $string ;";
        $string = strtolower($string); // Convertir en minuscules
        $string = preg_replace('/[áàâãäå]/u', 'a', $string);
        $string = preg_replace('/[éèêë]/u', 'e', $string);
        $string = preg_replace('/[íìîï]/u', 'i', $string);
        $string = preg_replace('/[óòôõö]/u', 'o', $string);
        $string = preg_replace('/[úùûü]/u', 'u', $string);
        $string = preg_replace('/[ýÿ]/u', 'y', $string);
        $string = preg_replace('/ç/u', 'c', $string);
        $string = preg_replace('/ñ/u', 'n', $string);
        $string = preg_replace('/[^a-z0-9\s]/', '', $string); // Supprimer les caractères non-alphanumériques
        $string = preg_replace('/\s+/', ' ', $string); // Réduire les espaces multiples
        echo " devient $string ;";
        return trim($string); // Supprimer les espaces au début et à la fin
    }
}

/// TODO fonctions à supprimer

// Cherche les chansons correspondant à un critère
function chercheChansons($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
{
    $maRequete = "SELECT * FROM chanson WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    if (!$bTriAscendant) {
        $maRequete .= " DESC";
    } else {
        $maRequete .= " ASC";
    }
    // Chanson::$_logger = init_logger();
    // Chanson::$_logger->debug($maRequete);
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheChanson #3 : " . $_SESSION ['mysql']->error);
    return $result;
}

// TODO : Mettre cette function dans une bibli ou utiliser une existante
// Limite la longeur d'une chaine à x caractères
function limiteLongueur($chaine, $tailleMax): string
{
    if (strlen($chaine) > $tailleMax) {
        return mb_substr($chaine, 0, $tailleMax - 4) . "...";
    } else {
        return $chaine;
    }
}
