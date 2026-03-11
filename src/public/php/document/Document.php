<?php
/**
 * Classe Document (Django Style)
 * Gère les fichiers attachés aux chansons, songbooks, etc.
 */

if (!defined("BR_REQUETE")) {
    define("BR_REQUETE", "<br>Requete : ");
}
if (!defined("DOSSIER_DATA")) {
    define("DOSSIER_DATA" , __DIR__ . "/../../data/");
}

require_once dirname(__DIR__) . "/lib/utilssi.php";
require_once dirname(__DIR__) . "/lib/configMysql.php";
// require_once dirname(__DIR__) . "/liens/lienDocSongbook.php"; // Sera chargé par l'autoloader si besoin

class Document
{
    /**
     * Cherche les documents correspondant à un critère
     */
    public static function chercheDocuments($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
    {
        $maRequete = "SELECT * FROM document WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
        $maRequete = !$bTriAscendant ? $maRequete . " DESC" : $maRequete . " ASC";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchedocument #1 : " . $_SESSION ['mysql']->error);
        return $result;
    }

    /**
     * Cherche un document et le renvoie s'il existe
     */
    public static function chercheDocument($id)
    {
        $maRequete = "SELECT * FROM document WHERE document.id = '$id'";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchedocument #2 : " . $_SESSION ['mysql']->error);
        if ($ligne = $result->fetch_row()) {
            return ($ligne);
        }
        return (0);
    }

    /**
     * Cherche un document par nom, table et id
     */
    public static function chercheDocumentNomTableId($nom, $table, $id)
    {
        $maRequete = "SELECT * FROM document WHERE document.nom = '$nom' AND document.idTable = '$id' AND document.nomTable = '$table'";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchedocument #3 : " . $_SESSION ['mysql']->error);
        if ($ligne = $result->fetch_row()) {
            return ($ligne);
        }
        return (0);
    }

    /**
     * Cherche les documents d'une entree d'une table et les renvoie s'ils existent
     */
    public static function chercheDocumentsTableId($table, $id)
    {
        $maRequete = "SELECT * FROM document WHERE document.idTable = '$id' AND document.nomTable = '$table' ORDER BY document.id ASC";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheDocumentsTableId #3 : " . $_SESSION ['mysql']->error);
        return ($result);
    }

    /**
     * Compose le nom du fichier avec sa version
     */
    public static function composeNomVersion($nom, $version)
    {
        $ext = strrchr($nom, ".");
        $nomSec = str_replace($ext, "", $nom);
        return $nomSec . "-v$version" . $ext;
    }

    /**
     * Crée un document en base de données
     */
    public static function creeDocument($nom, $tailleKo, $nomTable, $idTable)
    {
        $date = date("d/m/y");
        $date = convertitDateJJMMAAAAversMySql($date);
        $version = 1;

        $resultat = self::chercheDocumentNomTableId($nom, $nomTable, $idTable);
        if ($resultat != NULL) {
            return false;
        }
        $idUser = $_SESSION ['id'] ?? 1;
        $maRequete = "INSERT INTO document VALUES (NULL, '$nom', '$tailleKo', '$date', '$version', '$nomTable', '$idTable', '$idUser', '0')";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creedocument#1 : " . $_SESSION ['mysql']->error);
        return $result;
    }

    /**
     * Modifie un document (incrémente la version)
     */
    public static function modifieDocument($nom, $tailleKo, $nomTable, $idTable)
    {
        $date = date("d/m/y");
        $date = convertitDateJJMMAAAAversMySql($date);
        $idUser = $_SESSION ['id'] ?? 1;

        $resultat = self::chercheDocumentNomTableId($nom, $nomTable, $idTable);
        if ($resultat == NULL) {
            return false;
        }
        $version = $resultat [4] + 1;

        $maRequete = "UPDATE  document SET tailleKo = '$tailleKo', date = '$date', version = '$version', idUser = '$idUser'
	        WHERE nom = '$nom' AND nomTable = '$nomTable' and idTable = '$idTable'";
        $_SESSION ['mysql']->query($maRequete) or die ("Problème modifiedocument #1 : " . $_SESSION ['mysql']->error . BR_REQUETE . $maRequete);

        return $version;
    }

    /**
     * Renomme un document
     */
    public static function renommeDocument($id, $nouveauNom)
    {
        $document = self::chercheDocument($id);
        if (!$document) return -1;

        $nomTable = $document[5];
        $idTable = $document[6];
        $idUser = $_SESSION['id'] ?? 1;
        $numVersion = $document[4];
        $ancienNomBase = $document[1];

        $nouveauNom = basename($nouveauNom);
        $pathInfo = pathinfo($nouveauNom);
        $extension = isset($pathInfo['extension']) ? "." . $pathInfo['extension'] : "";
        $nomSansExt = $pathInfo['filename'];
        $nomSansExt = preg_replace('/-v[0-9]+$/', '', $nomSansExt);
        $nouveauNomFinal = $nomSansExt . $extension;

        $repertoire = DOSSIER_DATA . $nomTable . "s/" . $idTable . "/";
        $nouveauNomFic = $repertoire . self::composeNomVersion($nouveauNomFinal, $numVersion);

        if (file_exists($nouveauNomFic)) {
            return -2;
        }

        $ancienNomFic = $repertoire . self::composeNomVersion($ancienNomBase, $numVersion);
        if (file_exists($ancienNomFic)) {
            if (!rename($ancienNomFic, $nouveauNomFic)) {
                return -3;
            }
        }

        $maRequete = "UPDATE document SET nom = ?, idUser = ? WHERE id = ?";
        $stmt = $_SESSION['mysql']->prepare($maRequete);
        $stmt->bind_param("sii", $nouveauNomFinal, $idUser, $id);
        
        if ($stmt->execute()) {
            return 1;
        }
        return -4;
    }

    /**
     * Supprime un document
     */
    public static function supprimeDocument($id)
    {
        $maRequete = "DELETE FROM document WHERE id='$id'";
        $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimedocument : " . $_SESSION ['mysql']->error);
        // Les liens songbooks sont gérés via lienDocSongbook::supprimeliensDocSongbookDuDocument
        if (function_exists('supprimeliensDocSongbookDuDocument')) {
            supprimeliensDocSongbookDuDocument($id);
        }
    }

    public static function creeModifieDocument($nom, $tailleKo, $nomTable, $idTable)
    {
        $resultat = self::chercheDocumentNomTableId($nom, $nomTable, $idTable);
        if ($resultat == NULL) {
            return self::creeDocument($nom, $tailleKo, $nomTable, $idTable);
        }
        return self::modifieDocument($nom, $tailleKo, $nomTable, $idTable);
    }

    public static function lienUrlAffichageDocument($idDoc)
    {
        $ligne = self::chercheDocument($idDoc);
        if ($ligne != 0) {
            return DOSSIER_DATA . $ligne [5] . "s/" . $ligne [6] . "/" . self::composeNomVersion($ligne [1], $ligne [4]);
        }
        return "";
    }

    public static function lienUrlTelechargeDocument($idDoc)
    {
        $ligne = self::chercheDocument($idDoc);
        if ($ligne != 0) {
            return "getdoc.php?doc=" . $ligne [0];
        }
        return "";
    }

    public static function imageTableId($table, $id)
    {
        $maRequete = "SELECT * FROM document WHERE document.idTable = '$id' AND document.nomTable='$table' ";
        $maRequete .= " AND ( document.nom LIKE '%.png' OR document.nom LIKE '%.jpg' OR document.nom LIKE '%.webp')";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème imageSongbook #1 : " . $_SESSION ['mysql']->error);
        
        $tableImages = array();
        while ($ligne = $result->fetch_row()) {
            array_push($tableImages, $ligne);
        }
        if (empty($tableImages)) {
            return ("");
        }
        $imageChoisie = rand(0, count($tableImages) - 1);
        $ligne = $tableImages [$imageChoisie];
        return (self::composeNomVersion($ligne [1], $ligne [4]));
    }
}

// --- FONCTIONS WRAPPERS (POUR COMPATIBILITÉ) ---

function chercheDocuments($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true) {
    return Document::chercheDocuments($critere, $valeur, $critereTri, $bTriAscendant);
}
function chercheDocument($id) {
    return Document::chercheDocument($id);
}
function chercheDocumentNomTableId($nom, $table, $id) {
    return Document::chercheDocumentNomTableId($nom, $table, $id);
}
function chercheDocumentsTableId($table, $id) {
    return Document::chercheDocumentsTableId($table, $id);
}
function composeNomVersion($nom, $version) {
    return Document::composeNomVersion($nom, $version);
}
function creeDocument($nom, $tailleKo, $nomTable, $idTable) {
    return Document::creeDocument($nom, $tailleKo, $nomTable, $idTable);
}
function modifieDocument($nom, $tailleKo, $nomTable, $idTable) {
    return Document::modifieDocument($nom, $tailleKo, $nomTable, $idTable);
}
function renommeDocument($id, $nouveauNom) {
    return Document::renommeDocument($id, $nouveauNom);
}
function supprimeDocument($id) {
    Document::supprimeDocument($id);
}
function creeModifieDocument($nom, $tailleKo, $nomTable, $idTable) {
    return Document::creeModifieDocument($nom, $tailleKo, $nomTable, $idTable);
}
function imageTableId($table, $id) {
    return Document::imageTableId($table, $id);
}
function lienUrlAffichageDocument($idDoc) {
    return Document::lienUrlAffichageDocument($idDoc);
}
function lienUrlTelechargeDocument($idDoc) {
    return Document::lienUrlTelechargeDocument($idDoc);
}
