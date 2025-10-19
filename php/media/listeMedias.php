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
    'ogUrl' => $_SESSION['urlSite'] ,
    'ogImage' => $_SESSION['urlSite'] . 'apple-touch-icon-152x152-precomposed.png',
    'cookieMessage' => "Ce site utilise un cookie pour vous identifier comme visiteur ou contributeur. En poursuivant votre navigation, vous acceptez ce cookie et offrez votre cœur et votre âme au ukulélé !",
    'enterBtn' => 'Entrer',
    'lastPublications' => 'Nos dernières publications'
];
// === Récupération des médias ===
$idsMedias = Media::chercheTousLesMedias(); // pourrait être modifiée plus tard avec une pagination
$totalMedias = count($idsMedias);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="<?= htmlspecialchars($textes['siteTitle']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($textes['siteSubtitle']) ?>">
    <meta property="og:url" content="<?= $textes['ogUrl'] ?>">
    <meta property="og:image" content="<?= $textes['ogImage'] ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= "{$textes['siteTitle']} – Dernières publications" ?></title>
    <meta name="description" content="<?= htmlspecialchars($textes['siteSubtitle']) ?>">
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/styles.0.2.css">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
</head>

<body>
<header class="header-global">
    <div class="cookie-banner" id="cookieBanner">
        <button id="cookieToggle" aria-label="Afficher les informations sur les cookies">
            <img src="../../images/navigation/cookie-320.webp" alt="cookie">
        </button>
        <div class="cookie-popup" id="cookiePopup">
            <span><?= $textes['cookieMessage'] ?></span>
            <button class="close-cookie" aria-label="Fermer">×</button>
        </div>
    </div>

    <div class="titre-container">
        <div class="titre-gauche">
            <img src="../../images/navigation/<?= $_SESSION['logoSite'] ?? 'logo.png' ?>" width="128" class="logo" alt="Logo du site">
            <h1><?= htmlspecialchars($textes['siteTitle']) ?></h1>
        </div>
        <div class="titre-droite">
            <nav>
                <a href="../chanson/chanson_liste.php" class="btn btn-success entrer-btn fw-bold">
                    <?= $textes['enterBtn'] ?>
                </a>
            </nav>
        </div>
    </div>
</header>

<main class="container mt-4">
    <h2 class="text-center mb-4"><?= $textes['lastPublications'] ?></h2>

    <section class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 justify-content-center">
        <?php
        if (empty($idsMedias)) {
            echo "<p class='text-center'>Aucun média disponible pour le moment.</p>";
        } else {
            foreach ($idsMedias as $id) {
                $media = new Media();
                $media->chercheMedia($id);
                echo '<div class="col d-flex justify-content-center">';
                echo $media->afficheComposantMedia();
                echo '</div>';
            }

            // complète la ligne avec des colonnes vides pour arriver à 4
            $reste = 4 - (count($idsMedias) % 4);
            if ($reste < 4) {
                for ($i = 0; $i < $reste; $i++) {
                    echo '<div class="col d-flex justify-content-center invisible">';
                    echo '<div class="card media-card" style="width:220px; visibility:hidden;"></div>';
                    echo '</div>';
                }
            }
        }
        ?>
    </section>

    <p class="text-muted text-center mt-4">
        <?= "Voici les {$totalMedias} derniers médias ajoutés au site." ?>
    </p>
</main>



<footer class="text-center py-3 mt-4 text-muted">
    &copy; <?= date('Y') ?> – <?= htmlspecialchars($textes['siteTitle']) ?>
</footer>

<!-- Scripts -->

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
