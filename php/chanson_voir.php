<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("chanson.php");
include_once ("document.php");
$table = "chanson";
$sortie = "";
$monImage = "";

$fichiersDuSongbook = fichiersChanson ( $_GET ['id'] );

// foreach ( $fichiersDuSongbook as $fichier ) {
// 	// echo $fichier [0] . " " . $fichier [1] . " " . $fichier [2] . " <br>";
// 	if (stristr ( $fichier [1], "jpg" ) || stristr ( $fichier [1], "png" ))
// 		$monImage = $fichier;
// }

// On choisit une des images du songbook
$monImage = imageTableId("songbook", $_GET ['id']);

$donnee = chercheChanson ( $_GET ['id'] );
if ($donnee != null) {
	$nom = $donnee [1]; // nom
	$interprete = $donnee [2]; // interprete
	$annee = $donnee [3]; // annee
	$tempo = $donnee [4]; // tempo
	$mesure = $donnee [5]; // mesure
	$pulsation = $donnee [6]; // pulsation
	$datePub = dateMysqlVersTexte ( $donnee [7] ); // datePub
	$idauteur = $donnee [8]; // idUser
	$nomAuteur = chercheUtilisateur ( $idauteur );
	$nomAuteur = $nomAuteur [3];
	$hits = $donnee [9] + 1; // hits
	$tonalite = $donnee [10]; // tonalite
}

$sortie .= "<h2>$donnee[1]</h2>";
if ($_SESSION ['privilege'] > 1)
	$sortie .= Ancre ( "chanson_form.php?id=" . $_GET ['id'], Image ( $cheminImages . $iconeEdit, 32, 32 ) ); // Nom));

if ("" != $monImage) {
	$sortie .= Image ( $monImage [0] . $monImage [1], 200, "", "pochette" );
}
$sortie .= " $interprete - $annee <br>\n";
$sortie .= "Tonalité : $tonalite, Tempo : $tempo, mesure : $mesure, pulsation : $pulsation <br>\n";
$sortie .= "Publiée le  :$datePub, par $nomAuteur, affichée $hits fois. <br>\n";

// Propose des recherches sur la chanson
$sortie .= "<a href='https://www.youtube.com/results?search_query=" . urlencode ( $donnee [1] ) . "' target='_blank'><img src='https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/YouTube_Logo_2017.svg/280px-YouTube_Logo_2017.svg.png' width='64'></a>\n";
$rechercheWikipedia = "https://fr.wikipedia.org/w/index.php?search=" . urlencode ( ($donnee [1] . " " . $donnee [2]) );
$sortie .= "<a href='$rechercheWikipedia' target='_blank'><img src='https://fr.wikipedia.org/static/images/project-logos/frwiki.png' width='64'></a><br>\n";

$sortie .= "<h2> Liste des documents attachés à cette chanson</h2>";

// Cherche un document et le renvoie s'il existe
$result = chercheDocumentsTableId ( "chanson", $id );

augmenteHits ( $table, $id );

// Pour chaque document
while ( $ligne = $result->fetch_row () ) {
	$fichierCourt = composeNomVersion ( $ligne [1], $ligne [4] );
	// $fichier = "../data/chansons/" . $_GET ['id'] . "/" . composeNomVersion ( $ligne [1], $ligne [4] );
	$extension = substr ( strrchr ( $ligne [1], '.' ), 1 );
	$icone = Image ( "../images/icones/" . $extension . ".png", 32, 32, "icone" );
	if (! file_exists ( "../images/icones/" . $extension . ".png" ))
		$icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );
	
		$sortie .= "$icone <a href= '".lienUrlAffichageDocument($ligne [0])."' target='_blank'> " . htmlentities ( $fichierCourt ) . "</a> <br>\n";
}

// foreach ( $retour as $fichier ) {

// }

$sortie .= envoieFooter ( "Bienvenue chez nous !" );
echo $sortie;
?>