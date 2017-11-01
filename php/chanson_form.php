<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("chanson.php");
include_once ("document.php");
$table = "chanson";
$sortie = "";

// $id, $nom, $interprete, $annee, $idAuteur, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0

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
	$donnee [7] = htmlspecialchars ( $donnee [7] ); // date_publication
	$donnee [8] = $donnee [8]; // idAuteur
	$donnee [9] = intval ( htmlspecialchars ( $donnee [9] ) ); // hits
	$donnee [10] = htmlspecialchars ( $donnee [10] ); // tonalite
	$mode = "MAJ";
} else {
	$mode = "INS";
	$donnee [0] = 0; // id
	$donnee [1] = ""; // nom
	$donnee [2] = ""; // interprete
	$donnee [3] = ""; // annee
	$donnee [4] = ""; // tempo
	$donnee [5] = "4/4"; // mesure
	$donnee [6] = ""; // pulsation
	$donnee [7] = convertitDateJJMMAAAA ( date ( "d/m/Y" ) ); // date_publication
	$donnee [8] = $_SESSION ['id']; // idAuteur
	$donnee [9] = 0; // hits
	$donnee [10] = ""; // tonalite
}

if ($mode == "MAJ")
	$sortie .= "<H1> Mise à jour - " . $table . "</H1>";
if ($mode == "INS")
	$sortie .= "<H1> Création - " . $table . "</H1>";

$sortie .= "<Div class = 'centrer'>";
// Création du formulaire
$f = new Formulaire ( "POST", $table . "_post.php", $sortie );
$f->champCache ( "id", $donnee [0] );
// TODO : La longueur du champ n'est pas prise en compte dans formulaire!
$f->champTexte ( "Nom :", "fnom", $donnee [1], 64, 128 );
$f->champTexte ( "Interprète :", "finterprete", $donnee [2], 64, 128 );
$f->champTexte ( "Annee :", "fannee", $donnee [3], 4, 4 );
$f->champTexte ( "Tempo :", "ftempo", $donnee [4], 4, 4 );
$f->champTexte ( "Mesure :", "fmesure", $donnee [5], 4, 4 );
$f->champTexte ( "Pulsation :", "fpulsation", $donnee [6], 10, 10 );
$f->champTexte ( "Date publication :", "fdate", dateMysqlVersTexte ( $donnee [7] ), 10, 10 );
$f->champCache ( "fidAuteur", $donnee [8], 10, 10 );
$f->champTexte ( "Hits :", "fhits", $donnee [9], 10, 10 );
$f->champTexte ( "Tonalité :", "ftonalite", $donnee [10], 10, 10 );
$f->champCache ( "mode", $mode );
$f->champValider ( " Valider ", "valider" );
$sortie .= $f->fin ();

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
		$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
		if (! file_exists ( "../images/icones/" . $fichier [2] . ".png" ))
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