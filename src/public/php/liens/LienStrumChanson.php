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
        $db = $_SESSION ['mysql'];
        $maRequete = "SELECT * FROM lienstrumchanson WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
        $maRequete .= $bTriAscendant ? " ASC" : " DESC";
        $result = $db->query($maRequete) or die ("Problème chercheliensStrumChanson #1 : " . $db->error);
        return $result;
    }

    /**
     * Cherche un lienStrumChanson par son ID
     */
    public static function chercheLienStrumChanson($id)
    {
        $db = $_SESSION ['mysql'];
        $maRequete = "SELECT * FROM lienstrumchanson WHERE id = '$id'";
        $result = $db->query($maRequete) or die ("Problème cherchelienStrumChanson #1 : " . $db->error);
        return $result->fetch_row() ?: 0;
    }

    /**
     * Crée un lien entre un strum et une chanson
     */
    public static function creelienStrumChanson($_strum, $idchanson, $idStrum = 0)
    {
        $db = $_SESSION['mysql'];
        $res = $db->query("SELECT MAX(ordre) FROM lienstrumchanson WHERE idchanson = $idchanson");
        $nb = ($res && $row = $res->fetch_row()) ? (int)$row[0] + 1 : 1;
        
        $_strum = $db->real_escape_string($_strum);
        $maRequete = "INSERT INTO lienstrumchanson (strum, idchanson, ordre, idStrum) VALUES ('$_strum', '$idchanson', '$nb', $idStrum)";
        $db->query($maRequete) or die ("Problème creelienStrumChanson#1 : " . $db->error);
        return $db->insert_id;
    }

    /**
     * Supprime un lien par son ID
     */
    public static function supprimeLienStrumChanson($id)
    {
        $db = $_SESSION['mysql'];
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
