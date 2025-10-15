<?php
require_once("../lib/utilssi.php");
require_once("../lib/Pagination.php");
require_once("../chanson/chanson.php");
require_once("../document/document.php");
require_once("../navigation/menu.php");
require_once("../note/UtilisateurNote.php");
require_once 'Media.php';

$pasDeMenu = true;

// === Définition des textes utilisés ===
$textes = [
    'privilege' => 'privilege',
    'chanson' => 'chanson',
    'ordreAsc' => 'ordreAsc',
    'tri' => 'tri',
    'datePub' => 'datePub',
    'cherche' => 'cherche',
    'centrer' => 'centrer',
    'valFiltre' => 'valFiltre',
    'filtre' => 'filtre',
    'siteTitle' => $_SESSION['titreSite'] ?? 'Partoches Ukulélé',
    'siteSubtitle' => $_SESSION['sousTitreSite'] ?? 'Le meilleur du ukulélé',
    'ogUrl' => 'http://partoches.top5.re/',
    'ogImage' => 'http://partoches.top5.re/apple-touch-icon-152x152-precomposed.png',
    'cookieMessage' => "Ce site utilise un cookie pour vous identifier comme visiteur ou contributeur. En poursuivant votre navigation, vous acceptez ce cookie et offrez votre cœur et votre âme au ukulélé !",
    'enterBtn' => 'Entrer',
    'lastPublications' => 'Nos dernières publications',
    'lastMediaMsg' => ' voici les %d derniers médias ajoutés sur le site.'
];

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="<?= $textes['siteTitle'] ?>">
    <meta property="og:type" content="<?= $textes['siteSubtitle'] ?>">
    <meta property="og:url" content="<?= $textes['ogUrl'] ?>">
    <meta property="og:image" content="<?= $textes['ogImage'] ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $textes['siteTitle'] ?> - les derniers médias</title>
    <meta name="description" content="<?= $textes['siteSubtitle'] ?>">
    <link rel="stylesheet" type="text/css" href="../../css/styles.0.1.css">
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
</head>

<body>
<header>
    <div class="info-cookies" id="infoCookies">
        <button id="cookieToggle" aria-label="Afficher les informations sur les cookies">
            <img src="../../images/navigation/cookie-320.webp" alt="cookie">
        </button>
        <div class="cookie-popup" id="cookiePopup">
            <span class="cookie-text"><?= $textes['cookieMessage'] ?></span>
            <button class="close-cookie" aria-label="Fermer">×</button>
        </div>
    </div>

    <div class="titre-container">
        <div class="titre-gauche">
            <img src="../../images/navigation/<?= $_SESSION['logoSite'] ?>" width="128" class="logo">
            <h1><?= $textes['siteTitle'] ?></h1>
        </div>
        <div class="titre-droite">
            <nav>
                <a href="../chanson/chanson_liste.php" class="btn btn-success entrer-btn" style="font-weight: bold">
                    <?= $textes['enterBtn'] ?>
                </a>
            </nav>
        </div>
    </div>
</header>

<?php
$idsMedias = Media::chercheMediasParType("partoche");

echo '<div class="content-box">';
echo '<h2>' . $textes['lastPublications'] . '</h2><br>';
echo '</div>';
echo '<div class="content-box">';
$compteur = 0;

foreach ($idsMedias as $id) {
    $media = new Media();
    $media->chercheMedia($id);
    echo $media->afficheComposantMedia();
    $compteur++;
}
echo '</div>';
echo sprintf("<p>" . $textes['lastMediaMsg'] . "</p>", $compteur);
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
</html>
