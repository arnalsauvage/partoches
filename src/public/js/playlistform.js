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

    // --- 3. GESTION DU VISUEL & MOSAÏQUE (US-084: NxN & Harmonisation Chromatique) ---
    var selectedCovers = [];
    var maxCovers = 4;

    function updateGenerateButtonState() {
        var n = Math.round(Math.sqrt(maxCovers));
        if (selectedCovers.length > 0) {
            $('#btnGenerateMosaic').prop('disabled', false).html(
                '<i class="glyphicon glyphicon-th-large"></i> Générer la mosaïque ' + n + '×' + n + ' (' + selectedCovers.length + ' / ' + maxCovers + ')'
            );
        } else {
            $('#btnGenerateMosaic').prop('disabled', true).html(
                '<i class="glyphicon glyphicon-th-large"></i> Générer la pochette de playlist avec ces images'
            );
        }
    }

    var currentVal = $('#fimage').val();
    if (currentVal && currentVal.indexOf(',') !== -1) {
        selectedCovers = currentVal.split(',');
        // Déduire le maxCovers selon le nombre initial
        if (selectedCovers.length > 16) maxCovers = 25;
        else if (selectedCovers.length > 9) maxCovers = 16;
        else if (selectedCovers.length > 4) maxCovers = 9;
        else maxCovers = 4;

        $('#mosaic-grid-selector button').removeClass('active');
        $('#mosaic-grid-selector button[data-grid="' + maxCovers + '"]').addClass('active');
        $('#mosaic-max').text(maxCovers);

        $('.mosaic-thumbnail').each(function () {
            if (selectedCovers.indexOf($(this).data('url')) > -1) {
                $(this).addClass('selected-cover');
            }
        });
        $('#mosaic-count').text(selectedCovers.length);
        updateGenerateButtonState();
    }

    // Sélecteur de Grille NxN
    $('#mosaic-grid-selector button').on('click', function () {
        $('#mosaic-grid-selector button').removeClass('active');
        $(this).addClass('active');
        maxCovers = parseInt($(this).data('grid'), 10) || 4;
        $('#mosaic-max').text(maxCovers);

        // Tronquer si la sélection actuelle dépasse le nouveau format
        if (selectedCovers.length > maxCovers) {
            selectedCovers = selectedCovers.slice(0, maxCovers);
            $('.mosaic-thumbnail').each(function () {
                if (selectedCovers.indexOf($(this).data('url')) === -1) {
                    $(this).removeClass('selected-cover');
                }
            });
            $('#fimage').val(selectedCovers.join(','));
            $('#mosaic-count').text(selectedCovers.length);
        }
        updateGenerateButtonState();
    });

    // Remplissage automatique rapide
    $('#btnAutoFillMosaic').on('click', function () {
        selectedCovers = [];
        $('.mosaic-thumbnail').removeClass('selected-cover');

        var $thumbnails = $('.mosaic-thumbnail');
        if ($thumbnails.length === 0) {
            if (typeof toastr !== 'undefined') toastr.info('Aucune pochette disponible pour cette playlist.');
            return;
        }

        var toSelect = $thumbnails.slice(0, maxCovers);
        toSelect.each(function () {
            var url = $(this).data('url');
            selectedCovers.push(url);
            $(this).addClass('selected-cover');
        });

        $('#fimage').val(selectedCovers.join(','));
        $('#mosaic-count').text(selectedCovers.length);
        updateGenerateButtonState();

        if (typeof toastr !== 'undefined') {
            toastr.success(selectedCovers.length + ' pochettes sélectionnées automatiquement.');
        }
    });

    $('.mosaic-thumbnail').on('click', function () {
        var url = $(this).data('url');
        var index = selectedCovers.indexOf(url);
        if (index > -1) {
            selectedCovers.splice(index, 1);
            $(this).removeClass('selected-cover');
        } else {
            if (selectedCovers.length < maxCovers) {
                selectedCovers.push(url);
                $(this).addClass('selected-cover');
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Maximum ' + maxCovers + ' pochettes pour le format ' + Math.round(Math.sqrt(maxCovers)) + '×' + Math.round(Math.sqrt(maxCovers)) + '.');
                }
            }
        }
        $('#fimage').val(selectedCovers.join(','));
        $('#mosaic-count').text(selectedCovers.length);
        updateGenerateButtonState();
    });

    $('#btnClearMosaic').on('click', function () {
        selectedCovers = [];
        $('.mosaic-thumbnail').removeClass('selected-cover');
        $('#fimage').val('mosaique-automatique');
        $('#mosaic-count').text(0);
        updateGenerateButtonState();
        if (typeof toastr !== 'undefined') {
            toastr.info('Mosaïque réinitialisée en mode automatique.');
        }
    });

    // Génération AJAX immédiate de la mosaïque avec aperçu en temps réel
    $('#btnGenerateMosaic').on('click', function () {
        if (selectedCovers.length === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('Veuillez sélectionner au moins 1 pochette.');
            }
            return;
        }

        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="glyphicon glyphicon-refresh spin"></i> Génération en cours...');

        var playlistId = parseInt($('#playlist_id').val() || '0', 10);
        var playlistNom = $('#fnom').val() || 'Playlist';
        var colorOrder = $('#mosaic-color-order').val() || 'manual';

        $.ajax({
            url: 'playlist_mosaic_ajax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                id: playlistId,
                nom: playlistNom,
                covers: selectedCovers,
                grid_size: maxCovers,
                color_order: colorOrder
            },
            success: function (res) {
                $btn.prop('disabled', false).html(originalHtml);
                if (res.success) {
                    $('#fimage').val(res.fileName);
                    $('#playlist-current-cover-img').attr('src', res.imageUrl).removeClass('playlist-dynamic-hidden').show();
                    $('#playlist-cover-placeholder-box').addClass('playlist-dynamic-hidden').hide();
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Pochette générée avec succès !');
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.error || 'Erreur lors de la génération.');
                    } else {
                        alert(res.error || 'Erreur lors de la génération.');
                    }
                }
            },
            error: function () {
                $btn.prop('disabled', false).html(originalHtml);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Erreur technique lors de la communication serveur.');
                } else {
                    alert('Erreur technique lors de la génération.');
                }
            }
        });
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
