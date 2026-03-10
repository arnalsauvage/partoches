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
