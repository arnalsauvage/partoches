<?php
include_once ("lib/utilssi.php");
include ("menu.php");
include ("chanson.php");
$table = "chanson";
$sortie = "";

// Chargement des donnees de la chanson si l'identifiant est fourni
if (isset ( $_GET ['id'] ) && $_GET ['id'] != "") {
	$donnee = chercheChanson($_GET ['id']);
	$mode = "MAJ";
} else {
	$mode = "INS";
	$donnee [0] = 0;
	$donnee [1] = "";
	$donnee [2] = "";
	$donnee [3] = "";
}

if ($mode == "MAJ")
	$sortie .= "<H1> Mise à jour - " . $table . "</H1>";
else
	$sortie .= "<H1> Création - " . $table . "</H1>";

// Création du formulaire
$f = new Formulaire ( "POST", $table . "_get.php", $sortie );
$f->champCache ( "id", $donnee [0] );
// TODO : La longueur du champ n'est pas prise en compte dans formulaire!
$f->champTexte ( "Nom :", "fnom", $donnee[1], 64, 128 );
$f->champTexte ( "Interprète :", "finterprete", $donnee[2], 64, 128 );
$f->champTexte ( "Annee :", "fannee", $donnee [3], 4, 4 );
$f->champCache ( "mode", $mode );
$f->champValider ( "Valider la saisie", "valider" );
$sortie .= $f->fin();

echo $sortie;
?>

<h2>Envoyer un fichier pour cette chanson sur le serveur</h2>   
<form action="chanson_upload.php" method="post" enctype="multipart/form-data">
   <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
   <input type="hidden" name= "id" value = "<?php echo $donnee[0];?>" >
  <label for="fichier"> </label>

   <input type="file" id ="fichier" name="fichierUploade" size="40">
   <input type="submit" value="Envoyer">
</form>
<?php 
echo envoieFooter ( "Bienvenue chez nous !" );
?>