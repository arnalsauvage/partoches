<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
const CHANSON = "chanson";
const RETOUR_RACINE = "../";
const CHEMIN_CHANSON_VOIR_PHP = "chanson_voir.php";
const CHANSON_POST_PHP = "chanson_post.php";
const CHANSON_CHERCHER = "chanson_chercher";
const CHANSON_UPLOAD = "chanson_upload.php";
const CHEMIN_LIEN_URL_POST_PHP = RETOUR_RACINE ."liens/lienurlPost.php";
const LIENS_LIEN_STRUM_CHANSON_POST_PHP = RETOUR_RACINE . "liens/lienStrumChanson_post.php";
const CHEMIN_SONGBOOK_FORM = RETOUR_RACINE . "/songbook/songbook_form.php";
const JS_CHANSON_FORM_JS = RETOUR_RACINE . RETOUR_RACINE . "js/chansonForm.js?v=25.3.28";

require_once dirname(__DIR__, 3) . "/autoload.php";
require_once __DIR__ . "/../lib/utilssi.php";
require_once __DIR__ . "/../utilisateur/Utilisateur.php";

$table =  CHANSON;
$sortie = "";
global $iconePoubelle;
global $cheminImages;
global $_DOSSIER_CHANSONS;

$listeSongbooks = Songbook::listeSongbooks();

// Sécurité : Vérification des droits
if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $urlRedirection = $table . "_voir.php";
    if (isset ($_GET ['id']) && is_numeric($_GET ['id'])) {
        $urlRedirection .= "?id=" . $_GET ['id'];
    }
    redirection($urlRedirection);
}

$_chanson = new Chanson();
$id = 0;

if (isset ($_POST ['id'])) {
    $id = intval($_POST ['id']);
}

if (isset ($_GET ['id']) && is_numeric($_GET ['id'])) {
    $id = intval($_GET ['id']);
    $_chanson->chercheChanson($id);
    $mode = "MAJ";
} else {
    $mode = "INS";
    $_chanson->setIdUser($_SESSION ['id']);
}

$titrePage = ($mode == "MAJ") ? "Mise à jour - " . $_chanson->getNom() : "Création chanson (Expérimental)";

// --- RENDU HTML ---
// Suppression du vieux CSS chansonform.css qui causait des conflits
$headHtml = envoieHead($titrePage, ""); 
$pasDeMenu = true;
require_once "../navigation/menu.php";

$sortie .= "
<div class='container' id='django-config-page' style='padding-top: 20px;'>
    <div class='header-django'>
        <h1><i class='glyphicon glyphicon-music'></i> " . ($mode == "MAJ" ? "Mise à jour chanson" : "Nouvelle partition") . "</h1>
        <div class='actions'>
            <a href='chanson_liste.php' class='btn-dj btn-dj-default'><i class='glyphicon glyphicon-list'></i> Retour liste</a>
        </div>
    </div>

    <div class='content-django' style='border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
        <div id='tabs-1'>
            <form id='chanson-form' method='POST' action='". CHANSON_POST_PHP ."' name='Form' class='form-dj-reset' style='background: transparent !important; border: none !important;'>
                <input type='hidden' name='id' value='" . $_chanson->getId() . "'>
                
                <div class='row'>
                    <div class='col-md-8'>
                        <div class='form-group-django'>
                            <label class='label-django'>Titre de la chanson :</label>
                            <input class='input-django' type='text' id='fnom' name='fnom' value='" . htmlspecialchars($_chanson->getNom(), ENT_QUOTES) . "' size='64' maxlength='128' placeholder='Titre de la chanson' required>
                        </div>

                        <div class='form-group-django'>
                            <label class='label-django'>Interprète / Artiste :</label>
                            <input class='input-django' type='text' id='finterprete' name='finterprete' value='" . htmlspecialchars($_chanson->getInterprete(), ENT_QUOTES) . "' size='64' placeholder='Interprète'>
                        </div>

                        <div class='row'>
                            <div class='col-sm-6'>
                                <div class='form-group-django'>
                                    <label class='label-django'>Année de sortie :</label>
                                    <div class='input-group-django'>
                                        <input class='input-django' type='number' min='0' max='2100' name='fannee' id='fannee' value='" . $_chanson->getAnnee() . "' data-db-year='" . $_chanson->getAnnee() . "'>
                                        <span id='yearValidationDot' class='dot' style='width: 12px; height: 12px; border-radius: 50%; display: inline-block; position: absolute; right: 10px; top: 12px; border: 1px solid #ccc;'></span>
                                    </div>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                <div class='form-group-django'>
                                    <label class='label-django'>Tonalité :</label>
                                    <input id='input-tonalite' class='input-django' type='text' name='ftonalite' value='" . $_chanson->getTonalite() . "' placeholder='ex: Am, C, F#m'>
                                </div>
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-sm-6'>
                                <div class='form-group-django'>
                                    <label class='label-django'>Tempo (BPM) : <span id='tempo-val' style='font-weight: bold; color: var(--c-accent);'>" . $_chanson->getTempo() . "</span></label>
                                    <input type='range' id='fader' min='30' max='250' step='1' oninput='document.getElementById(\"tempo-val\").innerHTML = value' name='ftempo' value='" . $_chanson->getTempo() . "' style='width: 100%; margin: 10px 0;'>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                <div class='form-group-django'>
                                    <label class='label-django'>Mesure :</label>
                                    <input id='input-mesure' class='input-django' type='text' name='fmesure' value='" . $_chanson->getMesure() . "' placeholder='4/4'>
                                </div>
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-sm-6'>
                                <div class='form-group-django'>
                                    <label class='label-django'>Pulsation :</label>
                                    <select id='select-pulsation' class='input-django' name='fpulsation'>
                                        <option value='binaire'" . ($_chanson->getPulsation() == "binaire" ? " selected" : "") . ">Binaire</option>
                                        <option value='ternaire'" . ($_chanson->getPulsation() == "ternaire" ? " selected" : "") . ">Ternaire</option>
                                    </select>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                <div class='form-group-django'>
                                    <label class='label-django'>Date publication :</label>
                                    <input id='input-date-pub' class='input-django' type='text' name='fdate' value='" . dateMysqlVersTexte($_chanson->getDatePub()) . "'>
                                </div>
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-sm-6'>
                                <div class='form-group-django'>
                                    <label class='label-django'>Auteur / Propriétaire :</label>
                                    " . selectUtilisateur("nom", "%", "nom", true, $_chanson->getIdUser(), "fidUser") . "
                                </div>                            </div>
                            <div class='col-sm-6'>
                                <div class='form-group-django'>
                                    <label class='label-django'>Vues (Hits) :</label>
                                    <input id='input-hits' class='input-django' type='number' name='fhits' value='" . $_chanson->getHits() . "'>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='col-md-4'>
                        <div class='section-dj' style='background: #fdfdfd;'>
                            <div class='section-dj-title'><i class='glyphicon glyphicon-picture'></i> Visuel (Pochette)</div>
                            <input type='hidden' name='fcover' id='fcover' value='" . htmlspecialchars($_chanson->getCover() ?? '', ENT_QUOTES) . "'>

                            <div id='selectedCoverPreview' class='text-center mb-15' " . (empty($_chanson->getCover()) ? "style='display:none;'" : "") . ">
                                <img id='currentSelectedCover' src='" . htmlspecialchars($_chanson->getCover() ?? '', ENT_QUOTES) . "' alt='Cover' style='max-width:100%; height:auto; border: 3px solid var(--c-marron-clair); border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);'>
                                <div class='mt-5'>
                                    <button type='button' id='clearCoverSelection' class='btn btn-xs btn-danger'><i class='glyphicon glyphicon-remove'></i> Retirer</button>
                                </div>
                            </div>

                            <div class='cover-selection-section'>
                                <h5 style='font-weight: bold; color: #666;'>Images locales</h5>
                                <div id='localCoversContainer' style='display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 15px;'>
                                    <p id='noLocalCoversMessage' style='display:none; font-size: 0.8em; color: #999;'>Aucune image.</p>
                                </div>

                                <h5 style='font-weight: bold; color: #666;'>Recherche Discogs</h5>
                                <button type='button' id='searchDiscogsCovers' class='btn btn-block btn-dj btn-dj-info'><i class='glyphicon glyphicon-refresh'></i> Chercher covers</button>
                                <div id='discogsCoversContainer' style='display: flex; flex-wrap: wrap; gap: 5px; margin-top: 10px;'>
                                    <p id='noDiscogsCoversMessage' style='font-size: 0.8em; color: #999;'>Aucun résultat.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class='footer-save-dj' style='margin-top: 20px;'>
                            <input type='hidden' name='mode' value='$mode'>
                            <button id='btn-valider-chanson' type='submit' name='valider' class='btn btn-block btn-lg btn-dj btn-dj-primary' style='font-weight: bold; font-size: 1.2em;'>
                                <i class='glyphicon glyphicon-floppy-disk'></i> ENREGISTRER
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
";

$sortie .= "<script src='" . JS_CHANSON_FORM_JS . "'></script>";

$sortie .= <<<JAVASCRIPT
<script>
$(document).ready(function() {
    // On surcharge le selectUtilisateur pour lui mettre la classe input-django
    $('select[name="fidUser"]').addClass('input-django');

    // --- Fonctions de gestion des covers (Django Style) ---
    function selectCover(coverUrl, thumbnailUrl = coverUrl) {
        $('#fcover').val(coverUrl);
        $('#currentSelectedCover').attr('src', thumbnailUrl);
        $('#selectedCoverPreview').show();
        $('.cover-thumbnail').css({'border': '1px solid #ddd', 'transform': 'scale(1)'});
        $(`img[data-cover-url="\${coverUrl}"]`).css({'border': '3px solid var(--c-accent)', 'transform': 'scale(1.1)'});
    }

    $('#clearCoverSelection').on('click', function() {
        $('#fcover').val('');
        $('#currentSelectedCover').attr('src', '');
        $('#selectedCoverPreview').hide();
        $('.cover-thumbnail').css({'border': '1px solid #ddd', 'transform': 'scale(1)'});
    });

    function renderCovers(containerId, coversArray, isDiscogs = false) {
        const \$container = \$(containerId);
        \$container.empty();

        if (coversArray.length > 0) {
            $('#no' + containerId.replace('#', '') + 'Message').hide();
            coversArray.forEach(function(cover) {
                let imageUrl = isDiscogs ? cover.url : cover;
                let title = isDiscogs ? cover.title + ' (' + cover.year + ') - ' + cover.artist : imageUrl;

                const \$img = \$('<img>')
                    .attr('src', imageUrl)
                    .addClass('cover-thumbnail')
                    .attr('data-cover-url', imageUrl)
                    .attr('title', title)
                    .css({
                        'width': '60px',
                        'height': '60px',
                        'object-fit': 'cover',
                        'cursor': 'pointer',
                        'border': '1px solid #ddd',
                        'border-radius': '4px',
                        'transition': 'all 0.2s'
                    });

                if ($('#fcover').val() === imageUrl) {
                    \$img.css({'border': '3px solid var(--c-accent)', 'transform': 'scale(1.1)'});
                }

                \$container.append(\$img);
            });
        } else {
            $('#no' + containerId.replace('#', '') + 'Message').show();
        }
    }

    $(document).on('click', '.cover-thumbnail', function() {
        selectCover($(this).data('cover-url'));
    });

    function loadLocalCovers(chansonId) {
        if (!chansonId || chansonId === "0") return;
        $.ajax({
            url: '../api/local_cover_search.php?id=' + chansonId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.local_covers && data.local_covers.length > 0) {
                    renderCovers('#localCoversContainer', data.local_covers);
                }
            }
        });
    }

    function fetchDiscogsData(songTitle, artist) {
        if (!songTitle || !artist) return;
        $('#noDiscogsCoversMessage').show().text('Recherche...');
        
        $.ajax({
            url: '../api/discogs_proxy.php?q=' + encodeURIComponent(songTitle + " " + artist),
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.discogs_covers && data.discogs_covers.length > 0) {
                    renderCovers('#discogsCoversContainer', data.discogs_covers, true);
                    
                    // Validation année
                    let firstResult = data.discogs_covers[0];
                    let year = firstResult.year || '';
                    let dbYear = $('#fannee').data('db-year');
                    let \$yearDot = $('#yearValidationDot');

                    if (year) {
                        if (parseInt(dbYear) === parseInt(year)) {
                            \$yearDot.css('background-color', '#28a745').attr('title', 'Confirmé par Discogs');
                        } else {
                            \$yearDot.css('background-color', '#dc3545').attr('title', 'Discogs suggère : ' + year);
                        }
                    } else {
                        \$yearDot.css('background-color', '#6c757d');
                    }
                } else {
                    $('#noDiscogsCoversMessage').show().text('Aucun résultat.');
                }
            }
        });
    }

    $('#fnom, #finterprete').on('blur', function() {
        fetchDiscogsData($('#fnom').val().trim(), $('#finterprete').val().trim());
    });

    $('#searchDiscogsCovers').on('click', function() {
        fetchDiscogsData($('#fnom').val().trim(), $('#finterprete').val().trim());
    });

    // Chargement initial
    let cId = $('input[name="id"]').val();
    if (cId && cId !== "0") loadLocalCovers(cId);
    fetchDiscogsData($('#fnom').val().trim(), $('#finterprete').val().trim());
});
</script>
JAVASCRIPT;

// --- AFFICHAGE FINAL ---
echo $headHtml;
echo $MENU_HTML;
echo $sortie;
echo envoieFooter();
