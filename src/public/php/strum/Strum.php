<?php
require_once dirname(__DIR__) . "/lib/utilssi.php";

/**
 * Classe de gestion des rythmiques (Strums)
 */
class Strum
{
    const MYSQL = 'mysql';
    private int $_id;
    private string $_strum; // ex: "B-BH-HBH"
    private int $_unite; // 4, 8, 16
    private int $_longueur; // nb de temps/divisions
    private string $_description;
    private int $_swing; // 1 = ternaire/swing, 0 = binaire

    public function __construct()
    {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = '__construct' . $i)) {
            call_user_func_array(array($this, $f), $a);
        }
    }

    public function __construct0()
    {
        $this->_id = 0;
        $this->_strum = "";
        $this->_unite = 8;
        $this->_longueur = 8;
        $this->_description = "";
        $this->_swing = 0;
    }

    public function __construct1(int $id)
    {
        $this->__construct0();
        if ($id > 0) {
            $this->chercheStrumParId($id);
        }
    }

    public function __construct4(string $strum, int $unite, int $longueur, string $description)
    {
        $this->__construct0();
        $this->_strum = $strum;
        $this->_unite = $unite;
        $this->_longueur = $longueur;
        $this->_description = $description;
    }

    public function __construct5(int $id, string $strum, int $unite, int $longueur, string $description)
    {
        $this->__construct4($strum, $unite, $longueur, $description);
        $this->_id = $id;
    }

    // Getters & Setters
    public function getId(): int { return $this->_id; }
    public function setId(int $id): void { $this->_id = $id; }

    public function getStrum(): string { return $this->_strum; }
    public function setStrum(string $strum): void { $this->_strum = $strum; }

    public function getUnite(): int { return $this->_unite; }
    public function setUnite(int $unite): void { $this->_unite = $unite; }

    public function getLongueur(): int { return $this->_longueur; }
    public function setLongueur(int $longueur): void { $this->_longueur = $longueur; }

    public function getDescription(): string { return $this->_description; }
    public function setDescription(string $description): void { $this->_description = $description; }

    public function getSwing(): int { return $this->_swing; }
    public function setSwing(int $swing): void { $this->_swing = $swing; }

    /**
     * Charge les données depuis la BDD
     */
    public function chercheStrumParId(int $id): bool
    {
        $db = $_SESSION[self::MYSQL];
        $maRequete = "SELECT * FROM strum WHERE id = ?";
        $stmt = $db->prepare($maRequete);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            $this->mysqlRowVersObjet($row);
            return true;
        }
        return false;
    }

    /**
     * Hydrate l'objet depuis un tableau associatif MySQL
     */
    private function mysqlRowVersObjet(array $row): void
    {
        $this->_id = (int)($row['id'] ?? 0);
        $this->_unite = (int)($row['unite'] ?? 8);
        $this->_longueur = (int)($row['longueur'] ?? 8);
        $this->_strum = (string)($row['strum'] ?? "");
        $this->_description = (string)($row['description'] ?? "");
        $this->_swing = (int)($row['swing'] ?? 0);
    }

    /**
     * Enregistre ou met à jour en BDD
     */
    public function enregistreBDD(): int
    {
        $db = $_SESSION[self::MYSQL];
        
        if ($this->_id == 0) {
            $maRequete = "INSERT INTO strum (unite, longueur, strum, description, swing) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($maRequete);
            $stmt->bind_param("iisss", $this->_unite, $this->_longueur, $this->_strum, $this->_description, $this->_swing);
            if ($stmt->execute()) {
                $this->_id = $db->insert_id;
            }
        } else {
            $maRequete = "UPDATE strum SET unite=?, longueur=?, strum=?, description=?, swing=? WHERE id=?";
            $stmt = $db->prepare($maRequete);
            $stmt->bind_param("iisssi", $this->_unite, $this->_longueur, $this->_strum, $this->_description, $this->_swing, $this->_id);
            $stmt->execute();
        }
        return $this->_id;
    }

    /**
     * Supprime le strum et ses liens
     */
    public function supprimeBDD(): void
    {
        $db = $_SESSION[self::MYSQL];
        $db->query("DELETE FROM strum WHERE id = " . $this->_id);
        $db->query("DELETE FROM lienstrumchanson WHERE idStrum = " . $this->_id);
    }

    /**
     * Retourne le libellé de l'unité
     */
    public function renvoieUniteEnFrancais(): string
    {
        return match($this->_unite) {
            4 => "noires",
            8 => "croches",
            16 => "double-croches",
            default => (string)$this->_unite
        };
    }

    /**
     * Affiche une carte moderne (style Canopée) pour le strum
     */
    public function afficheCarteStrum(): string
    {
        $id = $this->getId();
        $strumDisplay = str_replace(" ", "-", $this->getStrum());
        $desc = htmlspecialchars(limiteLongueur($this->getDescription(), 80));
        $unite = $this->renvoieUniteEnFrancais();
        $longueur = $this->getLongueur();
        $swingParam = "&amp;ternaire=" . ($this->_swing ? "true" : "false");
        
        $urlBoiteAstrum = "../../html/boiteAstrum/index.html";
        $imageBoiteAstrum = "../../html/boiteAstrum/medias/img/boiteAstrum.png";

        // On compte le nombre d'utilisations
        $db = $_SESSION[self::MYSQL];
        $res = $db->query("SELECT COUNT(*) FROM lienstrumchanson WHERE idStrum = $id");
        $count = ($res) ? $res->fetch_row()[0] : 0;

        $badgeSwing = $this->_swing ? "<span class='label label-warning' style='background-color: #f39c12; margin-left: 5px;'>SWING</span>" : "";

        $html = "
        <div class='col-sm-6 col-md-4 col-lg-3'>
            <div class='thumbnail strum-card'>
                <div class='strum-card-header'>
                    <h2>$strumDisplay</h2>
                </div>
                <div class='strum-card-body'>
                    <p class='strum-card-desc' style='height: 40px; overflow: hidden;'>$desc</p>
                    <div style='margin-bottom: 15px;'>
                        <span class='label label-default' style='background-color: #777;'>$longueur $unite</span>
                        $badgeSwing
                        <button class='label strum-badge-pop' onclick='voirChansonsStrum($id, \"$strumDisplay\")' title='Voir les chansons liées'>
                            $count <i class='glyphicon glyphicon-music'></i>
                        </button>
                    </div>
                    
                    <div style='display: flex; justify-content: space-between; align-items: center; margin-top: 10px;'>
                        <a title='Ouvrir dans la Boîte à Strum' href='$urlBoiteAstrum?strum=$strumDisplay$swingParam' style='text-decoration: none;'>
                            <img src='$imageBoiteAstrum' alt='Boîte à Strum' height='35' style='border-radius: 4px;'>
                        </a>
                        <div class='btn-group'>";
        
        if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
            $html .= " <a href='strum_form.php?id=$id' class='btn btn-sm btn-primary' title='Editer' style='margin-right: 5px;'><i class='glyphicon glyphicon-pencil'></i></a>";
        }
        if (aDroits($GLOBALS["PRIVILEGE_EDITEUR"])) {
            $html .= " <button type='button' class='btn btn-sm btn-danger' title='Supprimer' onclick='supprimerStrum($id, \"$strumDisplay\")'><i class='glyphicon glyphicon-trash'></i></button>";
        }

        $html .= "      </div>
                    </div>
                </div>
            </div>
        </div>";
        
        return $html;
    }

    /**
     * Cherche un strum par sa chaîne de caractères (pour compatibilité)
     */
    public function chercheStrumParChaine(string $chaine): bool
    {
        $db = $_SESSION[self::MYSQL];
        $maRequete = "SELECT * FROM strum WHERE BINARY strum = ? LIMIT 1";
        $stmt = $db->prepare($maRequete);
        $stmt->bind_param("s", $chaine);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            $this->mysqlRowVersObjet($row);
            return true;
        }
        return false;
    }

    /**
     * Retourne la liste des chansons utilisant ce strum (formatté HTML)
     * @param int $limit Nombre max de chansons à afficher
     */
    public function chansonsDuStrum(int $limit = 3): string
    {
        $chaineRetour = " - utilisé dans ";
        $titresChansons = chargeLibelles("chanson", "nom");
        $db = $_SESSION[self::MYSQL];
        
        // On récupère tout pour compter, mais on limitera l'affichage
        $maRequete = "SELECT idChanson FROM lienstrumchanson WHERE idStrum = ? ORDER BY id DESC";
        $stmt = $db->prepare($maRequete);
        $stmt->bind_param("i", $this->_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $links = [];
        $total = 0;
        if ($result) {
            $total = $result->num_rows;
            $count = 0;
            while ($ligne = $result->fetch_row()) {
                $_idChanson = $ligne[0];
                if (isset($titresChansons[$_idChanson])) {
                    if ($count < $limit) {
                        $links[] = "<a href='../chanson/chanson_voir.php?id=$_idChanson' style='color: inherit; font-style: italic;'>" . $titresChansons[$_idChanson] . "</a>";
                    }
                    $count++;
                }
            }
        }

        if ($total == 0) return " - pas encore utilisé";

        $html = $chaineRetour . implode(", ", $links);
        if ($total > $limit) {
            $reste = $total - $limit;
            $strumDisplay = str_replace(" ", "-", $this->getStrum());
            $html .= " <a href='javascript:void(0)' onclick='voirChansonsStrum({$this->_id}, \"$strumDisplay\")' class='text-primary' style='font-size: 0.9em; font-weight: bold;'> (et $reste autres...)</a>";
        }

        return $html;
    }

    /**
     * Retourne la liste des chansons utilisant un strum par son ID (statique)
     */
    public static function chansonsDuStrumId(int $idStrum): string
    {
        $chaineRetour = " - utilisé dans ";
        $titresChansons = chargeLibelles("chanson", "nom");
        $db = $_SESSION[self::MYSQL];
        $maRequete = "SELECT idChanson FROM lienstrumchanson WHERE idStrum = ?";
        $stmt = $db->prepare($maRequete);
        $stmt->bind_param("i", $idStrum);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $links = [];
        if ($result) {
            while ($ligne = $result->fetch_row()) {
                $_idChanson = $ligne[0];
                if (isset($titresChansons[$_idChanson])) {
                    $links[] = $titresChansons[$_idChanson];
                }
            }
        }
        return count($links) > 0 ? $chaineRetour . implode(", ", $links) : " - pas encore utilisé";
    }

    /**
     * Charge tous les strums avec option de tri et filtre
     * @param string $tri Mode de tri : 'nom', 'date', 'pop'
     * @param string $mesure Filtre par mesure : '4/4', '3/4', '6/8'
     */
    public static function chargeStrumsBdd(string $tri = 'nom', string $mesure = ''): array
    {
        $db = $_SESSION[self::MYSQL];
        
        $where = "";
        if (!empty($mesure)) {
            switch ($mesure) {
                case '4/4':
                    $where = " WHERE (s.unite = 4 AND s.longueur = 4) OR (s.unite = 8 AND s.longueur = 8) OR (s.unite = 16 AND s.longueur = 16)";
                    break;
                case '3t':
                case '3/4':
                case '6/8':
                    $where = " WHERE (s.unite = 4 AND s.longueur = 3) OR (s.unite = 8 AND s.longueur = 6)";
                    break;
                default:
                    $parts = explode('/', $mesure);
                    if (count($parts) == 2) {
                        $where = " WHERE s.longueur = " . (int)$parts[0] . " AND s.unite = " . (int)$parts[1];
                    }
                    break;
            }
        }

        switch ($tri) {
            case 'date':
                $ordre = "s.id DESC";
                $select = "s.*";
                $join = "";
                break;
            case 'pop':
                $ordre = "nb_util DESC, s.strum ASC";
                $select = "s.*, COUNT(l.id) as nb_util";
                $join = "LEFT JOIN lienstrumchanson l ON s.id = l.idStrum";
                break;
            case 'nom':
            default:
                $ordre = "s.description ASC, s.strum ASC";
                $select = "s.*";
                $join = "";
                break;
        }

        $maRequete = "SELECT $select FROM strum s $join $where GROUP BY s.id ORDER BY $ordre";
        $result = $db->query($maRequete);
        
        $liste = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $s = new Strum();
                $s->mysqlRowVersObjet($row);
                $liste[] = $s;
            }
        }
        return $liste;
    }
}
