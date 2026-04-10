/**
 * Logique JavaScript pour le formulaire des Songbooks
 */
$(document).ready(function () {
    const $idSongbook = $('#idSongbook').val();

    // Cache le bouton d'envoi classique du formulaire de recherche de document
    $("#valider").hide();
    
    $(document).on("change", "input[type='radio']", function() {
        if ($("input[type='radio']:checked").length > 0) {
            $("#valider").show();
        }
    });

    // Drag'n drop pour l'ordre des morceaux
    if ($('#sortable').length > 0) {
        $('#sortable').sortable({
            axis: 'y',
            update: function (event, ui) {
                let positions = [];
                $(this).children().each(function () {
                    positions.push([$(this).attr('data-index'), $(this).attr('data-position')]);
                });
                $.ajax({
                    data: { idSongbook: $idSongbook, positions: positions },
                    type: 'POST',
                    url: 'traiteOrdre.php',
                    success: function() { 
                        if (typeof toastr !== 'undefined') toastr.success("Ordre mis à jour !"); 
                    }
                });
            }
        });
    }

    // --- SÉCURITÉ MODALES (Django Style) ---
    // Force la suppression du voile sombre quand on ferme une modale
    $(document).on('click', '[data-dismiss="modal"]', function() {
        console.log("BOUTON FERMER CLIQUÉ : Nettoyage forcé du voile sombre...");
        $('#modalPdfReport').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });

    // Fonction globale pour la génération PDF
    window.genereUnPdf = function(id) {
        if (typeof toastr !== 'undefined') toastr.info("Génération du PDF en cours...");
        
        let $modal = $('#modalPdfReport');
        let $reportBody = $('#pdf-report-body');
        
        $reportBody.html('<div class="alert alert-info"><span class="glyphicon glyphicon-refresh spin"></span> Travail en cours, veuillez patienter (cela peut prendre 30s)...</div>');
        $modal.modal('show');
        
        console.log("Démarrage AJAX pour songbook ID:", id);
        $.ajax({
            type: "POST",
            url: "songbook_get.php",
            data: { id: id, mode: "GENEREPDF" },
            success: function (response) {
                console.log("RETOUR SERVEUR BRUT :", response);
                let data = null;
                
                if (typeof response === 'object') {
                    console.log("La réponse est déjà un objet JSON.");
                    data = response;
                } else {
                    console.log("La réponse est une chaîne, tentative de parsing...");
                    try {
                        data = JSON.parse(response);
                    } catch (e) {
                        console.warn("Échec du parsing JSON direct, tentative de nettoyage...");
                        try {
                            let jsonStart = response.indexOf('{');
                            let jsonEnd = response.lastIndexOf('}');
                            if (jsonStart !== -1 && jsonEnd !== -1) {
                                let cleaned = response.substring(jsonStart, jsonEnd + 1);
                                console.log("JSON nettoyé trouvé :", cleaned);
                                data = JSON.parse(cleaned);
                            }
                        } catch (e2) {
                            console.error("Échec définitif du parsing JSON", e2);
                        }
                    }
                }

                if (data && data.success) {
                    const hasWarnings = data.has_warnings || (data.skipped && data.skipped.length > 0);
                    const alertClass = hasWarnings ? 'alert-warning' : 'alert-success';
                    const iconClass = hasWarnings ? 'glyphicon-warning-sign' : 'glyphicon-ok';
                    const titleText = hasWarnings ? 'Génération avec avertissements' : 'PDF généré avec succès !';

                    if (typeof toastr !== 'undefined') {
                        if (hasWarnings) toastr.warning("Génération terminée avec des morceaux manquants.");
                        else toastr.success("Génération réussie !");
                    }
                    
                    let html = '<div class="alert ' + alertClass + '">';
                    html += '<h4><i class="glyphicon ' + iconClass + '"></i> ' + titleText + '</h4>';
                    html += '<p>Le fichier <strong>' + data.file + '</strong> est prêt.</p>';
                    
                    if (data.skipped && data.skipped.length > 0) {
                        html += '<hr><p><strong>Attention :</strong> ' + data.skipped.length + ' morceau(x) ont été ignorés (incompatibles ou introuvables) :</p><ul class="small">';
                        data.skipped.forEach(s => html += '<li>' + s + '</li>');
                        html += '</ul>';
                    }
                    html += '</div>';
                    $reportBody.html(html);
                } else {
                    let html = '<div class="alert alert-danger"><h4>Échec de la génération</h4>';
                    if (data && data.errors && data.errors.length > 0) {
                        html += '<ul>' + data.errors.map(e => '<li>'+e+'</li>').join('') + '</ul>';
                    } else {
                        html += '<p>Le serveur a renvoyé une réponse invalide ou incomplète.</p>';
                        if (typeof response === 'string' && response.length > 0) {
                            html += '<hr><p>Réponse brute :</p><pre style="max-height:200px; overflow:auto;">' + response.substring(0, 500) + '</pre>';
                        }
                    }
                    html += '</div>';
                    $reportBody.html(html);
                }
            },
            error: function(xhr, status, error) {
                let html = '<div class="alert alert-danger">';
                html += '<h4><i class="glyphicon glyphicon-fire"></i> Erreur Serveur (' + xhr.status + ')</h4>';
                html += '<p>La génération a échoué du côté du serveur.</p>';
                if (xhr.responseText) {
                    html += '<hr><p>Détail technique :</p><pre style="max-height:200px; overflow:auto;">' + xhr.responseText.substring(0, 500) + '</pre>';
                }
                html += '</div>';
                $reportBody.html(html);
            }
        });
    };

    // --- RECHERCHE DYNAMIQUE DE CHANSONS ---
    let $inputRecherche = $('#rechercheChansonSB');
    let $resultsDiv = $('#resultsChansonSB');
    let $selectionPdf = $('#selectionPdfSB');
    let $listePdfs = $('#listePdfsSB');

    $inputRecherche.on('keyup', function() {
        let val = $(this).val();
        if (val.length >= 4) {
            $.ajax({
                url: '../chanson/chanson_recherche_ajax.php',
                data: { q: val },
                success: function(chansons) {
                    $resultsDiv.empty();
                    if (chansons.length > 0) {
                        chansons.forEach(function(c) {
                            $('<div class="list-group-item list-group-item-action" style="cursor:pointer;">')
                                .html('<strong>' + c.nom + '</strong> <small class="text-muted">(' + c.interprete + ')</small>')
                                .on('click', function() {
                                    chargerPdfsChanson(c.id, c.nom);
                                })
                                .appendTo($resultsDiv);
                        });
                        $resultsDiv.show();
                    } else {
                        $resultsDiv.html('<div class="list-group-item text-muted">Aucune chanson trouvée.</div>').show();
                    }
                }
            });
        } else {
            $resultsDiv.hide();
        }
    });

    function chargerPdfsChanson(chansonId, chansonNom) {
        $resultsDiv.hide();
        $inputRecherche.val(chansonNom);
        $.ajax({
            url: '../document/document_recherche_ajax.php',
            data: { chansonId: chansonId },
            success: function(docs) {
                $listePdfs.empty();
                if (docs.length > 0) {
                    docs.forEach(function(d) {
                        $('<button type="button" class="list-group-item list-group-item-action">')
                            .html('<i class="glyphicon glyphicon-file"></i> ' + d.nomVersion)
                            .on('click', function() {
                                ajouterDocAuSongbook(d.id);
                            })
                            .appendTo($listePdfs);
                    });
                    $selectionPdf.slideDown();
                } else {
                    if (typeof toastr !== 'undefined') toastr.warning("Aucun document PDF trouvé pour cette chanson.");
                    $selectionPdf.hide();
                }
            }
        });
    }

    function ajouterDocAuSongbook(docId) {
        $('#inputDocFinal').val(docId);
        $('#formFinalAjout').submit();
    }

    // Fermer les résultats si on clique ailleurs
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#resultsChansonSB, #rechercheChansonSB').length) {
            $resultsDiv.hide();
        }
    });
});
