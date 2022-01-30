// Clic sur un titre active le renommage

$("label").click(function () {
    $('h1').css('background', getRandomColor());
    $(this).parent().find("button").show("slow");
    $(this).hide();
    $(this).parent().find("input").show("slow");
});

$("button[name='creer']").click(function () {
    $('h2').css('background', getRandomColor());

    id = document.getElementById('id').value;
    strum = document.getElementById('strum').value;
    unite = document.getElementById('unite').value;
    longueur = document.getElementById('longueur').value;
    description = document.getElementById('description').value;

    // pour debug alert("id : " + id + " strum : " + strum + ' unite: ' + unite + "longueur: " + longueur);
    $.ajax({
        url: "strum_post.php",
        type: "POST",
        data: {mode: "NEW", id: id , strum: strum, description : description, unite : unite, longueur : longueur},
        contentType: 'application/x-www-form-urlencoded',
        datatype: 'json', // type de la donnée à recevoir
        success: function (code_html, statut) { // success est toujours en place, bien sûr !
            // pour debug alert("Code retourné : " + code_html);
            if (code_html.search("n'a pas été traité.") == -1)
                toastr.success("Le strum a été ajouté ! <br> Le strum est enregistré");
            else {
                toastr.warning("Erreur dans l'opération...<br>Le strum n'a pas pu être enregistré...");
                $("#retour").html(code_html);
            }
        },
        error: function (resultat, statut, erreur) {
            $("#retour").html(resultat);
        }
    });
//  toastr.success("Le fichier a été renommé ! <br> Un nouveau pdf a été rajouté aux fichiers du songbook. <br> Vous pouvez raffraîchir la page pour le voir.");
});

$("button[name='modifier']").click(function () {
    $('h2').css('background', getRandomColor());

    id = document.getElementById('id').value;
    strum = document.getElementById('strum').value;
    unite = document.getElementById('unite').value;
    longueur = document.getElementById('longueur').value;
    description = document.getElementById('description').value;

    // pour debug alert("id : " + id + " strum : " + strum + ' unite: ' + unite + "longueur: " + longueur);
    $.ajax({
        url: "strum_post.php",
        type: "POST",
        data: {mode: "UPDATE", id: id , strum: strum, description : description, unite : unite, longueur : longueur},
        contentType: 'application/x-www-form-urlencoded',
        datatype: 'json', // type de la donnée à recevoir
        success: function (code_html, statut) { // success est toujours en place, bien sûr !
            // pour debug alert("Code retourné : " + code_html);
            if (code_html.search("n'a pas été traité.") == -1){
                toastr.success("Le strum a été modifié ! <br> Le strum est enregistré");
                $("#retour").html(code_html);
            }
            else {
                toastr.warning("Erreur dans l'opération...<br>Le strum n'a pas pu être enregistré...");
                $("#retour").html(code_html);
            }
        },
        error: function (resultat, statut, erreur) {
            $("#retour").html(resultat);
        }
    });
//  toastr.success("Le fichier a été renommé ! <br> Un nouveau pdf a été rajouté aux fichiers du songbook. <br> Vous pouvez raffraîchir la page pour le voir.");
});

// Pour le débogage, un changement de couleur
function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function setRandomColor() {
    $("#colorpad").css("background-color", getRandomColor());
}