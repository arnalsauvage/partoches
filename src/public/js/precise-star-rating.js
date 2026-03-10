// source : http://www.tipocode.com/jquery/very-precise-jquery-ajax-star-rating-plugin-tutorial/
const NOTE_AJAX_ENREGISTRE_PHP = '../note/noteAjaxEnregistre.php';
const CHEMIN_LOADER = "../../images/icones/loader-small.gif";

function rateMedia(mediaName, mediaId, rate, numStar, starWidth) {
    // $('#' + mediaId + ' .star_bar #' + rate).removeAttr('onclick'); // Supprime attribut onclick : évite le multi-clic
    $('.box' + mediaId).html('<img src='+CHEMIN_LOADER+' alt="" />'); // Affiche icône chargement
    let data = {mediaName: mediaName, mediaId: mediaId, rate: rate}; // Crée le JSon qui sera envoyé par ajax
    $.ajax({ // JQuery Ajax
        type: 'POST',
        url: NOTE_AJAX_ENREGISTRE_PHP, // fichier php à appeler pour gérer le clic engendrant une notation
        data: data, // envoi des données
        dataType: 'json',
        timeout: 3000,
        success: function(data) {
            // Remercie pour le vote
            console.log("Merci!");
            $('.box' + mediaId).html('<div style="font-size: x-small; color: green">Merci pour ce vote (' + rate + ' / ' +numStar + ')</div>');
            // On met à jour la note et le nombre de votes
            $('.resultMedia' + mediaId).html('<div style="font-size: small; color: grey"> Moy.'+ data.avg + '/' + numStar + ' (' + data.nbrRate + ' votes)</div>');
            // On recalcule quelle portion doit être affichée en surbrillance selon le score,
            const nbrPixelsInDiv = numStar * starWidth;
            const numEnlightedPX = Math.round(nbrPixelsInDiv * data.avg / numStar);
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
    // console.log('Overstar sur #chanson_'+mediaId );
    for ( let i = 1; i <= numStar; i++ ) {
        if (i <= myRate) $('#chanson_'+mediaId + '_' + i).attr('class', 'star_hover');
        else $('#chanson_'+mediaId + '_' + i).attr('class', 'star');
    }
}

// Souris quitte une étoile, on redessine les étoiles de 1 à étoile survolée
function outStar(mediaId, myRate, numStar) {
    for ( let i = 1; i <= numStar; i++ ) {
        $('#chanson_'+mediaId + '_' + i).attr('class', 'star');
    }
}