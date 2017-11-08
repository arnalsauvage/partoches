<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("songbook.php");

$table = "songbook";
$fichiersDuSongbook = "";
$fichiersDuSongbook .= entreBalise("Songbooks", "H1");
$fichiersDuSongbook .= TblDebut(0);

// Gestion du paramètre de tri
if (isset ( $_GET ['tri'] ))
	$tri = $_GET ['tri'];
else
	$tri = "nom";

// Chargement de la liste des songbooks
$resultat = chercheSongbooks ( "nom", "%", $tri, true );
$numligne = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] >= 2)
	$fichiersDuSongbook .= "<BR>" . Ancre("$songbookForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouvel songbook");
// //////////////////////////////////////////////////////////////////////ADMIN

$fichiersDuSongbook .= Image($iconeAttention, "100%", 1, 1);
$fichiersDuSongbook .= TblDebut(0);
$fichiersDuSongbook .= TblDebutLigne() . TblCellule("Tri") . TblCellule(Ancre("?tri=nom", "Nom")) . TblCellule(Ancre("?tri=description", "Description")) . TblCellule(Ancre("?tri=date", "Date")) . TblCellule(Ancre("?tri=hits", "Hits")) . TblFinLigne();

while ( $ligne = $resultat->fetch_row () ) {
	$numligne ++;
	$fichiersDuSongbook .= TblDebutLigne();
	// Songbook : 	[0]id 	[1]nom 	[2]description 	[3]date  	[4]image 	[5]hits 	[6]idUser
	if ($ligne [4])
		// //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
		if ($_SESSION ['privilege'] >= 2)
			$fichiersDuSongbook .= TblCellule(Ancre($songbookForm . "?id=$ligne[0]", Image(($cheminImagesSongbook . $ligne[0] . "/" . $ligne [4]), 32, 32, "couverture"))); // image
		else
			$fichiersDuSongbook .= TblCellule(Image(($cheminImagesSongbook . $ligne[0] . "/" . $ligne [4]), 32, 32)); // image
	else
		$fichiersDuSongbook .= TblCellule(Ancre($songbookForm . "?id=$ligne[0]", "voir"));

	$fichiersDuSongbook .= TblCellule(Ancre($songbookVoir . "?id=$ligne[0]", entreBalise($ligne [1], "H2"))); // Nom

	$fichiersDuSongbook .= TblCellule("  " . $ligne [2]); // description
	$fichiersDuSongbook .= TblCellule(" " . dateMysqlVersTexte($ligne [3], 0)); // date
	$fichiersDuSongbook .= TblCellule("    " . $ligne [5] . " hit(s)"); // hits
	                                                        
	// //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
	if ($_SESSION ['privilege'] >= 2) {
		$fichiersDuSongbook .= TblCellule(boutonSuppression($songbookGet . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages));
		// //////////////////////////////////////////////////////////////////////ADMIN

		$fichiersDuSongbook .= TblFinLigne();
	}
}
$fichiersDuSongbook .= TblFin();

$fichiersDuSongbook .= Image($iconeAttention, "100%", 1, 1);
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] >= 2)
	$fichiersDuSongbook .= "<BR>" . Ancre("?page=$songbookForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouvel songbook");
// //////////////////////////////////////////////////////////////////////ADMIN
$fichiersDuSongbook .= envoieFooter("Bienvenue chez nous !");
echo $fichiersDuSongbook;
?>