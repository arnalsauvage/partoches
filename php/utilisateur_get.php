<?php
include_once("lib/utilssi.php");
include ("menu.php");

$nomTable = "utilisateur";
$utilisateurForm = "utilisateur_form.php";
$utilisateurGet = "utilisateur_get.php";
$utilisateurVoir = "utilisateur_voir.php";
$utilisteurListe = "utilisateur_liste.php";

//include_once ("params.php");
$sortie = envoieHead("Menu", "../css/index.css");
$table = "utilisateur";
entreBalise("Utilisateurs","H1");

$fdateDernierLogin = dateTexteVersMysql($fdateDernierLogin);
$flogin = addslashes($flogin);
$fnom = addslashes($fnom);
$fprenom = addslashes($fprenom);
$fsite = addslashes($fsite);
$femail = addslashes($femail);
$fimage = "/utilisateur/" . $fimage;

$fsignature = addslashes($fsignature);

// On gère 3 cas :  création d'utilisateur, modif ou suppression
  
  
if($mode=="MAJ"){
	$marequete = "UPDATE $nomTable SET login='$flogin', nom='$fnom', prenom='$fprenom', image='$fimage', site='$fsite', email='$femail', signature='$fsignature', dateDernierLogin='$fdateDernierLogin', nbreLogins='$fnbreLogins', privilege='$fprivilege' WHERE id=$id";
	$resultat = ExecRequete ( $marequete, $idconnect);
	// echo " <p> Bienvenue, $prenom";
	redirection ($utilisteurListe);
}
  
// Gestion de la demande de suppression
if($id && $mode == "SUPPR"){
	if($_SESSION['privilege']>1){
		$marequete = "DELETE FROM $nomTable WHERE id = '$id'";
		$resultat = ExecRequete ( $marequete, $idconnect);
		redirection ($utilisteurListe); 
	}
}

if($mode=="INS"){
	// Entrer une nouvelle fiche utilisateur
	$marequete = "INSERT INTO $nomTable 
	(login, nom, prenom, image, site, email, signature, dateDernierLogin, nbreLogins, privilege)
	VALUES
	('$flogin', '$fnom', '$fprenom', '$fimage', '$fsite', '$femail', '$fsignature', '$fdateDernierLogin', '$fnbreLogins', '$fprivilege' )";
	$resultat = ExecRequete ( $marequete, $idconnect);
	// echo "<P> Lancement de la requête : $marequete </p>";
	$numligne = 0;
	// echo "<p> Résultat : $resultat</p>";
	redirection($utilisteurListe);
}
?>