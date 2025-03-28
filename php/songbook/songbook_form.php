<?php
const RACINE = "../../";

require_once("../lib/utilssi.php");
require_once("songbook.php");
require_once("../document/document.php");
require_once("../liens/lienDocSongbook.php");
require_once("../navigation/menu.php");

global $iconePoubelle;
global $_DOSSIER_CHANSONS;
global $cheminImages;
global $songbookGet;

const ICONE = "icone";
$table = "songbook";
$sortie = "";

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on le redirige vers la page _voir
if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $urlRedirection = $table . "_voir.php";
    if (isset ($_GET ['id']) && (is_numeric($_GET ['doc']))) {
        $urlRedirection .= "?id=" . $_GET ['id'];
        redirection($urlRedirection);
    } else {
        echo "Erreur n°1 dans Songbook_form.php, merci de contacter notre numéro vert.";
        exit();
    }
}

// Traitement de l'ajout de document
if (isset ($_POST ['id']) && is_numeric($_POST ['id']) && (isset ($_POST ['documentJoint']))) {
    $id = $_POST ['id'];
    ordonneLiensSongbook($id);
    creeLienDocSongbook($_POST ['documentJoint'], $_POST ['id']);
    $id = $_POST ['id'];
    if ($_POST ['ajax']==11) {
        echo"succes";
        exit();
    }
}

// Chargement des donnees du songbook si l'identifiant est fourni
if (isset ($_POST ['id']) || (isset ($_GET ['id']) && (is_numeric($_GET ['id'])))) {
    if (isset ($_GET ['id'])) {
        $id = $_GET ['id'];
    }
    $donnee = chercheSongbook($id);
    $donnee [1] = htmlspecialchars($donnee [1]);
    $donnee [2] = htmlspecialchars($donnee [2]);
    $donnee [3] = dateMysqlVersTexte($donnee [3]);
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
    $donnee [7] = 1;
}

if ($mode == "MAJ") {
    $sortie .= "<H1> Mise à jour - " . $table . "</H1>";
    $sortie .= " <p> Vous êtes sur le point de modifier un Songbook !</p>
                <a href='songbook_voir.php?id=$id'> voir le songbook</a>";
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
$f->champTexte("Type :", "ftype", $donnee [7], 10, 10);
$f->champCache("mode", $mode);
$f->champValider(" Valider ", "valider");
$sortie .= $f->fin();
$sortie .= "</div>\n";

if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    // On verrouille le champ hits, date
    // $sortie = str_replace("NAME='fdate'", "NAME='fdate' disabled='disabled' ", $sortie);
    $sortie = str_replace("NAME='fhits'", "NAME='fhits' disabled='disabled' ", $sortie);
}

$sortie .= "<h2>Liste des fichiers rattachés à ce songbook</h2>";
$sortie .= "<p>Les fichiers à rattacher ici seront relatifs au songbook lui-même : illustration de couverture, pdf contenant toutes les chansons...</p>";

// Cherche un document et le renvoie s'il existe

if ($mode=="MAJ") {
    $lignes = chercheDocumentsTableId("songbook", $id);
    $listeDocs = "";
    // Pour chaque document
    while ($ligneDoc = $lignes->fetch_row()) {
        // var_dump( $ligneDoc);
        // renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
        $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
        // echo "Chanson id : $id fichier court : $fichierCourt";
        $fichier =  RACINE . "data/songbooks/$id/" . urlencode($fichierCourt);
        $extension = substr(strrchr($ligneDoc[1], '.'), 1);
        $icone = Image(RACINE ."images/icones/$extension.png", 32, 32, ICONE);
        if (!file_exists(RACINE . "images/icones/$extension.png")) {
            $icone = Image(RACINE . "images/icones/fichier.png", 32, 32, ICONE);
        }
        $listeDocs .= "$icone <a href= '" . $fichier . "' target='_blank'> " . htmlentities($fichierCourt) . "</a> ";
        $listeDocs .= "(" . intval($ligneDoc [2] / 1024) . " ko )";
        $listeDocs .= boutonSuppression("songbook_get.php" . "?id=$id&idDoc=$ligneDoc[0]&nomFic=$fichierCourt&mode=SUPPRFIC", $iconePoubelle, $cheminImages) . "<br>\n";
    }
    $sortie .= $listeDocs;
}
echo $sortie;

require('songbook_corbeille.php');

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
    <p>Il est possible de changer l'ordre des documents en déplaçant les titres dans la liste à la souris (drag'n drop)</p>
    <?php
    $lignes = chercheLiensDocSongbook('idSongbook', $id, "ordre" );
    $listeDocs = "<ul id='sortable'>";
    $numero = 0;
    while ($ligne = $lignes->fetch_row()) {
        $numero++;
        $ligneDoc = chercheDocument($ligne [1]);
        $idDoc = $ligneDoc[0];
        $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
        $fichier = RACINE .$_DOSSIER_CHANSONS . $ligneDoc [6] . "/" . urlencode($fichierCourt);
        $listeDocs .= "<li class='ui-state-default' data-index='$idDoc' data-position='$numero'>";
        $icone = Image(RACINE ."images/icones/" . $fichier [2] . ".png", 32, 32, ICONE);
        if (!file_exists(RACINE . "images/icones/" . $fichier [2] . ".png")) {
            $icone = Image(RACINE . "images/icones/fichier.png", 32, 32, ICONE);
        }
        $listeDocs .= "<a href= '" . htmlentities($fichier) . "' target='_blank'> " . htmlentities($fichierCourt) . "</a> ";
        $listeDocs .= boutonSuppression($songbookGet . "?id=$id&idDoc=$ligneDoc[0]&mode=SUPPRDOC", $iconePoubelle, $cheminImages);
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
        require "../document/documentCherche.php";
        ?>
        <input type="hidden" name="id" value="<?php echo $donnee[0]; ?>">
        <input id="envoyer" type="submit" value="Envoyer" style="display: none;">
    </form>
    <button onclick='genereUnPdf()'>Génère le songbook en pdf</button>

    <div id="div1"></div>
    <script>
        // bouton valider masqué par défaut
        $(document).ready(function () {
            $("#valider").hide();
        });
        // bouton validé montré après une recherche
        $(document).on("change", "input[type='radio']", function() {
            if ($("input[type='radio']:checked").length > 0) {
                $("#valider").show();
            } else {
                $("#valider").hide();
            }
        });
        // entrée ayu clavier lance la recherche
        // Ajoutez l'attribut autofocus à l'élément de recherche
        $("#nomCherche").attr("autofocus", true);

        // Ajoutez un événement keydown à l'élément de recherche
        $("#nomCherche").on("keydown", function(event) {
            if (event.which === 13) { // La touche Entrée a été appuyée
                $("#btnChercheDocuments").click(); // Déclenche le clic sur le bouton de recherche
                return false; // Empêche la soumission du formulaire
            }
        });

        function genereUnPdf() {
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
        // ex : si le 1er élément est passé en 2 :
        // positions[387,2][366,1][167,3][274,4]

         //   $('#sortable').sortable();
        $('#sortable').sortable({
            axis: 'y',
            update: function (index) {
                let positions = [];
                $(this).children().each(function (index) {
                    // console.log('déplacement du ' + $(this).attr('data-index'));
                    positions.push([$(this).attr('data-index'), $(this).attr('data-position')]);
                });
                $.ajax({
                    data: {
                        idSongbook: <?=$id?>,
                        positions: positions
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