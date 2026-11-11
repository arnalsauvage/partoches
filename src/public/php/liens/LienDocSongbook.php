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
        $db = $_SESSION['mysql'];
        $critere = $db->real_escape_string($critere);
        $valeur = $db->real_escape_string($valeur);
        $critereTri = $db->real_escape_string($critereTri);
        $direction = $bTriAscendant ? "ASC" : "DESC";

        $maRequete = "SELECT id, idDocument, idSongbook, ordre FROM liendocsongbook WHERE $critere LIKE '$valeur' ORDER BY $critereTri $direction";
        $result = $db->query($maRequete) or die ("Problème chercheliensDocSongbook #1 : " . $db->error);
        return $result;
    }

    /**
     * Cherche un lienDocSongbook par son ID
     */
    public static function chercheLienDocSongbook($id)
    {
        $db = $_SESSION['mysql'];
        $id = (int)$id;
        $maRequete = "SELECT id, idDocument, idSongbook, ordre FROM liendocsongbook WHERE id = '$id'";
        $result = $db->query($maRequete) or die ("Problème chercheLienDocSongbook #1 : " . $db->error);
        if ($ligne = $result->fetch_assoc()) {
            return array_merge(array_values($ligne), $ligne);
        }
        return 0;
    }

    /**
     * Renvoie le nombre de docs dans un songbook
     */
    public static function nombreDeLiensDuSongbook($idSongBook)
    {
        $db = $_SESSION['mysql'];
        $idSongBook = (int)$idSongBook;
        $maRequete = "SELECT COUNT(*) AS total FROM liendocsongbook WHERE idSongbook = '$idSongBook'";
        $result = $db->query($maRequete) or die ("Problème nombreDeLiensDuSongbook #1 : " . $db->error);
        $row = $result->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Cherche un lien par Songbook et Document
     */
    public static function chercheLienParIdSongbookIdDoc($idSongbook, $idDoc)
    {
        $db = $_SESSION['mysql'];
        $idSongbook = (int)$idSongbook;
        $idDoc = (int)$idDoc;
        $maRequete = "SELECT id, idDocument, idSongbook, ordre FROM liendocsongbook WHERE idDocument = '$idDoc' AND idSongbook = '$idSongbook'";
        $result = $db->query($maRequete) or die ("Problème chercheIdSongbookIdDoc #1 : " . $db->error);
        if ($ligne = $result->fetch_assoc()) {
            return array_merge(array_values($ligne), $ligne);
        }
        return 0;
    }

    /**
     * Cherche un lien par Songbook et Ordre
     */
    public static function chercheLienParIdSongbookOrdre($idSongbook, $ordre)
    {
        $db = $_SESSION['mysql'];
        $idSongbook = (int)$idSongbook;
        $ordre = (int)$ordre;
        $maRequete = "SELECT id, idDocument, idSongbook, ordre FROM liendocsongbook WHERE ordre = '$ordre' AND idSongbook = '$idSongbook'";
        $result = $db->query($maRequete) or die ("Problème chercheLienParIdSongbookOrdre #1 : " . $db->error);
        if ($ligne = $result->fetch_assoc()) {
            return array_merge(array_values($ligne), $ligne);
        }
        return 0;
    }

    /**
     * Crée un lien entre un document et un songbook
     */
    public static function creeLienDocSongbook($idDocument, $idSongbook)
    {
        $db = $_SESSION['mysql'];
        $idDocument = (int)$idDocument;
        $idSongbook = (int)$idSongbook;

        // Vérification existance
        if (self::chercheLienParIdSongbookIdDoc($idSongbook, $idDocument)) {
            return false;
        }

        $nb = self::nombreDeLiensDuSongbook($idSongbook) + 1;
        $maRequete = "INSERT INTO liendocsongbook (id, idDocument, idSongbook, ordre) VALUES (NULL, '$idDocument', '$idSongbook', '$nb')";
        return $db->query($maRequete) or die ("Problème creelienDocSongbook#1 : " . $db->error);
    }

    /**
     * Modifie un lienDocSongbook
     */
    public static function modifielienDocSongbook($id, $idDocument, $idSongbook, $ordre)
    {
        $db = $_SESSION['mysql'];
        $id = (int)$id;
        $idDocument = (int)$idDocument;
        $idSongbook = (int)$idSongbook;
        $ordre = (int)$ordre;

        $maRequete = "UPDATE liendocsongbook SET idDocument = '$idDocument', idSongbook = '$idSongbook', ordre = '$ordre' WHERE id='$id'";
        return $db->query($maRequete) or die ("Problème modifielienDocSongbook #1 : " . $db->error);
    }

    /**
     * Modifie l'ordre d'un lienDocSongbook
     */
    public static function modifieOrdreLienDocSongbook($idDocument, $idSongbook, $ordre)
    {
        $db = $_SESSION['mysql'];
        $idDocument = (int)$idDocument;
        $idSongbook = (int)$idSongbook;
        $ordre = (int)$ordre;

        $maRequete = "UPDATE liendocsongbook SET ordre = '$ordre' WHERE idDocument = '$idDocument' AND idSongbook = '$idSongbook'";
        return $db->query($maRequete) or die ("Problème modifielienDocSongbook #1 : " . $db->error);
    }

    /**
     * Supprime un lien par son ID
     */
    public static function supprimeLienDocSongbook($id)
    {
        $db = $_SESSION['mysql'];
        $id = (int)$id;
        $lien = self::chercheLienDocSongbook($id);
        $idSongbook = $lien ? (int)($lien['idSongbook'] ?? $lien[2] ?? 0) : 0;
        
        $maRequete = "DELETE FROM liendocsongbook WHERE id='$id'";
        $db->query($maRequete) or die ("Problème #1 dans supprimelienDocSongbook : " . $db->error);
        
        if ($idSongbook > 0) {
            self::ordonneLiensSongbook($idSongbook);
        }
    }

    /**
     * Supprime tous les liens d'un document
     */
    public static function supprimeliensDocSongbookDuDocument($idDoc)
    {
        $db = $_SESSION['mysql'];
        $idDoc = (int)$idDoc;
        $maRequete = "DELETE FROM liendocsongbook WHERE idDocument='$idDoc'";
        return $db->query($maRequete) or die ("Problème #1 dans supprimeliensDocSongbookDuDocument : " . $db->error);
    }

    /**
     * Supprime tous les liens d'un songbook
     */
    public static function supprimeliensDocSongbookDuSongbook($idSongbook)
    {
        $db = $_SESSION['mysql'];
        $idSongbook = (int)$idSongbook;
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
            self::supprimeLienDocSongbook((int)($lien['id'] ?? $lien[0]));
            return true;
        }
        return false;
    }

    /**
     * Ordonne les liens d'un songbook de 1 à n
     */
    public static function ordonneLiensSongbook($idSongbook)
    {
        $idSongbook = (int)$idSongbook;
        $lignes = self::chercheLiensDocSongbook('idSongbook', $idSongbook, "ordre", true);
        $numero = 1;
        while ($ligne = $lignes->fetch_assoc()) {
            self::modifielienDocSongbook(
                (int)$ligne['id'], 
                (int)$ligne['idDocument'], 
                (int)$ligne['idSongbook'], 
                $numero
            );
            $numero++;
        }
    }

    /**
     * Fait remonter un titre dans l'ordre
     */
    public static function remonteTitre($idSongbook, $rang, $longueurSaut)
    {
        $idSongbook = (int)$idSongbook;
        $rang = (int)$rang;
        $longueurSaut = (int)$longueurSaut;

        $fait = false;
        while ($longueurSaut > 0) {
            $lienAmonter = self::chercheLienParIdSongbookOrdre($idSongbook, $rang);
            $lienAbaisser = self::chercheLienParIdSongbookOrdre($idSongbook, $rang - 1);

            if ($lienAmonter && $lienAbaisser) {
                $idM = (int)($lienAmonter['id'] ?? $lienAmonter[0]);
                $docM = (int)($lienAmonter['idDocument'] ?? $lienAmonter[1]);
                $sbM = (int)($lienAmonter['idSongbook'] ?? $lienAmonter[2]);
                $ordM = (int)($lienAmonter['ordre'] ?? $lienAmonter[3]);

                $idB = (int)($lienAbaisser['id'] ?? $lienAbaisser[0]);
                $docB = (int)($lienAbaisser['idDocument'] ?? $lienAbaisser[1]);
                $sbB = (int)($lienAbaisser['idSongbook'] ?? $lienAbaisser[2]);
                $ordB = (int)($lienAbaisser['ordre'] ?? $lienAbaisser[3]);

                self::modifielienDocSongbook($idM, $docM, $sbM, $ordM - 1);
                self::modifielienDocSongbook($idB, $docB, $sbB, $ordB + 1);
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
if (!function_exists('modifieOrdreLienDocSongbook')) {
    function modifieOrdreLienDocSongbook($idDocument, $idSongbook, $ordre) {
        return LienDocSongbook::modifieOrdreLienDocSongbook($idDocument, $idSongbook, $ordre);
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
