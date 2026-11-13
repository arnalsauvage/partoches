/**
 * Logique JS pour la modale de duplication d'une chanson (Django Style)
 */

function ouvrirModalDupliquer(idChanson) {
    var $modal = $('#modalDupliquerChanson');
    var $body = $('#modalDupliquerBody');
    $body.html('<div class="text-center modal-duplication-loading"><i class="glyphicon glyphicon-refresh spin"></i> Chargement des options...</div>');
    $modal.modal('show');

    $.ajax({
        url: 'chanson_dupliquer_modal_ajax.php',
        type: 'GET',
        data: { id: idChanson },
        success: function(html) {
            $body.html(html);
        },
        error: function(xhr) {
            var msg = (xhr.status === 403) ? 'Accès réservé aux administrateurs.' : 'Erreur lors du chargement des options de duplication.';
            $body.html('<div class="alert alert-danger">' + msg + '</div>');
        }
    });
}

function cocherTous(classeCheck, etat) {
    $('.' + classeCheck).prop('checked', etat);
}
