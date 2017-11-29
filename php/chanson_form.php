<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("chanson.php");
include_once ("document.php");
$table = "chanson";
$sortie = "";

// $id, $nom, $interprete, $annee, $idUser, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0

// Chargement des donnees de la chanson si l'identifiant est fourni

if (isset ( $_POST ['id'] ))
	$id = $_POST ['id'];
if (isset ( $_GET ['id'] ) && $_GET ['id'] != "") {
	$id = $_GET ['id'];
	$donnee = chercheChanson ( $id );
	$donnee [1] = htmlspecialchars ( $donnee [1] ); // nom
	$donnee [2] = htmlspecialchars ( $donnee [2] ); // interprete
	$donnee [3] = intval ( htmlspecialchars ( $donnee [3] ) ); // annee
	$donnee [4] = intval ( htmlspecialchars ( $donnee [4] ) ); // tempo
	$donnee [5] = htmlspecialchars ( $donnee [5] ); // mesure
	$donnee [6] = htmlspecialchars ( $donnee [6] ); // pulsation
	$donnee [7] = htmlspecialchars ( $donnee [7] ); // datePub
//	$donnee [8] = $donnee [8]; // idUser
	$donnee [9] = intval ( htmlspecialchars ( $donnee [9] ) ); // hits
	$donnee [10] = htmlspecialchars ( $donnee [10] ); // tonalite
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
	$donnee [7] = convertitDateJJMMAAAA ( date ( "d/m/Y" ) ); // datePub
	$donnee [8] = $_SESSION ['id']; // idUser
	$donnee [9] = 0; // hits
	$donnee [10] = ""; // tonalite
}

if ($mode == "MAJ")
	$sortie .= "<H1> Mise à jour - " . $table . "</H1>";
if ($mode == "INS")
	$sortie .= "<H1> Création - " . $table . "</H1>";

$sortie .= "<Div class = 'centrer'>";
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
<label class='inline'>Annee :</label><INPUT TYPE='number' min='0' max='2100' NAME='fannee' VALUE='$donnee[3]' SIZE='4' MAXLENGTH='128'><br>

<script type='text/javascript' >function outputUpdate(vol) {
	document.querySelector('#tempo').value = vol;
}</script>

<label for='slider'>Tempo :</label><INPUT TYPE='range' id='fader' min='30' max='250' step='1' oninput='outputUpdate(value)' NAME='ftempo' VALUE='$donnee[4]' SIZE='3' >
<output for='fader' id='tempo'>$donnee[4]</output><br>
<label class='inline'>Mesure :</label><INPUT TYPE='TEXT' NAME='fmesure' VALUE='$donnee[5]' SIZE='4' MAXLENGTH='128'><br>
<label class='inline'>Pulsation :</label><select NAME='fpulsation' VALUE='$donnee[6]'  >
    <option value='binaire'";
    if ($donnee[6]=="binaire")
       $sortie .=  " selected";
$sortie .=  ">binaire
    </option>
    <option value='ternaire' ";
     if ($donnee[6]=="ternaire")
       $sortie .=  " selected";
       $sortie.= ">ternaire</option>
    </select>
<br>
<label class='inline'>Tonalité :</label><INPUT TYPE='TEXT' NAME='ftonalite' VALUE='$donnee[10]' SIZE='10' placeholder='ex :Am ou C ou F#'><br>
<INPUT TYPE=HIDDEN NAME='fidUser' VALUE='$donnee[8]'>
<label class='inline'>Date publication :</label><INPUT TYPE='TEXT' NAME='fdate'";
if ($_SESSION ['privilege'] <3)
 $sortie .= "disabled='disabled'  ";
$sortie.= "VALUE='" . dateMysqlVersTexte($donnee[7]) ."' SIZE='10' MAXLENGTH='128'><br>
<label class='inline'>Hits :</label><INPUT TYPE='TEXT' NAME='fhits' ";
if ($_SESSION ['privilege'] <3)
 $sortie .= "disabled='disabled'  ";
$sortie.= " VALUE='$donnee[9]' SIZE='10' MAXLENGTH='128'><br>
<INPUT TYPE=HIDDEN NAME='mode' VALUE='MAJ'>
<label class='inline'> </label><INPUT TYPE='SUBMIT' NAME='valider' VALUE=' Valider ' ><br>
</FORM>
";
$sortie .= "Pour trouver le tempo en tapant : <a href='http://www.tempotap.com' target='_blank'>tempotap.com</a><br>\n";
if ($donnee[1]){

	$sortie .= "Pour chercher la chanson sur youtube : <a href='https://www.youtube.com/results?search_query=" . urlencode($donnee[1]) . "' target='_blank'>ici</a><br>\n";
	$sortie .= "Pour chercher des images : <a href='https://www.qwant.com/?q=" . urlencode($donnee[1]) . "&t=images=' target='_blank'>ici</a><br>\n";

	$rechercheBpm = urlencode(str_replace(" ", "-", strtolower($donnee[1]) . "-" .strtolower($donnee[2])));
	$sortie .= "Pour chercher le tempo sur <a href='https://songbpm.com/$rechercheBpm' target='_blank'>songbpm</a><br>\n";

	$rechercheWikipedia = "https://fr.wikipedia.org/w/index.php?search=". urlencode(($donnee[1] . " " . $donnee[2]));
	$sortie .= "Pour chercher la chanson sur <a href='$rechercheWikipedia' target='_blank'>wikipedia</a><br>\n";
}

if ($_SESSION ['privilege'] < 3) {
	// On verrouille les champs hits, date publication
	$sortie = str_replace ( "NAME='fdate'", "NAME='fdate' disabled='disabled' ", $sortie );
	$sortie = str_replace ( "NAME='fhits'", "NAME='fhits' disabled='disabled' ", $sortie );
}

echo $sortie;
if ($mode == "MAJ") {
	?>
<h2>Liste des documents de cette chanson</h2>
<?php
	// Cherche un document et le renvoie s'il existe
	$lignes = chercheDocumentsTableId ( "chanson", $id );
	$listeDocs = "";
	// Pour chaque document
	while ( $ligneDoc = $lignes->fetch_row () ) {
		// var_dump( $ligneDoc);
		// renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
		$fichierCourt = composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
		// echo "Chanson id : $id fichier court : $fichierCourt";
		$fichier = "../data/chansons/$id/" . $fichierCourt;
		$extension = substr(strrchr($ligneDoc[1], '.'), 1);
		$icone = Image ( "../images/icones/$extension.png", 32, 32, "icone" );
		if (! file_exists ( "../images/icones/$extension.png" ))
			$icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );
		$listeDocs .= "$icone <a href= '" . htmlentities ( $fichier ) . "' target='_blank'> " . htmlentities ( $fichierCourt ) . "</a> ";
		$listeDocs .= boutonSuppression ( "chanson_post.php" . "?id=$id&idDoc=$ligneDoc[0]&mode=SUPPRDOC", $iconePoubelle, $cheminImages ) . "<br>\n";
	}
	echo $listeDocs;
	?>
<h2>Envoyer un fichier pour cette chanson sur le serveur</h2>
<form action="chanson_upload.php" method="post"
	enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000"> <input
		type="hidden" name="id" value="<?php echo $donnee[0];?>"> <label
		class="inline" for="fichier"> </label> <input type="file" id="fichier"
		name="fichierUploade" size="40"> <input type="submit" value="Envoyer">
</form>
</div>
<?php
}
echo envoieFooter ( "Bienvenue chez nous !" );
?>