/**
 * Logique JavaScript pour le formulaire de création/édition des strums
 */
$(document).ready(function () {
    
    // Boutons d'action
    const $btnCreer = $('button[name="creer"]');
    const $btnModifier = $('button[name="modifier"]');

    function envoyerStrum(mode) {
        const donnees = {
            id: $('#id').val(),
            unite: $('#unite').val(),
            longueur: $('#longueur').val(),
            strum: $('#strum').val(),
            description: $('#description').val(),
            swing: $('#swing').is(':checked') ? 1 : 0,
            mode: mode
        };

        if (donnees.strum === "") {
            toastr.error("Le motif du strum ne peut pas être vide !");
            return;
        }

        toastr.info("Enregistrement en cours...");

        $.ajax({
            url: 'strum_post.php',
            type: 'POST',
            data: donnees,
            success: function (response) {
                if (response.indexOf("Erreur") === -1) {
                    toastr.success("Strum enregistré avec succès !");
                    setTimeout(() => window.location.href = 'strum_liste.php', 1000);
                } else {
                    toastr.error("Erreur lors de l'enregistrement.");
                    $('#retour').html(response).show();
                }
            },
            error: function () {
                toastr.error("Erreur de connexion au serveur.");
            }
        });
    }

    $btnCreer.on('click', () => envoyerStrum('INS'));
    $btnModifier.on('click', () => envoyerStrum('MAJ'));

    // Aide toggle
    $('#btnAide').on('click', function() {
        $('#aideBox').slideToggle();
    });
});
