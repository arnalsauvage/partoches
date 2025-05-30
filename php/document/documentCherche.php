<h1>Rechercher un document </h1>

<script>
    $(document).ready(function () {
        $("#btnChercheDocuments").click(function () { // On sélectionne le formulaire par son identifiant
            var donnees = "typeDocument=" + $("#typeDocument").val(); // On crée une variable contenant le formulaire sérialisé
            donnees += "&nomCherche=" + $("#nomCherche").val();
            donnees += "&triPar=" + $("#triPar").val();
            donnees += "&triCroissant=" + $("#triCroissant").val();
            //alert("donnees :" + donnees);
            $.ajax({
                url: '../document/documentChercheAjax.php',
                type: 'POST', // Le type de la requête HTTP, ici devenu POST
                data: donnees,
                dataType: 'html'
            });
        });
    });

    $(document).ajaxSuccess(function (event, xhr, settings) {
        if (settings.url === "../document/documentChercheAjax.php") {
            $("#storage").html("Documents trouvés :  " + xhr.responseText);
            if ($("#storage").html().indexOf("Documents trouvés :  ") !== -1 && $("#storage").html().indexOf("Aucun document trouvé") === -1) {
                $("#envoyer").show();
            } else {
                $("#envoyer").hide();
            }
        }
    });
</script>

<p>Utilisez les filtres ci-dessous pour trouver un document à rattacher au songbook. Le type devrait rester à pdf pour
    que le songbook soit généré correctement</p>
<p>
    La recherche sur le nom permet de retrouver par exemple les documents contenant "Memphis" dans le titre. <br>
    On peut trier par id, nom, tailleKo, hits...
    L'ordre peut être asc (ascendant) ou 'desc' (descendant)
</p>
<hr/>
<div id="storage"> L'affichage des documents trouvés sera inséré ici</div>
<hr/>


<label>Type de document:
    <input id="typeDocument" name="typeDocument" type="text" value="pdf" size="20"/>
</label>
<label>Nom cherché:
    <input id="nomCherche" name="nomCherche" type="text" value="" size="20"/>
</label>
<label>Tri par:
    <select id="triPar">
        <option value="id">identifiant</option>
        <option value="nom">nom</option>
        <option value="tailleKo">taille</option>
        <option value="version">version</option>
        <option value="version">hits</option>
    </select>
</label>
<label>Croissant ou décroissant :
    <select id="triCroissant">
        <option value="desc">décroissant</option>
        <option value="asc">croissant</option>
    </select>
</label>
<input id="btnChercheDocuments" type="button" value="chercher">