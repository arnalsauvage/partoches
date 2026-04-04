<?php
const DATA_SONGBOOKS = "../../data/songbooks/";

require_once dirname(__DIR__) . "/lib/utilssi.php";
require_once dirname(__DIR__) . "/document/Document.php";
require_once dirname(__DIR__) . "/lib/Image.php";

$songbookForm = "songbook_form.php";
$songbookGet = "songbook_get.php";
$songbookVoir = "songbook_voir.php";
$songbookListe = "songbook_liste.php";
$cheminImagesSongbook = "../../data/songbooks/";

/**
 * Classe de gestion des Songbooks (Livres de partitions)
 */
class Songbook
{
    const MYSQL = 'mysql';
    private int $_id;
    private string $_nom;
    private string $_description;
    private string $_date;
    private string $_image;
    private int $_hits;
    private int $_idUser;
    private int $_type; // 1: Anthologie, 2: Concert, 3: Thème

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
        $this->setNom("");
        $this->setDescription("");
        $this->setDate(date("Y-m-d"));
        $this->setImage("");
        $this->setHits(0);
        $this->setIdUser($_SESSION['id'] ?? 1);
        $this->setType(1);
    }

    public function __construct1(int $id)
    {
        $this->__construct0();
        $this->chercheSongbook($id);
    }

    // Getters & Setters
    public function getId(): int { return $this->_id; }
    public function setId(int $id): void { $this->_id = $id; }

    public function getNom(): string { return $this->_nom; }
    public function setNom(string $nom): void { $this->_nom = $nom; }

    public function getDescription(): string { return $this->_description; }
    public function setDescription(string $description): void { $this->_description = $description; }

    public function getDate(): string { return $this->_date; }
    public function setDate(string $date): void { $this->_date = $date; }

    public function getImage(): string { return $this->_image; }
    public function setImage(string $image): void { $this->_image = $image; }

    public function getHits(): int { return $this->_hits; }
    public function setHits(int $hits): void { $this->_hits = $hits; }

    public function getIdUser(): int { return $this->_idUser; }
    public function setIdUser(int $idUser): void { $this->_idUser = $idUser; }

    public function getType(): int { return $this->_type; }
    public function setType(int $type): void { $this->_type = $type; }

    /**
     * Retourne le libellé du type de songbook
     */
    public function getLabelType(): string
    {
        return match($this->_type) {
            1 => "Anthologie",
            2 => "Concert",
            3 => "Thématique",
            default => "Inconnu"
        };
    }

    /**
     * Charge les données depuis la BDD
     */
    public function chercheSongbook(int $id): bool
    {
        $maRequete = sprintf("SELECT * FROM songbook WHERE id = '%s'", $id);
        $result = $_SESSION[self::MYSQL]->query($maRequete);
        if ($ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return true;
        }
        return false;
    }

    /**
     * Hydrate l'objet depuis une ligne MySQL
     */
    public function mysqlRowVersObjet(array $ligne): void
    {
        $this->_id = (int)$ligne[0];
        $this->_nom = $ligne[1];
        $this->_description = $ligne[2];
        $this->_date = $ligne[3];
        $this->_image = $ligne[4] ?? "";
        $this->_hits = (int)$ligne[5];
        $this->_idUser = (int)$ligne[6];
        $this->_type = (int)($ligne[7] ?? 1);
    }

    /**
     * Sauvegarde ou met à jour en BDD
     */
    public function enregistreBDD(): int
    {
        $db = $_SESSION[self::MYSQL];
        $nom = $db->real_escape_string($this->_nom);
        $desc = $db->real_escape_string($this->_description);
        $date = convertitDateJJMMAAAAversMySql($this->_date);
        $image = $db->real_escape_string($this->_image);

        if ($this->_id == 0) {
            $maRequete = sprintf("INSERT INTO songbook (nom, description, date, image, hits, idUser, type) 
                VALUES ('%s', '%s', '%s', '%s', %d, %d, %d)",
                $nom, $desc, $date, $image, $this->_hits, $this->_idUser, $this->_type);
            $db->query($maRequete) or die($db->error);
            $this->_id = $db->insert_id;
        } else {
            $maRequete = sprintf("UPDATE songbook SET nom='%s', description='%s', date='%s', image='%s', hits=%d, type=%d WHERE id=%d",
                $nom, $desc, $date, $image, $this->_hits, $this->_type, $this->_id);
            $db->query($maRequete) or die($db->error);
        }
        return $this->_id;
    }

    /**
     * Affiche une carte moderne (style Canopée) pour le songbook
     */
    public function afficheCarteSongbook(): string
    {
        $_id = $this->getId();
        $nom = htmlspecialchars($this->getNom());
        $desc = htmlspecialchars(limiteLongueur($this->getDescription(), 50));
        $typeLabel = $this->getLabelType();
        $hits = $this->getHits();
        $date = dateMysqlVersTexte($this->getDate());

        // Palette Canopée
        $c_marron_fonce = "#2b1d1a";
        $c_marron_clair = "#D2B48C"; 
        $c_accent = "#8B4513";
        $c_beige = "#F5F5DC";

        // Image de couverture moderne via Image.php
        $imageFile = imageSongbook($_id);
        if ($imageFile) {
            $srcImage = Image::getThumbnailUrl($_id . "/" . $imageFile, 'sd', 'songbooks');
            $imgHtml = "<a href='songbook_voir.php?id=$_id'><img src='$srcImage' alt='$nom' style='width: 100%; height: 100%; object-fit: cover;'></a>";
        } else {
            $imgHtml = "<a href='songbook_voir.php?id=$_id' style='text-decoration:none;'><span class='glyphicon glyphicon-book' style='font-size: 50px; color: $c_marron_fonce; opacity: 0.3;'></span></a>";
        }

        // Couleur selon le type
        $badgeColor = match($this->getType()) {
            1 => "#e67e22", // Orange (Anthologie)
            2 => "#d35400", // Orange Foncé (Concert)
            3 => "#27ae60", // Vert (Thème)
            default => "#777"
        };

        $html = "
        <div class='col-sm-6 col-md-4 col-lg-3' style='margin-bottom: 30px;'>
            <div class='thumbnail shadow-hover' style='height: 450px; width: 100%; max-width: 280px; margin: 0 auto; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.3s ease; padding: 0; border: 1px solid #ddd; background-color: white;'>
                <div style='height: 280px; overflow: hidden; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #eee; position: relative;'>
                    $imgHtml
                    <span class='label' style='position: absolute; bottom: 10px; right: 10px; background-color: $badgeColor; color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);'>$typeLabel</span>
                </div>
                <div class='caption' style='padding: 12px; text-align: center;'>
                    <h4 style='margin-top: 5px; margin-bottom: 8px; color: $c_marron_fonce; height: 40px; overflow: hidden; font-weight: bold; font-size: 16px;'>$nom</h4>
                    <p style='height: 35px; overflow: hidden; font-size: 11px; color: #888; margin-bottom: 10px;'>$desc</p>

                    <div style='display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f5f5f5; padding-top: 10px;'>
                        <span style='font-size: 10px; color: #bbb;'><i class='glyphicon glyphicon-calendar'></i> $date</span>
                        <span style='font-size: 10px; color: #bbb;'><i class='glyphicon glyphicon-eye-open'></i> $hits</span>
                    </div>
                    
                    <div style='margin-top: 15px;'>
                        <a href='songbook_voir.php?id=$_id' class='btn btn-xs' style='background-color: $c_marron_fonce; color: white; padding: 5px 20px; border-radius: 15px;'>OUVRIR</a>";
        
        if (aDroits($GLOBALS["PRIVILEGE_EDITEUR"])) {
            $html .= " <a href='songbook_form.php?id=$_id' class='btn btn-xs btn-link' style='color: $c_accent;'><i class='glyphicon glyphicon-pencil'></i></a>";
        }
        
        return $html . "
                    </div>
                </div>
            </div>
        </div>";
    }

    /**
     * Recherche statique de songbooks avec multi-critères
     */
    public static function chercheSongbooks(string $recherche = "%", string $type = "", string $tri = 'nom', bool $asc = true): array
    {
        $db = $_SESSION[self::MYSQL];
        $recherche = $db->real_escape_string($recherche);
        $order = $asc ? "ASC" : "DESC";
        
        $conditions = [];
        if ($recherche !== "%" && $recherche !== "") {
            $conditions[] = "(nom LIKE '%$recherche%' OR description LIKE '%$recherche%')";
        }
        if ($type !== "") {
            $type = (int)$type;
            $conditions[] = "type = $type";
        }
        
        $where = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";
        
        $maRequete = "SELECT * FROM songbook $where ORDER BY $tri $order";
        $result = $db->query($maRequete) or die($db->error);
        
        $liste = [];
        while ($row = $result->fetch_row()) {
            $sb = new Songbook();
            $sb->mysqlRowVersObjet($row);
            $liste[] = $sb;
        }
        return $liste;
    }

    /**
     * Retourne la liste des songbooks pour un combo
     */
    public static function listeSongbooks(int $type = 0): array
    {
        $critere = ($type == 0) ? "nom" : "type";
        $valeur = ($type == 0) ? "%" : $type;
        $sbs = self::chercheSongbooks($critere, $valeur);
        $liste = [];
        foreach ($sbs as $sb) {
            $liste[] = [$sb->getId(), $sb->getNom()];
        }
        return $liste;
    }
}

// --- FONCTIONS WRAPPERS (POUR COMPATIBILITÉ) ---

function chercheSongbooks($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true): object
{
    // On simule l'ancien retour (mysqli_result) pour ne pas tout casser d'un coup
    $order = $bTriAscendant ? "ASC" : "DESC";
    $maRequete = "SELECT * FROM songbook WHERE $critere LIKE '$valeur' ORDER BY $critereTri $order";
    return $_SESSION['mysql']->query($maRequete);
}

function chercheSongbook($id): array
{
    $sb = new Songbook((int)$id);
    // On simule l'ancien retour (tableau indexé)
    return [
        $sb->getId(), $sb->getNom(), $sb->getDescription(), $sb->getDate(), 
        $sb->getImage(), $sb->getHits(), $sb->getIdUser(), $sb->getType()
    ];
}

function chercheSongbookParLeNom($nom): array
{
    $db = $_SESSION['mysql'];
    $nomEsc = $db->real_escape_string($nom);
    $result = $db->query("SELECT id FROM songbook WHERE nom = '$nomEsc'");
    if ($row = $result->fetch_row()) {
        return chercheSongbook($row[0]);
    }
    return [];
}

function creeSongbook($nom, $description, $date, $image, $hits, $type)
{
    $sb = new Songbook();
    $sb->setNom((string)($nom ?? ''));
    $sb->setDescription((string)($description ?? ''));
    $sb->setDate((string)($date ?? date('d/m/Y')));
    $sb->setImage((string)($image ?? ''));
    $sb->setHits((int)($hits ?? 0));
    $sb->setType((int)($type ?? 1));
    return $sb->enregistreBDD();
}

function modifiesSongbook($id, $nom, $description, $date, $image, $hits, $type)
{
    $sb = new Songbook((int)$id);
    $sb->setNom((string)($nom ?? ''));
    $sb->setDescription((string)($description ?? ''));
    $sb->setDate((string)($date ?? date('d/m/Y')));
    $sb->setImage((string)($image ?? ''));
    $sb->setHits((int)($hits ?? 0));
    $sb->setType((int)($type ?? 1));
    return $sb->enregistreBDD();
}

function supprimeSongbook($idsongbook)
{
    $db = $_SESSION['mysql'];
    $maRequete = "DELETE FROM songbook WHERE id='$idsongbook'";
    $db->query($maRequete);
    supprimeliensDocSongbookDuSongbook($idsongbook);
    return true;
}

function dupliqueSongbook($idSongbook): bool
{
    $mod = new Songbook((int)$idSongbook);
    if ($mod->getId() == 0) return false;

    $new = new Songbook();
    $new->setNom("Copie de " . $mod->getNom());
    $new->setDescription("Songbook créé par copie");
    $new->setType($mod->getType());
    $newId = $new->enregistreBDD();

    $result = chercheLiensDocSongbook("idSongbook", $idSongbook, "ordre", true);
    while ($ligne = mysqli_fetch_assoc($result)) {
        creelienDocSongbook($ligne["idDocument"], $newId);
    }
    return true;
}

function imageSongbook($idSongbook): string
{
    $db = $_SESSION['mysql'];
    $maRequete = "SELECT * FROM document WHERE document.idTable = '$idSongbook' AND document.nomTable='songbook' ";
    $maRequete .= " AND ( document.nom LIKE '%.png' OR document.nom LIKE '%.jpg') LIMIT 1";
    $result = $db->query($maRequete);
    if ($ligne = $result->fetch_row()) {
        return composeNomVersion($ligne[1], $ligne[4]);
    }
    return "";
}

function infosSongbook($id): string
{
    $sb = new Songbook((int)$id);
    return "Id : " . $sb->getId() . " Nom : " . $sb->getNom() . " Type : " . $sb->getLabelType() . "<BR>\n";
}

function fichiersSongbook($id): array
{
    $retour = [];
    $repertoire = DATA_SONGBOOKS . "$id/";
    if (is_dir($repertoire)) {
        foreach (new DirectoryIterator ($repertoire) as $fileInfo) {
            if (!$fileInfo->isDot() && strpos($fileInfo->getFilename(), ".") != 0) {
                $retour[] = [$repertoire, $fileInfo->getFilename(), $fileInfo->getextension()];
            }
        }
    }
    return $retour;
}

function CreeSongBookPdf($idSongbook): array
{
    $db = $_SESSION['mysql'];
    $listeNomsChanson = []; $listeNomsFichier = []; $listeIdChanson = []; $listeVersionsDoc = [];

    $maRequete = "SELECT document.nom, chanson.nom as t, chanson.id, document.version 
                  FROM document 
                  LEFT JOIN liendocsongbook ON liendocsongbook.idDocument = document.id 
                  LEFT JOIN chanson ON document.idTable = chanson.id
                  WHERE liendocsongbook.idSongbook = '$idSongbook' ORDER BY liendocsongbook.ordre ASC";
    $result = $db->query($maRequete);
    while ($ligne = mysqli_fetch_row($result)) {
        $listeNomsFichier[] = $ligne[0];
        $listeNomsChanson[] = $ligne[1];
        $listeIdChanson[] = $ligne[2];
        $listeVersionsDoc[] = $ligne[3];
    }
    
    $sb = new Songbook((int)$idSongbook);
    $image = imageSongbook($idSongbook);
    $nomGenere = make_alias("songbook_" . $sb->getNom()) . '.pdf';
    $doc = chercheDocumentNomTableId($nomGenere, "songbook", $idSongbook);
    
    // On retourne le résultat du service
    return pdfCreeSongbookResult($idSongbook, $doc[4], $sb->getNom(), $image, $listeNomsChanson, $listeNomsFichier, $listeIdChanson, $listeVersionsDoc);
}

/**
 * Nouvelle version de la fonction de création qui retourne le tableau de résultats
 */
function pdfCreeSongbookResult($id, $version, $intitule, $image, $songs, $files, $ids, $versions): array
{
    ini_set('memory_limit', '512M');
    set_time_limit(300);
    $service = new SongbookPdfService();
    return $service->create($id, (int)$version, $intitule, $image, $songs, $files, $ids, $versions);
}

function listeSongbooks($type = 0): array
{
    return Songbook::listeSongbooks((int)$type);
}
