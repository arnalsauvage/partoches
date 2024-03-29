<?php
const BR_REQUETE = "<br>Requete : ";
define("DOSSIER_DATA" ,"../../data/");

include_once("../lib/utilssi.php");
include_once "../lib/configMysql.php";
include_once("../liens/lienDocSongbook.php");

// Fonctions de gestion de document

// TODO : faire une fonction de contrôle des documents sur disque :
// Documents sur disque non vus en BDD & documents BDD non vus sur disque

// TODO : contrôler que les id / Tables fournis existent bien

// Cherche les documents correspondant à un critère
function chercheDocuments($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
{
    $maRequete = "SELECT * FROM document WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    $maRequete = !$bTriAscendant ? $maRequete . " DESC" : $maRequete . " ASC";
    // echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchedocument #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Cherche un document et le renvoie s'il existe
function chercheDocument($id)
{
    $maRequete = "SELECT * FROM document WHERE document.id = '$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchedocument #2 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
    if ($ligne = $result->fetch_row()) {
        return ($ligne);
    }
    else {
        return (0);
    }
}

// Cherche un document et le renvoie s'il existe
function chercheDocumentNomTableId($nom, $table, $id)
{
    $maRequete = "SELECT * FROM document WHERE document.nom = '$nom' AND document.idTable = '$id' AND document.nomTable = '$table'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchedocument #3 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
    if ($ligne = $result->fetch_row()) {
        return ($ligne);
    }
    else {
        return (0);
    }
}

// Cherche les documents d'une entree d'une table et les renvoie s'ils existent
function chercheDocumentsTableId($table, $id)
{
    $maRequete = "SELECT * FROM document WHERE document.idTable = '$id' AND document.nomTable = '$table' ORDER BY document.id ASC";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheDocumentsTableId #3 : " . $_SESSION ['mysql']->error);
    return ($result);
}

// A partir d'un nom de fichier (monNom.txt) et un numéro de version du doc (2)
// donne un nom pour identifier le document : (monNom-v2.txt)
function composeNomVersion($nom, $version)
{
    // echo "Recherche $nom $version\n";
    // On cherche le rang du dernier point dans nom
    $ext = strrchr($nom, ".");
    $nomSec = str_replace($ext, "", $nom);
    $nouveauNom = $nomSec . "-v$version" . $ext;
    // echo $nouveauNom . "<br>";
    return $nouveauNom;
}

// Crée un document en base de données
function creeDocument($nom, $tailleKo, $nomTable, $idTable)
{
    $date = date("d/m/y");
    $date = convertitDateJJMMAAAAversMySql($date);
    $version = 1;

    $resultat = chercheDocumentNomTableId($nom, $nomTable, $idTable);
    if ($resultat != NULL) {
        return false;
    }
    $idUser = $_SESSION ['id'];
    $maRequete = "INSERT INTO document VALUES (NULL, '$nom', '$tailleKo', '$date', '$version', '$nomTable', '$idTable', '$idUser', '0')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creedocument#1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Modifie en base le document
/**
 * @param $nom : nom de fichier du document
 * @param $tailleKo : taille du fichier en ko
 * @param $nomTable : table à laquelle est rattaché le document
 * @param $idTable : identifiant de l'objet auquel est rattaché ce document
 * @return bool|int : false en cas d'erreur, sinon numéro de version du document
 */
function modifieDocument($nom, $tailleKo, $nomTable, $idTable)
{
    $date = date("d/m/y");
    $date = convertitDateJJMMAAAAversMySql($date);
    $idUser = $_SESSION ['id'];

    $resultat = chercheDocumentNomTableId($nom, $nomTable, $idTable);
    if ($resultat == NULL) {
        return false;
    }
    else {
        $version = $resultat [4] + 1;
    }

    $maRequete = "UPDATE  document
	SET tailleKo = '$tailleKo', date = '$date', version = '$version', idUser = '$idUser'
	WHERE nom = '$nom' AND nomTable = '$nomTable' and idTable = '$idTable'";
    $_SESSION ['mysql']->query($maRequete) or die ("Problème modifiedocument #1 : " . $_SESSION ['mysql']->error . BR_REQUETE . $maRequete);

    return $version;
}

// Renomme un document en base de données et sur disque si possible
function renommeDocument($id, $nouveauNom)
{
    $document = chercheDocument($id);
    //id, nom, taille, date, version, nomTable, idTable, idUser
    $nomTable = $document[5];
    $idTable = $document[6];
    $idUser = $document[7];

    // Si le nouveau nom contient le "-vx" indiquant le numéro de version, on le nettoie
    $extension = strrchr( $nouveauNom,".");
    $nouveauNomSansExtension = str_replace("$extension","", $nouveauNom);
    // On récupere le numéro de version du doc
    $maRequete = "SELECT version FROM document
	WHERE id = '$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème renommeDocument #2 : " . $_SESSION ['mysql']->error . BR_REQUETE . $maRequete);
    $ligne = $result->fetch_row();
    $numVersion = $ligne[0];
    $nouveauNomSansVersionNiExtension =     str_replace("-v$numVersion","", $nouveauNomSansExtension);
    $nouveauNom = $nouveauNomSansVersionNiExtension . $extension;

    // regarder s'il existe un fichier avec le nouveau nom
    $fichier = DOSSIER_DATA . $nomTable . "s/" . $idTable . "/" . composeNomVersion($nouveauNom, $numVersion);;
    if (file_exists($fichier))
        // s'il existe, renvoyer -2
    {
        //ecritFichierLog("ajaxlog.htm", "le fichier existe déja");
        return -2;
    }

    $ancienNomFic = DOSSIER_DATA . $nomTable . "s/" . $idTable . "/" . composeNomVersion($document[1], $document[4]);
    $nouveauNomFic = DOSSIER_DATA . $nomTable . "s/" . $idTable . "/" . composeNomVersion($nouveauNom, $document[4]);
    // renommer le fichier
    $resultRename = rename($ancienNomFic, $nouveauNomFic);
    if (!$resultRename) {
        return -3;
    }

    // mettre a jour base de données
    $maRequete = "UPDATE  document
	SET nom = '$nouveauNom', idUser = '$idUser'
	WHERE id = '$id'";
    $_SESSION ['mysql']->query($maRequete) or die ("Problème renommeDocument #1 : " . $_SESSION ['mysql']->error . BR_REQUETE . $maRequete);

    // renvoyer vrai
    return 1;
}

// Supprime un document en base de données si il existe
function supprimeDocument($id)
{
    // On supprime les enregistrements dans la table document
    $maRequete = "DELETE FROM document
	WHERE id='$id'";
    $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimedocument : " . $_SESSION ['mysql']->error);
    // On supprime également toutes les entrées Songbook lui correspondant
    supprimeliensDocSongbookDuDocument($id);
}

// Modifie ou crée un document si besoin
function creeModifieDocument($nom, $tailleKo, $nomTable, $idTable)
{
    $resultat = chercheDocumentNomTableId($nom, $nomTable, $idTable);
    if ($resultat == NULL) {
        return creeDocument($nom, $tailleKo, $nomTable, $idTable);
    }
    else {
        return modifieDocument($nom, $tailleKo, $nomTable, $idTable);
    }
}

// Prépare un combo en html avec les documents correspondant à un critère et triés selon un critereTri
// SELECT * FROM document WHERE $critere LIKE '$valeur' ORDER BY $critereTri
function selectDocument($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
{
    $retour = "<select name='documentJoint'>\n";
    // Ajouter des options
    $lignes = chercheDocuments($critere, $valeur, $critereTri, $bTriAscendant);
    while ($ligne = $lignes->fetch_row()) {
        if (strstr($ligne[1], "pdf")) {
            $retour .= "<option  value = '" . $ligne [0] . "'> " . htmlEntities($ligne [1] . " " . $ligne[3], ENT_HTML5) . "</option>\n";
        }
    }
    $retour .= "</select>\n";
    return $retour;
}

// Renvoie un lien pour affichage direct du document dans une url
function lienUrlAffichageDocument($idDoc)
{
    $ligne = chercheDocument($idDoc);
    if ($ligne != 0) {
        // Le fichier est stocke dans le répertoire data/NOMTABLEs/IDTABLE
        // ex: nom table = chanson, dossier = data/chansons/123/
        $url = DOSSIER_DATA . $ligne [5] . "s/" . $ligne [6] . "/" . composeNomVersion($ligne [1], $ligne [4]);
    } else {
        $url = "";
    }
    return $url;
}

// Renvoie un lien pour télécharger le document via une url
function lienUrlTelechargeDocument($idDoc)
{
    $ligne = chercheDocument($idDoc);
    if ($ligne != 0) {
        $url = "getdoc.php?doc=" . $ligne [0];
    } else {
        $url = "";
    }
    return $url;
}

// Renvoie une chaine de description du document pour test
function infosDocument($nom)
{
    $resultat = chercheDocuments("nom", $nom);
    $resultat = $resultat->fetch_row();
    if ($resultat != NULL) {
        $enr = $resultat;
        // id_journee id_joueur poste statut
        $retour = "id : " . $enr [0] . " nom : " . $enr [1] . " taille(ko) : " . $enr [2] . " Date : " . $enr [3];
        $retour .= " Version : " . $enr [4] . "nomTable : " . $enr [5] . " idTable : " . $enr [6] . " hits : " . $enr [8];
    } else {
        $retour = "$nom pas trouvé...";
    }
    return $retour . "<BR>\n";
}

// Fonction de test
function testeDocument()
{
    // if (creeDocument ( "enfant.pdf", "128", "chanson", 2 ) == FALSE)
    // echo "erreur de création, le document existe déjà en base";
    // else {
    // echo "document enfant créé";
    // }
    // echo infosDocument ( "enfant.pdf" );

    // creeModifieDocument ( "GrilleSaladeDeFruits.pdf", "179124", "chanson", 25 );
    // creeModifieDocument ( "RiffSaladeDeFruits ukulele.pdf", "34900", "chanson", 25 );
    // creeModifieDocument ( "SaladeDeFruits.pdf", "475024", "chanson", 4 );
    // creeModifieDocument ( "SaladeDeFruits-BOURVIL.png.pdf", "599551", "chanson", 4 );

    // creeModifieDocument ( "parent.pdf", "256", "chanson", 5 );
    echo infosDocument("enfant.pdf");

    for ($idDoc = 0; $idDoc < 100; $idDoc++) {
        echo "<a href='" . lienUrlAffichageDocument($idDoc) . "'>lien affichage</a> <BR>";
        echo "<a href='" . lienUrlTelechargeDocument($idDoc) . "'>lien téléchargement</a> <BR>";
    }
}

// TODO : utiliser cette fonction partout : users ...
// Cette fonction renvoie l'image vignette relative à une table et son id
function imageTableId($table, $id)
{
    $maRequete = "SELECT * FROM document WHERE document.idTable = '$id' AND document.nomTable='$table' ";
    $maRequete .= " AND ( document.nom LIKE '%.png' OR document.nom LIKE '%.jpg')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème imageSongbook #1 : " . $_SESSION ['mysql']->error);
    if (empty ($result)) {
        return ("");
    }
    $tableImages = array();
    // renvoie la ligne sélectionnée : id, nom, taille, date , version, nomtable, idTable, idUser, hits
    while ($ligne = $result->fetch_row()) {
        array_push($tableImages, $ligne);
    }
    if (empty($tableImages)) {
        return ("");
    }
    srand();
    $imageChoisie = rand(0, count($tableImages) - 1);
    $ligne = $tableImages [$imageChoisie];
    return (composeNomVersion($ligne [1], $ligne [4]));

}

// testeDocument ();
// TODO ajouter des logs pour tracer l'activité du site
