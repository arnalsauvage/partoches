// source : http://www.tipocode.com/jquery/very-precise-jquery-ajax-star-rating-plugin-tutorial/

function rateMedia(mediaName, mediaId, rate, numStar, starWidth) {
    // $('#' + mediaId + ' .star_bar #' + rate).removeAttr('onclick'); // Supprime attribut onclick : évite le mulri-clic
    $('.box' + mediaId).html('<img src="../images/icones/loader-small.gif" alt="" />'); // Affiche icône chargement
    var data = {mediaName: mediaName, mediaId: mediaId, rate: rate}; // Crée le JSon qui sera envoyé par ajax
    $.ajax({ // JQuery Ajax
        type: 'POST',
        url: 'noteAjaxEnregistre.php', // fichier php à appeler pour gérer le clic engendrant une notation
        data: data, // envoi des données
        dataType: 'json',
        timeout: 3000,
        success: function(data) {
            // Remercie pour le vote
            $('.box' + mediaId).html('<div style="font-size: x-small; color: green">Merci pour ce vote (' + rate + ' / ' +numStar + ')</div>');
            // On met à jour la note et le nombre de votes
            $('.resultMedia' + mediaId).html('<div style="font-size: small; color: grey"> Moy.'+ data.avg + '/' + numStar + ' (' + data.nbrRate + ' votes)</div>');
            // On recalcule quelle portion doit être affichée en surbrillance selon le score,
            var nbrPixelsInDiv = numStar * starWidth;
            var numEnlightedPX = Math.round(nbrPixelsInDiv * data.avg / numStar);
            $('#' + mediaId + ' .star_bar').attr('style', 'width:' + nbrPixelsInDiv + 'px; height:' + starWidth +
                'px; background: linear-gradient(to right, #ffc600 0%,#ffc600 ' + numEnlightedPX + 'px,#ccc ' +
                numEnlightedPX + 'px,#ccc 100%);');
            /*  Supprimé car on ne veut pas empêcher de revoter
            $.each($('#' + mediaId + ' .star_bar > div'), function () {
                             $(this).removeAttr('onmouseover onclick');
            });
            */
        },
        error: function() {
            $('#box').text('Problem');
        }
    });
}

// Souris passe sur une étoile : on dessine en bleu les étoiles de 1 à étoile survolée
function overStar(mediaId, myRate, numStar) {
    for ( var i = 1; i <= numStar; i++ ) {
        if (i <= myRate) $('#' + mediaId + ' .star_bar #' + i).attr('class', 'star_hover');
        else $('#' + mediaId + ' .star_bar #' + i).attr('class', 'star');
    }
}

// Souris quitte une étoile, on redessine les étoiles de 1 à étoile survolée
function outStar(mediaId, myRate, numStar) {
    for ( var i = 1; i <= numStar; i++ ) {
        $('#' + mediaId + ' .star_bar #' + i).attr('class', 'star');
    }
}