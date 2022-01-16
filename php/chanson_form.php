<?php
const CHANSON = "chanson";
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("chanson.php");
require_once("document.php");
require_once("songbook.php");
require_once ('lienurl.php');
require_once("lib/formulaire.php");
$table =  CHANSON;
$sortie = "";
global $iconePoubelle;
global $cheminImages;
global $_DOSSIER_CHANSONS;

$listeSongbooks =[];
$listeSongbooks = listeSongbooks();

function comboAjoutSongbook($listeSongbooks)
{
    $monCombo = " <br>   <label class='inline col-sm-4'> * Ajouter au songbook :</label> 
    <select name= 'idSongbook' >";
    foreach ( $listeSongbooks as $songbook)
    {
        $monCombo .= " <option value='".$songbook[0]."'>".$songbook[1]."</option>";
    }
    $monCombo .= "   </select>";
    return($monCombo);
}

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on le redirige vers la page _voir
if ($_SESSION ['privilege'] < 2) {
    $urlRedirection = $table . "_voir.php";
    if (isset ($_GET ['id']) && is_numeric($_GET ['id']))
    {
        $urlRedirection .= "?id=" . $_GET ['id'];
    }
    redirection($urlRedirection);
}

// $id, $nom, $interprete, $annee, $idUser, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0
$_chanson = new Chanson();

// Chargement des donnees de la chanson si l'identifiant est fourni

if (isset ($_POST ['id']))
{
    $id = $_POST ['id'];
}
if (isset ($_GET ['id']) && is_numeric($_GET ['id'])) {
    $id = $_GET ['id'];
    $_chanson->chercheChanson($id);
    $mode = "MAJ";
} else {
    $mode = "INS";
    $_chanson->setIdUser($_SESSION ['id']);
}

$sortie .= "
<div class='col-lg-12 centrer'>";
if ($mode == "MAJ"){
    $sortie .= "<H1> Mise à jour - " . $table . "</H1>";
}
if ($mode == "INS"){
    $sortie .= "<H1> Création - " . $table . "</H1>";
}

$sortie .= "<a href = 'chanson_voir.php?id=".$_chanson->getId()."'>voir la chanson</a>";
// Création du formulaire

$sortie .= "
<FORM  METHOD='POST' ACTION='chanson_post.php' NAME='Form'>
<INPUT TYPE=HIDDEN NAME='id' VALUE='" . $_chanson->getId() . "'>
<div class = 'row'>
<label class='inline col-sm-3'>Nom :</label><INPUT class= 'col-sm-7' TYPE='TEXT' NAME='fnom' VALUE='" . htmlspecialchars($_chanson->getNom(), ENT_QUOTES) . "' SIZE='64' MAXLENGTH='128' placeholder='titre de la chanson'><br>
</div>
<div class = 'row'>
<label class='inline col-sm-3'>Interprète :</label><INPUT class = 'col-sm-7' TYPE='TEXT' NAME='finterprete' VALUE='" . htmlspecialchars($_chanson->getInterprete(), ENT_QUOTES) . "' SIZE='64'  placeholder='interprète'><br>
</div>
<div class = 'row'>
<label class='inline col-sm-3'>Année :</label><INPUT class= 'col-sm-7' TYPE='number' min='0' max='2100' NAME='fannee' VALUE='" . $_chanson->getAnnee() . "' SIZE='4'><br>
</div>
<script>function outputUpdate(vol) {
	document.querySelector('#tempo').value = vol;
}</script>
<div class = 'row'>
    <label class='inline col-sm-3' for='fader'>Tempo :</label>
        <div class = 'col-sm-5'>
        <input  TYPE='range' id='fader' min='30' max='250' step='1' oninput='outputUpdate(value)' name='ftempo' value='" . $_chanson->getTempo() . "' size='3' >
        </div>
    <output class = 'inline col-sm-2' for='fader' id='tempo'>" . $_chanson->getTempo() . "</output>
</div>
<div class = 'row'>
<label class='inline col-sm-3'>Mesure :</label><INPUT class= 'col-sm-7' TYPE='TEXT' NAME='fmesure' VALUE='" . $_chanson->getMesure() . "' SIZE='4' MAXLENGTH='128'>
</div>
<div class = 'row'>
<label class='inline col-sm-3'> Pulsation :</label>
    <select class= 'col-sm-7' NAME='fpulsation' >
    <option value='binaire'";
if ($_chanson->getPulsation() == "binaire"){
    $sortie .= " selected";
}
$sortie .= ">binaire
    </option>
    <option value='ternaire' ";
if ($_chanson->getPulsation() == "ternaire"){
    $sortie .= " selected";
}
$sortie .= "
>ternaire</option>
    </select>";

$sortie .= "
</div>
<div class = 'row'>
<label class='inline col-sm-3'> Tonalité :</label>
<INPUT class= 'col-sm-7' TYPE='TEXT' NAME='ftonalite' VALUE='" . $_chanson->getTonalite() . "' SIZE='10' placeholder='ex :Am ou C ou F#'>
</div>
<div class = 'row'>
<label class='inline col-sm-3'> Date publication :</label>
<INPUT class= 'col-sm-7' TYPE='TEXT' NAME='fdate' VALUE='" . dateMysqlVersTexte($_chanson->getDatePub()) . "' SIZE='10' MAXLENGTH='128'>
 </div>
<div class = 'row'>
<label class='inline col-sm-3'> Hits :</label>
<INPUT class= 'col-sm-7' TYPE='number' NAME='fhits' VALUE='" . $_chanson->getHits() . "' SIZE='10'>
</div>
<div class = 'row'>
<label class='inline col-sm-3'> Utilisateur :</label>"
    . selectUtilisateur("nom", "%", "login", true, $_chanson->getIdUser()) . "
<INPUT TYPE=HIDDEN NAME='mode' VALUE='$mode'>
<label class='inline'> </label><INPUT TYPE='SUBMIT' NAME='valider' VALUE=' Valider ' >
</div>
</FORM>
";
$urlCherche = "chanson_chercher.php?chanson=". urlencode($_chanson->getNom())."&artiste=".urlencode($_chanson->getInterprete());
$urlCherche .= "&idChanson=$id";

$sortie .= "<br> Nouvelle fonctionnalité !!! Enregistrer la chanson avec titre et interprête, puis cliquez ici pour remplir automatiquement via une base de données !!!";
$sortie .= "<br> <a href='" . $urlCherche."'>Chercher année, tempo, mesure, tonalité et image depuis getsongbpm !</a><br>";
if ($_chanson->getNom()) {

    $sortie .= "Pour chercher la chanson sur youtube : <a href='https://www.youtube.com/results?search_query=" . urlencode($_chanson->getNom()) . "' target='_blank'>ici</a><br>\n";
    $sortie .= "Pour chercher des images : <a href='https://www.qwant.com/?q=" . urlencode($_chanson->getNom()) . "&amp;t=images=' target='_blank'>ici</a><br>\n";

    $rechercheBpm = htmlentities(str_replace(" ", "-", strtolower($_chanson->getNom())));
    $sortie .= "Pour chercher le tempo sur <a href='https://songbpm.com/$rechercheBpm' target='_blank'>songbpm</a><br>\n";

    $rechercheWikipedia = "https://fr.wikipedia.org/w/index.php?search=" . urlencode(($_chanson->getNom() . " " . $_chanson->getInterprete()));
    $sortie .= "Pour chercher la chanson sur <a href='$rechercheWikipedia' target='_blank'>wikipedia</a><br>\n";
}

if ($_SESSION ['privilege'] < 3) {
    // On verrouille les champs hits, date publication, et utilisateur
    $sortie = str_replace("NAME='fdate'", "NAME='fdate' disabled='disabled' ", $sortie);
    $sortie = str_replace("NAME='fhits'", "NAME='fhits' disabled='disabled' ", $sortie);
    $sortie = str_replace("name='fidUser'", "NAME='fidUser' disabled='disabled' ", $sortie);
}

echo $sortie;
if ($mode == "MAJ") {
    ?>
    <h2>Liste des documents de cette chanson</h2>
    <ul>
        <?php
        // Cherche un document et le renvoie s'il existe
        $lignes = chercheDocumentsTableId(CHANSON, $id);
        $listeDocs = "";
        // Pour chaque document
        while ($ligneDoc = $lignes->fetch_row()) {
            // var_dump( $ligneDoc);
            $idDoc = $ligneDoc [0];
            // renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
            $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
            $fichier = "../" . $_DOSSIER_CHANSONS . "$id/" . rawurlencode($fichierCourt);
            $extension = substr(strrchr($ligneDoc[1], '.'), 1);
            $icone = Image("../images/icones/$extension.png", 32, 32, "icone");
            if (!file_exists("../images/icones/$extension.png"))
            {
                $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
            }
            $listeDocs .= "<li class='fichiers'> <div> <a href= '" . $fichier . "' target='_blank'> $icone </a> ";
            //    $listeDocs .= "Id chanson : $id  id doc : " . $ligneDoc[0] . "fichier court : $fichierCourt <br>";
            $listeDocs .= "<label class='doc'>" . htmlentities($fichierCourt) . "</label>";
            $listeDocs .= " (" . intval($ligneDoc [2] / 1024) . " ko )
		    <input size='16' id='$idDoc' name='user' value='" . htmlentities($fichierCourt) . "' placeholder='nomDeFichier.ext' style='display:none;'>
		    <button name='renommer' style='display:none;'>renommer</button>
            <button style='display:none;'>x</button>";
            if ($_SESSION ['privilege'] > 2) {
                $listeDocs .= boutonSuppression("chanson_post.php" . "?id=$id&idDoc=$ligneDoc[0]&mode=SUPPRDOC", $iconePoubelle, $cheminImages);
            }
            //$listeDocs .= " ajouter au songbook id le document " . $ligneDoc[0];
            $listeDocs .= "</li>";
            $listeDocs .= "<button onclick=envoieFichierDansSongbook(".$ligneDoc[0].") >ajouter au songbook</button>";
        }
        $listeDocs .= "<br><div>" . comboAjoutSongbook($listeSongbooks) ."</div><br>";
        echo $listeDocs;
        ?>
    </ul>
    <h2>Envoyer un fichier pour cette chanson sur le serveur</h2>
    <form action="chanson_upload.php" method="post"
          enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
        <input type="hidden" name="id" value="<?php echo $_chanson->getId(); ?>">
        <label class="inline" for="fichier"> </label>
        <input type="file" id="fichier" name="fichierUploade" size="40">
        <input type="submit" value="Envoyer">
    </form>

    <h2> Corbeille des fichiers effacés</h2>
    <?php
    $fichiersEnBdd = [];
    $resultat = chercheDocumentsTableId(CHANSON, "$id");
    while ($fichierEnBdd = $resultat->fetch_row()) {
        array_push($fichiersEnBdd, $fichierEnBdd);
    }

    $fichiersSurDisque = $_chanson->fichiersChanson($_DOSSIER_CHANSONS); // repertoire nom extension
//    $maRequete = "INSERT INTO document VALUES (NULL, '$nom', '$tailleKo', '$date', '$version', '$nomTable', '$idTable', '$idUser', '0')";

    $nbFichiersKO = 0;
    while (count($fichiersSurDisque) > 0) {
        //echo "nb fichiers : "     .   count($fichiersSurDisque) / 3;

        $fichierSurDisque[0] = array_shift($fichiersSurDisque);
        $fichierSurDisque[1] = array_shift($fichiersSurDisque);
        $fichierSurDisque[2] = array_shift($fichiersSurDisque);
        //echo ".......FichierDisque ". $fichierSurDisque[1] ."<br>";
        $fichierOk = false;
        foreach ($fichiersEnBdd as $fichierEnBdd) {
            //echo "cherche version du " . $fichierEnBdd[1] . " " . $fichierEnBdd[4] . "<br>";
            // si le fichierBDD est sur disque, alors fichierOk
            if (composeNomVersion($fichierEnBdd[1], $fichierEnBdd[4]) == $fichierSurDisque[1]) {
                $fichierOk = true;
                //echo "Fichier $fichierSurDisque[1] trouvé !!!!!!!!!!!!!!!!!!!<br>";
            }
        }
        $numeroElement = 1;
        if (! $fichierOk) {
            $nbFichiersKO++;
            echo "Fichier corbeille : " . $fichierSurDisque[1] . " non répertorié par la Bdd ";
            $urlFichier = "chanson_post.php?nomFic=" . urlencode("../".$_DOSSIER_CHANSONS . $id . "/" . $fichierSurDisque[1] . "&mode=SUPPRFIC&id=$id");
            echo boutonSuppression($urlFichier, $iconePoubelle, $cheminImages) . "<br>$numeroElement";
            $numeroElement = count($fichiersSurDisque) + 1;
            ?>
            <button onclick='restaureDocument<?php echo $numeroElement; ?>()'>Restaurer le document dans la chanson
            </button>

            <div id="div<?php echo $numeroElement; ?>"></div>
            <script>
                function restaureDocument<?php echo $numeroElement;?>() {
                    $.ajax({
                        type: "POST",
                        url: "chanson_post.php",
                        data: "id=<?php echo $id;?>&nomFic=<?php echo $fichierSurDisque[1];?>&mode=RESTAUREDOC",
                        datatype: 'html', // type de la donnée à recevoir
                        success: function (code_html, statut) { // success est toujours en place, bien sûr !
                            if (code_html.search("n'a pas été traité.") === -1)
                                toastr.success("Le document a été restauré ! <br> Le fichier a été raccroché à la chanson <br> Vous pouvez raffraîchir la page pour le voir.");
                            else {
                                toastr.warning("Erreur dans l'opération...<br>Le document n'a pas pu être raccroché...");
                                $("#div1").html(code_html);
                            }
                        },
                        error: function (resultat, statut, erreur) {
                            $("#div<?php echo $numeroElement;?>").html(resultat);
                        }
                    });
                }
                function formSuccess() {
                    $("#msgSubmit").removeClass("hidden");
                }
            </script>
            <?php
        }
    }
    if ($nbFichiersKO == 0)
    {
        echo "La corbeille est vide pour cette chanson.\n";
    }
}
    echo "<h2>Liste des liens de cette chanson</h2>";
    echo "<ul>";

echo "<li>
                <input size='255' id='lienType0' name='type' value='' placeholder='video ou article'>
                <input size='255' id='lienDescription0' name='description' value='' placeholder='description'>
                <input size='255' id='lienUrl0' name='url' value='' placeholder='http://youtu.be/3456' >
                <button name='createLien' onclick=\"updateLienurl('NEW',0,'chanson', $id) \">créer</button>
              ";
echo "</li><br>";

    // Cherche un lienurl et le renvoie s'il existe
    $lignes = chercheLiensUrlsTableId(CHANSON, $id);
    $listeLiensUrl = "";
    // Pour chaque lien
    while ($ligneLien = $lignes->fetch_row()) {
        // var_dump( $ligneLien);
        $idLien = $ligneLien [0];
        // renvoie la ligne sélectionnée : //  id	nomtable	idtable	url	type	description
        $url = $ligneLien[3] ;
        $type = $ligneLien[4];
        $description = $ligneLien[5];
        $nomtable = "chanson";
        $idtable = $id;

        echo "<li>
                <input size='255' id='lienType$idLien' name='type' value='" . htmlentities($type) . "' placeholder='video ou article'>
                <input size='255' id='lienDescription$idLien' name='description' value='" . htmlentities($description) . "' placeholder='description'>
                <input size='255' id='lienUrl$idLien' name='url' value='" . htmlentities($url) . "' placeholder='http://youtu.be/3456' >
                <button name='updateLien$idLien' onclick=\"updateLienurl('UPDATE',$idLien,'chanson', $id) \">modifier</button>
                <button name='deleteLien$idLien' onclick=\"updateLienurl('DEL',$idLien,'chanson', $id) \">supprimer</button>
              ";
        echo "</li><br>";
    }
?>
    <script>
        function updateLienurl(mode,id, nomtable, idtable) {
            url = document.getElementById("lienUrl"+id).value;
            type = document.getElementById("lienType"+id).value;
            description = document.getElementById("lienDescription"+id).value;
            $.ajax({
                type: "POST",
                url: "lienurlPost.php",
                data: "mode="+mode+"&id="+id+"&url="+url+"&type="+type+"&description="+description+"&nomtable="+nomtable+"&idtable="+idtable,
                datatype: 'html', // type de la donnée à recevoir
                success: function (code_html, statut) { // success est toujours en place, bien sûr !
                    if (code_html.search("n'a pas été traité.") === -1)
                        toastr.success("Le lien a été modifié ! <br> Le lien de la chanson a été modifié <br> Vous pouvez raffraîchir la page pour le voir.");
                    else {
                        toastr.warning("Erreur dans l'opération...<br>Le lien n'a pas pu être modifié...");
                        $("#div1").html(code_html);
                    }
                },
                error: function (resultat, statut, erreur) {
                    $("#div<?php echo $numeroElement;?>").html(resultat);
                }
            });
        }
        function formSuccess() {
            $("#msgSubmit").removeClass("hidden");
        }
    </script>
<?php
    echo "</ul>";
    echo "    </div> \n";
    echo "        	<script src='../js/chansonForm.js '></script>";
?>
    <script>
        function envoieFichierDansSongbook(idFichier) {
            // Récupérer l'id du songbook dans la combo
            var idSongbook = $("select[name='idSongbook'] option:selected").val() ;
            // alert('idSongbook = ' + idSongbook);
            // Appel Ajax
            $.ajax({
                type: "POST",
                url: "songbook_form.php",
                data: "id="+idSongbook+"&documentJoint="+idFichier+"&ajax=11",
                datatype: 'html', // type de la donnée à recevoir
                success: function (code_html, statut) { // success est toujours en place, bien sûr !
                    if (code_html.search("succes") > -1)
                        toastr.success("Le document a été ajouté au songbook ! <br> Le fichier a été raccroché au songbook <br> Vous pouvez raffraîchir la page pour le voir.");
                    else {
                        toastr.warning("Erreur dans l'opération...<br>Le document n'a pas pu être raccroché... :-(");
                        $("#div1").html(code_html);
                    }
                },
                error: function (resultat, statut, erreur) {
                    $("#div1").html(resultat);
                }
            });
            // Retour ok : toast vert
            // Retour ko : toast rouge
        }
    </script>
<?php
echo envoieFooter();
?>