<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("playlist.php");

$table = "playlist";
$fichiersDuPlaylist = "";
$fichiersDuPlaylist .= entreBalise("Playlists", "H1");
$fichiersDuPlaylist .= TblDebut(0);

// Gestion du paramètre de tri
if (isset ($_GET ['tri'])) {
	$tri = $_GET ['tri'];
	$ordreAsc = true;
} else {
	if (isset ($_GET ['triDesc'])) {
		$tri = $_GET ['triDesc'];
		$ordreAsc = false;
	} else {
		$tri = "date";
		$ordreAsc = false;
	}
}

// Chargement de la liste des playlists
$resultat = cherchePlaylists("nom", "%", $tri, $ordreAsc);
$numligne = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] >= 2)
	$fichiersDuPlaylist .= "<BR>" . Ancre("$playlistForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouvel playlist");
// //////////////////////////////////////////////////////////////////////ADMIN

$fichiersDuPlaylist .= Image($iconeAttention, "100%", 1, 1);
$fichiersDuPlaylist .= TblDebut(0);
TblCellule(Ancre("?tri=hits", "Hits")) . TblFinLigne();

$fichiersDuPlaylist .= TblDebut(0);
$fichiersDuPlaylist .= TblDebutLigne() . TblCellule("  Tri  ");
$fichiersDuPlaylist .= titreColonne("Nom", "nom");
$fichiersDuPlaylist .= titreColonne("Description", "description");
$fichiersDuPlaylist .= titreColonne("Date", "date");
$fichiersDuPlaylist .= titreColonne("Vues", "hits");
$fichiersDuPlaylist .= TblFinLigne();

while ( $ligne = $resultat->fetch_row () ) {
	$numligne ++;
	$fichiersDuPlaylist .= TblDebutLigne();
	// Playlist : 	[0]id 	[1]nom 	[2]description 	[3]date  	[4]image 	[5]hits 	[6]idUser
	if ($ligne [4])
		// //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
		if ($_SESSION ['privilege'] >= 2)
			$fichiersDuPlaylist .= TblCellule(Ancre($playlistForm . "?id=$ligne[0]", "modifier"));
		else
			$fichiersDuPlaylist .= TblCellule(Image(($cheminImagesPlaylist . $ligne[0] . "/" . $ligne [4]), 32, 32)); // image
	else
		$fichiersDuPlaylist .= TblCellule(Ancre($playlistForm . "?id=$ligne[0]", "voir"));

	$fichiersDuPlaylist .= TblCellule(Ancre($playlistVoir . "?id=$ligne[0]", entreBalise($ligne [1], "H2"))); // Nom

	$fichiersDuPlaylist .= TblCellule("  " . $ligne [2]); // description
	$fichiersDuPlaylist .= TblCellule(" " . dateMysqlVersTexte($ligne [3], 0)); // date
	$fichiersDuPlaylist .= TblCellule("  -  " . $ligne [5] . " hit(s)"); // hits
	                                                        
	// //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
	if ($_SESSION ['privilege'] >= 2) {
		$fichiersDuPlaylist .= TblCellule(boutonSuppression($playlistGet . "?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages));
		// //////////////////////////////////////////////////////////////////////ADMIN

		$fichiersDuPlaylist .= TblFinLigne();
	}
}
$fichiersDuPlaylist .= TblFin();

$fichiersDuPlaylist .= Image($iconeAttention, "100%", 1, 1);
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION ['privilege'] >= 2)
	$fichiersDuPlaylist .= "<BR>" . Ancre("?page=$playlistForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer une nouvelle playlist");
// //////////////////////////////////////////////////////////////////////ADMIN
$fichiersDuPlaylist .= envoieFooter();
echo $fichiersDuPlaylist;

function titreColonne($libelle, $nomRubrique)
{
	$chaine = TblCellule(Ancre("?tri=$nomRubrique", "<span class='glyphicon glyphicon-chevron-up'> ") . "  $libelle   " . Ancre("?triDesc=$nomRubrique", "  <span class='glyphicon glyphicon-chevron-down'> "));
	return $chaine;
}
