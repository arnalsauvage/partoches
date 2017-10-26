<?php
include_once ("lib/utilssi.php");
include_once "lib/configMysql.php";

// Fonctions de gestion de la chanson

// Cherche les chansons correspondant à un critère
function chercheChansons($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true) {
	$maRequete = "SELECT * FROM chanson WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
	if ($bTriAscendant == false)
		$maRequete .= " DESC";
	else
		$maRequete .= " ASC";
	// echo "ma requete : " . $maRequete;
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème chercheChanson #3 : " .  $_SESSION ['mysql']->error  );
	return $result;
}

// Cherche un chanson et le renvoie s'il existe
function chercheChanson($id) {
	$maRequete = "SELECT * FROM chanson WHERE chanson.id = '$id'";
	$result = $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème chercheChanson #1 : " .  $_SESSION ['mysql']->error  );
	// renvoie la lisgne sélectionnée : id, nom, interprète, année
	if (($ligne = $result->fetch_row()))
		return ($ligne);
	else
		return (0);
}

// Cherche un chanson et la renvoie si elle existe
function chercheChansonParLeNom($nom) {
	$maRequete = "SELECT * FROM chanson WHERE chanson.nom = '$nom'";
	$result =  $_SESSION ['mysql']->query ( $maRequete )or die ( "Problème chercheChansonParLeNom #1 : " .  $_SESSION ['mysql']->error  );
	// renvoie la lisgne sélectionnée : id, nom, taille, date
	if (($ligne = $result->fetch_row()))
		return ($ligne);
	else
		return (0);
}

// Crée un chanson
function creeChanson($nom, $interprete, $annee,  $idAuteur, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0, $tonalite="") {
	$nom = $_SESSION ['mysql']->real_escape_string($nom);
	$interprete = $_SESSION ['mysql']->real_escape_string($interprete);
	$annee = $_SESSION ['mysql']->real_escape_string($annee);
	$date_publication =  convertitDateJJMMAAAA ( date("d/m/Y") );
	$idAuteur = $_SESSION ['id'];
	$maRequete = "INSERT INTO chanson (id, nom, interprete, annee, idAuteur, tempo, mesure, pulsation, date_publication, hits, tonalite)
	VALUES (NULL, '$nom', '$interprete', '$annee', '$idAuteur', '$tempo', '$mesure', '$pulsation', '$date_publication' ,  '$hits', '$tonalite')";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème creeChanson#1 : " .  $_SESSION ['mysql']->error  );
}

// Modifie en base la chanson
function modifieChanson($id, $nom, $interprete, $annee, $idAuteur, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0, $tonalite="") {
	$nom = $_SESSION ['mysql']->real_escape_string($nom);
	$interprete = $_SESSION ['mysql']->real_escape_string($interprete);
	$annee = $_SESSION ['mysql']->real_escape_string($annee);
	$date_publication =  convertitDateJJMMAAAA ( date("d/m/Y") );
	
	$maRequete = "UPDATE  chanson
	SET nom = '$nom', interprete = '$interprete', annee = '$annee', idauteur = $idAuteur, tempo = '$tempo', mesure='$mesure',
	pulsation='$pulsation',  date_publication = '$date_publication', hits='$hits', tonalite='$tonalite'
	WHERE id='$id'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème modifieChanson #1 : " .  $_SESSION ['mysql']->error  );
}

// Cette fonction supprime un chanson si elle existe
function supprimeChanson($idChanson) {
	// On supprime les enregistrements dans chanson
	$maRequete = "DELETE FROM chanson
	WHERE id='$idChanson'";
	$result =  $_SESSION ['mysql']->query ( $maRequete ) or die ( "Problème #1 dans supprimeChanson : " .  $_SESSION ['mysql']->error  );
}

// Cette fonction modifie ou crée un chanson si besoin
function creeModifiechanson($id, $nom, $interprete, $annee, $idAuteur, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0, $tonalite="") {
	if (chercheChanson ( $id ))
		modifieChanson ( $id, $nom, $interprete, $annee, $idAuteur, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0, $tonalite="");
	else
		creeChanson ( $nom, $interprete, $annee, $idAuteur, $tempo =0, $mesure = "4/4", $pulsation = "binaire", $hits = 0, $tonalite="");
}

// Cette fonction renvoie une chaine de description de la chanson
function infosChanson($id) {
	$enr = chercheChanson ( $id );
	// id_journee id_joueur poste statut
	$retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Interprète : " . $enr [2] . " Année : " . $enr [3];
	$retour .= " idAuteur : " . $enr[4] . " tempo : " . $enr[5] . " mesure : " . $enr[6] . " pulsation : " . $enr[7] ;
	$retour .= " binaire : " . $enr[8] . " hits : " . $enr[9] . " tonalité : " . $enr[10] ;
	return $retour . "<BR>\n";
}

// Cette fonction renvoie la liste des fichiers attachés à la chanson
function fichiersChanson($id) {
	$enr = chercheChanson ( $id );
	$retour = []; // repertoire, nom, extension
	$repertoire = "../data/chansons/$id/";
	if (is_dir ( $repertoire )) {
		foreach ( new DirectoryIterator ( $repertoire ) as $fileInfo ) {
			if ($fileInfo->isDot ()|| strpos($fileInfo->getFilename (),".")==0)
				continue;
			array_push($retour, [$repertoire , $fileInfo->getFilename (),$fileInfo->getextension()]);
		}
	}
	return $retour;
}
// Fonction de test
function testeChanson() {
	echo "On crée la nuit je mens.<br>\n";
	creeChanson ( "La nuit je mens", "Bashung", 1998, $_SESSION ['id'], 120, "4/4", "binaire",10,"Em");
	$id = chercheChansonParLeNom ( "La nuit je mens" );
	$id = $id [0];
	echo infosChanson ( $id );
	
	$enr = chercheChanson ( $id );
	$id = $id [0];
	echo infosChanson ( $id );
	
	creeChanson ( "La javanaise", "Gainsbourg", 1962 , $_SESSION ['id'], 110, "3/4", "binaire",50,"Dm");
	$id = chercheChansonParLeNom ( "La javanaise" );
	$id = $id [0];
	echo infosChanson ( $id );
	
	creeModifieChanson ( $id, "La javanaise remake", "Gainsbarre", 1979, $_SESSION ['id'], 80, "4/4", "ternaire",1,"C");
	$id = chercheChansonParLeNom ( "La javanaise remake" );
	$id = $id [0];
	echo infosChanson ( $id );
	
	$id = chercheChansonParLeNom ( "La nuit je mens" );
	$id = $id [0];
	// supprimeChanson($id);
	echo infosChanson ( $id );
	
	$id = chercheChansonParLeNom ( "La javanaise remake" );
	// supprimeChanson($id[0]);
	$id = chercheChansonParLeNom ( "La javanaise" );
	// supprimechanson($id[0]);
}

// TODO ajouter des logs pur tracer l'activité du site
?>