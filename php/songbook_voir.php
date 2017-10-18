<?php
include_once ("lib/utilssi.php");
include ("menu.php");
include ("songbook.php");
$table = "songbook";
$sortie = "";
$monImage = "";

$retour = fichierssongbook ( $_GET ['id'] );

foreach ( $retour as $fichier ) {
//	echo $fichier [0] . " " . $fichier [1] . " " . $fichier [2] . " <br>";
	if (stristr ( $fichier [1], "jpg" ) || stristr ( $fichier [1], "png" ))
		$monImage = $fichier;
}

$donnee = cherchesongbook ( $_GET ['id'] );
$sortie .= "<h2>$donnee[1]</h2>"; // Titre
if ("" != $monImage) {
	$sortie .= Image ( $monImage [0] . $monImage [1], 200, "", "pochette" );
}
$sortie .= $donnee [2] . "-" . $donnee [3] ."-". $donnee [5] . " hit(s)<br>\n";

foreach ( $retour as $fichier ) {
	$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
	if (! file_exists (  "../images/icones/" . $fichier [2] . ".png"))
		$icone = Image ( "../images/icones/fichier.png" , 32, 32, "icone" );
	$sortie .= "$icone <a href= '" . htmlentities($fichier [0] . $fichier [1]) . "' target='_blank'> " . htmlentities($fichier[1]) . "</a> <br>\n";
}

$sortie .= envoieFooter ( "Bienvenue chez nous !" );
echo $sortie;
// TODO ajouter un bouton : supprimer fichiers
?>