<?php
require_once dirname(__DIR__, 3) . "/autoload.php";
require_once PHP_DIR . "/lib/utilssi.php";

class MediaRepository
{
    const MYSQL = 'mysql';
    const CONFIG_MYSQL = "/lib/configMysql.php";

    /**
     * Enregistre ou met à jour un média
     */
    public static function persist(Media $media): bool|int
    {
        self::checkDbConnection();
        $idExistant = self::verifieExistenceMedia($media->getLien());
        if ($idExistant !== null) {
            $media->setId($idExistant);
            return self::modifieMediaBDD($media);
        } else {
            return self::creeMediaBDD($media);
        }
    }

    private static function verifieExistenceMedia(string $lienurl): ?int
    {
        self::checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $lienurl = $db->real_escape_string($lienurl);
        $requete = "SELECT id FROM media WHERE lien = '$lienurl' LIMIT 1";
        $result = $db->query($requete);

        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['id'];
        }
        return null;
    }

    private static function creeMediaBDD(Media $media): bool|int
    {
        self::checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $maRequete = sprintf("INSERT INTO media (type, titre, image, auteur, lien, description, tags, datePub, hits)
            VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            $db->real_escape_string($media->getType()),
            $db->real_escape_string($media->getTitre()),
            $db->real_escape_string($media->getImage()),
            $db->real_escape_string((string)$media->getAuteur()),
            $db->real_escape_string($media->getLien()),
            $db->real_escape_string($media->getDescription()),
            $db->real_escape_string($media->getTags()),
            $db->real_escape_string($media->getDatePub()),
            $db->real_escape_string((string)$media->getHits()));

        $result = $db->query($maRequete);
        if (!$result) {
            return false;
        }

        $media->setId($db->insert_id);
        return $media->getId();
    }

    private static function modifieMediaBDD(Media $media): bool
    {
        self::checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $maRequete = sprintf(
            "UPDATE media SET type='%s', titre='%s', image='%s', auteur='%s', lien='%s', description='%s', tags='%s', datePub='%s', hits='%s' WHERE id=%d",
            $db->real_escape_string($media->getType()),
            $db->real_escape_string($media->getTitre()),
            $db->real_escape_string($media->getImage()),
            $db->real_escape_string((string)$media->getAuteur()),
            $db->real_escape_string($media->getLien()),
            $db->real_escape_string($media->getDescription()),
            $db->real_escape_string($media->getTags()),
            $db->real_escape_string($media->getDatePub()),
            $db->real_escape_string((string)$media->getHits()),
            (int)$media->getId()
        );

        return $db->query($maRequete);
    }

    public static function supprimeMediaBDD(int $id): void
    {
        self::checkDbConnection();
        $maRequete = "DELETE FROM media WHERE id = " . (int)$id;
        $_SESSION[self::MYSQL]->query($maRequete);
    }

    public static function chercheMedia(int $id): ?Media
    {
        self::checkDbConnection();
        $maRequete = sprintf("SELECT * FROM media WHERE id = %d", (int)$id);
        $result = $_SESSION[self::MYSQL]->query($maRequete);
        if ($result && $ligne = $result->fetch_row()) {
            return self::mysqlRowVersObjet($ligne);
        }
        return null;
    }

    private static function mysqlRowVersObjet(array $ligne): Media
    {
        return new Media([
            'id'          => (int)$ligne[0],
            'type'        => (string)$ligne[1],
            'titre'       => (string)$ligne[2],
            'image'       => (string)$ligne[3],
            'auteur'      => (int)$ligne[4],
            'lien'        => (string)$ligne[5],
            'description' => (string)$ligne[6],
            'tags'        => (string)$ligne[7],
            'datePub'     => (string)$ligne[8],
            'hits'        => (int)$ligne[9],
        ]);
    }

    public static function chercheMediasParType(string $type): array
    {
        self::checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $type = $db->real_escape_string($type);
        $maRequete = "SELECT id FROM media WHERE type = '$type'";
        $result = $db->query($maRequete);
        $tableau = [];
        if ($result) {
            while ($row = $result->fetch_row()) {
                $tableau[] = (int)$row[0];
            }
        }
        return $tableau;
    }

    public static function chercheTousLesMedias(int $limit = 50, int $offset = 0, array $filtres = []): array
    {
        self::checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $where = "";
        
        if (!empty($filtres) && !in_array('tous', $filtres)) {
            $escapedFiltres = array_map(fn($f) => "'" . $db->real_escape_string($f) . "'", $filtres);
            $where = "WHERE type IN (" . implode(',', $escapedFiltres) . ")";
        }

        $maRequete = "SELECT id FROM media $where ORDER BY datePub DESC LIMIT $limit OFFSET $offset";
        $result = $db->query($maRequete);
        $tableau = [];
        if ($result) {
            while ($row = $result->fetch_row()) {
                $tableau[] = (int)$row[0];
            }
        }
        return $tableau;
    }

    public static function compteTousLesMedias(array $filtres = []): int
    {
        self::checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $where = "";
        
        if (!empty($filtres) && !in_array('tous', $filtres)) {
            $escapedFiltres = array_map(fn($f) => "'" . $db->real_escape_string($f) . "'", $filtres);
            $where = "WHERE type IN (" . implode(',', $escapedFiltres) . ")";
        }
        
        $res = $db->query("SELECT COUNT(*) FROM media $where");
        if ($res) {
            $row = $res->fetch_row();
            return (int)$row[0];
        }
        return 0;
    }

    public static function getIdChansonAssocie(Media $media): ?int
    {
        self::checkDbConnection();
        $requete = "";
        $typeMedia = $media->getType();
        
        $typesFichiers = ['partoche', 'audio', 'musescore', 'songpress', 'document', 'pdf', 'fichier'];
        
        if (in_array($typeMedia, $typesFichiers)) {
            if (preg_match('/doc=(\d+)/', $media->getLien(), $matches)) {
                $idDocument = (int)$matches[1];
                $requete = "SELECT idTable FROM document WHERE id = $idDocument AND nomTable = 'chanson' LIMIT 1";
            }
        } else {
            $lienEscaped = $_SESSION[self::MYSQL]->real_escape_string($media->getLien());
            $requete = "SELECT idtable FROM lienurl WHERE nomtable = 'chanson' AND url = '$lienEscaped' LIMIT 1";
        }

        if (!empty($requete)) {
            $result = $_SESSION[self::MYSQL]->query($requete);
            if ($result && $row = $result->fetch_row()) {
                return (int)$row[0];
            }
        }
        return null;
    }

    public static function truncateTable(): void
    {
        self::checkDbConnection();
        $_SESSION[self::MYSQL]->query("TRUNCATE TABLE media");
    }

    private static function checkDbConnection(): void
    {
        if (!isset($_SESSION[self::MYSQL]) || !($_SESSION[self::MYSQL] instanceof mysqli) || $_SESSION[self::MYSQL]->connect_error) {
            require_once PHP_DIR . self::CONFIG_MYSQL;
        }
    }
}
