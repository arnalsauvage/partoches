<?php
include_once("lib/utilssi.php");
include ("menu.php");
//include ("utilisateur.php");

$nomTable = "utilisateur";
$utilisateurForm = "utilisateur_form.php";
$utilisateurGet = "utilisateur_get.php";
$utilisateurVoir = "utilisateur_voir.php";
$utilisteurListe = "utilisateur_liste.php";

//include_once ("params.php");
$sortie = envoieHead("Menu", "../css/index.css");
$table = "utilisateur";
entreBalise("Utilisateurs","H1");
$flogin = addslashes($flogin);
$fnom = addslashes($fnom);
$fprenom = addslashes($fprenom);
$fsite = addslashes($fsite);
$femail = addslashes($femail);
$fimage = "/utilisateur/" . $fimage;
$fmdp = addslashes($fmdp);
$fsignature = addslashes($fsignature);
$fprivilege = addslashes($fprivilege);
// Un non-admin ne peut changer ses privilèges
if ($_SESSION ['privilege']<3)
{
	$fprivilege = $_SESSION ['privilege'];
}

// On gère 3 cas :  création d'utilisateur, modif ou suppressions	
  
if($mode=="MAJ"){
	modifieUtilisateur($id, $flogin, $fmdp, $fprenom, $fnom, $fimage, $fsite, $femail, $fsignature, $fdateDernierLogin, $fnbreLogins, $fprivilege );
	redirection ($utilisteurListe);
}
  
// Gestion de la demande de suppression
if($id && $mode == "SUPPR"){
	if($_SESSION['privilege']>2){
		supprimeUtilisateur($idUtilisateur);
		redirection ($utilisteurListe); 
	}
}

if($mode=="INS"){
	// Entrer une nouvelle fiche utilisateur
	creeUtilisateur('$flogin', '$fmdp','$fprenom','$fnom',  '$fimage', '$fsite', '$femail', '$fsignature', '$fdateDernierLogin', '$fnbreLogins', '$fprivilege' );
	redirection($utilisteurListe);
}
?>