<?php
const PRIVILEGE = 'privilege';
const CHANSON = "chanson";
const ORDRE_ASC = 'ordreAsc';
const TRI = 'tri';
const DATE_PUB = "datePub";
const CHERCHE = 'cherche';
const CENTRER = "centrer";
const VAL_FILTRE = "valFiltre";
const FILTRE = "filtre";
require_once("../lib/utilssi.php");
require_once("../lib/Pagination.php");
require_once("../chanson/chanson.php");
require_once("../document/document.php");
require_once("../navigation/menu.php");
require_once("../note/UtilisateurNote.php");
require_once 'Media.php';


// Récupération des ID de médias de type "partoche"
$idsMedias = Media::chercheMediasParType("partoche");

// Affichage HTML
echo '<div style="display:flex;flex-wrap:wrap;">';
$compteur =0;

foreach ($idsMedias as $id) {
    $media = new Media();
    $media->chercheMedia($id); // Cette méthode doit exister dans ta classe
    if ($media) {
        echo $media->afficheComposantMedia();
    }
    $compteur++;
}

echo '</div>';
echo "<p> voici les $compteur derniers médias ajouté sur le site.</p>";

/*
DELETE FROM media
WHERE id NOT IN (
    SELECT id FROM (
    SELECT MIN(id) AS id
        FROM media
        GROUP BY lien
    ) AS temp
);
*/
