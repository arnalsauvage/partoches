/**
 * Logique JavaScript pour la liste des strums (Django Style)
 */

$(document).ready(function() {
    // Initialisation des tooltips Bootstrap si présents
    if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
});

/**
 * Ouvre la modale pour voir les chansons liées à un strum
 */
function voirChansonsStrum(idStrum, nomStrum) {
    const $modal = $('#modalChansonsStrum');
    const $title = $('#modalStrumNom');
    const $body = $('#modalChansonsBody');

    // Mise à jour de l'interface
    $title.html('<code style="background:#eee; color:#e67e22; padding: 2px 6px;">' + nomStrum + '</code>');
    $body.html('<div class="text-center" style="padding:20px;"><i class="glyphicon glyphicon-refresh spin"></i> Chargement des chansons...</div>');
    
    // Affichage de la modale
    $modal.modal('show');

    // Appel AJAX pour récupérer la liste
    $.ajax({
        url: 'chansons_par_strum_ajax.php',
        type: 'GET',
        data: { idStrum: idStrum },
        success: function(html) {
            $body.html(html);
        },
        error: function() {
            $body.html('<div class="alert alert-danger">Erreur lors du chargement des chansons.</div>');
        }
    });
}

// --- LOGIQUE D'ÉDITION (Ancien code conservé et nettoyé) ---

$("button[name='creer']").click(function () {
    const id = $('#id').val();
    const strum = $('#strum').val();
    const unite = $('#unite').val();
    const longueur = $('#longueur').val();
    const description = $('#description').val();
    const swing = $('#swing').is(':checked') ? 1 : 0;

    $.ajax({
        url: "strum_post.php",
        type: "POST",
        data: {
            mode: "NEW", 
            id: id, 
            strum: strum, 
            description: description, 
            unite: unite, 
            longueur: longueur,
            swing: swing
        },
        success: function (response) {
            if (response.indexOf("ok") !== -1) {
                toastr.success("Le strum a été ajouté !");
                setTimeout(() => window.location.href = 'strum_liste.php', 1000);
            } else {
                toastr.warning("Erreur : " + response);
                $("#retour").show().html(response);
            }
        },
        error: function (xhr, status, error) {
            toastr.error("Erreur réseau : " + error);
        }
    });
});

$("button[name='modifier']").click(function () {
    const id = $('#id').val();
    const strum = $('#strum').val();
    const unite = $('#unite').val();
    const longueur = $('#longueur').val();
    const description = $('#description').val();
    const swing = $('#swing').is(':checked') ? 1 : 0;

    $.ajax({
        url: "strum_post.php",
        type: "POST",
        data: {
            mode: "UPDATE", 
            id: id, 
            strum: strum, 
            description: description, 
            unite: unite, 
            longueur: longueur,
            swing: swing
        },
        success: function (response) {
            if (response.indexOf("ok") !== -1){
                toastr.success("Le strum a été modifié !");
                setTimeout(() => window.location.href = 'strum_liste.php', 1000);
            } else {
                toastr.warning("Erreur : " + response);
                $("#retour").show().html(response);
            }
        },
        error: function (xhr, status, error) {
            toastr.error("Erreur réseau : " + error);
        }
    });
});

// Pour le débogage, un changement de couleur (Optionnel)
function getRandomColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}
