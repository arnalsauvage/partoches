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
// === Récupération des paramètres ===
$defautFiltres = ['partoche', 'audio', 'vidéo'];
$filtresRaw = $_GET['filtres'] ?? null;
$isExpanded = isset($_GET['expanded']) && $_GET['expanded'] == '1';

if ($filtresRaw === null) {
    $filtresActuels = $defautFiltres;
    $filtresRaw = implode(',', $defautFiltres);
} elseif ($filtresRaw === 'tous') {
    $filtresActuels = ['tous'];
} elseif ($filtresRaw === '') {
    $filtresActuels = [];
} else {
    $filtresActuels = explode(',', $filtresRaw);
}

$pageActuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsParPage = 50;

// === Définition des types pour les filtres ===
$typesFiltres = [
    'partoche' => ['label' => 'Partoches (PDF)', 'color' => 'danger'],
    'audio' => ['label' => 'Audios', 'color' => 'warning'],
    'vidéo' => ['label' => 'Vidéos', 'color' => 'primary'],
    'musescore' => ['label' => 'MuseScore', 'color' => 'success'],
    'document' => ['label' => 'Documents (Doc, PPT, SVG)', 'color' => 'default'],
    'songpress' => ['label' => 'SongPress', 'color' => 'success']
];

// Si "tous" est sélectionné, on remplit virtuellement avec tous les types
$filtresRecherche = in_array('tous', $filtresActuels) ? array_keys($typesFiltres) : $filtresActuels;

// === Pagination ===
$totalItems = Media::compteTousLesMedias($filtresRecherche);
$pagination = new Pagination($totalItems, $itemsParPage, $pageActuelle);
$offset = ($pageActuelle - 1) * $itemsParPage;

// === Récupération des médias ===
$idsMedias = Media::chercheTousLesMedias($itemsParPage, $offset, $filtresRecherche);
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
    <title><?= "{$textes['siteTitle']} – Bibliothèque Médias" ?></title>
    <meta name="description" content="<?= htmlspecialchars($textes['siteSubtitle']) ?>">
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="stylesheet"
          href="../../css/styles-communs.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/css/styles-communs.css'); ?>">
    <link rel="stylesheet"
          href="../../css/canopee-medias.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/css/canopee-medias.css'); ?>">
    <link rel="stylesheet"
          href="../../css/django-admin.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/css/django-admin.css'); ?>">
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

    <!-- Section Filtres Repliable -->
    <div class="filter-section text-center">
        <button class="btn toggle-filters-btn" type="button" data-toggle="collapse" data-target="#filterConsole" aria-expanded="false" aria-controls="filterConsole">
            <span class="glyphicon glyphicon-filter"></span> FILTRER LES MÉDIAS
        </button>

        <div class="collapse" id="filterConsole">
            <div class="filter-bar" style="position: relative; padding-top: 40px;">
                <!-- Croix de fermeture -->
                <button type="button" class="close" data-toggle="collapse" data-target="#filterConsole" aria-label="Fermer" 
                        style="position: absolute; top: 10px; right: 20px; opacity: 0.8; font-size: 30px; color: #333; background: transparent; border: none;">
                    &times;
                </button>

                <!-- Ligne unique de boutons -->
                <div class="d-flex flex-row justify-content-center align-items-center" style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; padding-bottom: 10px;">
                    <button type="button" id="btnAll" class="btn btn-xs btn-outline-success filter-btn" style="white-space: nowrap;">✅ TOUS</button>
                    
                    <div style="border-left: 1px solid #ccc; height: 25px; margin: 0 5px; flex-shrink: 0;"></div>

                    <?php foreach ($typesFiltres as $key => $config): 
                        $isActive = in_array('tous', $filtresActuels) || in_array($key, $filtresActuels);
                    ?>
                        <button type="button" 
                                class="btn btn-xs btn-<?= $config['color'] ?> filter-btn js-type-btn <?= $isActive ? '' : 'inactive' ?>"
                                data-type="<?= $key ?>"
                                style="white-space: nowrap;">
                            <?= $config['label'] ?>
                        </button>
                    <?php endforeach; ?>

                    <div style="border-left: 1px solid #ccc; height: 25px; margin: 0 5px; flex-shrink: 0;"></div>

                    <button type="button" id="btnNone" class="btn btn-xs btn-outline-secondary filter-btn" style="white-space: nowrap;">❌ AUCUN</button>
                </div>
                
                <div class="text-center mt-3">
                    <button type="button" id="applyFilters" class="btn btn-success btn-apply fw-bold" style="margin-top: 10px;">
                        🚀 APPLIQUER LES FILTRES
                    </button>
                </div>
                
                <p class="text-muted small mt-2 mb-0 text-center">Sélectionnez vos types de médias, puis cliquez sur Appliquer.</p>
            </div>
        </div>
    </div>

    <section class="row media-grid">
        <?php
        if (empty($idsMedias)) {
            echo "<div class='col-12 text-center'><p class='alert alert-info'>Aucun média ne correspond à cette sélection. Ouvrez la configuration pour allumer quelques boutons ! 🎸</p></div>";
        } else {
            foreach ($idsMedias as $id) {
                $media = new Media();
                $media->chercheMedia($id);
                echo $media->afficheComposantMedia();
            }
        }
        ?>
    </section>

    <!-- Pagination -->
    <?php if ($totalItems > $itemsParPage): ?>
    <div class="pagination-container d-flex justify-content-center">
        <nav aria-label="Navigation des pages">
            <ul class="pagination">
                <?php if ($pageActuelle > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?filtres=<?= urlencode($filtresRaw) ?>&amp;page=<?= $pageActuelle - 1 ?>">Précédent</a>
                    </li>
                <?php endif; ?>

                <?php
                $nbPages = ceil($totalItems / $itemsParPage);
                for ($i = 1; $i <= $nbPages; $i++): 
                    if ($i == $pageActuelle || ($i > $pageActuelle - 3 && $i < $pageActuelle + 3)):
                ?>
                    <li class="page-item <?= ($i === $pageActuelle) ? 'active' : '' ?>">
                        <a class="page-link" href="?filtres=<?= urlencode($filtresRaw) ?>&amp;page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php 
                    endif;
                endfor; 
                ?>

                <?php if ($pageActuelle < $nbPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?filtres=<?= urlencode($filtresRaw) ?>&amp;page=<?= $pageActuelle + 1 ?>">Suivant</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>

    <p class="text-muted text-center mt-4">
        <?= "Affichage de " . count($idsMedias) . " médias sur un total de {$totalItems}." ?>
    </p>
</main>

<footer class="text-center py-3 mt-4 text-muted">
    &copy; <?= date('Y') ?> – <?= htmlspecialchars($textes['siteTitle']) ?>
</footer>

<!-- Scripts -->
<script src="../../js/jquery-1.12.4.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        // Toggle individuel des types
        $('.js-type-btn').on('click', function() {
            $(this).toggleClass('inactive');
        });

        // Bouton TOUS
        $('#btnAll').on('click', function() {
            $('.js-type-btn').removeClass('inactive');
        });

        // Bouton AUCUN
        $('#btnNone').on('click', function() {
            $('.js-type-btn').addClass('inactive');
        });

        // Bouton APPLIQUER
        $('#applyFilters').on('click', function() {
            const selected = [];
            $('.js-type-btn').not('.inactive').each(function() {
                selected.push($(this).data('type'));
            });
            
            let url = "?filtres=" + selected.join(',');
            if (selected.length === $('.js-type-btn').length) {
                url = "?filtres=tous";
            }
            url += "&expanded=1"; // On garde la console ouverte après l'application
            
            window.location.href = url;
        });

        // Gestion des cookies
        document.getElementById('cookieToggle').addEventListener('click', () => {
            const popup = document.getElementById('cookiePopup');
            const banner = document.getElementById('cookieBanner');
            popup.classList.add('active');
            setTimeout(() => {
                banner.style.transition = 'opacity 1s ease-out, transform 1s ease-out';
                banner.style.opacity = '0';
                banner.style.transform = 'translateY(-20px)';
                setTimeout(() => { banner.style.display = 'none'; }, 1000);
            }, 10000);
        });

        document.getElementById('cookiePopup').addEventListener('click', () => {
            document.getElementById('cookieBanner').style.display = 'none';
        });
    });
</script>
</body>
</html>
