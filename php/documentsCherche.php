<?php
include_once("lib/utilssi.php");
include_once("chanson.php");
include_once("document.php");
include_once("lib/formulaire.php");
$table =  "documents";
$sortie = "";

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on ne répond pas
if ($_SESSION ['privilege'] < 2) {
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
while (($ligneDoc = $listeDocs->fetch_row()) && ($nombreItems<$nombreItemsMax)) {
    $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $fichier = "../data/chansons/" . $ligneDoc [6] . "/" . composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    $extension = substr(strrchr($ligneDoc [1], '.'), 1);
    //echo "extension " . $extension . " et filtre : " . $contenuFiltrer . " - " ;
    if ($typeDocument != $extension) {
        continue;
    }

    if ($typeDocument!="*") {
        if (($typeDocument == "son") && ($extension <> "mp3")) {
            continue;
        }
        if (($typeDocument == "pdf") && ($extension <> "pdf")) {
            continue;
        }
        if (($typeDocument == "doc") && ($extension <> "doc")) {
            continue;
        }
    }
    $nombreItems++;
/*    <div>
  <input type="radio" id="huey" name="drone" value="huey"
         checked>
  <label for="huey">Huey</label>
</div>

<div>
  <input type="radio" id="dewey" name="drone" value="dewey">
  <label for="dewey">Dewey</label>
</div>*/
    $sortie .= "<tr> \n";
    $sortie .= '<td><input type="radio" id = '.$ligneDoc [0] . ' name="documentJoint" value = '.$ligneDoc [0] . '></td>';
 //   $sortie .= "<td>" . $ligneDoc [0] .  "</td>";
    $sortie .= "<td> " . "<a href= '" . $fichier . "' target='_blank'> " . $fichierCourt . "</a> \n";
    $sortie .= "<td>" . intval($ligneDoc [2] / 1024) . " ko  </td>";
    $sortie .= "<td>" . " - " . dateMysqlVersTexte($ligneDoc [3]) . " </td>";
    $sortie .= "<td> &nbsp - " . $ligneDoc [8] . " vues </td></tr>\n";
}
$sortie .= "</table>";
echo $sortie;
if ($nombreItems >= $nombreItemsMax) {
    echo "le nombre de résultats est limité à $nombreItemsMax... Mettre plus de critères !";
}