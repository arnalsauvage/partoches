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

    // Fonction globale pour la génération PDF
    window.genereUnPdf = function(id) {
        if (typeof toastr !== 'undefined') toastr.info("Génération du PDF en cours...");
        
        // On prépare une zone pour les messages détaillés si elle n'existe pas
        if ($('#pdf-report-zone').length === 0) {
            $('.sb-footer-actions').before('<div id="pdf-report-zone" style="margin-bottom:20px;"></div>');
        }
        $('#pdf-report-zone').empty().html('<div class="alert alert-info"><span class="glyphicon glyphicon-refresh spin"></span> Travail en cours, veuillez patienter...</div>');
        
        $.ajax({
            type: "POST",
            url: "songbook_get.php",
            data: { id: id, mode: "GENEREPDF" },
            success: function (response) {
                let data = response;
                
                // Si la réponse n'est pas un objet (ex: JSON corrompu par un warning)
                if (typeof response !== 'object') {
                    try {
                        // On tente de trouver le JSON dans la masse de texte si besoin
                        let jsonStart = response.indexOf('{');
                        let jsonEnd = response.lastIndexOf('}');
                        if (jsonStart !== -1 && jsonEnd !== -1) {
                            data = JSON.parse(response.substring(jsonStart, jsonEnd + 1));
                        }
                    } catch (e) {
                        console.error("Impossible de parser la réponse", e);
                    }
                }

                if (data && data.success) {
                    if (typeof toastr !== 'undefined') toastr.success("PDF régénéré !");
                    
                    let html = '<div class="alert alert-success">';
                    html += '<h4><i class="glyphicon glyphicon-ok"></i> Génération réussie !</h4>';
                    html += '<p>Le fichier <strong>' + data.file + '</strong> a été créé.</p>';
                    
                    if (data.skipped && data.skipped.length > 0) {
                        html += '<hr><p><strong>Attention :</strong> Certains morceaux ont été ignorés (PDF incompatibles ou manquants) :</p><ul>';
                        data.skipped.forEach(s => html += '<li>' + s + '</li>');
                        html += '</ul><p><small>Astuce : Enregistrez ces PDF au format "PDF 1.4" ou "Optimisé" pour les rendre compatibles.</small></p>';
                    }
                    html += '</div>';
                    $('#pdf-report-zone').html(html);
                } else {
                    let html = '<div class="alert alert-danger"><h4>Échec de la génération</h4>';
                    if (data && data.errors) {
                        html += '<ul>' + data.errors.map(e => '<li>'+e+'</li>').join('') + '</ul>';
                    } else {
                        html += '<p>Une erreur inconnue est survenue.</p>';
                    }
                    html += '</div>';
                    $('#pdf-report-zone').html(html);
                }
            },
            error: function(xhr, status, error) {
                let html = '<div class="alert alert-danger">';
                html += '<h4><i class="glyphicon glyphicon-fire"></i> Erreur Serveur (' + xhr.status + ')</h4>';
                html += '<p>La génération a échoué du côté du serveur.</p>';
                if (xhr.responseText) {
                    html += '<hr><p>Détail technique :</p><pre style="max-height:150px; overflow:auto;">' + xhr.responseText.substring(0, 500) + '</pre>';
                }
                html += '</div>';
                $('#pdf-report-zone').html(html);
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
