<?php
if (!defined("BR_REQUETE")) {
    define("BR_REQUETE", "<br>Requete : ");
}
if (!defined("DOSSIER_DATA")) {
    define("DOSSIER_DATA", "../../data/");
}
include_once "../lib/configMysql.php";
include_once "../lib/utilssi.php";

// Fonctions de gestion de lien url
//  id	nomtable	idtable	url	type	description

// TODO : contrôler que les id / Tables fournis existent bien

// Cherche les Lienurls correspondant à un critère
function chargeLiensurls($critereTri = 'date', $bTriAscendant = true)
{
    $maRequete = "SELECT * FROM lienurl ORDER BY $critereTri";
    $maRequete = !$bTriAscendant ? $maRequete . " DESC" : $maRequete . " ASC";
    // echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienurls #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Cherche les Lienurls correspondant à un critère
function chercheLienurls($critere, $valeur, $critereTri = 'type', $bTriAscendant = true)
{
    $maRequete = "SELECT * FROM lienurl WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    $maRequete = !$bTriAscendant ? $maRequete . " DESC" : $maRequete . " ASC";
    // echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienurls #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Cherche un Lienurl et le renvoie s'il existe
function chercheLienurlId($id)
{
    $maRequete = "SELECT * FROM lienurl WHERE lienurl.id = '$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienurlId #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée :id	nomtable	idtable	url	type	description
    if ($ligne = $result->fetch_row()) {
        return ($ligne);
    } else {
        return (0);
    }
}

// Cherche un Lienurl et le renvoie s'il existe
function chercheLienurlUrlTableId($url, $table, $id)
{
    $maRequete = "SELECT * FROM lienurl WHERE lienurl.url = '$url' AND lienurl.idTable = '$id' AND lienurl.nomTable = '$table'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienurlUrlTableId #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
    if ($ligne = $result->fetch_row()) {
        return ($ligne);
    } else {
        return (0);
    }
}

// Cherche les documents d'une entrée d'une table et les renvoie s'ils existent
function chercheLiensUrlsTableId($table, $id)
{
    $maRequete = "SELECT * FROM lienurl WHERE lienurl.idtable = '$id' AND lienurl.nomtable = '$table' ORDER BY lienurl.id ASC";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLiensUrlsTableId #1 : " . $_SESSION ['mysql']->error);
    return ($result);
}

// Crée un Lienurl en base de données
function creeLienurl($url, $type, $description, $nomTable, $idTable, $date, $iduser, $hits)
{
    $date = convertitDateJJMMAAAAversMySql($date);
    echo "Nouveau format de date : $date ";
    // id	nomtable	idtable	url	type	description
    $maRequete = "INSERT INTO lienurl VALUES (NULL,  '$nomTable', '$idTable', '$url', '$type', '$description', '$date', $iduser,$hits)";
    echo $maRequete;
    $resultat = chercheLienurlUrlTableId($url, $nomTable, $idTable);
    // Si le lien existe déjà, on ne le crée pas
    if ($resultat != NULL) {
        return false;
    }
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creeLienurl#1 : " . $_SESSION ['mysql']->error);
    return $result;
}


// Cherche les documents correspondant à un critère
function chercheNderniersLiens($type)
{
    $maRequete = "SELECT * FROM lienurl WHERE type LIKE '$type' ORDER BY date DESC";
    // echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheNderniersLiens #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Modifie en base le Lienurl
/**
 * @param $url : url du lien
 * @param $type : type du lien url : vidéo, site, doc...
 * @param $description : description du lien
 * @param $nomTable : table à laquelle est rattaché le Lienurl
 * @param $idTable : identifiant de l'objet auquel est rattaché ce Lienurl
 */
function modifieLienurl($id, $url, $type, $description, $nomTable, $idTable, $date, $idUser, $hits)
{
    $resultat = chercheLienurlId($id);
    if ($resultat == NULL) {
        return false;
    }
    $date = convertitDateJJMMAAAAversMySql($date);
    $maRequete = "UPDATE  lienurl
	SET type = '$type', url = '$url', description = '$description', nomtable = '$nomTable', idtable = '$idTable', date = '$date', idUser = '$idUser', hits = '$hits'
	WHERE id = '$id' ";
    $_SESSION ['mysql']->query($maRequete) or die ("Problème modifieLienurl #1 : " . $_SESSION ['mysql']->error . BR_REQUETE . $maRequete);
}

// Supprime un Lienurl en base de données si il existe
function supprimeLienurl($id)
{
    // On supprime les enregistrements dans la table Lienurl
    $maRequete = "DELETE FROM lienurl
	WHERE id='$id'";
    $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimeLienurl : " . $_SESSION ['mysql']->error);
}

function ajouteUnHit($idLien)
{
    $resultat = chercheLienurlId($idLien);
    if ($resultat == NULL) {
        return false;
    }
    $nbHits = $resultat[8] + 1;

    $maRequete = "UPDATE  lienurl
	SET hits = '$nbHits'
	WHERE id = '$idLien' ";
    $_SESSION ['mysql']->query($maRequete) or die ("Problème modifieLienurl #1 : " . $_SESSION ['mysql']->error . BR_REQUETE . $maRequete);
}

// Renvoie une chaine de description du Lienurl pour test
//  id	nomtable	idtable	url	type	description
function infosLienurl($url)
{
    $resultat = chercheLienurls("url", $url);
    $resultat = $resultat->fetch_row();
    if ($resultat != NULL) {
        $enr = $resultat;
        // id_journee id_joueur poste statut
        $retour = "id : " . $enr [0] . " nomtable : " . $enr [1] . " idtable : " . $enr [2] . " url : " . $enr [3];
        $retour .= " type : " . $enr [4] . "description : " . $enr [5];
    } else {
        $retour = "$url pas trouvé...";
    }
    return $retour . "<BR>\n";
}

// Fonction de test
function testeLienurl()
{
    // if (creeLienurl ( "enfant.pdf", "128", "chanson", 2 ) == FALSE)
    // echo "erreur de création, le Lienurl existe déjà en base";
    // else {
    // echo "Lienurl enfant créé";
    // }
    // echo infosLienurl ( "enfant.pdf" );

    // creeModifieLienurl ( "GrilleSaladeDeFruits.pdf", "179124", "chanson", 25 );
    // creeModifieLienurl ( "RiffSaladeDeFruits ukulele.pdf", "34900", "chanson", 25 );
    // creeModifieLienurl ( "SaladeDeFruits.pdf", "475024", "chanson", 4 );
    // creeModifieLienurl ( "SaladeDeFruits-BOURVIL.png.pdf", "599551", "chanson", 4 );

    // creeModifieLienurl ( "parent.pdf", "256", "chanson", 5 );
    echo infosLienurl("enfant.pdf");

    for ($idDoc = 0; $idDoc < 100; $idDoc++) {
        echo "<a href='" . lienUrlAffichageLienurl($idDoc) . "'>lien affichage</a> <BR>";
        echo "<a href='" . lienUrlTelechargeLienurl($idDoc) . "'>lien téléchargement</a> <BR>";
    }
}
