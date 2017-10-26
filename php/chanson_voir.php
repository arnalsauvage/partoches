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
if ($donnee <> null){
$nom = $donnee [1]; // nom
$interprete = $donnee [2]; // interprete
$annee = $donnee [3]; // annee
$tempo = $donnee [4]; // tempo
$mesure = $donnee [5]; // mesure
$pulsation = $donnee [6]; // pulsation
$date_publication =  dateMysqlVersTexte ( $donnee [7]); // date_publication
$idauteur = $donnee [8]; // idAuteur
$nomAuteur = chercheUtilisateur($idauteur);
$nomAuteur = $nomAuteur[3];
$hits = $donnee [9] +1 ; // hits
$tonalite = $donnee [10]; // tonalite
}

$sortie .= "<h2>$donnee[1]</h2>";
if ("" != $monImage) {
	$sortie .= Image ( $monImage [0] . $monImage [1], 200, "", "pochette" );
}
$sortie .= " $interprete - $annee <br>\n";
$sortie .= "Tonalité : $tonalite, Tempo : $tempo, mesure : $mesure, pulsation : $pulsation <br>\n";
$sortie .= "Publiée le  :$date_publication, par $nomAuteur, affichée $hits fois. <br>\n";
$sortie .= "<h2> Liste des documents attachés à cette chanson</h2>";

// Cherche un document et le renvoie s'il existe
$result = chercheDocumentsTableId ( "chanson", $id );

augmenteHits ($table,$id);

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