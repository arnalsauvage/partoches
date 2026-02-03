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

const DIV = "</div>";
require_once("chanson.php");
require_once("../document/document.php");
require_once('../liens/lienStrumChanson.php');
require_once('../liens/lienurl.php');
require_once("../navigation/menu.php");
require_once("../songbook/songbook.php");
require_once('../strum/strum.php');
require_once("../lib/utilssi.php");
// Inclusion de FichierIni pour lire params.ini
require_once __DIR__ . '/../lib/FichierIni.php'; // Chemin relatif vers FichierIni

$table =  CHANSON;
$sortie = "";
global $iconePoubelle;
global $cheminImages;
global $_DOSSIER_CHANSONS;

$listeSongbooks = [];
$listeSongbooks = listeSongbooks();

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on le redirige vers la page _voir
if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $urlRedirection = $table . "_voir.php";
    if (isset ($_GET ['id']) && is_numeric($_GET ['id']))
    {
        $urlRedirection .= "?id=" . $_GET ['id'];
    }
    redirection($urlRedirection);
}

// $id, $nom, $interprete, $année, $idUser, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0
$_chanson = new Chanson();

// Chargement des donnees de la chanson si l'identifiant est fourni

if (isset ($_POST ['id']))
{
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

echo "alive";

$sortie .= "
  <script>
  $( function() {
    $( '#tabs' ).tabs();
  } );
  </script>
 
";


if ($mode == "MAJ"){
    $sortie .= sprintf("<H1> Mise à jour - %s</H1>", $table);
}
if ($mode == "INS"){
    $sortie .= sprintf("<H1> Création - %s</H1>", $table);
    $id = 0;
}

// --- Début du premier onglet : Chanson ---
// Ce formulaire ne contient pour l'instant que les champs du premier onglet.
// Les informations externes (BPM, Année, Visuel) seront ajoutées via des appels API.

$sortie .= "
    <div id='tabs-1' class='col-lg-12 centrer'>
        <FORM  METHOD='POST' ACTION='". CHANSON_POST_PHP ."' name='Form'>
            <input type=HIDDEN name='id' VALUE='" . $_chanson->getId() . "'>
            
            <div class = 'row'>
                <label class='inline col-sm-3'>Nom :</label><input class= 'col-sm-7' type='text' id='fnom' name='fnom' VALUE='" . htmlspecialchars($_chanson->getNom(), ENT_QUOTES) . "' SIZE='64' MAXLENGTH='128' placeholder='titre de la chanson'><br>
            </div>
            <div class = 'row'>
                <label class='inline col-sm-3'>Interprète :</label><input class = 'col-sm-7' type='text' id='finterprete' name='finterprete' VALUE='" . htmlspecialchars($_chanson->getInterprete(), ENT_QUOTES) . "' SIZE='64'  placeholder='interprète'><br>
            </div>
            <div class = 'row'>
                <label class='inline col-sm-3'>Année de sortie:</label><input class= 'col-sm-7' type='number' min='0' max='2100' name='fannee' id='fannee' VALUE='" . $_chanson->getAnnee() . "' data-db-year='" . $_chanson->getAnnee() . "'>
                <span id='yearValidationDot' class='ml-2 dot' style='width: 10px; height: 10px; border-radius: 50%; display: inline-block; vertical-align: middle;'></span>
                ";
// --- Placeholder pour l'année via Discogs API ---
$yearFromDiscogsPlaceholder = "[Année via Discogs API]"; // Ceci est un commentaire, donc pas de changement
$sortie .= "
            </div>
            <script>function outputUpdate(vol) {
                document.querySelector('#tempo').value = vol;
            }</script>
            <div class = 'row'>
                <label class='inline col-sm-3' for='fader'>Tempo (BPM) :</label>
                <div class = 'col-sm-5'>
                    <input  type='range' id='fader' min='30' max='250' step='1' oninput='outputUpdate(value)' name='ftempo' value='" . $_chanson->getTempo() . "' >
                </div>
                <output class = 'inline col-sm-2' for='fader' id='tempo'>" . $_chanson->getTempo() . "</output>
                ";

// Affichage d'un lien de recherche Google pour les admins pour trouver le BPM manuellement
if ($_SESSION['privilege'] >= $GLOBALS["PRIVILEGE_ADMIN"]) { // Ou PRIVILEGE_EDITEUR si tu veux que les éditeurs y aient accès
    $googleSearchBaseUrl = "https://www.google.com/search?q=";
    $searchQuery = urlencode($_chanson->getNom() . " " . $_chanson->getInterprete() . " songbpm tunebat bpm");
    $googleSearchLink = $googleSearchBaseUrl . $searchQuery;

    $sortie .= "<div class='col-sm-12' style='margin-top: 10px;'>";
    $sortie .= "<a href='" . $googleSearchLink . "' target='_blank' class='btn btn-info btn-sm'>";
    $sortie .= "<span class='glyphicon glyphicon-search' aria-hidden='true'></span> Rechercher BPM sur Google";
    $sortie .= "</a>";
    $sortie .= "</div>";
} else {
    // Si ce n'est pas un admin, on peut laisser un message ou rien
    $sortie .= "<div class='col-sm-12' style='margin-top: 10px; font-size: 0.8em; color: #999;'>BPM externe non disponible.</div>";
}

$sortie .= "
            </div>
            <div class = 'row'>
                <label class='inline col-sm-3'>Mesure :</label>
                <input class= 'col-sm-7' type='text' name='fmesure' VALUE='" . $_chanson->getMesure() . "' SIZE='4' MAXLENGTH='128'>
            </div>
            <div class = 'row'>
                <label class='inline col-sm-3'> Pulsation :</label>
                <select class= 'col-sm-7' name='fpulsation' >
                    <option value='binaire'";
                    if ($_chanson->getPulsation() == "binaire") {
                        $sortie .= " selected";
                    }
                    $sortie .= ">binaire
                    </option>
                    <option value='ternaire' ";
                    if ($_chanson->getPulsation() == "ternaire") {
                        $sortie .= " selected";
                    }
                    $sortie .= ">ternaire</option>
                </select>";

                $sortie .= "
            </div>
            <div class = 'row'>
                <label class='inline col-sm-3'> Tonalité :</label>
                <input class= 'col-sm-7' type='text' name='ftonalite' VALUE='" . $_chanson->getTonalite() . "' SIZE='10' placeholder='ex :Am ou C ou F#'>
            </div>
            <div class = 'row'>
                <label class='inline col-sm-3'> Date publication :</label>
                <input class= 'col-sm-7' type='text' name='fdate' VALUE='" . dateMysqlVersTexte($_chanson->getDatePub()) . "' SIZE='10' MAXLENGTH='128'>
             </div>
            <div class = 'row'>
                <label class='inline col-sm-3'> Hits :</label>
                <input class= 'col-sm-7' type='number' name='fhits' VALUE='" . $_chanson->getHits() . "' >
            </div>
            <!-- Section de sélection de cover DÉPLACÉE À L'INTÉRIEUR DU FORMULAIRE -->
            <div class='row mt-3'>
                <label class='inline col-sm-3'>Visuel (Pochette) :</label>
                <div class='col-sm-9'>

                    <input type='hidden' name='fcover' id='fcover' value='" . htmlspecialchars($_chanson->getCover() ?? '', ENT_QUOTES) . "'>

                    <div id='selectedCoverPreview' style='margin-bottom: 10px; " . (empty($_chanson->getCover()) ? "display:none;" : "") . "'>
                        <h4>Cover sélectionnée :</h4>
                        <img id='currentSelectedCover' src='" . htmlspecialchars($_chanson->getCover() ?? '', ENT_QUOTES) . "' alt='Cover sélectionnée' style='max-width:150px; height:auto; border: 2px solid #007bff; border-radius: 5px;'>
                        <button type='button' id='clearCoverSelection' class='btn btn-warning btn-sm ml-2'>Effacer la sélection</button>";
                        // La valeur fcover est déjà dans le input caché.
                        // On n'a pas besoin de la réinitialiser si elle est déjà dans le hidden input.
                        // On peut mettre à jour la preview ici si fcover n'est pas vide
                        if (!empty($_chanson->getCover())) {
                            $sortie .= "<script>$('#selectedCoverPreview').show(); $('#currentSelectedCover').attr('src', '". htmlspecialchars($_chanson->getCover(), ENT_QUOTES) ."');</script>";
                        }
$sortie .= "
                    </div>

                    <div class='cover-selection-section mt-3'>
                        <h4>Images locales :</h4>
                        <div id='localCoversContainer' class='d-flex flex-wrap'>
                            <p id='noLocalCoversMessage' style='display:none;'>Aucune image locale trouvée.</p>
                        </div>
                    </div>

                    <div class='cover-selection-section mt-3'>
                        <h4>Recherche Discogs :</h4>
                        <button type='button' id='searchDiscogsCovers' class='btn btn-primary btn-sm mb-2'>
                            <span class='glyphicon glyphicon-search' aria-hidden='true'></span> Rechercher sur Discogs
                        </button>
                        <div id='discogsCoversContainer' class='d-flex flex-wrap'>
                            <p id='noDiscogsCoversMessage' style='display:none;'>Cliquez sur \"Rechercher sur Discogs\" pour trouver des covers.</p>
                        </div>
                    </div>

                </div>
            </div>
            <!-- FIN Section de sélection de cover -->

            <div class = 'row'>
                <label class='inline col-sm-3'> Utilisateur :</label>"
                        . selectUtilisateur("nom", "%", "login", true, $_chanson->getIdUser()) . "
                <input type=hidden name='mode' VALUE='$mode'>
                <label class='inline'> </label><input type='submit' name='valider' VALUE=' Valider ' >
            </div>
    </FORM>
";
// On sort l'inclusion du script externe du bloc Heredoc pour que PHP l'interprète
$sortie .= "<script src='" . JS_CHANSON_FORM_JS . "'></script>";

            $sortie .= <<<JAVASCRIPT
    <script>
    $(document).ready(function() {
        // Initialisation des onglets jQuery UI
        $('#tabs').tabs();

        // Initialisation de la puce de validation de l'année
        let initialDbYear = null;
        let \$fanneeInput = $('input[name="fannee"]');
        if (\$fanneeInput.length) { // Vérifier si l'élément input[name="fannee"] existe
            initialDbYear = \$fanneeInput.data('db-year');
        }

        let \$initialYearDot = $('#yearValidationDot');
        if (\$initialYearDot.length) { // Vérifier si l'élément #yearValidationDot existe
            if (initialDbYear) {
                \$initialYearDot.css('background-color', 'lightgray').attr('title', 'Recherche Discogs en attente...');
            } else {
                \$initialYearDot.css('background-color', 'gray').attr('title', 'Aucune année de BDD.');
            }
        }

        // --- Fonctions de gestion des covers ---

        // Fonction pour sélectionner une cover
        function selectCover(coverUrl, thumbnailUrl = coverUrl) {
            $('#fcover').val(coverUrl);
            $('#currentSelectedCover').attr('src', thumbnailUrl);
            $('#selectedCoverPreview').show();
            $('.cover-thumbnail').removeClass('selected').css('border', '1px solid #ddd');
            $(`img[data-cover-url="\${coverUrl}"]`).addClass('selected').css('border', '2px solid #007bff');
        }

        // Fonction pour effacer la sélection de la cover
        $('#clearCoverSelection').on('click', function() {
            $('#fcover').val('');
            $('#currentSelectedCover').attr('src', '');
            $('#selectedCoverPreview').hide();
            $('.cover-thumbnail').removeClass('selected').css('border', '1px solid #ddd');
        });

        // Fonction pour rendre les vignettes de cover
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
                            'max-width': '80px',
                            'height': 'auto',
                            'margin': '5px',
                            'cursor': 'pointer',
                            'border': '1px solid #ddd',
                            'border-radius': '3px'
                        });

                    if ($('#fcover').val() === imageUrl) {
                        \$img.addClass('selected').css('border', '2px solid #007bff');
                    }

                    \$container.append(\$img);
                });
            } else {
    $('#no' + containerId.replace('#', '') + 'Message').show();
}
        }

        // Gestionnaire de clic pour la sélection des vignettes
        \$(document).on('click', '.cover-thumbnail', function() {
    const coverUrl = \$(this).data('cover-url');
            selectCover(coverUrl);
        });

        // Fonction pour charger les covers locales
        function loadLocalCovers(chansonId) {
            if (!chansonId || chansonId === "0") {
                console.log("ID de chanson manquant ou nul, pas de recherche de covers locales.");
                $('#noLocalCoversMessage').show();
                return;
            }
            const localCoverUrl = `../api/local_cover_search.php?id=\${chansonId}`;
            \$.ajax({
                url: localCoverUrl,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                if (data.local_covers && data.local_covers.length > 0) {
                    renderCovers('#localCoversContainer', data.local_covers);
                } else {
                    $('#localCoversContainer').empty();
                    $('#noLocalCoversMessage').show();
                }
            },
                error: function(jqXHR, textStatus, errorThrown) {
                console.error("Erreur lors de l'appel local_cover_search API:", textStatus, errorThrown);
                $('#localCoversContainer').empty();
                $('#noLocalCoversMessage').show();
            }
            });
        }

        // Fonction pour déclencher la recherche Discogs
        function fetchDiscogsData(songTitle, artist) {
            $('#discogsCoversContainer').empty();
            $('#noDiscogsCoversMessage').show().text('Recherche en cours...');

            if (!songTitle || !artist) {
                console.log("Titre ou artiste manquant, recherche Discogs annulée.");
                $('#noDiscogsCoversMessage').text('Titre ou artiste manquant pour la recherche Discogs.');
                return;
            }

            let query = songTitle + " " + artist;
            let discogsProxyUrl = '../api/discogs_proxy.php?q=' + encodeURIComponent(query);
            console.log("Appel Discogs Proxy : " + discogsProxyUrl);

            \$.ajax({
                url: discogsProxyUrl,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                console.log("Réponse Discogs:", data);
                if (data.discogs_covers && data.discogs_covers.length > 0) {
                    renderCovers('#discogsCoversContainer', data.discogs_covers, true);
                } else {
                    $('#discogsCoversContainer').empty();
                    $('#noDiscogsCoversMessage').show().text('Aucune cover Discogs trouvée.');
                }

                // Logique de la puce de validation de l'année
                if (data.discogs_covers && data.discogs_covers.length > 0) { // Utilise le nouveau format discogs_covers
                    let firstResult = data.discogs_covers[0]; // Utilise le nouveau format
                        let year = firstResult.year || '';

                        let dbYear = \$('input[name="fannee"]').data('db-year');
                        let \$yearDot = $('#yearValidationDot');

                        if (year) {
                            if (parseInt(dbYear) === parseInt(year)) {
                                \$yearDot.css('background-color', 'green').attr('title', 'Année confirmée par Discogs');
                            } else {
                                \$yearDot.css('background-color', 'red').attr('title', 'Année Discogs : ' + year);
                            }
                        } else {
                            \$yearDot.css('background-color', 'gray').attr('title', 'Année Discogs non trouvée');
                        }
                    } else {
                    $('#yearValidationDot').css('background-color', 'gray').attr('title', 'Année Discogs non trouvée');
                }
            },
                error: function(jqXHR, textStatus, errorThrown) {
                console.error("Erreur lors de l'appel Discogs API:", textStatus, errorThrown, jqXHR.responseText);
                $('#discogsCoversContainer').empty();
                $('#noDiscogsCoversMessage').show().text('Erreur lors de la recherche Discogs.');
                $('#yearValidationDot').css('background-color', 'gray').attr('title', 'Erreur de recherche Discogs.');
            }
            });
        }

        // Event Listeners
        let debounceTimer;
        $('#fnom, #finterprete').on('input', function() {
            let songTitle = $('#fnom').val().trim();
            let artist = $('#finterprete').val().trim();

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchDiscogsData(songTitle, artist);
            }, 500);
        });

        $('#searchDiscogsCovers').on('click', function() {
            let songTitle = $('#fnom').val().trim();
            let artist = $('#finterprete').val().trim();
            fetchDiscogsData(songTitle, artist);
        });

        // Recherche initiale
        let initialSongTitle = '';
        if ($('#fnom').length) {
            initialSongTitle = $('#fnom').val().trim();
        }

        let initialArtist = '';
        if ($('#finterprete').length) {
            initialArtist = $('#finterprete').val().trim();
        }

        let chansonId = \$('input[name="id"]').val();

        if (chansonId && chansonId !== "0") {
            loadLocalCovers(chansonId);
        }

        if (initialSongTitle || initialArtist) {
            fetchDiscogsData(initialSongTitle, initialArtist);
        } else {
            $('#noDiscogsCoversMessage').show().text('Cliquez sur "Rechercher sur Discogs" pour trouver des covers.');
        }

        // Gestion de la cover sélectionnée
        if ($('#fcover').val()) {
            $('#selectedCoverPreview').show();
            $('#currentSelectedCover').attr('src', $('#fcover').val());
        } else {
            $('#selectedCoverPreview').hide();
        }
    });
</script>
JAVASCRIPT;

echo $sortie;
echo envoieFooter();

/////////////////////////////////: fonctions //////////////////////////////////////
///
/// Les fonctions comme selectUtilisateur, chercheDocumentsTableId, etc.
/// sont supposées être définies dans les fichiers inclus (utilssi.php, etc.)
/// et ne sont pas incluses ici pour la lisibilité du refactoring.
///