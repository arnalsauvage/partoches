<?php
/**
 * Classe LienDocSongbook (Django Style)
 * Gère les liens entre Documents (fichiers) et Songbooks (recueils).
 */

require_once dirname(__DIR__) . "/lib/configMysql.php";

class LienDocSongbook
{
    /**
     * Cherche les lienDocSongbooks correspondant à un critère
     */
    public static function chercheLiensDocSongbook($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
    {
        $db = $_SESSION ['mysql'];
        $maRequete = "SELECT * FROM liendocsongbook WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
        $maRequete .= $bTriAscendant ? " ASC" : " DESC";
        $result = $db->query($maRequete) or die ("Problème chercheliensDocSongbook #1 : " . $db->error);
        return $result;
    }

    /**
     * Cherche un lienDocSongbook par son ID
     */
    public static function chercheLienDocSongbook($id)
    {
        $db = $_SESSION ['mysql'];
        $maRequete = "SELECT * FROM liendocsongbook WHERE id = '$id'";
        $result = $db->query($maRequete) or die ("Problème chercheLienDocSongbook #1 : " . $db->error);
        return $result->fetch_row() ?: 0;
    }

    /**
     * Renvoie le nombre de docs dans un songbook
     */
    public static function nombreDeLiensDuSongbook($idSongBook)
    {
        $db = $_SESSION['mysql'];
        $maRequete = "SELECT * FROM liendocsongbook WHERE idSongbook = '$idSongBook'";
        $result = $db->query($maRequete) or die ("Problème nombreDeLiensDuSongbook #1 : " . $db->error);
        return $result->num_rows;
    }

    /**
     * Cherche un lien par Songbook et Document
     */
    public static function chercheLienParIdSongbookIdDoc($idSongbook, $idDoc)
    {
        $db = $_SESSION['mysql'];
        $maRequete = "SELECT * FROM liendocsongbook WHERE idDocument = '$idDoc' AND idSongbook = '$idSongbook'";
        $result = $db->query($maRequete) or die ("Problème chercheIdSongbookIdDoc #1 : " . $db->error);
        return $result->fetch_row() ?: 0;
    }

    /**
     * Cherche un lien par Songbook et Ordre
     */
    public static function chercheLienParIdSongbookOrdre($idSongbook, $ordre)
    {
        $db = $_SESSION['mysql'];
        $maRequete = "SELECT * FROM liendocsongbook WHERE ordre = '$ordre' AND idSongbook = '$idSongbook'";
        $result = $db->query($maRequete) or die ("Problème chercheLienParIdSongbookOrdre #1 : " . $db->error);
        return $result->fetch_row() ?: 0;
    }

    /**
     * Crée un lien entre un document et un songbook
     */
    public static function creeLienDocSongbook($idDocument, $idSongbook)
    {
        $db = $_SESSION['mysql'];
        // Vérification existance
        if (self::chercheLienParIdSongbookIdDoc($idSongbook, $idDocument)) {
            return false;
        }

        $nb = self::nombreDeLiensDuSongbook($idSongbook) + 1;
        $maRequete = "INSERT INTO liendocsongbook VALUES (NULL, '$idDocument', '$idSongbook', '$nb')";
        return $db->query($maRequete) or die ("Problème creelienDocSongbook#1 : " . $db->error);
    }

    /**
     * Modifie un lienDocSongbook
     */
    public static function modifielienDocSongbook($id, $idDocument, $idSongbook, $ordre)
    {
        $db = $_SESSION['mysql'];
        $maRequete = "UPDATE liendocsongbook SET idDocument = '$idDocument', idSongbook = '$idSongbook', ordre = '$ordre' WHERE id='$id'";
        return $db->query($maRequete) or die ("Problème modifielienDocSongbook #1 : " . $db->error);
    }

    /**
     * Modifie l'ordre d'un lienDocSongbook
     */
    public static function modifieOrdreLienDocSongbook($idDocument, $idSongbook, $ordre)
    {
        $db = $_SESSION['mysql'];
        $maRequete = "UPDATE liendocsongbook SET ordre = '$ordre' WHERE idDocument = '$idDocument' AND idSongbook = '$idSongbook'";
        return $db->query($maRequete) or die ("Problème modifielienDocSongbook #1 : " . $db->error);
    }

    /**
     * Supprime un lien par son ID
     */
    public static function supprimeLienDocSongbook($id)
    {
        $db = $_SESSION['mysql'];
        $lien = self::chercheLienDocSongbook($id);
        $idSongbook = $lien ? $lien[2] : 0;
        
        $maRequete = "DELETE FROM liendocsongbook WHERE id='$id'";
        $db->query($maRequete) or die ("Problème #1 dans supprimelienDocSongbook : " . $db->error);
        
        if ($idSongbook) {
            self::ordonneLiensSongbook($idSongbook);
        }
    }

    /**
     * Supprime tous les liens d'un document
     */
    public static function supprimeliensDocSongbookDuDocument($idDoc)
    {
        $db = $_SESSION['mysql'];
        $maRequete = "DELETE FROM liendocsongbook WHERE idDocument='$idDoc'";
        return $db->query($maRequete) or die ("Problème #1 dans supprimeliensDocSongbookDuDocument : " . $db->error);
    }

    /**
     * Supprime tous les liens d'un songbook
     */
    public static function supprimeliensDocSongbookDuSongbook($idSongbook)
    {
        $db = $_SESSION['mysql'];
        $maRequete = "DELETE FROM liendocsongbook WHERE idSongbook='$idSongbook'";
        return $db->query($maRequete) or die ("Problème #1 dans supprimeliensDocSongbookDuSongbook : " . $db->error);
    }

    /**
     * Supprime un lien par Document et Songbook
     */
    public static function supprimeLienIdDocIdSongbook($idDoc, $idSongbook)
    {
        $lien = self::chercheLienParIdSongbookIdDoc($idSongbook, $idDoc);
        if ($lien) {
            self::supprimeLienDocSongbook($lien[0]);
            return true;
        }
        return false;
    }

    /**
     * Ordonne les liens d'un songbook de 1 à n
     */
    public static function ordonneLiensSongbook($idSongbook)
    {
        $lignes = self::chercheLiensDocSongbook('idSongbook', $idSongbook, "ordre", true);
        $numero = 1;
        while ($ligne = $lignes->fetch_row()) {
            self::modifielienDocSongbook($ligne[0], $ligne[1], $ligne[2], $numero);
            $numero++;
        }
    }

    /**
     * Fait remonter un titre dans l'ordre
     */
    public static function remonteTitre($idSongbook, $rang, $longueurSaut)
    {
        $fait = false;
        while ($longueurSaut > 0) {
            $lienAmonter = self::chercheLienParIdSongbookOrdre($idSongbook, $rang);
            $lienAbaisser = self::chercheLienParIdSongbookOrdre($idSongbook, $rang - 1);

            if ($lienAmonter && $lienAbaisser) {
                self::modifielienDocSongbook($lienAmonter[0], $lienAmonter[1], $lienAmonter[2], $lienAmonter[3] - 1);
                self::modifielienDocSongbook($lienAbaisser[0], $lienAbaisser[1], $lienAbaisser[2], $lienAbaisser[3] + 1);
                $fait = true;
            }
            $longueurSaut--;
            $rang--;
        }
        self::ordonneLiensSongbook($idSongbook);
        return $fait;
    }
}

// --- FONCTIONS WRAPPERS (POUR COMPATIBILITÉ) ---

if (!function_exists('chercheLiensDocSongbook')) {
    function chercheLiensDocSongbook($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true) {
        return LienDocSongbook::chercheLiensDocSongbook($critere, $valeur, $critereTri, $bTriAscendant);
    }
}
if (!function_exists('chercheLienDocSongbook')) {
    function chercheLienDocSongbook($id) {
        return LienDocSongbook::chercheLienDocSongbook($id);
    }
}
if (!function_exists('nombreDeLiensDuSongbook')) {
    function nombreDeLiensDuSongbook($idSongBook) {
        return LienDocSongbook::nombreDeLiensDuSongbook($idSongBook);
    }
}
if (!function_exists('creeLienDocSongbook')) {
    function creeLienDocSongbook($idDoc, $idSongbook) {
        return LienDocSongbook::creeLienDocSongbook($idDoc, $idSongbook);
    }
}
if (!function_exists('modifielienDocSongbook')) {
    function modifielienDocSongbook($id, $idDocument, $idSongbook, $ordre) {
        return LienDocSongbook::modifielienDocSongbook($id, $idDocument, $idSongbook, $ordre);
    }
}
if (!function_exists('supprimeLienDocSongbook')) {
    function supprimeLienDocSongbook($id) {
        return LienDocSongbook::supprimeLienDocSongbook($id);
    }
}
if (!function_exists('supprimeliensDocSongbookDuDocument')) {
    function supprimeliensDocSongbookDuDocument($idDoc) {
        return LienDocSongbook::supprimeliensDocSongbookDuDocument($idDoc);
    }
}
if (!function_exists('supprimeliensDocSongbookDuSongbook')) {
    function supprimeliensDocSongbookDuSongbook($idSongbook) {
        return LienDocSongbook::supprimeliensDocSongbookDuSongbook($idSongbook);
    }
}
if (!function_exists('supprimeLienIdDocIdSongbook')) {
    function supprimeLienIdDocIdSongbook($idDoc, $idSongbook) {
        return LienDocSongbook::supprimeLienIdDocIdSongbook($idDoc, $idSongbook);
    }
}
if (!function_exists('ordonneLiensSongbook')) {
    function ordonneLiensSongbook($idSongbook) {
        return LienDocSongbook::ordonneLiensSongbook($idSongbook);
    }
}
if (!function_exists('remonteTitre')) {
    function remonteTitre($idSongbook, $rang, $longueurSaut) {
        return LienDocSongbook::remonteTitre($idSongbook, $rang, $longueurSaut);
    }
}
