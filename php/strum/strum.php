<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/utilssi.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/configMysql.php";

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
        $maRequete = sprintf("SELECT * FROM strum WHERE id = %d", $id);
        $result = $db->query($maRequete);
        if ($result && $ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return true;
        }
        return false;
    }

    /**
     * Hydrate l'objet depuis une ligne MySQL
     */
    private function mysqlRowVersObjet(array $ligne): void
    {
        $this->_id = (int)$ligne[0];
        $this->_unite = (int)$ligne[1];
        $this->_longueur = (int)$ligne[2];
        $this->_strum = (string)$ligne[3];
        $this->_description = (string)$ligne[4];
        $this->_swing = (int)($ligne[5] ?? 0);
    }

    /**
     * Enregistre ou met à jour en BDD
     */
    public function enregistreBDD(): int
    {
        $db = $_SESSION[self::MYSQL];
        $strum = $db->real_escape_string($this->_strum);
        $desc = $db->real_escape_string($this->_description);

        if ($this->_id == 0) {
            $maRequete = sprintf("INSERT INTO strum (unite, longueur, strum, description, swing) 
                VALUES (%d, %d, '%s', '%s', %d)",
                $this->_unite, $this->_longueur, $strum, $desc, $this->_swing);
            $db->query($maRequete) or die($db->error);
            $this->_id = $db->insert_id;
        } else {
            $maRequete = sprintf("UPDATE strum SET unite=%d, longueur=%d, strum='%s', description='%s', swing=%d WHERE id=%d",
                $this->_unite, $this->_longueur, $strum, $desc, $this->_swing, $this->_id);
            $db->query($maRequete) or die($db->error);
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
        $swingParam = $this->_swing ? "&swing=1" : "";
        
        $urlBoiteAstrum = "../../html/boiteAstrum/index.html";
        $imageBoiteAstrum = "../../html/boiteAstrum/medias/img/boiteAstrum.png";

        // On compte le nombre d'utilisations
        $db = $_SESSION[self::MYSQL];
        $res = $db->query("SELECT COUNT(*) FROM lienstrumchanson WHERE idStrum = $id");
        $count = ($res) ? $res->fetch_row()[0] : 0;

        $badgeSwing = $this->_swing ? "<span class='label label-warning' style='background-color: #f39c12; margin-left: 5px;'>SWING</span>" : "";

        return "
        <div class='col-sm-6 col-md-4 col-lg-3'>
            <div class='thumbnail strum-card'>
                <div class='strum-card-header'>
                    <h2>$strumDisplay</h2>
                </div>
                <div class='strum-card-body'>
                    <p class='strum-card-desc'>$desc</p>
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
            $return .= " <a href='strum_form.php?id=$id' class='btn btn-xs btn-primary' title='Editer'><i class='glyphicon glyphicon-pencil'></i></a>";
        }
        if (aDroits($GLOBALS["PRIVILEGE_EDITEUR"])) {
            $return .= " <a href='strum_post.php?id=$id&mode=SUPPR' class='btn btn-xs btn-danger' title='Supprimer' onclick='return confirm(\"Supprimer ce strum ?\")'><i class='glyphicon glyphicon-trash'></i></a>";
        }
        
        return $return . "
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    }

    /**
     * Charge tous les strums
     */
    public static function chargeStrumsBdd(): array
    {
        $db = $_SESSION[self::MYSQL];
        $result = $db->query("SELECT * FROM strum ORDER BY strum");
        $liste = [];
        while ($ligne = $result->fetch_row()) {
            $s = new Strum();
            $s->mysqlRowVersObjet($ligne);
            $liste[] = $s;
        }
        return $liste;
    }
}
