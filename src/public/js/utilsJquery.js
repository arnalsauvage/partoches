$(function () {

    //Fonction d'animation de la fenêtre. Elle permet d'afficher ou de masquer la fenêtre
    $.fn.slideFadeToggle = function (easing, callback) {
        return this.animate({opacity: 'toggle', height: 'toggle'}, 'slow', easing, callback);
    };

    //Fonction utilisée pour fermer la popup et enlever la classe selected sur le lien
    function deselect(e) {
        $('.contenu_popup').slideFadeToggle(function () {
            e.removeClass('selected');
        });
    }

    //Fonction appelée lorsque l'on clique sur le lien Afficher la fenêtre
    $('#afficherPopup').on('click', function () {
        if ($(this).hasClass('selected')) {
            deselect($(this));
        } else {
            $(this).addClass('selected');
            $('.contenu_popup').slideFadeToggle(function() {
                $('#login').focus();
            });
        }
        return false;
    });
    //Fonction appelée lorsque l'on clique sur le lien Fermer la fenêtre
    $(document).on('click', '.btn-fermer-popup', function () {
        deselect($('#afficherPopup'));
        return false;
    });

    // --- Gestion de la largeur de fenêtre (Ajax) ---
    let windowWidth = window.innerWidth;
    $(window).on('resize', function() {
        // On attend la fin du redimensionnement pour envoyer l'ajax (debounce)
        clearTimeout(window.resizeTimer);
        window.resizeTimer = setTimeout(function() {
            if (window.innerWidth !== windowWidth) {
                windowWidth = window.innerWidth;
                $.post('../lib/ajaxappli.php', { largeur_fenetre: windowWidth });
            }
        }, 500);
    });

    // --- Configuration globale Toastr ---
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "positionClass": "toast-top-center",
            "closeButton": true,
            "progressBar": true,
            "timeOut": "5000"
        };
    }
});

/**
 * Fonction de confirmation de suppression moderne (Django Style)
 * Remplace l'ancienne fonction du fichier javascript.js
 * @param {string} url - L'URL de redirection en cas de succès
 * @param {string} message - Le message à afficher
 */
function confirmeSuppr(url, message) {
    const $modal = $('#modalConfirmation');
    if ($modal.length) {
        $('#modalConfirmationMessage').text(message || 'Voulez-vous vraiment supprimer cet élément ?');
        $('#btnConfirmAction').off('click').on('click', function() {
            window.location.href = url;
        });
        $modal.modal('show');
    } else {
        // Fallback si la modale n'est pas dans le DOM
        if (confirm(message || 'Voulez-vous vraiment supprimer cet élément ?')) {
            window.location.href = url;
        }
    }
}

/**
 * Change la vignette affichée dans un formulaire (hérité de javascript.js)
 */
function changeListeImage(formulaire) {
    const l1 = formulaire.elements["listeImage"];
    if (l1) {
        const image = l1.options[l1.selectedIndex].value;
        const $vignette = $('#vignette');
        if ($vignette.length) {
            $vignette.attr('src', "../../data/vignettes/" + image);
        }
    }
}

/**
 * Met à jour la liste des images via AJAX (hérité de javascript.js)
 */
function miseAjourListeImages(scriptPhpListeImages) {
    $.get(scriptPhpListeImages, function(data) {
        if (typeof toastr !== 'undefined') {
            toastr.info("Nouvelle liste reçue du serveur.");
        } else {
            alert("Nouvelle liste reçue du serveur.");
        }
        // Attention : eval est dangereux mais on garde la compatibilité pour l'instant
        eval(data);
    }).fail(function() {
        if (typeof toastr !== 'undefined') {
            toastr.error("Erreur lors de la mise à jour de la liste.");
        }
    });
}
