<?php
require_once __DIR__ . "/../lib/utilssi.php";
require_once __DIR__ . "/../lib/Pagination.php";
require_once __DIR__ . "/../chanson/Chanson.php";
require_once __DIR__ . "/../document/Document.php";
require_once __DIR__ . "/../note/UtilisateurNote.php";
require_once __DIR__ . '/Media.php';

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
    'siteTitle' => $_SESSION['titreSite'] ?? 'Partoches Canopée',
    'siteSubtitle' => $_SESSION['sousTitreSite'] ?? 'Le meilleur de la musique',
    'ogUrl' => $_SESSION['urlSite'],
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
    <link rel="stylesheet"
          href="../../css/styles-communs.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/css/styles-communs.css'); ?>">
    <link rel="stylesheet"
          href="../../css/canopee-medias.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/css/canopee-medias.css'); ?>">
    <link rel="icon" href="../../favicon.ico" type="image/x-icon">
</head>

<body>
<header class="header-global">
    <div class="info-cookies">
        <div class="cookie-banner" id="cookieBanner">
            <button id="cookieToggle" aria-label="Afficher les informations sur les cookies">
                <img src="../../images/navigation/cookie-320.webp" alt="cookie">
            </button>
            <div class="cookie-popup" id="cookiePopup">
                <span><?= $textes['cookieMessage'] ?></span>
                <button class="close-cookie" aria-label="Fermer">×</button>
            </div>
        </div>
    </div>

    <div class="titre-container container">
        <div class="titre-gauche">
            <img src="../../images/navigation/<?= $_SESSION['logoSite'] ?? 'logo_site.png' ?>" width="128" class="logo"
                 alt="Logo du site">
            <h1><?= htmlspecialchars($textes['siteTitle']) ?></h1>
        </div>
        <div class="titre-droite">
            <nav>
                <a href="../chanson/chanson_liste.php"
                   class="btn btn-success entrer-btn fw-bold"
                   aria-label="Entrer sur la liste des chansons"
                >
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
        const popup = document.getElementById('cookiePopup');
        const banner = document.getElementById('cookieBanner');

        // Affichage de la popup via la classe active
        popup.classList.add('active');

        // Disparition automatique après 10 secondes
        setTimeout(() => {
            banner.style.transition = 'opacity 1s ease-out, transform 1s ease-out';
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(-20px)';

            setTimeout(() => {
                banner.style.display = 'none';
            }, 1000);
        }, 10000);
    });

    // Fermeture manuelle au clic sur la popup
    document.getElementById('cookiePopup').addEventListener('click', () => {
        const banner = document.getElementById('cookieBanner');
        banner.style.display = 'none';
    });
</script>
</body>
</html>
