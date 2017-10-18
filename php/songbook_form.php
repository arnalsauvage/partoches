<?php
include_once ("lib/utilssi.php");
include ("menu.php");
include ("songbook.php");
$table = "songbook";
$sortie = "";

// Chargement des donnees de la songbook si l'identifiant est fourni
if (isset ( $_GET ['id'] ) && $_GET ['id'] != "") {
	$donnee = cherchesongbook ( $_GET ['id'] );
	$donnee [1] = htmlspecialchars($donnee [1]);
	$donnee [2] = htmlspecialchars($donnee [2]);
	$donnee [3] = dateMysqlVersTexte($donnee [3]);
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
$f->champTexte ( "Nom :", "fnom", $donnee [1], 64, 128  );
$f->champTexte ( "Description :", "fdescription", $donnee [2], 64, 128 );
$f->champTexte ( "Date :", "fdate", $donnee [3],10,10);
$f->champTexte ( "Image :", "fimage", $donnee [4],64,64 );
$f->champTexte ( "Hits :", "fhits", $donnee [5],10,10);
$f->champCache ( "mode", $mode );
$f->champValider ( " Valider ", "valider" );
$sortie .= $f->fin ();

echo $sortie;
if ($mode == "MAJ") {
	?>

<h2>Envoyer un fichier pour ce songbook sur le serveur</h2>
<form action="songbook_upload.php" method="post"
	enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
	<input type="hidden" name="id" value="<?php echo $donnee[0];?>">
	<label class="inline" for="fichier"> </label> <input type="file" id="fichier" name="fichierUploade" size="40">
	<input type="submit" value="Envoyer">
</form>
</div>
<?php
}
echo envoieFooter ( "Bienvenue chez nous !" );
?>