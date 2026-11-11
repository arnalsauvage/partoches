<?php
/**
 * Classe LienUrl (Django Style)
 * Gère les liens externes (YouTube, articles, etc.) rattachés aux objets.
 */

if (!defined("BR_REQUETE")) {
    define("BR_REQUETE", "<br>Requete : ");
}
if (!defined("DOSSIER_DATA")) {
    define("DOSSIER_DATA", "../../data/");
}

require_once dirname(__DIR__) . "/lib/configMysql.php";
require_once dirname(__DIR__) . "/lib/utilssi.php";

class LienUrl
{
    /**
     * Charge tous les liens
     */
    public static function chargeLiensurls($critereTri = 'date', $bTriAscendant = true)
    {
        $db = $_SESSION['mysql'];
        $critereTri = $db->real_escape_string($critereTri);
        $direction = $bTriAscendant ? "ASC" : "DESC";

        $maRequete = "SELECT id, nomTable, idTable, url, type, description, date, idUser, hits FROM lienurl ORDER BY $critereTri $direction";
        $result = $db->query($maRequete) or die ("Problème chercheLienurls #1 : " . $db->error);
        return $result;
    }

    /**
     * Cherche des liens selon un critère
     */
    public static function chercheLienurls($critere, $valeur, $critereTri = 'type', $bTriAscendant = true)
    {
        $db = $_SESSION['mysql'];
        $critere = $db->real_escape_string($critere);
        $valeur = $db->real_escape_string($valeur);
        $critereTri = $db->real_escape_string($critereTri);
        $direction = $bTriAscendant ? "ASC" : "DESC";

        $maRequete = "SELECT id, nomTable, idTable, url, type, description, date, idUser, hits FROM lienurl WHERE $critere LIKE '$valeur' ORDER BY $critereTri $direction";
        $result = $db->query($maRequete) or die ("Problème chercheLienurls #1 : " . $db->error);
        return $result;
    }

    /**
     * Cherche un lien par son ID
     */
    public static function chercheLienurlId($id)
    {
        $db = $_SESSION['mysql'];
        $id = (int)$id;
        $maRequete = "SELECT id, nomTable, idTable, url, type, description, date, idUser, hits FROM lienurl WHERE id = '$id'";
        $result = $db->query($maRequete) or die ("Problème chercheLienurlId #1 : " . $db->error);
        if ($ligne = $result->fetch_assoc()) {
            return array_merge(array_values($ligne), $ligne);
        }
        return 0;
    }

    /**
     * Cherche un lien par URL, table et ID
     */
    public static function chercheLienurlUrlTableId($url, $table, $id)
    {
        $db = $_SESSION['mysql'];
        $url = $db->real_escape_string($url);
        $table = $db->real_escape_string($table);
        $id = (int)$id;

        $maRequete = "SELECT id, nomTable, idTable, url, type, description, date, idUser, hits FROM lienurl WHERE url = '$url' AND idTable = '$id' AND nomTable = '$table'";
        $result = $db->query($maRequete) or die ("Problème chercheLienurlUrlTableId #1 : " . $db->error);
        if ($ligne = $result->fetch_assoc()) {
            return array_merge(array_values($ligne), $ligne);
        }
        return 0;
    }

    /**
     * Cherche les liens d'un objet spécifique
     */
    public static function chercheLiensUrlsTableId($table, $id)
    {
        $db = $_SESSION['mysql'];
        $table = $db->real_escape_string($table);
        $id = (int)$id;

        $maRequete = "SELECT id, nomTable, idTable, url, type, description, date, idUser, hits FROM lienurl WHERE idTable = '$id' AND nomTable = '$table' ORDER BY id ASC";
        $result = $db->query($maRequete) or die ("Problème chercheLiensUrlsTableId #1 : " . $db->error);
        return $result;
    }

    /**
     * Crée un nouveau lien
     */
    public static function creeLienurl($url, $type, $description, $nomTable, $idTable, $date, $iduser, $hits)
    {
        $db = $_SESSION['mysql'];
        $urlEsc = $db->real_escape_string($url);
        $typeEsc = $db->real_escape_string($type);
        $descEsc = $db->real_escape_string($description);
        $nomTableEsc = $db->real_escape_string($nomTable);
        $idTable = (int)$idTable;
        $iduser = (int)$iduser;
        $hits = (int)$hits;
        $date = convertitDateJJMMAAAAversMySql($date);

        $resultat = self::chercheLienurlUrlTableId($url, $nomTable, $idTable);
        if ($resultat != NULL) {
            return false;
        }

        $maRequete = "INSERT INTO lienurl (id, nomTable, idTable, url, type, description, date, idUser, hits) VALUES (NULL, '$nomTableEsc', '$idTable', '$urlEsc', '$typeEsc', '$descEsc', '$date', $iduser, $hits)";
        $result = $db->query($maRequete) or die ("Problème creeLienurl#1 : " . $db->error);
        return $result;
    }

    /**
     * Modifie un lien existant
     */
    public static function modifieLienurl($id, $url, $type, $description, $nomTable, $idTable, $date, $idUser, $hits)
    {
        $db = $_SESSION['mysql'];
        $id = (int)$id;
        $resultat = self::chercheLienurlId($id);
        if ($resultat == NULL) {
            return false;
        }

        $urlEsc = $db->real_escape_string($url);
        $typeEsc = $db->real_escape_string($type);
        $descEsc = $db->real_escape_string($description);
        $nomTableEsc = $db->real_escape_string($nomTable);
        $idTable = (int)$idTable;
        $idUser = (int)$idUser;
        $hits = (int)$hits;
        $date = convertitDateJJMMAAAAversMySql($date);

        $maRequete = "UPDATE lienurl
	        SET type = '$typeEsc', url = '$urlEsc', description = '$descEsc', nomTable = '$nomTableEsc', idTable = '$idTable', date = '$date', idUser = '$idUser', hits = '$hits'
	        WHERE id = '$id'";
        $db->query($maRequete) or die ("Problème modifieLienurl #1 : " . $db->error . BR_REQUETE . $maRequete);
        return true;
    }

    /**
     * Supprime un lien
     */
    public static function supprimeLienurl($id)
    {
        $db = $_SESSION['mysql'];
        $id = (int)$id;
        $maRequete = "DELETE FROM lienurl WHERE id='$id'";
        $db->query($maRequete) or die ("Problème #1 dans supprimeLienurl : " . $db->error);
    }

    /**
     * Incrémente le compteur de clics
     */
    public static function ajouteUnHit($idLien)
    {
        $idLien = (int)$idLien;
        $resultat = self::chercheLienurlId($idLien);
        if ($resultat == NULL) {
            return false;
        }
        $nbHits = (int)($resultat['hits'] ?? $resultat[8] ?? 0) + 1;
        $db = $_SESSION['mysql'];
        $maRequete = "UPDATE lienurl SET hits = '$nbHits' WHERE id = '$idLien'";
        $db->query($maRequete) or die ("Problème modifieLienurl #1 : " . $db->error . BR_REQUETE . $maRequete);
        return true;
    }

    /**
     * Cherche les n derniers liens d'un type donné
     */
    public static function chercheNderniersLiens($type)
    {
        $db = $_SESSION['mysql'];
        $type = $db->real_escape_string($type);
        $maRequete = "SELECT id, nomTable, idTable, url, type, description, date, idUser, hits FROM lienurl WHERE type LIKE '$type' ORDER BY date DESC";
        $result = $db->query($maRequete) or die ("Problème chercheNderniersLiens #1 : " . $db->error);
        return $result;
    }

    /**
     * Retourne les informations sur l'objet auquel le lien est rattaché
     */
    public static function getInfosObjetLie(string $nomTable, int $idTable): string
    {
        $label = "";
        $url = "";
        $type = ucfirst($nomTable);

        switch (strtolower($nomTable)) {
            case 'chanson':
                $obj = Chanson::load($idTable);
                $label = $obj->getNom();
                $url = "../chanson/chanson_voir.php?id=$idTable";
                break;
            case 'songbook':
                $obj = new Songbook($idTable);
                $label = $obj->getNom();
                $url = "../songbook/songbook_voir.php?id=$idTable";
                break;
            case 'strum':
                $obj = new Strum($idTable);
                $label = $obj->getStrum();
                $url = "../strum/strum_liste.php"; // Les strums n'ont pas de page 'voir' individuelle
                break;
            default:
                $label = "ID: $idTable";
                break;
        }

        if (!$label) return "";

        $html = "<div class='objet-lie' style='margin-top: 10px; font-size: 0.9em;'>";
        $html .= "<span class='label label-default' style='background-color: #D2B48C; color: #2b1d1a; border: 1px solid #2b1d1a; margin-right: 5px;'>$type</span>";
        if ($url) {
            $html .= "<a href='$url' style='color: #8B4513; font-weight: bold;'>$label</a>";
        } else {
            $html .= "<span style='color: #8B4513; font-weight: bold;'>$label</span>";
        }
        $html .= "</div>";

        return $html;
    }
}

// --- FONCTIONS WRAPPERS (POUR COMPATIBILITÉ) ---

function chargeLiensurls($critereTri = 'date', $bTriAscendant = true) {
    return LienUrl::chargeLiensurls($critereTri, $bTriAscendant);
}
function chercheLienurls($critere, $valeur, $critereTri = 'type', $bTriAscendant = true) {
    return LienUrl::chercheLienurls($critere, $valeur, $critereTri, $bTriAscendant);
}
function chercheLienurlId($id) {
    return LienUrl::chercheLienurlId($id);
}
function chercheLienurlUrlTableId($url, $table, $id) {
    return LienUrl::chercheLienurlUrlTableId($url, $table, $id);
}
function chercheLiensUrlsTableId($table, $id) {
    return LienUrl::chercheLiensUrlsTableId($table, $id);
}
function creeLienurl($url, $type, $description, $nomTable, $idTable, $date, $iduser, $hits) {
    return LienUrl::creeLienurl($url, $type, $description, $nomTable, $idTable, $date, $iduser, $hits);
}
function modifieLienurl($id, $url, $type, $description, $nomTable, $idTable, $date, $idUser, $hits) {
    return LienUrl::modifieLienurl($id, $url, $type, $description, $nomTable, $idTable, $date, $idUser, $hits);
}
function supprimeLienurl($id) {
    LienUrl::supprimeLienurl($id);
}
function ajouteUnHit($idLien) {
    return LienUrl::ajouteUnHit($idLien);
}
function chercheNderniersLiens($type) {
    return LienUrl::chercheNderniersLiens($type);
}
