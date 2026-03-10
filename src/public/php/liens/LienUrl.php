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
        $maRequete = "SELECT * FROM lienurl ORDER BY $critereTri";
        $maRequete = !$bTriAscendant ? $maRequete . " DESC" : $maRequete . " ASC";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienurls #1 : " . $_SESSION ['mysql']->error);
        return $result;
    }

    /**
     * Cherche des liens selon un critère
     */
    public static function chercheLienurls($critere, $valeur, $critereTri = 'type', $bTriAscendant = true)
    {
        $maRequete = "SELECT * FROM lienurl WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
        $maRequete = !$bTriAscendant ? $maRequete . " DESC" : $maRequete . " ASC";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienurls #1 : " . $_SESSION ['mysql']->error);
        return $result;
    }

    /**
     * Cherche un lien par son ID
     */
    public static function chercheLienurlId($id)
    {
        $maRequete = "SELECT * FROM lienurl WHERE lienurl.id = '$id'";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienurlId #1 : " . $_SESSION ['mysql']->error);
        if ($ligne = $result->fetch_row()) {
            return ($ligne);
        }
        return (0);
    }

    /**
     * Cherche un lien par URL, table et ID
     */
    public static function chercheLienurlUrlTableId($url, $table, $id)
    {
        $maRequete = "SELECT * FROM lienurl WHERE lienurl.url = '$url' AND lienurl.idTable = '$id' AND lienurl.nomTable = '$table'";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienurlUrlTableId #1 : " . $_SESSION ['mysql']->error);
        if ($ligne = $result->fetch_row()) {
            return ($ligne);
        }
        return (0);
    }

    /**
     * Cherche les liens d'un objet spécifique
     */
    public static function chercheLiensUrlsTableId($table, $id)
    {
        $maRequete = "SELECT * FROM lienurl WHERE lienurl.idtable = '$id' AND lienurl.nomtable = '$table' ORDER BY lienurl.id ASC";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLiensUrlsTableId #1 : " . $_SESSION ['mysql']->error);
        return ($result);
    }

    /**
     * Crée un nouveau lien
     */
    public static function creeLienurl($url, $type, $description, $nomTable, $idTable, $date, $iduser, $hits)
    {
        $date = convertitDateJJMMAAAAversMySql($date);
        $maRequete = "INSERT INTO lienurl VALUES (NULL,  '$nomTable', '$idTable', '$url', '$type', '$description', '$date', $iduser, $hits)";
        
        $resultat = self::chercheLienurlUrlTableId($url, $nomTable, $idTable);
        if ($resultat != NULL) {
            return false;
        }
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creeLienurl#1 : " . $_SESSION ['mysql']->error);
        return $result;
    }

    /**
     * Modifie un lien existant
     */
    public static function modifieLienurl($id, $url, $type, $description, $nomTable, $idTable, $date, $idUser, $hits)
    {
        $resultat = self::chercheLienurlId($id);
        if ($resultat == NULL) {
            return false;
        }
        $date = convertitDateJJMMAAAAversMySql($date);
        $maRequete = "UPDATE lienurl
	        SET type = '$type', url = '$url', description = '$description', nomtable = '$nomTable', idtable = '$idTable', date = '$date', idUser = '$idUser', hits = '$hits'
	        WHERE id = '$id' ";
        $_SESSION ['mysql']->query($maRequete) or die ("Problème modifieLienurl #1 : " . $_SESSION ['mysql']->error . BR_REQUETE . $maRequete);
        return true;
    }

    /**
     * Supprime un lien
     */
    public static function supprimeLienurl($id)
    {
        $maRequete = "DELETE FROM lienurl WHERE id='$id'";
        $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimeLienurl : " . $_SESSION ['mysql']->error);
    }

    /**
     * Incrémente le compteur de clics
     */
    public static function ajouteUnHit($idLien)
    {
        $resultat = self::chercheLienurlId($idLien);
        if ($resultat == NULL) {
            return false;
        }
        $nbHits = $resultat[8] + 1;
        $maRequete = "UPDATE lienurl SET hits = '$nbHits' WHERE id = '$idLien' ";
        $_SESSION ['mysql']->query($maRequete) or die ("Problème modifieLienurl #1 : " . $_SESSION ['mysql']->error . BR_REQUETE . $maRequete);
        return true;
    }

    /**
     * Cherche les n derniers liens d'un type donné
     */
    public static function chercheNderniersLiens($type)
    {
        $db = $_SESSION ['mysql'];
        $type = $db->real_escape_string($type);
        $maRequete = "SELECT * FROM lienurl WHERE type LIKE '$type' ORDER BY date DESC";
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
                $obj = new Chanson($idTable);
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
