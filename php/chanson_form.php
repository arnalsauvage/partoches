<?php
include_once("lib/utilssi.php");
include_once("menu.php");
include_once("chanson.php");
include_once("document.php");
include_once("lib/formulaire.php");
$table = "chanson";
$sortie = "";

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on le redirige vers la page _voir
if ($_SESSION ['privilege'] < 2) {
    $urlRedirection = $table . "_voir.php";
    if (isset ($_GET ['id']))
        $urlRedirection .= "?id=" . $_GET ['id'];
    redirection($urlRedirection);
}

// $id, $nom, $interprete, $annee, $idUser, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0

// Chargement des donnees de la chanson si l'identifiant est fourni

if (isset ($_POST ['id']))
    $id = $_POST ['id'];
if (isset ($_GET ['id']) && $_GET ['id'] != "") {
    $id = $_GET ['id'];
    $donnee = chercheChanson($id);
    $donnee [1] = htmlspecialchars($donnee [1], ENT_QUOTES); // nom
    $donnee [2] = htmlspecialchars($donnee [2], ENT_QUOTES); // interprete
    $donnee [3] = intval(htmlspecialchars($donnee [3], ENT_QUOTES)); // annee
    $donnee [4] = intval(htmlspecialchars($donnee [4], ENT_QUOTES)); // tempo
    $donnee [5] = htmlspecialchars($donnee [5], ENT_QUOTES); // mesure
    $donnee [6] = htmlspecialchars($donnee [6], ENT_QUOTES); // pulsation
    $donnee [7] = htmlspecialchars($donnee [7], ENT_QUOTES); // datePub
    $donnee [8] = $donnee [8]; // idUser
    $donnee [9] = intval(htmlspecialchars($donnee [9], ENT_QUOTES)); // hits
    $donnee [10] = htmlspecialchars($donnee [10], ENT_QUOTES); // tonalite
    $mode = "MAJ";
} else {
    $mode = "INS";
    $donnee [0] = 0; // id
    $donnee [1] = ""; // nom
    $donnee [2] = ""; // interprete
    $donnee [3] = "1964"; // annee
    $donnee [4] = "90"; // tempo
    $donnee [5] = "4/4"; // mesure
    $donnee [6] = ""; // pulsation
    $donnee [7] = convertitDateJJMMAAAA(date("d/m/Y")); // datePub
    $donnee [8] = $_SESSION ['id']; // idUser
    $donnee [9] = 0; // hits
    $donnee [10] = ""; // tonalite
}

$sortie .= "<div class = 'centrer'>";
if ($mode == "MAJ")
    $sortie .= "<H1> Mise à jour - " . $table . "</H1>";
if ($mode == "INS")
    $sortie .= "<H1> Création - " . $table . "</H1>";

// Création du formulaire
/*$f = new Formulaire ( "POST", $table . "_post.php", $sortie );
$f->champCache ( "id", $donnee [0] );
// TODO : La longueur du champ n'est pas prise en compte dans formulaire!
$f->champTexte ( "Nom :", "fnom", $donnee [1], 64, 128 );
$f->champTexte ( "Interprète :", "finterprete", $donnee [2], 64, 128 );
$f->champTexte ( "Annee :", "fannee", $donnee [3], 4, 4 );
$f->champTexte ( "Tempo :", "ftempo", $donnee [4], 4, 4 );
$f->champTexte ( "Mesure :", "fmesure", $donnee [5], 4, 4 );
$f->champTexte ( "Pulsation :", "fpulsation", $donnee [6], 10, 10 );
$f->champTexte ( "Tonalité :", "ftonalite", $donnee [10], 10, 10 );
$f->champCache ( "fidUser", $donnee [8]);
$f->champTexte ( "Date publication :", "fdate", dateMysqlVersTexte ( $donnee [7] ), 10, 10 );
$f->champTexte ( "Hits :", "fhits", $donnee [9], 10, 10 );
$f->champCache ( "mode", $mode );
$f->champValider ( " Valider ", "valider" );
$sortie .= $f->fin ();*/
$sortie .= "
<FORM  METHOD='POST' ACTION='chanson_post.php' NAME='Form'>
<INPUT TYPE=HIDDEN NAME='id' VALUE='$donnee[0]'>
<label class='inline'>Nom :</label><INPUT TYPE='TEXT' NAME='fnom' VALUE='$donnee[1]' SIZE='64' MAXLENGTH='128' placeholder='titre de la chanson'><br>
<label class='inline'>Interprète :</label><INPUT TYPE='TEXT' NAME='finterprete' VALUE='$donnee[2]' SIZE='64'  placeholder='interprète'><br>
<label class='inline'>Annee :</label><INPUT TYPE='number' min='0' max='2100' NAME='fannee' VALUE='$donnee[3]' SIZE='4'><br>

<script>function outputUpdate(vol) {
	document.querySelector('#tempo').value = vol;
}</script>

<label for='fader'>Tempo :</label><INPUT TYPE='range' id='fader' min='30' max='250' step='1' oninput='outputUpdate(value)' NAME='ftempo' VALUE='$donnee[4]' SIZE='3' >
<output for='fader' id='tempo'>$donnee[4]</output><br>
<label class='inline'>Mesure :</label><INPUT TYPE='TEXT' NAME='fmesure' VALUE='$donnee[5]' SIZE='4' MAXLENGTH='128'><br>
<label class='inline'>Pulsation :</label><select NAME='fpulsation' >
    <option value='binaire'";
if ($donnee[6] == "binaire")
    $sortie .= " selected";
$sortie .= ">binaire
    </option>
    <option value='ternaire' ";
if ($donnee[6] == "ternaire")
    $sortie .= " selected";
$sortie .= ">ternaire</option>
    </select>
  ";
// TODO : ajouter un combo des utilisateurs pour l'admin
//  $listeUsers =
//  $sortie .= champSELECT("idUser", $listeUSers, $idUser );
//<INPUT TYPE=HIDDEN NAME='fidUser' VALUE='$donnee[8]'>
$sortie .= "<br>
<label class='inline'>Tonalité :</label><INPUT TYPE='TEXT' NAME='ftonalite' VALUE='$donnee[10]' SIZE='10' placeholder='ex :Am ou C ou F#'><br>
<label class='inline'>Date publication :</label><INPUT TYPE='TEXT' NAME='fdate'";

$sortie .= " VALUE='" . dateMysqlVersTexte($donnee[7]) . "' SIZE='10' MAXLENGTH='128'><br>
<label class='inline'>Hits :</label><INPUT TYPE='number' NAME='fhits'  VALUE='$donnee[9]' SIZE='10'><br>";

$sortie .= "<label class='inline'>Utilisateur :</label>" . selectUtilisateur("nom", "%", "login", true, $donnee[8]);

$sortie .= "<INPUT TYPE=HIDDEN NAME='mode' VALUE='$mode'>
<label class='inline'> </label><INPUT TYPE='SUBMIT' NAME='valider' VALUE=' Valider ' ><br>";

$sortie .= "
</FORM>
";

if ($donnee[1]) {


    $sortie .= "Pour chercher la chanson sur youtube : <a href='https://www.youtube.com/results?search_query=" . urlencode($donnee[1]) . "' target='_blank'>ici</a><br>\n";
    $sortie .= "Pour chercher des images : <a href='https://www.qwant.com/?q=" . urlencode($donnee[1]) . "&amp;t=images=' target='_blank'>ici</a><br>\n";

    $rechercheBpm = htmlentities(str_replace(" ", "-", strtolower($donnee[1])));
    $sortie .= "Pour chercher le tempo sur <a href='https://songbpm.com/$rechercheBpm' target='_blank'>songbpm</a><br>\n";

    $rechercheWikipedia = "https://fr.wikipedia.org/w/index.php?search=" . urlencode(($donnee[1] . " " . $donnee[2]));
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
        $lignes = chercheDocumentsTableId("chanson", $id);
        $listeDocs = "";
        // Pour chaque document
        while ($ligneDoc = $lignes->fetch_row()) {
            // var_dump( $ligneDoc);
            $idDoc = $ligneDoc [0];
            // renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
            $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);
            // echo "Chanson id : $id fichier court : $fichierCourt";
            $fichier = "../data/chansons/$id/" . htmlentities($fichierCourt);
            $extension = substr(strrchr($ligneDoc[1], '.'), 1);
            $icone = Image("../images/icones/$extension.png", 32, 32, "icone");
            if (!file_exists("../images/icones/$extension.png"))
                $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
            $listeDocs .= "<li class='fichiers'> <a href= '" . urlencode($fichier) . "' target='_blank'> $icone </a> ";
            $listeDocs .= "(" . intval($ligneDoc [2] / 1024) . " ko )";
            $listeDocs .= "<label>" . htmlentities($fichierCourt) . "</label>
		<input size='16' id='$idDoc' name='user' value='" . htmlentities($fichierCourt) . "' placeholder='nomDeFichier.ext' style='display:none;'>
		<button name='renommer' style='display:none;'>renommer</button>
  <button style='display:none;'>x</button>";
            $listeDocs .= boutonSuppression("chanson_post.php" . "?id=$id&idDoc=$ligneDoc[0]&mode=SUPPRDOC", $iconePoubelle, $cheminImages) . "</li>\n";
        }
        echo $listeDocs;
        ?>
    </ul>
    <h2>Envoyer un fichier pour cette chanson sur le serveur</h2>
    <form action="chanson_upload.php" method="post"
          enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
        <input type="hidden" name="id" value="<?php echo $donnee[0]; ?>">
        <label class="inline" for="fichier"> </label>
        <input type="file" id="fichier" name="fichierUploade" size="40">
        <input type="submit" value="Envoyer">
    </form>

    <h2> Corbeille des fichiers effacés</h2>
    <?php
    $fichiersEnBdd = [];
    $resultat = chercheDocumentsTableId("chanson", "$id");
    while (($fichierEnBdd = $resultat->fetch_row())) {
        array_push($fichiersEnBdd, $fichierEnBdd);
    }

    $fichiersSurDisque = fichiersChanson($id); // repertoire nom extension
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
        if ($fichierOk == false) {
            $nbFichiersKO++;
            echo "Fichier corbeille : " . $fichierSurDisque[1] . " non répertorié par la Bdd ";
            echo boutonSuppression("chanson_post.php?nomFic=" . urlencode("../data/chansons/" . $id . "/" . $fichierSurDisque[1]) . "&mode=SUPPRFIC&id=$id", $iconePoubelle, $cheminImages) . "<br>";
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
                            if (code_html.search("n'a pas été traité.") == -1)
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
        echo "La corbeille est vide pour cette chanson.\n";
}
echo "    </div> \n";
echo "        	<script src='../js/chansonForm.js '></script>";
echo envoieFooter();

?>