<?php
require_once("../lib/Pagination.php");
require_once dirname(__DIR__) . "/lib/utilssi.php";
require_once("LienUrl.php");
require_once("lienurl_voir.php");
$pasDeMenu = true;
require_once("../navigation/menu.php");

/**
 * Galerie des Liens Multimédia - Vue moderne et polyvalente
 */

global $cheminImages, $iconePoubelle, $iconeCreer, $iconeEdit;

const LIENSURL_PAGE = 'liensurlPage';
$nombreLiensParPage = 12;
$_lienurlForm = "../chanson/chanson_form.php";
$_lienurlPost = "lienurlPost.php";

// 1. Logique de récupération des données
$_listeDeslienurls = chargeLiensurls("date", false);

if (!$_listeDeslienurls) {
    die ("Problème lienurlsListe #1 : " . $_SESSION ['mysql']->error);
}

// 2. Gestion de la pagination
$pagination = new Pagination($_listeDeslienurls->num_rows, $nombreLiensParPage);
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
$pagination->setPageEnCours($page);

// 3. Construction du HTML
$headHtml = envoieHead("Galerie des Liens", "../../css/galerie_liens.css");
$pasDeMenu = true;
require_once("../navigation/menu.php");

echo $headHtml;
echo $MENU_HTML;

$html = "<div class='container'>";
$html .= "  <h1 class='text-center' style='margin-bottom: 30px;'><i class='glyphicon glyphicon-link'></i> Galerie des Liens</h1>";

// Bouton d'ajout
if ($_SESSION['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $html .= "  <div class='text-center' style='margin-bottom: 30px;'>";
    $html .= "    <a href='$_lienurlForm' class='btn btn-primary'><i class='glyphicon glyphicon-plus'></i> Ajouter un lien</a>";
    $html .= "  </div>";
}

$html .= "  <div class='video-grid'>";

$_numLigneParcourue = 0;
while ($_lienurl = $_listeDeslienurls->fetch_row()) {
    $_numLigneParcourue++;
    if (($_numLigneParcourue < $pagination->getItemDebut()) || $_numLigneParcourue > $pagination->getItemFin()) {
        continue;
    }

    // [0]id [1]table [2]idTable [3]url [4]nom [5]description
    $idLien = $_lienurl[0];
    $nomTable = $_lienurl[1];
    $idTable = (int)$_lienurl[2];
    $url = $_lienurl[3];
    $titre = htmlspecialchars($_lienurl[4]);
    $desc = htmlspecialchars(limiteLongueur($_lienurl[5], 80));
    
    $infosObjet = LienUrl::getInfosObjetLie($nomTable, $idTable);
    
    // --- DETECTION DU TYPE DE CONTENU ---
    $videoId = "";
    $isAudio = preg_match('/\.(mp3|wav|ogg|m4a)$/i', $url);
    $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url);
    
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
        $videoId = $match[1];
    }
    
    $thumbHtml = "";
    if ($videoId) {
        // CAS YOUTUBE : Vignette + Play (Lazy Loading)
        $thumbUrl = "https://img.youtube.com/vi/$videoId/hqdefault.jpg";
        $thumbHtml = <<<HTML
        <div class="video-card-thumb" onclick="loadVideo(this, '$videoId')">
            <img src="$thumbUrl" alt="$titre" loading="lazy">
            <div class="video-play-btn"><i class="glyphicon glyphicon-play"></i></div>
        </div>
HTML;
    } elseif ($isAudio) {
        // CAS AUDIO : Icône Musique
        $thumbHtml = <<<HTML
        <a href="$url" target="_blank" class="video-card-thumb" style="display:flex; align-items:center; justify-content:center; background:#e8f4fd; text-decoration:none;">
            <i class="glyphicon glyphicon-headphones" style="font-size:64px; color:#31708f;"></i>
            <div class="video-play-btn" style="font-size:24px; top:75%; color:#31708f;"><i class="glyphicon glyphicon-volume-up"></i></div>
        </a>
HTML;
    } elseif ($isImage) {
        // CAS IMAGE : Affichage direct
        $thumbHtml = <<<HTML
        <a href="$url" target="_blank" class="video-card-thumb">
            <img src="$url" alt="$titre" style="opacity:1;" loading="lazy">
        </a>
HTML;
    } else {
        // CAS PAR DEFAUT : Site Web / Lien externe
        $thumbHtml = <<<HTML
        <a href="$url" target="_blank" class="video-card-thumb" style="display:flex; align-items:center; justify-content:center; background:#fdfaf5; text-decoration:none;">
            <i class="glyphicon glyphicon-globe" style="font-size:64px; color:#D2B48C;"></i>
            <div class="video-play-btn" style="font-size:24px; top:75%; color:#D2B48C;"><i class="glyphicon glyphicon-new-window"></i></div>
        </a>
HTML;
    }

    // Boutons d'action
    $actionsHtml = "";
    if ($_SESSION['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
        $idConcerne = $_lienurl[2];
        $editUrl = ($_lienurl[1] == "chanson") ? "$_lienurlForm?id=$idConcerne" : "#";
        $actionsHtml .= "<a href='$editUrl' class='btn btn-xs btn-default' title='Modifier source'><i class='glyphicon glyphicon-pencil'></i></a>";
    }
    
    $actionsHtml .= "<a href='$url' target='_blank' class='btn btn-xs btn-info' title='Ouvrir le lien'><i class='glyphicon glyphicon-new-window'></i></a>";
    if ($_SESSION['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
        $actionsHtml .= "<button type='button' class='btn btn-xs btn-danger' title='Supprimer' onclick=\"supprimerLienUrlAjax($idLien);\"><i class='glyphicon glyphicon-trash'></i></button>";
    }

    // Badge ID (uniquement pour les admins)
    $badgeId = estAdmin() ? "<span class='label label-default' style='font-weight:normal;'>ID: $idLien</span>" : "<span></span>";

    $html .= <<<HTML
    <div class="video-card" id="card-lien-$idLien">
        $thumbHtml
        <div class="video-card-content">
            <h3 title="$titre">$titre</h3>
            <p>$desc</p>
            $infosObjet
        </div>
        <div class="video-card-actions">
            $badgeId
            <div class="btn-group">
                $actionsHtml
            </div>
        </div>
    </div>
HTML;
}

$html .= "  </div>"; // .video-grid

// Pagination
$html .= "<div class='text-center' style='margin-top: 40px;'>";
$html .= $pagination->barrePagination();
$html .= "</div>";

$html .= "</div>"; // .container

// Script pour charger la vidéo YouTube au clic et gérer la suppression AJAX
$html .= <<<JS
<script>
function loadVideo(container, videoId) {
    container.innerHTML = '<iframe width="100%" height="180" src="https://www.youtube.com/embed/' + videoId + '?autoplay=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen><\/iframe>';
    container.onclick = null;
    container.style.cursor = 'default';
}

function supprimerLienUrlAjax(id) {
    if (!confirm('Voulez-vous vraiment supprimer ce lien ?')) return;

    $.post('lienurlPost.php', { id: id, mode: 'DEL' }, function(response) {
        if (response.indexOf('ok suppression') !== -1) {
            $('#card-lien-' + id).fadeOut(500, function() {
                $(this).remove();
                if ($('.video-card').length === 0) {
                    location.reload(); // Recharger pour afficher le message "vide" ou pagination
                }
            });
            if (typeof toastr !== 'undefined') {
                toastr.success('Le lien a été supprimé avec succès.');
            } else {
                alert('Le lien a été supprimé avec succès.');
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error('Erreur lors de la suppression : ' + response);
            } else {
                alert('Erreur lors de la suppression : ' + response);
            }
        }
    }).fail(function() {
        if (typeof toastr !== 'undefined') {
            toastr.error('Erreur réseau lors de la suppression.');
        } else {
            alert('Erreur réseau lors de la suppression.');
        }
    });
}
</script>
JS;

$html .= envoieFooter();
echo $html;
