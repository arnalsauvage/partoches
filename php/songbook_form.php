<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("songbook.php");
include_once ("document.php");
include_once ("lienDocSongbook.php");
$table = "songbook";
$sortie = "";

// Traitement de l'ajout de document
if (isset ( $_POST ['id'] ) && (isset ( $_POST ['documentJoint'] ))) {
	
	$id = $_POST ['id'];
	ordonneLiensSongbook ( $id );
	creeLienDocSongbook ( $_POST ['documentJoint'], $_POST ['id'] );
	$id = $_POST ['id'];
}

// Chargement des donnees de la songbook si l'identifiant est fourni
if ($id || (isset ( $_GET ['id'] ) && $_GET ['id'] != "")) {
	if (isset ( $_GET ['id'] ))
		$id = $_GET ['id'];
	$donnee = cherchesongbook ( $id );
	$donnee [1] = htmlspecialchars ( $donnee [1] );
	$donnee [2] = htmlspecialchars ( $donnee [2] );
	$donnee [3] = dateMysqlVersTexte ( $donnee [3] );
//	$donnee [4] = $donnee [4];
//	$donnee [5] = $donnee [5];
	$mode = "MAJ";
	ordonneLiensSongbook ( $id );
}
else {
	$mode = "INS";
	$donnee [0] = 0;
	$donnee [1] = "";
	$donnee [2] = "";
	$donnee [3] = "01/01/1970";
	$donnee [4] = "";
	$donnee [5] = 0;
}

if ($mode == "MAJ")
	$sortie .= "<H1> Mise à jour - " . $table . "</H1>";
if ($mode == "INS")
	$sortie .= "<H1> Création - " . $table . "</H1>";

$sortie .= "<Div class = 'centrer'>";
// Création du formulaire
$f = new Formulaire ( "POST", $table . "_get.php", $sortie );
$f->champCache ( "id", $donnee [0] );
// TODO : La longueur du champ n'est pas prise en compte dans formulaire!
$f->champTexte ( "Nom :", "fnom", $donnee [1], 64, 128 );
$f->champTexte ( "Description :", "fdescription", $donnee [2], 64, 128 );
$f->champTexte ( "Date :", "fdate", $donnee [3], 10, 10 );
$f->champTexte ( "Image :", "fimage", $donnee [4], 64, 64 );
$f->champTexte ( "Hits :", "fhits", $donnee [5], 10, 10 );
$f->champCache ( "mode", $mode );
$f->champValider ( " Valider ", "valider" );
$sortie .= $f->fin ();

if ($_SESSION ['privilege'] < 3) {
	// On verrouille les champs hits, date publication
	$sortie = str_replace ( "NAME='fdate'", "NAME='fdate' disabled='disabled' ", $sortie );
	$sortie = str_replace ( "NAME='fhits'", "NAME='fhits' disabled='disabled' ", $sortie );
}

echo $sortie;
if ($mode == "MAJ") {
	?>

<h2>Envoyer un fichier pour ce songbook sur le serveur</h2>
<form action="songbook_upload.php" method="post"
	enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000"> <input
		type="hidden" name="id" value="<?php echo $donnee[0];?>"> <label
		class="inline" for="fichier"> </label> <input type="file" id="fichier"
		name="fichierUploade" size="40"> <input type="submit" value="Envoyer">
</form>

<h2>Liste des documents dans ce songbook</h2>
<?php
	$lignes = chercheLiensDocSongbook ( 'idSongbook', $id, "ordre", true );
	$listeDocs = "";
	while ( $ligne = $lignes->fetch_row () ) {
		$ligneDoc = chercheDocument ( $ligne [1] );
		$fichierCourt = composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
		$fichier = "../data/chansons/" . $ligneDoc [6] . "/" . composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
		$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
		if (! file_exists ( "../images/icones/" . $fichier [2] . ".png" ))
			$icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );
		$listeDocs .= "$icone <a href= '" . htmlentities ( $fichier ) . "' target='_blank'> " . htmlentities ( $fichierCourt ) . "</a> ";
// TODO : recopier ce bouton dans chansonform
		$listeDocs .= boutonSuppression ( $songbookGet . "?idSongbook=$id&idDoc=$ligneDoc[0]&mode=SUPPRDOC", $iconePoubelle, $cheminImages ) . "<br>\n";
	}
	echo $listeDocs;
	?>

<h2>Insérer un document dans ce songbook</h2>
<form action="songbook_form.php" method="post" name="form2">
<?php
	echo selectDocument ( "nomTable", "chanson", "id", false );
	?>
	<input type="hidden" name="id" value="<?php echo $donnee[0];?>"> <input
		type="submit" value="Envoyer">
</form>
</div>
<?php
}
echo envoieFooter ( "Bienvenue chez nous !" );
?>