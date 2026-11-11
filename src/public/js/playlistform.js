/**
 * Script dédié au formulaire de Playlist (Canopée / SOLID)
 * Gère les onglets, les options dynamiques, les mosaïques d'illustration
 * et la recherche autocomplétée prédictive de morceaux (US-083).
 */
$(document).ready(function () {
    // --- 1. GESTION DES ONGLETS (JQUERY UI TABS) ---
    var activeTab = sessionStorage.getItem('playlistFormActiveTab') || 0;
    $("#tabs").tabs({
        active: parseInt(activeTab, 10),
        activate: function (event, ui) {
            sessionStorage.setItem('playlistFormActiveTab', ui.newTab.index());
        }
    });

    if ($.fn.select2) {
        $('.select2').select2({ theme: "bootstrap", width: '100%' });
    }

    // --- 2. OPTIONS DYNAMIQUES ---
    $('#playlist_type').on('change', function () {
        if ($(this).val() == '1') {
            $('#dynamic_options').slideDown();
        } else {
            $('#dynamic_options').slideUp();
        }
    });

    // --- 3. GESTION DU VISUEL & MOSAÏQUE ---
    var selectedCovers = [];
    var currentVal = $('#fimage').val();
    if (currentVal && currentVal.indexOf(',') !== -1) {
        selectedCovers = currentVal.split(',');
        $('.mosaic-thumbnail').each(function () {
            if (selectedCovers.indexOf($(this).data('url')) > -1) {
                $(this).addClass('selected-cover');
            }
        });
        $('#mosaic-count').text(selectedCovers.length);
    }

    $('.mosaic-thumbnail').on('click', function () {
        var url = $(this).data('url');
        var index = selectedCovers.indexOf(url);
        if (index > -1) {
            selectedCovers.splice(index, 1);
            $(this).removeClass('selected-cover');
        } else {
            if (selectedCovers.length < 4) {
                selectedCovers.push(url);
                $(this).addClass('selected-cover');
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Maximum 4 pochettes pour la mosaïque.');
                }
            }
        }
        $('#fimage').val(selectedCovers.join(','));
        $('#mosaic-count').text(selectedCovers.length);
    });

    $('#btnClearMosaic').on('click', function () {
        selectedCovers = [];
        $('.mosaic-thumbnail').removeClass('selected-cover');
        $('#fimage').val('mosaique-automatique');
        $('#mosaic-count').text(0);
        if (typeof toastr !== 'undefined') {
            toastr.info('Mosaïque réinitialisée en mode automatique.');
        }
    });

    // --- 4. RECHERCHE AUTOCOMPLÉTÉE PRÉDICTIVE (US-083) ---
    var $inputRecherche = $('#rechercheChansonPlaylist');
    var $resultsDiv = $('#resultsChansonPlaylist');
    var $inputChansonId = $('#inputChansonIdPlaylist');
    var $btnSubmit = $('#btnSubmitAddSong');
    var $btnReset = $('#btnResetSearch');
    var debounceTimer = null;

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }

    $inputRecherche.on('input keyup', function () {
        var query = $(this).val().trim();

        // Réinitialiser la sélection si l'utilisateur modifie la saisie
        $inputChansonId.val('');
        $btnSubmit.prop('disabled', true);

        if (query.length < 4) {
            $resultsDiv.empty().hide();
            clearTimeout(debounceTimer);
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            $.ajax({
                url: '../chanson/chanson_recherche_ajax.php',
                type: 'GET',
                dataType: 'json',
                data: { q: query },
                success: function (chansons) {
                    $resultsDiv.empty();
                    if (Array.isArray(chansons) && chansons.length > 0) {
                        chansons.forEach(function (c) {
                            var nomSafe = escapeHtml(c.nom);
                            var interpSafe = escapeHtml(c.interprete || 'Interprète inconnu');
                            var tonaliteSafe = escapeHtml(c.tonalite || '');

                            var htmlItem = '<div class="playlist-search-item-info">';
                            htmlItem += '<strong>' + nomSafe + '</strong> - ' + interpSafe;
                            htmlItem += '</div>';

                            if (tonaliteSafe !== '') {
                                htmlItem += '<span class="playlist-badge-tonalite" title="Tonalité">' + tonaliteSafe + '</span>';
                            }

                            var $item = $('<div class="playlist-search-item" role="option"></div>')
                                .html(htmlItem)
                                .on('click', function () {
                                    // Formatage du champ : Titre - Interprète [- Tonalité]
                                    var displayLabel = c.nom + ' - ' + (c.interprete || '');
                                    if (c.tonalite && c.tonalite.trim() !== '') {
                                        displayLabel += ' - ' + c.tonalite;
                                    }
                                    $inputRecherche.val(displayLabel);
                                    $inputChansonId.val(c.id);
                                    $btnSubmit.prop('disabled', false).focus();
                                    $resultsDiv.empty().hide();
                                });

                            $resultsDiv.append($item);
                        });
                        $resultsDiv.show();
                    } else {
                        $resultsDiv.html('<div class="playlist-search-no-result">Aucun morceau trouvé pour cette recherche.</div>').show();
                    }
                },
                error: function () {
                    $resultsDiv.html('<div class="playlist-search-no-result text-danger">Erreur lors de la recherche.</div>').show();
                }
            });
        }, 250);
    });

    $btnReset.on('click', function () {
        $inputRecherche.val('').focus();
        $inputChansonId.val('');
        $btnSubmit.prop('disabled', true);
        $resultsDiv.empty().hide();
    });

    // Mémoriser l'onglet 1 (Morceaux) lors de la soumission du formulaire d'ajout
    $('#formAjoutChansonPlaylist').on('submit', function () {
        sessionStorage.setItem('playlistFormActiveTab', 1);
    });

    // Fermer le dropdown lors d'un clic en dehors
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.playlist-search-wrapper').length) {
            $resultsDiv.hide();
        }
    });

    // Touche Échap pour fermer la liste
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            $resultsDiv.hide();
        }
    });
});
