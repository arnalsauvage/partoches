// Clic sur un titre active le renommage

$("label.doc").click(function () {
    $('h1').css('background', getRandomColor());
    $(this).parent().find("button").show("slow");
    $(this).hide();
    $(this).parent().find("input").show("slow");
});

$("button[name='renommer']").click(function () {
    $('h2').css('background', getRandomColor());
    let nouveauNom = $(this).parent().children("input").val();
    const id = $(this).parent().children("input").attr("id");
    $(this).parent().children("label").text(nouveauNom);
    alert("nouveau nom : " + encodeURI(nouveauNom) + " \n idDoc : " + id );
    $.ajax({
        url: "chanson_post.php",
        type: "POST",
        data: {mode: "RENDOC", idDoc: id , nomDoc : encodeURI(nouveauNom)},
        contentType: 'application/x-www-form-urlencoded',
        datatype: 'json', // type de la donnée à recevoir
        success: function (code_html, statut) { // success est toujours en place, bien sûr !
            alert("Code retourné : " + code_html);
            if (code_html.search("n'a pas été traité.") == -1)
                toastr.success("Le document a été renommé ! <br> Le fichier a été renommé !");
            else {
                toastr.warning("Erreur dans l'opération...<br>Le document n'a pas pu être renommé...");
                $("#div1").html(code_html);
            }
        },
        error: function (resultat, statut, erreur) {
            $("#erreurAjax").html(resultat);
        }
    });
//  toastr.success("Le fichier a été renommé ! <br> Un nouveau pdf a été rajouté aux fichiers du songbook. <br> Vous pouvez raffraîchir la page pour le voir.");
});

// Clic sur un des boutons referme la saisie
$("button.document").click(function () {
    //						toastr.warning("Erreur dans la génération du pdf... un des pdf à assembler n'est pas pris en compte par nos outils .<br>Message d'erreur en bas de la page.");
    masqueSaisie(this);
});

function masqueSaisie(monThis) {
    // On masque les boutons renommer et annuler
    $(monThis).parent().children("button").hide();
    //On montre le label
    $(monThis).parent().children("label").show();
    // On masque la saisie de texte
    $(monThis).parent().find("input").hide();
}

// Pour le débogage, un changement de couleur
function getRandomColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function setRandomColor() {
    $("#colorpad").css("background-color", getRandomColor());
}

$(document).ready(function() {
    $('.js-example-basic-single').select2();
});

// Gestion de la modale "envoyer une nouvelle version"

function openModaleNouvelleVersionDocument(fileId) {
    document.getElementById('myModalEnvoieNouvelleVersion' ).style.display = 'block';
    document.getElementById('oldFile' ).value = fileId;
}

function closeModaleNouvelleVersionDocument() {
    document.getElementById('myModalEnvoieNouvelleVersion').style.display = 'none';
}

// Gestion de la modale "envoyer une nouvelle version"

function openModaleAjoutAuSongbook(fileId) {
    document.getElementById('myModalAjouterAuSongbook' ).style.display = 'block';
    document.getElementById('idDocumentEnvoiSongbook' ).value = fileId;
}

function closeModaleAjouterAuSongbook() {
    document.getElementById('myModalAjouterAuSongbook').style.display = 'none';
}