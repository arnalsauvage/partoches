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

    // --- 3. GESTION DU VISUEL & MOSAÏQUE (US-084: NxN, Harmonisation Chromatique & Aperçu Avant Validation) ---
    var selectedCovers = [];
    var maxCovers = 4;
    var currentPreviewFile = null;

    function updateGenerateButtonState() {
        var n = Math.round(Math.sqrt(maxCovers));
        if (selectedCovers.length > 0) {
            $('#btnGenerateMosaic').prop('disabled', false).html(
                '<i class="glyphicon glyphicon-eye-open"></i> Prévisualiser la mosaïque ' + n + '×' + n + ' (' + selectedCovers.length + ' / ' + maxCovers + ')'
            );
        } else {
            $('#btnGenerateMosaic').prop('disabled', true).html(
                '<i class="glyphicon glyphicon-eye-open"></i> Prévisualiser la mosaïque avec ces images'
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

    // 1. Génération de l'Aperçu (sans écraser la couverture actuelle ni altérer la BDD)
    $('#btnGenerateMosaic').on('click', function () {
        if (selectedCovers.length === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('Veuillez sélectionner au moins 1 pochette.');
            }
            return;
        }

        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="glyphicon glyphicon-refresh spin"></i> Génération de l\'aperçu...');

        var playlistId = parseInt($('#playlist_id').val() || '0', 10);
        var playlistNom = $('#fnom').val() || 'Playlist';
        var colorOrder = $('#mosaic-color-order').val() || 'manual';

        $.ajax({
            url: 'playlist_mosaic_ajax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'preview',
                id: playlistId,
                nom: playlistNom,
                covers: selectedCovers,
                grid_size: maxCovers,
                color_order: colorOrder
            },
            success: function (res) {
                $btn.prop('disabled', false).html(originalHtml);
                if (res.success) {
                    currentPreviewFile = res.fileName;
                    $('#playlist-preview-img').attr('src', res.imageUrl);
                    $('#playlist-preview-box').removeClass('playlist-dynamic-hidden').hide().slideDown();
                    if (typeof toastr !== 'undefined') {
                        toastr.info(res.message || 'Aperçu généré ! Validez-le si vous êtes satisfait.');
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.error || 'Erreur lors de la génération.');
                    } else {
                        alert(res.error || 'Erreur lors de la génération.');
                    }
                }
            },
            error: function (xhr, status, error) {
                $btn.prop('disabled', false).html(originalHtml);
                console.error('Erreur AJAX Prévisualisation :', status, error, xhr.responseText);
                var errMsg = 'Erreur technique lors de la communication serveur.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errMsg = xhr.responseJSON.error;
                } else if (xhr.status === 403 || xhr.status === 401) {
                    errMsg = 'Session expirée ou privilèges insuffisants.';
                } else if (xhr.status === 404) {
                    errMsg = 'Point de terminaison AJAX introuvable (404).';
                } else if (xhr.responseText && xhr.responseText.length > 0 && xhr.responseText.length < 300) {
                    errMsg += ' (' + xhr.responseText.replace(/<[^>]*>?/gm, '').trim() + ')';
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(errMsg);
                } else {
                    alert(errMsg);
                }
            }
        });
    });

    // 2. Validation explicite de l'aperçu (officialisation en BDD & mise à jour illustration)
    $('#btnValidateMosaic').on('click', function () {
        if (!currentPreviewFile) return;

        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="glyphicon glyphicon-refresh spin"></i> Enregistrement...');

        var playlistId = parseInt($('#playlist_id').val() || '0', 10);
        var playlistNom = $('#fnom').val() || 'Playlist';

        $.ajax({
            url: 'playlist_mosaic_ajax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'apply',
                preview_file: currentPreviewFile,
                id: playlistId,
                nom: playlistNom
            },
            success: function (res) {
                $btn.prop('disabled', false).html(originalHtml);
                if (res.success) {
                    $('#fimage').val(res.fileName);
                    $('#playlist-current-cover-img').attr('src', res.imageUrl).removeClass('playlist-dynamic-hidden').show();
                    $('#playlist-cover-placeholder-box').addClass('playlist-dynamic-hidden').hide();
                    $('#playlist-preview-box').slideUp();
                    currentPreviewFile = null;
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Pochette validée et enregistrée avec succès !');
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.error || 'Erreur lors de la validation.');
                    } else {
                        alert(res.error || 'Erreur.');
                    }
                }
            },
            error: function (xhr, status, error) {
                $btn.prop('disabled', false).html(originalHtml);
                console.error('Erreur AJAX Validation :', status, error, xhr.responseText);
                var errMsg = 'Erreur technique lors de la validation.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errMsg = xhr.responseJSON.error;
                } else if (xhr.status === 403 || xhr.status === 401) {
                    errMsg = 'Session expirée ou privilèges insuffisants.';
                } else if (xhr.status === 404) {
                    errMsg = 'Point de terminaison AJAX introuvable (404).';
                } else if (xhr.responseText && xhr.responseText.length > 0 && xhr.responseText.length < 300) {
                    errMsg += ' (' + xhr.responseText.replace(/<[^>]*>?/gm, '').trim() + ')';
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(errMsg);
                } else {
                    alert(errMsg);
                }
            }
        });
    });

    // 3. Rejet / Annulation de l'aperçu
    $('#btnCancelPreview').on('click', function () {
        if (currentPreviewFile) {
            $.ajax({
                url: 'playlist_mosaic_ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'cancel',
                    preview_file: currentPreviewFile
                }
            });
            currentPreviewFile = null;
        }
        $('#playlist-preview-box').slideUp();
        if (typeof toastr !== 'undefined') {
            toastr.info("Aperçu rejeté. L'illustration précédente est conservée.");
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
