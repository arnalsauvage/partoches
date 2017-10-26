<?php
include_once ("lib/utilssi.php");
include ("menu.php");
include ("songbook.php");
include ("document.php");
include ("lienDocSongbook.php");
$table = "songbook";
$sortie = "";

// Traitement de l'ajout de document
if (isset ( $_POST ['id'] ) && (isset ( $_POST ['documentJoint'] ))) {
	creeModifielienDocSongbook ( 0, $_POST ['documentJoint'], $_POST ['id'], 5 );
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
	$donnee [4] = $donnee [4];
	$donnee [5] = $donnee [5];
	$mode = "MAJ";
} else {
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
else
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
	$lignes = cherchelienDocSongbooks ( 'idSongbook', $id, "ordre", true );
	$listeDocs = "";
	while ( $ligne = $lignes->fetch_row () ) {
		$ligneDoc = chercheDocument ( $ligne [1] );
		$fichierCourt = composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
		$fichier = "../data/chansons/" .$ligneDoc [6]. "/" . composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
		$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
		if (! file_exists ( "../images/icones/" . $fichier [2] . ".png" ))
			$icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );
		$listeDocs .= "$icone <a href= '" . htmlentities ( $fichier ) . "' target='_blank'> " . htmlentities ( $fichierCourt ) . "</a> <br>\n";
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