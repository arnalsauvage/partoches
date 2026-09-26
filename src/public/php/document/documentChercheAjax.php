<?php
require_once file_exists(dirname(__DIR__, 2) . '/autoload.php') ? dirname(__DIR__, 2) . '/autoload.php' : dirname(__DIR__) . '/autoload.php';
include_once PHP_DIR . "/chanson/Chanson.php";
include_once PHP_DIR . "/document/Document.php";
include_once LIB_DIR . "/formulaire.php";
include_once LIB_DIR . "/utilssi.php";
if (!class_exists('Utilisateur')) {
    require_once PHP_DIR . "/utilisateur/Utilisateur.php";
}

global $_DOSSIER_CHANSONS;
const RACINE = "../../";

$table =  "documents";
$sortie = "";

// Si l'utilisateur n'est pas au moins EDITEUR, on ne répond pas
if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"])
{
    return(0);
}

// On récupère les paramètre par POST : type, nomContient, triPar, triCroissant
if (isset ($_POST ['typeDocument']))
{
    $typeDocument = $_POST ['typeDocument'];
}
else {
    $typeDocument = "*";
}
//echo "typeDocument : " . $typeDocument;

if (isset ($_POST ['nomCherche']))
{
    $nomContient = $_POST ['nomCherche'];
}
else {
    $nomContient = "";
}
//echo "<br> Nom contient : " .$nomContient;

if (isset ($_POST ['triPar']))
{
    $triPar = $_POST ['triPar'];
}
else {
    $triPar = "nom";
}

//echo "<br> Tri par : " .$triPar;

    $triCroissant = true;
if (isset ($_POST ['triCroissant'])&&($_POST ['triCroissant']=="desc"))
{
    $triCroissant = false;
}
/*
if ($triCroissant)
    echo "<br> Tri croissant : true";
else
    echo "<br> Tri croissant : false";
*/
// On fait une requête pour récupérer les documents concernés
$listeDocs = chercheDocuments("nom", "%".$nomContient."%", $triPar, $triCroissant);

// On transforme les données en objet json


// On retourne l'objet json
$sortie = "<table>";
$nombreItems = 0;
$nombreItemsMax = 10;
while (($ligneDoc = $listeDocs->fetch_assoc()) && ($nombreItems < $nombreItemsMax)) {
    $idDoc = (int)$ligneDoc['id'];
    $nomDoc = $ligneDoc['nom'];
    $versionDoc = (int)$ligneDoc['version'];
    $idTableDoc = (int)$ligneDoc['idTable'];
    $tailleKoDoc = (int)$ligneDoc['tailleKo'];
    $dateDoc = $ligneDoc['date'];
    $hitsDoc = (int)$ligneDoc['hits'];

    $fichierCourt = composeNomVersion($nomDoc, $versionDoc);
    $fichier = RACINE . $_DOSSIER_CHANSONS . $idTableDoc . "/" . $fichierCourt;
    $extension = substr(strrchr($nomDoc, '.'), 1);

    if ($typeDocument != $extension) {
        continue;
    }

    if ($typeDocument != "*") {
        if (($typeDocument == "son") && ($extension != "mp3")) {
            continue;
        }
        if (($typeDocument == "pdf") && ($extension != "pdf")) {
            continue;
        }
        if (($typeDocument == "doc") && ($extension != "doc")) {
            continue;
        }
    }
    $nombreItems++;

    $sortie .= "<tr> \n";
    $sortie .= '<td><input type="radio" id = ' . $idDoc . ' name="documentJoint" value = ' . $idDoc . '></td>';
    $sortie .= "<td> " . "<a href= '" . $fichier . "' target='_blank'> " . $fichierCourt . "</a> \n";
    $sortie .= "<td>" . intval($tailleKoDoc / 1024) . " ko  </td>";
    $sortie .= "<td>" . " - " . dateMysqlVersTexte($dateDoc) . " </td>";
    $sortie .= "<td> &nbsp; - " . $hitsDoc . " vues </td></tr>\n";
}
$sortie .= "</table>";
echo $sortie;
if ($nombreItems >= $nombreItemsMax) {
    echo "le nombre de résultats est limité à $nombreItemsMax... Mettre plus de critères !";
}
if ($nombreItems == 0) {

    $sortie = "Aucun document trouvé";

}