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
$pasDeMenu = true;
require_once("../lib/Pagination.php");
require_once("../chanson/chanson.php");
require_once("../document/document.php");
require_once("../navigation/menu.php");
require_once("../note/UtilisateurNote.php");
require_once 'Media.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">

    <!--    Indexation OpenGraph pourles réseaux sociaux-->
    <meta property="og:title" content="Songbooks ukulele en ligne : les partoches du club de ukulele top 5">
    <meta property="og:type" content="sur partoches, les amis de top 5 partagent des partoches de ukulélé venues
    de top 5 ou d'ailleurs, pour le plaisir de chanter, en grattant son ukulélé.">
    <meta property="og:url" content="http://partoches.top5.re/">
    <meta property="og:image" content="http://partoches.top5.re/apple-touch-icon-152x152-precomposed.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Songbooks ukulele en ligne : les partoches du club de ukulele top 5</title>
    <meta name="description" content="sur partoches, les amis de top 5 partagent des partoohes de ukulélé venues
    de top 5 ou d'ailleurs, pour le plaisir de chanter, en grattant son ukulélé.">
    <link rel="stylesheet" type="text/css" href="../../css/styles.0.1.css">
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120-precomposed.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152-precomposed.png">

</head>

<body>
<header>
    <div class="info-cookies" id="infoCookies">
        <button id="cookieToggle" aria-label="Afficher les informations sur les cookies">
            <img src="../../images/navigation/cookie-320.webp" alt="cookie">
        </button>
        <div class="cookie-popup" id="cookiePopup">
        <span class="cookie-text">
            Ce site utilise un cookie pour vous identifier comme visiteur ou contributeur.
            En poursuivant votre navigation, vous acceptez ce cookie et offrez votre cœur et votre âme au ukulélé !
        </span>
            <button class="close-cookie" aria-label="Fermer">×</button>
        </div>
    </div>


    <div class="titre-container">
        <div class="titre-gauche">
            <img src="../../images/navigation/top-5-logo-officiel-300x.webp" alt="logo Top 5 ukulélé" width="128"
                 class="top5-logo">
            <h1>Partoches Top 5 Ukulélé</h1>
        </div>
        <div class="titre-droite">
            <nav>
                <a href="../chanson/chanson_liste.php" class="btn btn-success entrer-btn" style="font-weight: bold">
                    Entrer
                </a>
            </nav>
        </div>
    </div>
</header>


<?php

// Récupération des ID de médias de type "partoche"
$idsMedias = Media::chercheMediasParType("partoche");

// Affichage HTML
echo '<div class="content-box">';
echo '<h2>Nos dernières publications</h2><br>';
echo '</div>';
echo '<div class="content-box">';
$compteur = 0;

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

?>

<script>
    document.getElementById('cookieToggle').addEventListener('click', () => {
        document.getElementById('infoCookies').classList.add('active');
    });

    document.getElementById('cookiePopup').addEventListener('click', () => {
        document.getElementById('infoCookies').style.display = 'none';
    });
</script>

</body>


