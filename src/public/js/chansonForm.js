/**
 * Gestion du formulaire de chanson (Django Style)
 */

$(document).ready(function() {
    // Initialisation des onglets jQuery UI
    if ($.fn.tabs) {
        $('#tabs').tabs();
    }

    // Initialisation de Select2
    if ($.fn.select2) {
        $('.js-example-basic-single').select2();
    }
});

// --- GESTION DU RENOMMAGE DE DOCUMENT ---

/**
 * Active l'interface d'édition pour un document
 */
function activeRenommage(idDoc) {
    const input = $('#input-' + idDoc);
    const container = input.closest('.doc-info-container');
    container.find('label.doc').hide();
    container.find('i.glyphicon-pencil').hide();
    container.find('.edit-doc-container').show().css('display', 'inline-flex');
    input.focus().select();
}

$(document).on('click', 'label.doc', function() {
    const idDoc = $(this).closest('.doc-info-container').find('.edit-doc-input').attr('id').split('-')[1];
    activeRenommage(idDoc);
});

/**
 * Annule l'édition en cours
 */
function annuleRenommage(idDoc) {
    const input = $('#input-' + idDoc);
    const container = input.closest('.doc-info-container');
    container.find('.edit-doc-container').hide();
    container.find('label.doc').show();
    container.find('i.glyphicon-pencil').show();
}

/**
 * Valide et envoie le renommage via AJAX
 */
function valideRenommage(idDoc) {
    const input = $('#input-' + idDoc);
    const nouveauNom = input.val().trim();
    const container = input.closest('.doc-info-container');
    const label = container.find('label.doc');

    if (nouveauNom === "") {
        toastr.error("Le nom ne peut pas être vide.");
        return;
    }

    $.ajax({
        url: "chanson_post.php",
        type: "POST",
        data: {
            mode: "RENDOC", 
            idDoc: idDoc, 
            nomDoc: nouveauNom
        },
        success: function (response) {
            if (response.indexOf("bien passé") !== -1) {
                label.text(nouveauNom);
                toastr.success("Document renommé avec succès !");
                annuleRenommage(idDoc);
            } else {
                toastr.error("Erreur : " + response);
            }
        },
        error: function (xhr, status, error) {
            toastr.error("Erreur réseau lors du renommage.");
            console.error(error);
        }
    });
}

// Gestion de la touche Entrée et Echap dans l'input de renommage
$(document).on('keydown', '.edit-doc-input', function(e) {
    const idDoc = $(this).attr('id').split('-')[1];
    if (e.key === "Enter") {
        valideRenommage(idDoc);
    } else if (e.key === "Escape") {
        annuleRenommage(idDoc);
    }
});


// --- GESTION DES MODALES ---

/**
 * Ouvre la modale pour envoyer une nouvelle version d'un document
 */
function openModaleNouvelleVersionDocument(fileId, nomDocument) {
    const modal = document.getElementById('myModalEnvoieNouvelleVersion');
    if (modal) {
        modal.style.display = 'block';
        document.getElementById('oldFile').value = fileId;
        document.getElementById('texteNomDocument').innerHTML = "pour le document <strong>" + nomDocument + "</strong>";
    }
}

function closeModaleNouvelleVersionDocument() {
    const modal = document.getElementById('myModalEnvoieNouvelleVersion');
    if (modal) modal.style.display = 'none';
}

/**
 * Ouvre la modale pour ajouter un document à un songbook
 */
function openModaleAjoutAuSongbook(fileId) {
    const modal = document.getElementById('myModalAjouterAuSongbook');
    if (modal) {
        modal.style.display = 'block';
        document.getElementById('idDocumentEnvoiSongbook').value = fileId;
    }
}

function closeModaleAjouterAuSongbook() {
    const modal = document.getElementById('myModalAjouterAuSongbook');
    if (modal) modal.style.display = 'none';
}

// Fermeture des modales au clic en dehors du contenu
window.onclick = function(event) {
    const modalVersion = document.getElementById('myModalEnvoieNouvelleVersion');
    const modalSongbook = document.getElementById('myModalAjouterAuSongbook');
    if (event.target == modalVersion) closeModaleNouvelleVersionDocument();
    if (event.target == modalSongbook) closeModaleAjouterAuSongbook();
};


// --- FONCTIONS AJAX POUR LES LIENS ET DOCUMENTS ---

/**
 * Ajoute un paramètre à une chaîne de query
 */
function ajouteParametre(nomParametre, valeurParametre, chaine) {
    chaine = chaine + "&" + nomParametre + "=" + valeurParametre;
    return (chaine);
}

/**
 * Met à jour ou supprime un lien externe
 */
function updateLienurl(mode, id, nomtable, idtable) {
    let chaineData = "mode=" + mode;
    
    if (mode === "DEL"){
        chaineData = ajouteParametre("id", id, chaineData);
        // supprimer la div du lien dans le DOM
        let maDiv = document.getElementById("divlienUrl"+id);
        if (maDiv) maDiv.remove();
    }
    
    if (mode === "NEW" || mode === "UPDATE"){
        let url = document.getElementById("lienUrl" + id).value;
        let type = document.getElementById("lienType" + id).value;
        let description = document.getElementById("lienDescription" + id).value;
        let date = document.getElementById("date" + id).value;
        let idUser = document.getElementById("idUser" + id).value;
        let hits = document.getElementById("hits" + id).value;
        
        chaineData = ajouteParametre("id", id, chaineData);
        chaineData = ajouteParametre("nomtable", nomtable, chaineData);
        chaineData = ajouteParametre("idtable", idtable, chaineData);
        chaineData = ajouteParametre("url", url, chaineData);
        chaineData = ajouteParametre("type", type, chaineData);
        chaineData = ajouteParametre("description", description, chaineData);
        chaineData = ajouteParametre("date", date, chaineData);
        chaineData = ajouteParametre("idUser", idUser, chaineData);
        chaineData = ajouteParametre("hits", hits, chaineData);
    }

    // On récupère l'URL AJAX
    const urlPost = "../liens/lienurlPost.php"; 

    $.ajax({
        type: "POST",
        url: urlPost,
        data: chaineData,
        datatype: 'html',
        success: function (code_html, status) {
            if (code_html.search("n'a pas été traité.") === -1) {
                toastr.success("L'opération a réussi !");
                if (mode === "NEW" || mode === "UPDATE") {
                    location.reload(); // Pour voir les changements
                }
            } else {
                toastr.warning("Erreur dans l'opération...");
                $("#msgLien"+id).html("Status : " + status + ". retour : " + code_html);
            }
        },
        error: function (resultat, status, erreur) {
            toastr.error("Erreur réseau : " + erreur);
        }
    });
}

/**
 * Envoie un document dans un songbook
 */
function envoieFichierDansSongbook() {
    const idSongbook = $("select[name='idSongbook'] option:selected").val();
    const idFichier = $("input[id='idDocumentEnvoiSongbook']").val();
    
    if (idSongbook === '') {
        toastr.warning("Veuillez sélectionner un songbook !");
        return;
    }

    $.ajax({
        type: "POST",
        url: "../songbook/songbook_form.php",
        data: "id=" + idSongbook + "&documentJoint=" + idFichier + "&ajax=11",
        datatype: 'html',
        success: function (code_html, status) {
            if (code_html.search("succes") > -1) {
                toastr.success("Document ajouté au songbook !");
                closeModaleAjouterAuSongbook();
            } else {
                toastr.warning("Le document n'a pas pu être raccroché...");
            }
        },
        error: function (resultat, status, erreur) {
            toastr.error("Erreur réseau : " + erreur);
        }
    });
}

/**
 * Restaure un document depuis la corbeille
 */
function restaureDocument(idChanson, nomFic, elementId) {
    $.ajax({
        type: "POST",
        url: "chanson_post.php",
        data: "id=" + idChanson + "&nomFic=" + nomFic + "&mode=RESTAUREDOC",
        datatype: 'html',
        success: function (code_html) {
            if (code_html.search("n'a pas été traité.") === -1) {
                toastr.success("Le document a été restauré !");
                location.reload();
            } else {
                toastr.warning("Le document n'a pas pu être raccroché...");
            }
        },
        error: function (resultat, status, erreur) {
            toastr.error("Erreur réseau : " + erreur);
        }
    });
}
