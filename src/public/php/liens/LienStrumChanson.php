<?php
/**
 * Classe LienStrumChanson (Django Style)
 */

require_once dirname(__DIR__) . "/lib/utilssi.php";
require_once dirname(__DIR__) . "/lib/configMysql.php";

class LienStrumChanson
{
    /**
     * Cherche les lienStrumChansons correspondant à un critère
     */
    public static function chercheLiensStrumChanson($critere, $valeur, $critereTri = 'ordre', $bTriAscendant = true)
    {
        $db = $_SESSION['mysql'];
        $critere = $db->real_escape_string($critere);
        $valeur = $db->real_escape_string($valeur);
        $critereTri = $db->real_escape_string($critereTri);
        $direction = $bTriAscendant ? "ASC" : "DESC";

        $maRequete = "SELECT id, idChanson, idStrum, strum, ordre FROM lienstrumchanson WHERE $critere LIKE '$valeur' ORDER BY $critereTri $direction";
        $result = $db->query($maRequete) or die ("Problème chercheliensStrumChanson #1 : " . $db->error);
        return $result;
    }

    /**
     * Cherche un lienStrumChanson par son ID
     */
    public static function chercheLienStrumChanson($id)
    {
        $db = $_SESSION['mysql'];
        $id = (int)$id;
        $maRequete = "SELECT id, idChanson, idStrum, strum, ordre FROM lienstrumchanson WHERE id = '$id'";
        $result = $db->query($maRequete) or die ("Problème cherchelienStrumChanson #1 : " . $db->error);
        if ($ligne = $result->fetch_assoc()) {
            return array_merge(array_values($ligne), $ligne);
        }
        return 0;
    }

    /**
     * Crée un lien entre un strum et une chanson
     */
    public static function creelienStrumChanson($_strum, $idchanson, $idStrum = 0)
    {
        $db = $_SESSION['mysql'];
        $idchanson = (int)$idchanson;
        $idStrum = (int)$idStrum;
        $res = $db->query("SELECT MAX(ordre) AS max_ordre FROM lienstrumchanson WHERE idchanson = $idchanson");
        $nb = ($res && $row = $res->fetch_assoc()) ? (int)($row['max_ordre'] ?? 0) + 1 : 1;
        
        $_strum = $db->real_escape_string($_strum);
        $maRequete = "INSERT INTO lienstrumchanson (strum, idChanson, ordre, idStrum) VALUES ('$_strum', '$idchanson', '$nb', $idStrum)";
        $db->query($maRequete) or die ("Problème creelienStrumChanson#1 : " . $db->error);
        return $db->insert_id;
    }

    /**
     * Supprime un lien par son ID
     */
    public static function supprimeLienStrumChanson($id)
    {
        $db = $_SESSION['mysql'];
        $id = (int)$id;
        $maRequete = "DELETE FROM lienstrumchanson WHERE id='$id'";
        $db->query($maRequete) or die ("Problème #1 dans supprimeLienStrumChanson : " . $db->error);
    }
}

// --- FONCTIONS WRAPPERS (POUR COMPATIBILITÉ) ---

function chercheLiensStrumChanson($critere, $valeur, $critereTri = 'ordre', $bTriAscendant = true) {
    return LienStrumChanson::chercheLiensStrumChanson($critere, $valeur, $critereTri, $bTriAscendant);
}
function chercheLienStrumChanson($id) {
    return LienStrumChanson::chercheLienStrumChanson($id);
}
function creelienStrumChanson($_strum, $idchanson, $idStrum = 0) {
    return LienStrumChanson::creelienStrumChanson($_strum, $idchanson, $idStrum);
}
function supprimeLienStrumChanson($id) {
    LienStrumChanson::supprimeLienStrumChanson($id);
}
