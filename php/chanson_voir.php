<?php
include_once ("lib/utilssi.php");
include ("menu.php");
include ("chanson.php");
include ("document.php");
$table = "chanson";
$sortie = "";
$monImage = "";

$retour = fichiersChanson ( $_GET ['id'] );

foreach ( $retour as $fichier ) {
	// echo $fichier [0] . " " . $fichier [1] . " " . $fichier [2] . " <br>";
	if (stristr ( $fichier [1], "jpg" ) || stristr ( $fichier [1], "png" ))
		$monImage = $fichier;
}

$donnee = chercheChanson ( $_GET ['id'] );
$sortie .= "<h2>$donnee[1]</h2>";
if ("" != $monImage) {
	$sortie .= Image ( $monImage [0] . $monImage [1], 200, "", "pochette" );
}
$sortie .= $donnee [2] . "-" . $donnee [3] . "<br>\n";

$sortie .= "<h2> Liste des documents attachés à cette chanson</h2>";

// Cherche un document et le renvoie s'il existe
$result = chercheDocumentsTableId ( "chanson", $id );

// Pour chaque document
while ( $ligne = $result->fetch_row () ) {
	$fichierCourt = composeNomVersion ( $ligne [1], $ligne [4] );
	$fichier = "../data/chansons/" . $_GET ['id'] . "/" . composeNomVersion ( $ligne [1], $ligne [4] );
	$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
	if (! file_exists ( "../images/icones/" . $fichier [2] . ".png" ))
		$icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );
		$sortie .= "$icone <a href= '" . htmlentities ( $fichier ) . "' target='_blank'> " . htmlentities ($fichierCourt) . "</a> <br>\n";
}

// foreach ( $retour as $fichier ) {

// }

$sortie .= envoieFooter ( "Bienvenue chez nous !" );
echo $sortie;
// TODO ajouter un bouton : supprimer fichiers
?>