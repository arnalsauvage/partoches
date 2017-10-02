<?php

include_once "lib/configMysql.php";

// Fonctions de gestion de la chanson

// Cherche un chanson et le renvoie s'il existe
function chercheChanson($id){
	$maRequete = "SELECT * FROM chanson WHERE chanson.id = '$id'";
	$result = mysql_query ( $maRequete ) or die ( "Problème chercheChanson #1 : " . mysql_error () );
	// renvoie la lisgne sélectionnée : id, nom, interprète, année
	if(($ligne = mysql_fetch_array ( $result )))
		return ($ligne);
	else
		return (0);
}

// Cherche un chanson et la renvoie si elle existe
function chercheChansonParLeNom($nom){
	$maRequete = "SELECT * FROM chanson WHERE chanson.nom = '$nom'";
	$result = mysql_query ( $maRequete ) or die ( "Problème chercheChansonParLeNom #1 : " . mysql_error () );
	// renvoie la lisgne sélectionnée : id, nom, taille, date
	if(($ligne = mysql_fetch_array ( $result )))
		return ($ligne);
	else
		return (0);
}

// Crée un chanson
function creeChanson( $nom, $interprete, $annee ){
	$maRequete = "INSERT INTO chanson VALUES (NULL, '$nom', '$interprete', '$annee')";
	$result = mysql_query ( $maRequete ) or die ( "Problème creeChanson#1 : " . mysql_error () );
}

// Modifie en base la chanson
function modifieChanson($id, $nom, $interprete, $annee ){
	$maRequete = "UPDATE  chanson
	SET nom = '$nom', interprete = '$interprete', annee = '$annee'
	WHERE id='$id'";
	$result = mysql_query ( $maRequete ) or die ( "Problème modifieChanson #1 : " . mysql_error () );
}

// Cette fonction supprime un chanson si elle existe
function supprimeChanson($idChanson){
	// On supprime les enregistrements dans chanson
	$maRequete = "DELETE FROM chanson
	WHERE id='$idChanson'";
	$result = mysql_query ( $maRequete ) or die ( "Problème #1 dans supprimeChanson : " . mysql_error () );
}

// Cette fonction modifie ou crée un chanson si besoin
function creeModifiechanson($id, $nom, $interprete, $annee ){
	if(chercheChanson ( $id ))
		modifieChanson ( $id, $nom, $interprete, $annee );
	else
		creeChanson ( $nom, $interprete, $annee );
}

// Cette fonction renvoie une chaine de description de la chanson
function infosChanson($id){
	$enr = chercheChanson ( $id );
	// id_journee id_joueur poste statut
	$retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Interprète : " . $enr [2] . " Année : " . $enr [3] ;
	return $retour . "<BR>\n";
}

// Fonction de test
function testeChanson(){
	creeChanson ("La nuit je mens", "Bashung", 1998);
	$id = chercheChansonParLeNom ( "La nuit je mens" );
	$id = $id [0];
	echo infosChanson ( $id );
	
	$enr = chercheChanson ( $id );
	$id = $id [0];
	echo infosChanson ( $id );
	
	creeChanson ("La javanaise", "Gainsbourg", 1962);
	$id = chercheChansonParLeNom ( "La javanaise" );
	$id = $id [0];
	echo infosChanson ( $id );
	
	creeModifieChanson ($id, "La javanaise remake", "Gainsbarre", 1979 );
	$id = chercheChansonParLeNom ( "La javanaise remake" );
	$id = $id [0];
	echo infosChanson ( $id );
		
	$id = chercheChansonParLeNom ( "La nuit je mens" );
	$id = $id [0];
//	supprimeChanson($id);
	echo infosChanson($id);
	
	$id = chercheChansonParLeNom ( "La javanaise remake" );
//	supprimeChanson($id[0]);
	$id = chercheChansonParLeNom ( "La javanaise" );
//	supprimechanson($id[0]);
}

testeChanson ();

?>
