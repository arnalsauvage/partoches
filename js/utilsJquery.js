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
            $('.contenu_popup').slideFadeToggle();
        }
        return false;
    });
    //Fonction appelée lorsque l'on clique sur le lien Fermer la fenêtre
    $('.close').on('click', function () {
        deselect($('#afficherPopup'));
        return false;
    });
});
