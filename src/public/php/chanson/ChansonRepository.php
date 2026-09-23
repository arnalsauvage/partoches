<?php
/**
 * CLASSE : ChansonRepository
 * Responsabilité : Gérer les requêtes SQL complexes et les recherches pour l'entité Chanson.
 */

class ChansonRepository
{
    /**
     * Recherche avancée de chansons avec filtres et tris.
     * @return array Tableau d'identifiants de chansons
     */
    public static function search(
        string $query = '%', 
        string $sortBy = 'nom', 
        bool $asc = true, 
        string $filterField = "", 
        $filterValue = "", 
        int $limit = -1, 
        int $offset = 0
    ): array {
        $db = $_SESSION['mysql'];
        $query = $db->real_escape_string($query);

        // Construction du SELECT
        if ($sortBy == "votes") {
            if ($_SESSION['privilege'] == $GLOBALS["PRIVILEGE_INVITE"]) {
                $sql = "SELECT chanson.id, COALESCE(AVG(noteUtilisateur.note), 0) as moy_note FROM chanson 
                        LEFT JOIN noteUtilisateur ON (noteUtilisateur.idObjet = chanson.id AND noteUtilisateur.nomObjet = 'chanson')";
            } else {
                $sql = "SELECT chanson.id, COALESCE(noteUtilisateur.note, 0) as ma_note FROM chanson 
                        LEFT JOIN noteUtilisateur ON (noteUtilisateur.idObjet = chanson.id AND noteUtilisateur.nomObjet = 'chanson' AND noteUtilisateur.idUtilisateur = '" . $_SESSION['id'] . "')";
            }
        } else {
            $sql = "SELECT chanson.id FROM chanson";
        }

        $where = [];

        // Filtre de publication (Sécurité publique)
        if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
            $where[] = "chanson.publication = 1";
        }

        // Recherche textuelle (Titre ou Interprète)
        if ($query != "" && $query != "%") {
            $where[] = "( chanson.nom LIKE '$query' OR chanson.interprete LIKE '$query' )";
        }

        // Filtres spécifiques
        if ($filterField != "" && $filterValue != "") {
            $valEscaped = $db->real_escape_string($filterValue);
            
            if ($filterField == "contributeur") {
                $where[] = "chanson.iduser = $valEscaped";
            } elseif ($filterField == "tonalite") {
                $equivalents = Chanson::getTonaliteEquivalents($filterValue);
                $condTona = [];
                foreach ($equivalents as $eq) {
                    $condTona[] = "chanson.tonalite = '" . $db->real_escape_string($eq) . "'";
                }
                $where[] = "(" . implode(" OR ", $condTona) . ")";
            } elseif ($filterField == "tonalite_originale") {
                $where[] = "chanson.tonalite_originale = '$valEscaped'";
            } elseif ($filterField == "tempo_famille") {
                $where[] = match ($valEscaped) {
                    "Largo"    => " chanson.tempo < 60",
                    "Adagio"   => " chanson.tempo BETWEEN 60 AND 75",
                    "Andante"  => " chanson.tempo BETWEEN 76 AND 107",
                    "Moderato" => " chanson.tempo BETWEEN 108 AND 119",
                    "Allegro"  => " chanson.tempo BETWEEN 120 AND 155",
                    "Vivace"   => " chanson.tempo BETWEEN 156 AND 175",
                    "Presto"   => " chanson.tempo >= 176",
                    default    => " 1=1"
                };
            } elseif ($filterField == "annee" || $filterField == "tempo") {
                $where[] = "chanson.$filterField = '$valEscaped'";
            } else {
                $where[] = "chanson.$filterField LIKE '$valEscaped'";
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // Group by pour le tri par votes
        if ($sortBy == "votes" && $_SESSION['privilege'] == $GLOBALS["PRIVILEGE_INVITE"]) {
            $sql .= " GROUP BY chanson.id";
        }

        // Tri
        if ($sortBy == "votes") {
            $colTri = ($_SESSION['privilege'] == $GLOBALS["PRIVILEGE_INVITE"]) ? "moy_note" : "ma_note";
            $sql .= " ORDER BY $colTri " . ($asc ? "ASC" : "DESC");
        } else {
            $sql .= " ORDER BY chanson.$sortBy " . ($asc ? "ASC" : "DESC");
        }

        // Pagination
        if ($limit > 0) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $result = $db->query($sql) or die ("ChansonRepository::search error : " . $db->error . " SQL: $sql");
        $ids = [];
        while ($row = $result->fetch_row()) {
            $ids[] = (int)$row[0];
        }
        return $ids;
    }

    /**
     * Compte les chansons selon les critères.
     */
    public static function count(string $query = '%', string $filterField = "", $filterValue = ""): int
    {
        $db = $_SESSION['mysql'];
        $query = $db->real_escape_string($query);
        $sql = "SELECT COUNT(chanson.id) FROM chanson";
        
        $where = [];
        if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
            $where[] = "chanson.publication = 1";
        }
        if ($query != "" && $query != "%") {
            $where[] = "( chanson.nom LIKE '$query' OR chanson.interprete LIKE '$query' )";
        }
        if ($filterField != "" && $filterValue != "") {
            $valEscaped = $db->real_escape_string($filterValue);
            if ($filterField == "contributeur") {
                $where[] = "chanson.iduser = $valEscaped";
            } elseif ($filterField == "tonalite") {
                $equivalents = Chanson::getTonaliteEquivalents($filterValue);
                $condTona = [];
                foreach ($equivalents as $eq) {
                    $condTona[] = "chanson.tonalite = '" . $db->real_escape_string($eq) . "'";
                }
                $where[] = "(" . implode(" OR ", $condTona) . ")";
            } elseif ($filterField == "tonalite_originale") {
                $where[] = "chanson.tonalite_originale = '$valEscaped'";
            } elseif ($filterField == "tempo_famille") {
                $where[] = match ($valEscaped) {
                    "Largo" => " chanson.tempo < 60",
                    "Adagio" => " chanson.tempo BETWEEN 60 AND 75",
                    "Andante" => " chanson.tempo BETWEEN 76 AND 107",
                    "Moderato" => " chanson.tempo BETWEEN 108 AND 119",
                    "Allegro" => " chanson.tempo BETWEEN 120 AND 155",
                    "Vivace" => " chanson.tempo BETWEEN 156 AND 175",
                    "Presto" => " chanson.tempo >= 176",
                    default => " 1=1"
                };
            } elseif ($filterField == "annee" || $filterField == "tempo") {
                $where[] = "chanson.$filterField = '$valEscaped'";
            } else {
                $where[] = "chanson.$filterField LIKE '$valEscaped'";
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $result = $db->query($sql) or die ("ChansonRepository::count error : " . $db->error);
        $row = $result->fetch_row();
        return (int) $row[0];
    }

    /**
     * Récupère les liens URL associés à une chanson.
     */
    public static function getLinks(int $idChanson): ?mysqli_result
    {
        $db = $_SESSION['mysql'];
        $sql = sprintf("SELECT * FROM lienurl WHERE nomTable = 'chanson' AND idTable = %d", $idChanson);
        return $db->query($sql);
    }

    /**
     * Récupère les songbooks associés aux documents d'une chanson.
     */
    public static function getSongbooks(int $idChanson): ?mysqli_result
    {
        $db = $_SESSION['mysql'];
        $sql = "SELECT DISTINCT songbook.id, songbook.nom FROM songbook
                INNER JOIN liendocsongbook ON songbook.id = liendocsongbook.idSongbook
                INNER JOIN document ON liendocsongbook.idDocument = document.id
                WHERE document.nomTable = 'chanson' AND document.idTable = $idChanson";
        return $db->query($sql);
    }

    /**
     * Liste les fichiers physiques présents dans le dossier d'une chanson.
     */
    public static function getPhysicalFiles(int $idChanson, string $baseDir): array
    {
        $files = [];
        $dirPath = dirname(__DIR__, 3) . "/" . $baseDir . $idChanson;
        if (is_dir($dirPath)) {
            foreach (new DirectoryIterator($dirPath) as $fileInfo) {
                if ($fileInfo->isDot() || str_starts_with($fileInfo->getFilename(), ".")) continue;
                $files[] = [
                    'path' => $dirPath,
                    'name' => $fileInfo->getFilename(),
                    'ext'  => $fileInfo->getExtension()
                ];
            }
        }
        return $files;
    }
}
