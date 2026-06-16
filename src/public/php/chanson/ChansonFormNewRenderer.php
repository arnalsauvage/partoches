<?php
/**
 * Renderer du formulaire de chanson nouvelle génération.
 *
 * Responsabilité unique :
 * - Générer la page HTML complète (head, menu, formulaire, scripts, footer).
 * - Ne fait aucune logique métier ni accès direct à la BDD en dehors des delegates explicites.
 */

class ChansonFormNewRenderer
{
    // Constantes internes pour centraliser les chemins
    private const RETOUR_RACINE = '../';
    private const CHANSON_POST_PHP = 'chanson_post.php';
    private const JS_CHANSON_FORM_JS = '../../js/chansonForm.js?v=25.3.28';

    /**
     * Rendu complet de la page.
     *
     * @param Chanson $_chanson
     * @param string $mode
     * @param array $context ['listeSongbooks', 'dossierChanson', 'iconePoubelle', 'cheminImages']
     * @return string
     */
    public static function render(Chanson $_chanson, string $mode, array $context = []): string
    {
        $titrePage = ($mode === 'MAJ')
            ? 'Mise à jour - ' . $_chanson->getNom()
            : 'Création chanson (Expérimental)';

        // Inclusion explicite de la CSS spécifique
        $headHtml = envoieHead($titrePage, '../../css/chansonform.css');
        $pasDeMenu = true;
        require_once self::RETOUR_RACINE . 'navigation/menu.php';

        $sortie = '';
        $sortie .= self::renderContainer($_chanson, $mode, $context);
        $sortie .= self::renderScript();

        $final = $headHtml;
        $final .= $MENU_HTML;
        $final .= $sortie;
        $final .= envoieFooter();

        return $final;
    }

    // --------------------------------------------------------------------
    //  STRUCTURE DE PAGE
    // --------------------------------------------------------------------

    private static function renderContainer(Chanson $_chanson, string $mode, array $context): string
    {
        $html  = "<div class='container sb-form-container' id='django-config-page'>";
        $html .= self::renderHeader($mode);
        $html .= "<div class='content-django card-shadow-django'>";
        $html .= "<div id='tabs-1'>";
        $html .= self::renderForm($_chanson, $mode);
        $html .= self::renderFilesTab($_chanson, $context);
        $html .= self::renderStrumTab($_chanson);
        $html .= self::renderLinksTab($_chanson->getId());
        $html .= "</div></div></div>";

        return $html;
    }

    private static function renderHeader(string $mode): string
    {
        $titre = ($mode === 'MAJ') ? 'Mise à jour chanson' : 'Nouvelle partition';

        return <<<HTML
        <div class='header-django'>
            <h1><i class='glyphicon glyphicon-music'></i> {$titre}</h1>
            <div class='actions'>
                <a href='chanson_liste.php' class='btn-dj btn-dj-default'>
                    <i class='glyphicon glyphicon-list'></i> Retour liste
                </a>
            </div>
        </div>
    HTML;
    }

    // --------------------------------------------------------------------
    //  FORMULAIRE PRINCIPAL
    // --------------------------------------------------------------------

    private static function renderForm(Chanson $_chanson, string $mode): string
    {
        $html = "<form id='chanson-form' method='POST' action='" . self::CHANSON_POST_PHP . "' name='Form' class='form-dj-reset form-chanson-new'>";
        $html .= "<input type='hidden' name='id' value='" . $_chanson->getId() . "'>";
        $html .= "<div class='row'><div class='col-md-8'>";

        // Titre
        $html .= self::fieldText('Titre de la chanson :', 'fnom', 'fnom', htmlspecialchars($_chanson->getNom(), ENT_QUOTES), 'Titre de la chanson', 64, 128, true);

        // Interprète
        $html .= self::fieldText('Interprète / Artiste :', 'finterprete', 'finterprete', htmlspecialchars($_chanson->getInterprete(), ENT_QUOTES), 'Interprète', 64);

        // Année / Tonalité
        $html .= self::renderBlockRow(function () use ($_chanson) {
            $annee = (int) $_chanson->getAnnee();
            $tonalite = htmlspecialchars($_chanson->getTonalite(), ENT_QUOTES);

            $html  = "<div class='col-sm-6'>";
            $html .= "<div class='form-group-django'><label class='label-django'>Année de sortie :</label>";
            $html .= "<div class='input-group-django'>";
            $html .= "<input class='input-django' type='number' min='0' max='2100' name='fannee' id='fannee' value='{$annee}' data-db-year='{$annee}'>";
            $html .= "<span id='yearValidationDot' class='dot year-validation-dot'></span>";
            $html .= "</div></div></div>";

            $html .= "<div class='col-sm-6'><div class='form-group-django'><label class='label-django'>Tonalité :</label>";
            $html .= "<input id='input-tonalite' class='input-django' type='text' name='ftonalite' value='{$tonalite}' placeholder='ex: Am, C, F#m'></div></div>";

            return $html;
        });

        // Tempo / Mesure
        $html .= self::renderBlockRow(function () use ($_chanson) {
            $tempo = (int) $_chanson->getTempo();
            $mesure = htmlspecialchars($_chanson->getMesure(), ENT_QUOTES);

            $html  = "<div class='col-sm-6'>";
            $html .= "<div class='form-group-django'><label class='label-django'>Tempo (BPM) : <span id='tempo-val' class='tempo-value'>{$tempo}</span></label>";
            $html .= "<input type='range' id='fader' min='30' max='250' step='1' oninput='document.getElementById(\"tempo-val\").innerHTML = value' name='ftempo' value='{$tempo}' class='tempo-slider'></div>";
            $html .= "</div>";

            $html .= "<div class='col-sm-6'><div class='form-group-django'><label class='label-django'>Mesure :</label>";
            $html .= "<input id='input-mesure' class='input-django' type='text' name='fmesure' value='{$mesure}' placeholder='4/4'></div></div>";

            return $html;
        });

        // Pulsation / Date pub
        $html .= self::renderBlockRow(function () use ($_chanson) {
            $pubBinaire = ($_chanson->getPulsation() === 'binaire') ? ' selected' : '';
            $pubTernaire = ($_chanson->getPulsation() === 'ternaire') ? ' selected' : '';

            if (function_exists('dateMysqlVersTexte')) {
                $date = dateMysqlVersTexte($_chanson->getDatePub());
            } else {
                $date = $_chanson->getDatePub();
            }

            $html  = "<div class='col-sm-6'>";
            $html .= "<div class='form-group-django'><label class='label-django'>Pulsation :</label>";
            $html .= "<select id='select-pulsation' class='input-django' name='fpulsation'>";
            $html .= "<option value='binaire'{$pubBinaire}>Binaire</option>";
            $html .= "<option value='ternaire'{$pubTernaire}>Ternaire</option>";
            $html .= "</select></div></div>";

            $html .= "<div class='col-sm-6'><div class='form-group-django'><label class='label-django'>Date publication :</label>";
            $html .= "<input id='input-date-pub' class='input-django' type='text' name='fdate' value='{$date}'></div></div>";

            return $html;
        });

        // Publication
        $checkedPub = ($_chanson->getPublication() === 1) ? ' checked' : '';
        $html .= "<div class='row'><div class='col-sm-12'>";
        $html .= "<div class='form-group-django'>";
        $html .= "<label class='label-django' for='fpublication'>Publication :</label>";
        $html .= "<input type='checkbox' id='fpublication' name='fpublication' value='1'{$checkedPub}>";
        $html .= "<span class='text-muted small'> (Visible par tous si coché)</span>";
        $html .= "</div></div></div>";

        // Auteur / Hits
        $html .= self::renderBlockRow(function () use ($_chanson) {
            $idUser = (int) $_chanson->getIdUser();
            $hits = (int) $_chanson->getHits();

            $selectUser = Utilisateur::selectUtilisateur('nom', '%', 'login', true, $idUser, 'fidUser');

            $html  = "<div class='col-sm-6'>";
            $html .= "<div class='form-group-django'><label class='label-django'>Auteur / Propriétaire :</label>";
            $html .= $selectUser;
            $html .= "</div></div>";

            $html .= "<div class='col-sm-6'><div class='form-group-django'><label class='label-django'>Vues (Hits) :</label>";
            $html .= "<input id='input-hits' class='input-django' type='number' name='fhits' value='{$hits}'></div></div>";

            return $html;
        });

        $html .= "</div>"; // /col-md-8

        // Colonne droite : pochette + sauvegarde
        $html .= self::renderCoverSide($_chanson);

        $html .= "</div></form>";

        return $html;
    }

    private static function fieldText(string $label, string $id, string $name, string $value, string $placeholder = '', int $size = 64, int $maxlength = 255, bool $required = false): string
    {
        $req = $required ? ' required' : '';
        $max = $maxlength < 255 ? " maxlength='{$maxlength}'" : '';

        return "<div class='form-group-django'><label class='label-django' for='{$id}'>{$label}</label>"
             . "<input class='input-django' type='text' id='{$id}' name='{$name}' value='{$value}' size='{$size}'{$max} placeholder='{$placeholder}'{$req}>"
             . "</div>";
    }

    private static function renderBlockRow(callable $callback): string
    {
        return "<div class='row'>" . $callback() . "</div>";
    }

    // --------------------------------------------------------------------
    //  COTE DROIT : POCHETTE + BOUTON ENREGISTRER
    // --------------------------------------------------------------------

    private static function renderCoverSide(Chanson $_chanson): string
    {
        $cover = htmlspecialchars($_chanson->getCover() ?? '', ENT_QUOTES);
        $emptyClass = empty($cover) ? " hidden-element" : '';

        $html  = "<div class='col-md-4'>";
        $html .= "<div class='section-dj carte-canopee cover-section-dj'>";
        $html .= "<div class='section-dj-title caption'><i class='glyphicon glyphicon-picture'></i> Visuel (Pochette)</div>";
        $html .= "<input type='hidden' name='fcover' id='fcover' value='{$cover}'>";

        $html .= "<div id='selectedCoverPreview' class='text-center mb-15{$emptyClass}'>";
        $html .= "<img id='currentSelectedCover' src='{$cover}' alt='Cover' class='img-cover-preview'>";
        $html .= "<div class='mt-5'><button type='button' id='clearCoverSelection' class='btn btn-xs btn-danger'><i class='glyphicon glyphicon-remove'></i> Retirer</button></div>";
        $html .= "</div>";

        $html .= "<div class='cover-selection-section caption'>";
        $html .= "<h5 class='cover-section-subtitle'>Images locales</h5>";
        $html .= "<div id='localCoversContainer' class='covers-container mb-15'>";
        $html .= "<p id='noLocalCoversMessage' class='no-covers-message hidden-element'>Aucune image.</p>";
        $html .= "</div>";

        $html .= "<h5 class='cover-section-subtitle'>Recherche Discogs</h5>";
        $html .= "<button type='button' id='searchDiscogsCovers' class='btn btn-block btn-dj btn-dj-info'><i class='glyphicon glyphicon-refresh'></i> Chercher covers</button>";
        $html .= "<div id='discogsCoversContainer' class='covers-container mt-10'>";
        $html .= "<p id='noDiscogsCoversMessage' class='no-covers-message'>Aucun résultat.</p>";
        $html .= "</div></div></div>";

        $modeValue = ($_chanson->getId() > 0) ? 'MAJ' : 'INS';
        $html .= "<div class='footer-save-dj mt-20'>";
        $html .= "<input type='hidden' name='mode' value='{$modeValue}'>";
        $html .= "<button id='btn-valider-chanson' type='submit' class='btn btn-block btn-lg btn-dj btn-dj-primary btn-save-chanson'>";
        $html .= "<i class='glyphicon glyphicon-floppy-disk'></i> ENREGISTRER</button>";
        $html .= "</div></div>";

        return $html;
    }

    // --------------------------------------------------------------------
    //  ONGLET FICHIERS
    // --------------------------------------------------------------------

    private static function renderFilesTab(Chanson $_chanson, array $context): string
    {
        $id = $_chanson->getId();
        if ($id <= 0) {
            return "<div class='alert alert-info'>Enregistrez d'abord la chanson pour pouvoir y ajouter des fichiers.</div>";
        }

        return ChansonFormRenderer::renderFiles(
            $id,
            $context['dossierChanson'] ?? '/',
            $context['iconePoubelle'] ?? '',
            $context['cheminImages'] ?? '',
            $context['listeSongbooks'] ?? [],
            $_chanson
        ) . ChansonFormRenderer::renderTrash(
            $id,
            $context['dossierChanson'] ?? '/',
            $context['iconePoubelle'] ?? '',
            $context['cheminImages'] ?? '',
            $_chanson
        );
    }

    // --------------------------------------------------------------------
    //  ONGLET STRUMS
    // --------------------------------------------------------------------

    private static function renderStrumTab(Chanson $_chanson): string
    {
        return ChansonFormRenderer::renderStrums($_chanson);
    }

    // --------------------------------------------------------------------
    //  ONGLET LIENS
    // --------------------------------------------------------------------

    private static function renderLinksTab(int $id): string
    {
        return ChansonFormRenderer::renderLinks($id);
    }

    // --------------------------------------------------------------------
    //  SCRIPTS
    // --------------------------------------------------------------------

    private static function renderScript(): string
    {
        return "<script src='" . self::JS_CHANSON_FORM_JS . "'></script>" . self::inlineScript();
    }

    private static function inlineScript(): string
    {
        return <<<JAVASCRIPT
<script>
$(document).ready(function() {
    $('select[name="fidUser"]').addClass('input-django');

    // --- Gestion des pochettes ---
    function selectCover(coverUrl, thumbnailUrl = coverUrl) {
        $('#fcover').val(coverUrl);
        $('#currentSelectedCover').attr('src', thumbnailUrl);
        $('#selectedCoverPreview').removeClass('hidden-element');
        $('.cover-thumbnail').removeClass('selected-cover');
        $(`img[data-cover-url="\${coverUrl}"]`).addClass('selected-cover');
    }

    $('#clearCoverSelection').on('click', function() {
        $('#fcover').val('');
        $('#currentSelectedCover').attr('src', '');
        $('#selectedCoverPreview').addClass('hidden-element');
        $('.cover-thumbnail').removeClass('selected-cover');
    });

    function renderCovers(containerId, coversArray, isDiscogs = false) {
        const $container = $(containerId);
        $container.empty();
        if (coversArray.length > 0) {
            $('#no' + containerId.replace('#', '') + 'Message').addClass('hidden-element');
            coversArray.forEach(function(cover) {
                let imageUrl = isDiscogs ? cover.url : cover;
                let title = isDiscogs ? cover.title + ' (' + cover.year + ') - ' + cover.artist : imageUrl;
                const $img = $('<img>')
                    .attr('src', imageUrl)
                    .addClass('cover-thumbnail')
                    .attr('data-cover-url', imageUrl)
                    .attr('title', title);
                if ($('#fcover').val() === imageUrl) {
                    $img.addClass('selected-cover');
                }
                $container.append($img);
            });
        } else {
            $('#no' + containerId.replace('#', '') + 'Message').removeClass('hidden-element');
        }
    }

    $(document).on('click', '.cover-thumbnail', function() {
        selectCover($(this).data('cover-url'));
    });

    function loadLocalCovers(chansonId) {
        if (!chansonId || chansonId === "0") return;
        $.ajax({
            url: '../api/local_cover_search.php?id=' + chansonId,
            type: 'GET', dataType: 'json',
            success: function(data) {
                if (data.local_covers && data.local_covers.length > 0) {
                    renderCovers('#localCoversContainer', data.local_covers);
                }
            }
        });
    }

    function fetchDiscogsData(songTitle, artist) {
        if (!songTitle || !artist) return;
        $('#noDiscogsCoversMessage').removeClass('hidden-element').text('Recherche...');
        $.ajax({
            url: '../api/discogs_proxy.php?q=' + encodeURIComponent(songTitle + " " + artist),
            type: 'GET', dataType: 'json',
            success: function(data) {
                if (data.discogs_covers && data.discogs_covers.length > 0) {
                    renderCovers('#discogsCoversContainer', data.discogs_covers, true);
                    let firstResult = data.discogs_covers[0];
                    let year = firstResult.year || '';
                    let dbYear = $('#fannee').data('db-year');
                    let $yearDot = $('#yearValidationDot');
                    if (year) {
                        if (parseInt(dbYear) === parseInt(year)) {
                            $yearDot.css('background-color', '#28a745').attr('title', 'Confirmé par Discogs');
                        } else {
                            $yearDot.css('background-color', '#dc3545').attr('title', 'Discogs suggère : ' + year);
                        }
                    } else {
                        $yearDot.css('background-color', '#6c757d');
                    }
                } else {
                    $('#noDiscogsCoversMessage').removeClass('hidden-element').text('Aucun résultat.');
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

    let cId = $('input[name="id"]').val();
    if (cId && cId !== "0") loadLocalCovers(cId);
    fetchDiscogsData($('#fnom').val().trim(), $('#finterprete').val().trim());
});
</script>
JAVASCRIPT;
    }
}
