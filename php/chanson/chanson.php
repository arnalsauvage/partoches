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
    private ?string $_cover; // URL de la pochette
    private int $_publication; // 1 = publié, 0 = brouillon

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
        $this->setCover(null); 
        $this->setPublication(1); // Publié par défaut
    }

    /**
     * Chanson constructor.
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
        $this->setCover(null);
        $this->setPublication(1);
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
    } 

    /**
     * @return string|null
     */
    public function getCover(): ?string
    {
        return $this->_cover;
    }

    /**
     * @param string|null $cover
     */
    public function setCover(?string $cover): void
    {
        $this->_cover = $cover;
    }

    /**
     * @return int
     */
    public function getPublication(): int
    {
        return $this->_publication;
    }

    /**
     * @param int $publication
     */
    public function setPublication(int $publication): void
    {
        $this->_publication = $publication;
    }


    // Cherche une chanson et la renvoie si elle existe
    public function chercheChanson($id): int
    {
        $maRequete = sprintf("SELECT * FROM chanson WHERE chanson.id = '%s'",
            $id);
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheChanson #1 : " . $_SESSION ['mysql']->error);
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
        $this->_cover = $ligne[11] ?? null;
        $this->_publication = (int)($ligne[12] ?? 1); // Nouvelle colonne publication
    }

    // Cherche un chanson, la charge et renvoie vrai si elle existe
    public function chercheChansonParLeNom($nom): int
    {
        $maRequete = sprintf("SELECT * FROM chanson WHERE chanson.nom = '%s'", $nom);
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheChansonParLeNom #1 : " . $_SESSION ['mysql']->error);
        if ($ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return (1);
        } else {
            return (0);
        }
    }

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
            $this->_cover = $_SESSION [self::MYSQL]->real_escape_string($this->_cover ?? '');
            $maRequete = sprintf("UPDATE  chanson SET nom = '%s', interprete = '%s', annee = '%s',
            idUser = %s, tempo = '%s', mesure='%s', pulsation='%s', 
            hits='%s', tonalite='%s', datePub='%s', cover='%s', publication=%s WHERE id='%s'", 
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
                $this->_cover,
                $this->_publication,
                $this->_id);
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
        $this->_cover = $_SESSION [self::MYSQL]->real_escape_string($this->_cover ?? '');
        $this->_datePub = convertitDateJJMMAAAAversMySql(date(self::D_M_Y));
        $maRequete = sprintf("INSERT INTO chanson (id, nom, interprete, annee, idUSer, tempo, mesure, pulsation, datePub, hits, tonalite, cover, publication)
	        VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', 
	        '%s', '%s' ,  '%s', '%s', '%s', %s)", 
            $this->_nom,
            $this->_interprete,
            $this->_annee,
            $this->_idUser,
            $this->_tempo,
            $this->_mesure,
            $this->_pulsation,
            $this->_datePub,
            $this->_hits,
            $this->_tonalite,
            $this->_cover,
            $this->_publication);
        $result = $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème creeChansonBDD#1 : " . $_SESSION [self::MYSQL]->error);
        $this->setId($_SESSION [self::MYSQL]->insert_id);
        return ($this->getId());
    }

    // Supprime un chanson si elle existe
    public function supprimeChansonBddFile()
    {
        $maRequete = "DELETE FROM chanson WHERE id='" . $this->getId() . "'";
        $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème #1 dans supprimeChanson : " . $_SESSION [self::MYSQL]->error);
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
        $retour .= " hits : " . $this->_hits . " tonalité : " . $this->_tonalite . " publication : " . $this->_publication;
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
        // Ajout du filtre publication pour le moteur de recherche si pas admin
        if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
            $maRequete .= " WHERE publication = 1";
        }
        
        $retour = "";
        $result = $_SESSION[self::MYSQL]->query($maRequete) or die("Problème chercheChanson #2 : " . $_SESSION[self::MYSQL]->error . " Requete : $maRequete");
        $matches = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $normalized_titre = self::normalize($row["nom"]);
                $normalized_interprete = self::normalize($row["interprete"]);
                $distance_titre = levenshtein($rechercheNormalisee, $normalized_titre);
                $distance_interprete = levenshtein($rechercheNormalisee, $normalized_interprete);
                $distance = min($distance_titre, $distance_interprete);
                $row['distance'] = $distance;
                $matches[] = $row;
            }
        }
        usort($matches, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
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
    public static function chercheChansons($critere, $critereTri = 'nom', $bTriAscendant = true, $champFiltre = "", $valfiltre = "", $limit = -1, $offset = 0): array
    {
        $critere = $_SESSION [self::MYSQL]->real_escape_string($critere);

        // Si on trie par votes, on doit faire une jointure
        if ($critereTri == "votes") {
            if ($_SESSION['privilege'] == $GLOBALS["PRIVILEGE_INVITE"]) {
                $maRequete = "SELECT chanson.id, COALESCE(AVG(noteUtilisateur.note), 0) as moy_note FROM chanson 
                              LEFT JOIN noteUtilisateur ON (noteUtilisateur.idObjet = chanson.id AND noteUtilisateur.nomObjet = 'chanson')";
            } else {
                $maRequete = "SELECT chanson.id, COALESCE(noteUtilisateur.note, 0) as ma_note FROM chanson 
                              LEFT JOIN noteUtilisateur ON (noteUtilisateur.idObjet = chanson.id AND noteUtilisateur.nomObjet = 'chanson' AND noteUtilisateur.idUtilisateur = '" . $_SESSION['id'] . "')";
            }
        } else {
            $maRequete = "SELECT chanson.id FROM chanson";
        }

        $_bool_where_defini = false;

        // Filtre de publication pour les non-admins
        if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
            $maRequete .= " WHERE chanson.publication = 1";
            $_bool_where_defini = true;
        }

        if ($critere != "" && $critere != "%") {
            $maRequete .= ($_bool_where_defini ? " AND " : " WHERE ") . "( chanson.nom LIKE '$critere' OR chanson.interprete LIKE '$critere' )";
            $_bool_where_defini = true;
        }

        if ($champFiltre != "" && $valfiltre != "") {
            $maRequete .= ($_bool_where_defini ? " AND " : " WHERE ");
            $valEscaped = $_SESSION[self::MYSQL]->real_escape_string($valfiltre);
            
            if ($champFiltre == "contributeur") {
                $maRequete .= " chanson.iduser =  " . $valEscaped;
            } elseif ($champFiltre == "tempo_famille") {
                $maRequete .= match ($valEscaped) {
                    "Largo" => " chanson.tempo < 60",
                    "Adagio" => " chanson.tempo BETWEEN 60 AND 75",
                    "Andante" => " chanson.tempo BETWEEN 76 AND 107",
                    "Moderato" => " chanson.tempo BETWEEN 108 AND 119",
                    "Allegro" => " chanson.tempo BETWEEN 120 AND 155",
                    "Vivace" => " chanson.tempo BETWEEN 156 AND 175",
                    "Presto" => " chanson.tempo >= 176",
                    default => " 1=1"
                };
            } elseif ($champFiltre == "annee" || $champFiltre == "tempo") {
                 // Pour les nombres, on peut utiliser = au lieu de LIKE
                 $maRequete .= "chanson." . $champFiltre . " = '" . $valEscaped . "'";
            } else {
                $maRequete .= "chanson." . $champFiltre . " LIKE '" . $valEscaped . "'";
            }
            $_bool_where_defini = true;
        }

        // Group by si tri par votes (pour l'AVG)
        if ($critereTri == "votes" && $_SESSION['privilege'] == $GLOBALS["PRIVILEGE_INVITE"]) {
            $maRequete .= " GROUP BY chanson.id";
        }

        // Gestion du tri
        if ($critereTri == "votes") {
            $colTri = ($_SESSION['privilege'] == $GLOBALS["PRIVILEGE_INVITE"]) ? "moy_note" : "ma_note";
            $maRequete .= " ORDER BY $colTri " . ($bTriAscendant ? "ASC" : "DESC");
        } else {
            $maRequete .= " ORDER BY chanson.$critereTri " . ($bTriAscendant ? "ASC" : "DESC");
        }

        if ($limit > 0) {
            $maRequete .= " LIMIT $limit OFFSET $offset";
        }

        $result = $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème chercheChanson #2 : " . $_SESSION [self::MYSQL]->error . "Requete : $maRequete");
        $tableau = [];
        while ($row = $result->fetch_row()) {
            array_push($tableau, $row[0]);
        }
        return $tableau;
    }

    /**
     * Compte le nombre de chansons correspondant aux critères (pour la pagination)
     */
    public static function compteChansons($critere, $champFiltre = "", $valfiltre = ""): int
    {
        $critere = $_SESSION [self::MYSQL]->real_escape_string($critere);
        $maRequete = "SELECT COUNT(chanson.id) FROM chanson";
        $_bool_where_defini = false;

        if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
            $maRequete .= " WHERE chanson.publication = 1";
            $_bool_where_defini = true;
        }

        if ($critere != "" && $critere != "%") {
            $maRequete .= ($_bool_where_defini ? " AND " : " WHERE ") . "( chanson.nom LIKE '$critere' OR chanson.interprete LIKE '$critere' )";
            $_bool_where_defini = true;
        }

        if ($champFiltre != "" && $valfiltre != "") {
            $maRequete .= ($_bool_where_defini ? " AND " : " WHERE ");
            $valEscaped = $_SESSION[self::MYSQL]->real_escape_string($valfiltre);

            if ($champFiltre == "contributeur") {
                $maRequete .= " chanson.iduser =  " . $valEscaped;
            } elseif ($champFiltre == "tempo_famille") {
                $maRequete .= match ($valEscaped) {
                    "Largo" => " chanson.tempo < 60",
                    "Adagio" => " chanson.tempo BETWEEN 60 AND 75",
                    "Andante" => " chanson.tempo BETWEEN 76 AND 107",
                    "Moderato" => " chanson.tempo BETWEEN 108 AND 119",
                    "Allegro" => " chanson.tempo BETWEEN 120 AND 155",
                    "Vivace" => " chanson.tempo BETWEEN 156 AND 175",
                    "Presto" => " chanson.tempo >= 176",
                    default => " 1=1"
                };
            } elseif ($champFiltre == "annee" || $champFiltre == "tempo") {
                $maRequete .= "chanson." . $champFiltre . " = '" . $valEscaped . "'";
            } else {
                $maRequete .= "chanson." . $champFiltre . " LIKE '" . $valEscaped . "'";
            }
            $_bool_where_defini = true;
        }

        $result = $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème compteChansons : " . $_SESSION [self::MYSQL]->error . " Requete : $maRequete");
        $row = $result->fetch_row();
        return (int) $row[0];
    }
    /**
     * Cherche les songbooks associés aux documents de cette chanson
     * @return mysqli_result|bool
     */
    public function chercheSongbooksDocuments()
    {
        $maRequete = "SELECT DISTINCT songbook.id, songbook.nom from songbook, liendocsongbook , document ,
        chanson WHERE liendocsongbook.idDocument = document.id AND document.nomTable='chanson'
        AND document.idTable = chanson.id AND chanson.id = " . $this->_id . "  AND songbook.id = liendocsongbook.idSongbook";
        $result = $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème chercheSongbooksDocuments #1 : " . $_SESSION [self::MYSQL]->error);
        return $result;
    }

    /**
     * Cherche les liens URL associés à cette chanson
     * @return mysqli_result|bool
     */
    public function chercheLiensChanson()
    {
        $maRequete = sprintf("SELECT * from lienurl WHERE lienurl.nomtable = 'chanson' AND lienurl.idtable = %s",
            $this->_id);
        $result = $_SESSION [self::MYSQL]->query($maRequete) or die ("Problème chercheLiensChanson #1 : " . $_SESSION [self::MYSQL]->error);
        return $result;
    }
// Fonction pour normaliser les chaînes de caractères
    public
    static function normalize($string)
    {
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
        return trim($string); // Supprimer les espaces au début et à la fin
    }

    /**
     * Affiche une carte moderne (thumbnail Bootstrap 3) pour la chanson
     * @return string HTML de la carte
     */
    public function afficheCarteChanson(): string
    {
        $_id = $this->getId();
        $nomImage = imageTableId("chanson", $_id);
        $imagePochette = affichePochette($nomImage, $_id, 200, 200);
        $titre = htmlspecialchars(limiteLongueur($this->getNom(), 25));
        $interpreteFull = $this->getInterprete();
        $interpreteAffiche = htmlspecialchars(limiteLongueur($interpreteFull, 25));
        $annee = $this->getAnnee();
        $tempo = $this->getTempo();
        $tonalite = $this->getTonalite();

        // Palette Canopée
        $c_marron_fonce = "#2b1d1a";
        $c_marron_clair = "#D2B48C"; // Bois clair
        $c_accent = "#8B4513";
        $c_beige = "#F5F5DC";

        // Construction des liens de filtrage
        $urlInterprete = "?filtre=interprete&valFiltre=" . urlencode($interpreteFull);
        $urlAnnee = "?filtre=annee&valFiltre=" . urlencode($annee);
        $urlTempo = "?filtre=tempo&valFiltre=" . urlencode($tempo);
        $urlTonalite = "?filtre=tonalite&valFiltre=" . urlencode($tonalite);

        // Badge Non Publié (visible pour admin et auteur)
        $badgeNonPublie = "";
        if ($this->getPublication() == 0) {
            if (estAdmin() || (isset($_SESSION['id']) && $_SESSION['id'] == $this->getIdUser())) {
                $badgeNonPublie = "<div style='position: absolute; top: 10px; left: 10px; z-index: 10; background-color: #d9534f; color: white; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; text-transform: uppercase;'>Brouillon</div>";
            }
        }

        $html = "
        <div class='col-sm-6 col-md-4 col-lg-3' style='margin-bottom: 25px;'>
            <div class='thumbnail shadow-hover' style='height: 400px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease; padding: 0; border: 1px solid $c_marron_clair; background-color: $c_marron_fonce; position: relative;'>
                $badgeNonPublie
                <a href='chanson_voir.php?id=$_id' style='text-decoration: none;'>
                    <div style='height: 180px; overflow: hidden; background-color: $c_marron_clair; display: flex; align-items: center; justify-content: center; border-bottom: 3px solid $c_accent;'>
                        $imagePochette
                    </div>
                </a>
                <div class='caption' style='padding: 15px; text-align: center; color: $c_beige;'>
                    <h4 style='margin-top: 0; margin-bottom: 5px; color: $c_marron_clair; height: 44px; overflow: hidden; font-weight: bold;'>$titre</h4>
                    <p style='height: 20px; overflow: hidden; margin-bottom: 10px; font-style: italic;'>
                        <a href='$urlInterprete' title='Filtrer par cet interprète' style='color: #9e8d8a; text-decoration: none;'>$interpreteAffiche</a>
                    </p>
                    <div style='margin-bottom: 15px; height: 25px;'>
                        <a href='$urlAnnee' title='Filtrer par cette année' style='text-decoration: none;'>
                            <span class='label' style='background-color: $c_marron_clair; color: $c_marron_fonce; cursor: pointer;'>$annee</span>
                        </a>
                        <a href='$urlTempo' title='Filtrer par ce tempo' style='text-decoration: none;'>
                            <span class='label' style='background-color: #777; color: white; cursor: pointer;'>$tempo BPM</span>
                        </a>
                        <a href='$urlTonalite' title='Filtrer par cette tonalité' style='text-decoration: none;'>
                            <span class='label' style='background-color: $c_accent; color: white; cursor: pointer;'>$tonalite</span>
                        </a>
                    </div>
                    <div class='btn-group btn-group-justified' role='group'>
                        <div class='btn-group' role='group'>
                            <a href='chanson_voir.php?id=$_id' class='btn' style='background-color: $c_marron_clair; color: $c_marron_fonce; font-weight: bold; border-radius: 0;'>Voir</a>
                        </div>";
        
        if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
            $html .= "
                        <div class='btn-group' role='group'>
                            <a href='chanson_form.php?id=$_id' class='btn' style='background-color: $c_accent; color: white; border-radius: 0; border: none;'>Editer</a>
                        </div>";
        }
        
        $html .= "
                    </div>
                </div>
            </div>
        </div>";
        
        return $html;
    }
}

/// TODO fonctions à supprimer

// Cherche les chansons correspondant à un critère
function chercheChansons($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
{
    $maRequete = "SELECT * FROM chanson WHERE $critere LIKE '$valeur'";
    // Ajout filtre publication si pas admin
    if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
        $maRequete .= " AND publication = 1";
    }
    $maRequete .= " ORDER BY $critereTri";
    if (!$bTriAscendant) {
        $maRequete .= " DESC";
    } else {
        $maRequete .= " ASC";
    }
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
