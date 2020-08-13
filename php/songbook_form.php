<?php /** @noinspection HtmlUnknownTarget */
include_once("lib/utilssi.php");
include_once("menu.php");
include_once("songbook.php");
include_once("document.php");
include_once("lienDocSongbook.php");
$table = "songbook";
$sortie = "";

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on le redirige vers la page _voir
if ($_SESSION ['privilege'] < 2) {
    $urlRedirection = $table . "_voir.php";
    if (isset ($_GET ['id']))
        $urlRedirection .= "?id=" . $_GET ['id'];
    redirection($urlRedirection);
}

// Traitement de l'ajout de document
if (isset ($_POST ['id']) && (isset ($_POST ['documentJoint']))) {
    $id = $_POST ['id'];
    ordonneLiensSongbook($id);
    creeLienDocSongbook($_POST ['documentJoint'], $_POST ['id']);
    $id = $_POST ['id'];
    if ($_POST ['ajax']==11) {
        echo("succes");
        exit();
    }
}

// Chargement des donnees du songbook si l'identifiant est fourni
if (isset ($_POST ['id']) || (isset ($_GET ['id']) && $_GET ['id'] != "")) {
    if (isset ($_GET ['id']))
        $id = $_GET ['id'];
    $donnee = chercheSongbook($id);
    $donnee [1] = htmlspecialchars($donnee [1]);
    $donnee [2] = htmlspecialchars($donnee [2]);
    $donnee [3] = dateMysqlVersTexte($donnee [3]);
//	$donnee [4] = $donnee [4];
//	$donnee [5] = $donnee [5];
    $mode = "MAJ";
    ordonneLiensSongbook($id);
} else {
    $mode = "INS";
    $donnee [0] = 0;
    $donnee [1] = "";
    $donnee [2] = "";
    $donnee [3] = "01/01/1970";
    $donnee [4] = "";
    $donnee [5] = 0;
}

if ($mode == "MAJ") {
    $sortie .= "<H1> Mise à jour - " . $table . "</H1>";
    $sortie .= " <p> Vous êtes sur le point de modifier un Songbook !</p>";
}
if ($mode == "INS") {
    $sortie .= "<H1> Création - " . $table . "</H1>";
    $sortie .= "<p>Vous êtes sur le point de créer un nouveau Songbook !</p>";
}
$sortie .= "<Div class = 'centrer'>";
// Création du formulaire
$f = new Formulaire ("POST", $table . "_get.php", $sortie);
$f->champCache("id", $donnee [0]);
// TODO : La longueur du champ n'est pas prise en compte dans formulaire!
$f->champTexte("Nom :", "fnom", $donnee [1], 64, 128);
$f->champTexte("Description :", "fdescription", $donnee [2], 64, 128);
$f->champTexte("Date :", "fdate", $donnee [3], 10, 10);
$f->champTexte("Image :", "fimage", $donnee [4], 64, 64);
$f->champTexte("Hits :", "fhits", $donnee [5], 10, 10);
$f->champCache("mode", $mode);
$f->champValider(" Valider ", "valider");
$sortie .= $f->fin();
$sortie .= "</div>\n";

if ($_SESSION ['privilege'] < 3) {
    // On verrouille les champs hits, date publication
    $sortie = str_replace("NAME='fdate'", "NAME='fdate' disabled='disabled' ", $sortie);
    $sortie = str_replace("NAME='fhits'", "NAME='fhits' disabled='disabled' ", $sortie);
}

$sortie .= "<h2>Liste des fichiers rattachés à ce songbook</h2>";
$sortie .= "<p>Les fichiers à rattacher ici seront relatifs au songbook lui-même : illustration de couverture, pdf contenant toutes les chansons...</p>";

// Cherche un document et le renvoie s'il existe
$lignes = chercheDocumentsTableId("songbook", $id);
$listeDocs = "";
// Pour chaque document
while ($ligneDoc = $lignes->fetch_row()) {
    // var_dump( $ligneDoc);
    // renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
    $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
    // echo "Chanson id : $id fichier court : $fichierCourt";
    $fichier = "../data/songbooks/$id/" . urlencode($fichierCourt);
    $extension = substr(strrchr($ligneDoc[1], '.'), 1);
    $icone = Image("../images/icones/$extension.png", 32, 32, "icone");
    if (!file_exists("../images/icones/$extension.png"))
        $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
    $listeDocs .= "$icone <a href= '" . $fichier . "' target='_blank'> " . htmlentities($fichierCourt) . "</a> ";
    $listeDocs .= "(" . intval($ligneDoc [2] / 1024) . " ko )";
    $listeDocs .= boutonSuppression("songbook_get.php" . "?idSongbook=$id&idDoc=$ligneDoc[0]&nomFic=$fichierCourt&mode=SUPPRFIC", $iconePoubelle, $cheminImages) . "<br>\n";
}
$sortie .= $listeDocs;

// On récupère les fichiers du Songbook
//$affichage = fichiersSongbook($id);
//$lignes = chercheDocumentsTableId ( "chanson", $id );
//
//foreach ($affichage as $fichier) {
//	$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
//	if (! file_exists (  "../images/icones/" . $fichier [2] . ".png"))
//		$icone = Image ( "../images/icones/fichier.png" , 32, 32, "icone" );
//	$sortie .= "$icone <a href= '" . htmlentities($fichier [0] . $fichier [1]) . "' target='_blank'> " . htmlentities($fichier[1]) . "</a> \n";
//	$sortie .= boutonSuppression ( "songbook_get.php?nomFic=" . urlencode($fichier [0] . $fichier [1]) . "&idDoc=$fichier[0]&mode=SUPPRFIC", $iconePoubelle, $cheminImages ) . "<br>\n";
//	// echo "Fichier : $fichier[1]";
//}

echo $sortie;

if ($mode == "MAJ") {
    ?>
    <h2>Envoyer un fichier pour ce songbook sur le serveur</h2>
    <form action="songbook_upload.php" method="post"
          enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
        <input type="hidden" name="id" value="<?php echo $donnee[0]; ?>">
        <label class="inline" for="fichier"> </label>
        <input type="file" id="fichier" name="fichierUploade" size="40">
        <input type="submit" value="Envoyer">
    </form>

    <h2>Liste des documents dans ce songbook</h2>
    <p>Voici la liste des documents rattachés au songbook :grilles, partoches, partitions...</p>
    <p>Il est possible de changer l'ordre des documents via les chevrons (déplacement d'un cran) ou les fléches (début
        ou fin de la liste)</p>
    <?php
    $lignes = chercheLiensDocSongbook('idSongbook', $id, "ordre", true);
    $listeDocs = "<ul id='sortable'>";
    $numero = 0;
    while ($ligne = $lignes->fetch_row()) {
        $numero++;
        $ligneDoc = chercheDocument($ligne [1]);
        $idDoc = $ligneDoc[0];
        $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
        $fichier = "../data/chansons/" . $ligneDoc [6] . "/" . urlencode($fichierCourt);
        $listeDocs .= "<li class='ui-state-default' data-index='$idDoc' data-position='$numero'>";
        $icone = Image("../images/icones/" . $fichier [2] . ".png", 32, 32, "icone");
        if (!file_exists("../images/icones/" . $fichier [2] . ".png"))
            $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
        $listeDocs .= "<a href= '" . htmlentities($fichier) . "' target='_blank'> " . htmlentities($fichierCourt) . "</a> ";
        $listeDocs .= boutonSuppression($songbookGet . "?idSongbook=$id&idDoc=$ligneDoc[0]&mode=SUPPRDOC", $iconePoubelle, $cheminImages);
        $listeDocs .= "</li>\n";
    }
    echo $listeDocs . "</ul>";
    ?>

    <h2>Lier un document existant à ce songbook</h2>
    <p>Ici on rattache des documents au songbook. Ce seront des documents rattachés à une chanson. </p>
    <p>Par exemple, grille, partoche, partition... Pour uploader sur le site des documents, il faut d'abord créer une
        chanson, et lui rattacher des documents. </p>
    <p>Dans la liste combo ci-dessous, vous trouverez les derniers documents uploadés sur le site au format pdf.</p>

    <form action="songbook_form.php" method="post" name="form2">
        <?php
        // echo selectDocument("nomTable", "chanson", "id", false);
        include "../php/documentCherche.php";
        ?>
        <input type="hidden" name="id" value="<?php echo $donnee[0]; ?>">
        <input type="submit" value="Envoyer">


    </form>
    <button onclick='demandeUnPdf()'>Genère le songbook en pdf</button>

    <div id="div1"></div>
    <script>
        function demandeUnPdf() {
            $.ajax({
                type: "POST",
                url: "songbook_get.php",
                data: "id=" + <?=$id?> +"&mode=GENEREPDF",
                datatype: 'html', // type de la donnée à recevoir
                success: function (code_html, statut) { // success est toujours en place, bien sûr !
                    if (code_html.search("n'a pas été traité.") === -1)
                        toastr.success("La génération du pdf a abouti ! <br> Un nouveau pdf a été rajouté aux fichiers du songbook. <br> Vous pouvez raffraîchir la page pour le voir.");
                    else {
                        toastr.warning("Erreur dans la génération du pdf... un des pdf à assembler n'est pas pris en compte par nos outils .<br>Message d'erreur en bas de la page.");
                        $("#div1").html(code_html);
                    }
                },
                error: function (resultat, statut, erreur) {
                    $("#div1").html(resultat);
                }
            });
        }

        function formSuccess() {
            $("#msgSubmit").removeClass("hidden");
        }

        // Gestion de l'ordre dans le songbook
        // On va renvoyer à la page traiteOrdre.php l'identifiant du songbook, et un tableau avec
        // positions[idDoc,ancienRang]
        // ex : si le 1er élémelent est passé en 2 :
        // positions[387,2][366,1][167,3][274,4]

         //   $('#sortable').sortable();
            $('#sortable').sortable({
                axis: 'y',
                update: function(index) {
                    var positions = [];
                    $(this).children().each(function( index)
                    {
                        console.log($(this).attr('data-index'));
                        positions.push([$(this).attr('data-index'),$(this).attr('data-position')]);
                    });
                    $.ajax({
                        data: {
                            idSongbook :  <?=$id?>,
                            positions : positions
                        },
                        type: 'POST',
                        url: 'traiteOrdre.php'
                    });
                }
            });
            $('#sortable').disableSelection();
    </script>
    <?php
    echo envoieFooter();
}
?>