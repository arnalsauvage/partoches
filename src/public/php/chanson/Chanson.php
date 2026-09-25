<?php
if (!defined('PHP_DIR')) {
    $autoload = file_exists(dirname(__DIR__, 2) . '/autoload.php') ? dirname(__DIR__, 2) . '/autoload.php' : dirname(__DIR__, 3) . '/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }
}

/**
 * CLASSE : Chanson (Entité)
 * Responsabilité : Gérer les données d'une partition et sa persistance unitaire.
 */
class Chanson
{
    const D_M_Y = "d/m/Y";
    const MYSQL = 'mysql';

    private int $_id = 0;
    private string $_nom = "";
    private string $_interprete = "";
    private int $_annee = 1975;
    private int $_idUser = 1;
    private int $_tempo = 120;
    private string $_mesure = "4/4";
    private string $_pulsation = "binaire";
    private string $_datePub = "";
    private int $_hits = 0;
    private string $_tonalite = "C";
    private ?string $_tonaliteOriginale = null;
    private ?string $_cover = null;
    private int $_publication = 1;

    function __construct()
    {
        $this->__construct0();
    }

    public function __construct0()
    {
        $this->_id = 0;
        $this->_nom = "";
        $this->_interprete = "";
        $this->_annee = 1975;
        $this->_idUser = 1;
        $this->_tempo = 120;
        $this->_mesure = "4/4";
        $this->_pulsation = "binaire";
        $this->_datePub = convertitDateJJMMAAAAversMySql(date(self::D_M_Y));
        $this->_hits = 0;
        $this->_tonalite = "C";
        $this->_tonaliteOriginale = null;
        $this->_cover = null; 
        $this->_publication = 1;
    }

    public static function load(int $id): self
    {
        $c = new self();
        $c->loadInstance($id);
        return $c;
    }

    private function loadInstance(int $id): bool
    {
        $db = $_SESSION[self::MYSQL];
        $sql = sprintf("SELECT * FROM chanson WHERE id = %d", $id);
        $res = $db->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            $this->mysqlRowVersObjet($row);
            return true;
        }
        return false;
    }

    private function mysqlRowVersObjet(array $row)
    {
        if (isset($row['id'])) {
            $this->_id = (int)$row['id'];
            $this->_nom = (string)($row['nom'] ?? '');
            $this->_interprete = (string)($row['interprete'] ?? '');
            $this->_annee = (int)($row['annee'] ?? 1975);
            $this->_tempo = (int)($row['tempo'] ?? 120);
            $this->_mesure = (string)($row['mesure'] ?? '4/4');
            $this->_pulsation = (string)($row['pulsation'] ?? 'binaire');
            $this->_datePub = (string)($row['datePub'] ?? '');
            $this->_idUser = (int)($row['idUser'] ?? 1);
            $this->_hits = (int)($row['hits'] ?? 0);
            $this->_tonalite = (string)($row['tonalite'] ?? 'C');
            $this->_tonaliteOriginale = $row['tonalite_originale'] ?? null;
            $this->_cover = $row['cover'] ?? null;
            $this->_publication = (int)($row['publication'] ?? 1);
        } else {
            $this->_id = (int)($row[0] ?? 0);
            $this->_nom = (string)($row[1] ?? '');
            $this->_interprete = (string)($row[2] ?? '');
            $this->_annee = (int)($row[3] ?? 1975);
            $this->_tempo = (int)($row[4] ?? 120);
            $this->_mesure = (string)($row[5] ?? '4/4');
            $this->_pulsation = (string)($row[6] ?? 'binaire');
            $this->_datePub = (string)($row[7] ?? '');
            $this->_idUser = (int)($row[8] ?? 1);
            $this->_hits = (int)($row[9] ?? 0);
            $this->_tonalite = (string)($row[10] ?? 'C');
            $this->_tonaliteOriginale = $row[11] ?? null;
            $this->_cover = $row[12] ?? null;
            $this->_publication = (int)($row[13] ?? 1);
        }
    }

    public function save(): int
    {
        $db = $_SESSION[self::MYSQL];
        $nom = $db->real_escape_string($this->_nom);
        $interprete = $db->real_escape_string($this->_interprete);
        $annee = (int)$this->_annee;
        $cover = $db->real_escape_string($this->_cover ?? '');
        
        if (empty($this->_datePub) || $this->_datePub == '0000-00-00') {
            $this->_datePub = convertitDateJJMMAAAAversMySql(date(self::D_M_Y));
        }

        if ($this->_id == 0) {
            $sql = sprintf("INSERT INTO chanson (nom, interprete, annee, idUser, tempo, mesure, pulsation, datePub, hits, tonalite, tonalite_originale, cover, publication)
                VALUES ('%s', '%s', %d, %d, %d, '%s', '%s', '%s', %d, '%s', '%s', '%s', %d)",
                $nom, $interprete, $annee, $this->_idUser, $this->_tempo,
                $db->real_escape_string($this->_mesure), $db->real_escape_string($this->_pulsation),
                $db->real_escape_string($this->_datePub), $this->_hits, 
                $db->real_escape_string($this->_tonalite), $db->real_escape_string($this->getTonaliteOriginale() ?? ''), $cover, $this->_publication);
            $db->query($sql) or die ("Chanson::save(INSERT) error : " . $db->error);
            $this->_id = $db->insert_id;
        } else {
            $sql = sprintf("UPDATE chanson SET nom='%s', interprete='%s', annee=%d, idUser=%d, tempo=%d, mesure='%s', pulsation='%s', 
                hits=%d, tonalite='%s', tonalite_originale='%s', datePub='%s', cover='%s', publication=%d WHERE id=%d",
                $nom, $interprete, $annee, $this->_idUser, $this->_tempo,
                $db->real_escape_string($this->_mesure), $db->real_escape_string($this->_pulsation),
                $this->_hits, $db->real_escape_string($this->_tonalite), $db->real_escape_string($this->getTonaliteOriginale() ?? ''),
                $db->real_escape_string($this->_datePub), $cover, $this->_publication, $this->_id);
            $db->query($sql) or die ("Chanson::save(UPDATE) error : " . $db->error);
        }
        return $this->_id;
    }

    // --- GETTERS / SETTERS ---

    public function getId(): int { return $this->_id; }
    public function setId(int $id): void { if ($id >= 0) $this->_id = $id; }

    public function getNom(): string { return $this->_nom; }
    public function setNom(string $nom): void { $this->_nom = $nom; }

    public function getInterprete(): string { return $this->_interprete; }
    public function setInterprete(string $interprete) { $this->_interprete = $interprete; }

    public function getAnnee(): int { return $this->_annee; }
    public function setAnnee(int $annee): void { if ($annee > 0) $this->_annee = $annee; }

    public function getIdUser(): int { return $this->_idUser; }
    public function setIdUser(int $idUser) { if ($idUser > 0) $this->_idUser = $idUser; }

    public function getTempo(): int { return $this->_tempo; }
    public function setTempo(int $tempo): void { if ($tempo > 0) $this->_tempo = $tempo; }

    public function getMesure(): string { return $this->_mesure; }
    public function setMesure(string $mesure) { $this->_mesure = $mesure; }

    public function getPulsation(): string { return $this->_pulsation ?? ""; }
    public function setPulsation(string $pulsation) { $this->_pulsation = $pulsation; }

    public function getDatePub(): string { return $this->_datePub; }
    public function setDatePub(string $datePub) { $this->_datePub = $datePub; }

    public function getHits(): int { return $this->_hits; }
    public function setHits(int $hits): void { if ($hits >= 0) $this->_hits = $hits; }

    public function getTonalite(): string { return $this->_tonalite; }
    public function setTonalite(string $tonalite) { $this->_tonalite = $tonalite; } 

    public function getTonaliteOriginale(): ?string { return $this->_tonaliteOriginale; }
    public function setTonaliteOriginale(?string $v): void { $this->_tonaliteOriginale = self::normalizeTonaliteOriginale($v); }

    public static function normalizeTonaliteOriginale(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim($v);
        if ($v === '') return null;
        $v = preg_replace('/\s+/', '', $v);
        $v = str_replace(['maj', 'M'], '', $v);
        $v = str_replace(['min', 'mineur'], 'm', $v);
        $v = preg_replace('/[^A-Ga-g#bm]/', '', $v);
        return $v !== '' ? $v : null;
    }

    public function getCover(): ?string { return $this->_cover; }
    public function setCover(?string $cover): void { $this->_cover = $cover; }

    public function getPublication(): int { return $this->_publication; }
    public function setPublication(int $publication): void { $this->_publication = $publication; }

    // --- COMPATIBILITÉ TEST & LEGACY ---

    public function creeChansonBDD() { return $this->save(); }
    public function modifieChansonBDD() { return $this->save(); }
    public function creeModifieChansonBDD() { return $this->save(); }
    public function chercheChanson($id) { return $this->loadInstance((int)$id) ? 1 : 0; }
    public function supprimeChansonBddFile() { $this->delete(); }
    public function chercheChansonParLeNom($nom) { 
        $db = $_SESSION[self::MYSQL];
        $res = $db->query(sprintf("SELECT * FROM chanson WHERE nom = '%s'", $db->real_escape_string($nom)));
        if ($res && ($row = $res->fetch_assoc())) { $this->mysqlRowVersObjet($row); return 1; }
        return 0;
    }

    // --- PERSISTANCE (CRUD UNITAIRE) ---

    public function delete(): void
    {
        $db = $_SESSION[self::MYSQL];
        $db->query("DELETE FROM chanson WHERE id = " . $this->_id);
        if (class_exists('Document')) {
            $res = Document::chercheDocumentsTableId("chanson", $this->_id);
            while ($row = $res->fetch_row()) {
                Document::supprimeDocument((int)$row[0]);
            }
        }
    }

    // --- RECHERCHE ET LISTING (Délégation au Repository) ---

    public static function search($query = '%', $sortBy = 'nom', $asc = true, $filterField = "", $filterValue = "", $limit = -1, $offset = 0): array
    {
        if (!class_exists('ChansonRepository')) require_once __DIR__ . '/ChansonRepository.php';
        return ChansonRepository::search($query, $sortBy, $asc, $filterField, $filterValue, $limit, $offset);
    }

    public static function count(string $query = '%', string $filterField = "", $filterValue = ""): int
    {
        if (!class_exists('ChansonRepository')) require_once __DIR__ . '/ChansonRepository.php';
        return ChansonRepository::count($query, $filterField, $filterValue);
    }

    /**
     * @deprecated Utiliser ChansonRepository::search
     */
    public static function chercheChansons($critere, $critereTri = 'nom', $bTriAscendant = true, $champFiltre = "", $valfiltre = "", $limit = -1, $offset = 0): array
    {
        return self::search($critere, $critereTri, $bTriAscendant, $champFiltre, $valfiltre, $limit, $offset);
    }

    /**
     * @deprecated Utiliser ChansonRepository::count
     */
    public static function compteChansons($critere, $champFiltre = "", $valfiltre = ""): int
    {
        return self::count($critere, $champFiltre, $valfiltre);
    }

    public static function moteurRecherche($recherche): string
    {
        $rechercheNormalisee = self::normalize($recherche);
        $db = $_SESSION[self::MYSQL];
        $maRequete = "SELECT id, nom, interprete FROM chanson";
        if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
            $maRequete .= " WHERE publication = 1";
        }
        
        $result = $db->query($maRequete) or die("Chanson::moteurRecherche error : " . $db->error);
        $matches = [];
        while ($row = $result->fetch_assoc()) {
            $normalized_titre = self::normalize($row["nom"]);
            $normalized_interprete = self::normalize($row["interprete"]);
            $compact_recherche = str_replace(' ', '', $rechercheNormalisee);
            $compact_titre = str_replace(' ', '', $normalized_titre);
            $compact_interprete = str_replace(' ', '', $normalized_interprete);

            if (str_contains($normalized_titre, $rechercheNormalisee) || 
                str_contains($normalized_interprete, $rechercheNormalisee) ||
                ($compact_recherche !== '' && (str_contains($compact_titre, $compact_recherche) || str_contains($compact_interprete, $compact_recherche)))) {
                $distance = 0;
            } else {
                $distance = min(levenshtein($rechercheNormalisee, $normalized_titre), levenshtein($rechercheNormalisee, $normalized_interprete));
            }
            $row['distance'] = $distance;
            $matches[] = $row;
        }
        usort($matches, fn($a, $b) => $a['distance'] <=> $b['distance']);
        $top = array_slice($matches, 0, 10);
        return (count($top) > 0) ? $top[0]["nom"] : "0 résultats";
    }

    /**
     * @deprecated Utiliser ChansonRepository::getLinks
     */
    public function chercheLiensChanson(): ?mysqli_result
    {
        if (!class_exists('ChansonRepository')) require_once __DIR__ . '/ChansonRepository.php';
        return ChansonRepository::getLinks($this->_id);
    }

    /**
     * @deprecated Utiliser ChansonRepository::getSongbooks
     */
    public function chercheSongbooksDocuments(): ?mysqli_result
    {
        if (!class_exists('ChansonRepository')) require_once __DIR__ . '/ChansonRepository.php';
        return ChansonRepository::getSongbooks($this->_id);
    }

    /**
     * @deprecated Utiliser ChansonRepository::getPhysicalFiles
     */
    public function fichiersChanson(string $dossier): array
    {
        if (!class_exists('ChansonRepository')) require_once __DIR__ . '/ChansonRepository.php';
        return ChansonRepository::getPhysicalFiles($this->_id, $dossier);
    }

    public function infosChanson(): string
    {
        return "Id : {$this->_id} Nom : {$this->_nom} Interprète : {$this->_interprete} Année : {$this->_annee} " .
               "idUSer : {$this->_idUser} tempo : {$this->_tempo} mesure : {$this->_mesure} pulsation : {$this->_pulsation} " .
               "hits : {$this->_hits} tonalité : {$this->_tonalite} publication : {$this->_publication}<BR>\n";
    }

    // --- UTILITAIRES ---

    public static function normalize($string): string
    {
        $string = mb_strtolower($string, 'UTF-8');
        $string = preg_replace('/[áàâãäå]/u', 'a', $string);
        $string = preg_replace('/[éèêë]/u', 'e', $string);
        $string = preg_replace('/[íìîï]/u', 'i', $string);
        $string = preg_replace('/[óòôõö]/u', 'o', $string);
        $string = preg_replace('/[úùûü]/u', 'u', $string);
        $string = preg_replace('/[ýÿ]/u', 'y', $string);
        $string = preg_replace('/ç/u', 'c', $string);
        $string = preg_replace('/ñ/u', 'n', $string);
        $string = preg_replace('/[^a-z0-9]/', ' ', $string);
        $string = preg_replace('/\s+/', ' ', $string);
        return trim($string);
    }

    public static function getTonaliteEquivalents(string $tonalite): array
    {
        $isMinor = (str_ends_with($tonalite, 'm'));
        $root = $isMinor ? substr($tonalite, 0, -1) : $tonalite;
        $map = ['A#'=>'Bb','Bb'=>'A#','C#'=>'Db','Db'=>'C#','D#'=>'Eb','Eb'=>'D#','F#'=>'Gb','Gb'=>'F#','G#'=>'Ab','Ab'=>'G#','B#'=>'C','C'=>'B#','E#'=>'F','F'=>'E#','Cb'=>'B','B'=>'Cb','Fb'=>'E','E'=>'Fb'];
        $suffix = $isMinor ? 'm' : '';
        $equivalents = [$root . $suffix];
        if (isset($map[$root])) $equivalents[] = $map[$root] . $suffix;
        return array_unique($equivalents);
    }

    // --- RENDU (Délégation au Renderer) ---

    public function afficheCarteChanson(): string
    {
        if (!class_exists('ChansonRenderer')) require_once __DIR__ . '/ChansonRenderer.php';
        return ChansonRenderer::renderCard($this);
    }
}
