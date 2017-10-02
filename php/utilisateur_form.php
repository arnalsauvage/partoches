<?php
include_once("lib/utilssi.php");
include ("menu.php");

$table = "utilisateur";
$sortie = envoieHead("Menu", "../css/index.css");

// Chargement des donnees de l'album si l'identifiant est fourni
if(isset($_GET['id'])&&$_GET['id']<>""){
	$marequete = "select * from $table where id = '" . $_GET['id'] . "'";
	$resultat = ExecRequete ( $marequete, $idconnect);
	$donnee = LigneSuivante($resultat);
	$mode = "MAJ";
}
else {
	$mode="INS";
	$donnee[0]=0;
	$donnee[1]="";
	$donnee[2]="";
	$donnee[3]="";
	$donnee[4]="";
	$donnee[6]="http://";
	$donnee[5]="";
	$donnee[7]="@";
	$donnee[8]="Devise ou citation...";
	$donnee[9]="1970-01-01";
	$donnee[10]=0;
	$donnee[11]=0;
}

if ($mode=="MAJ")
	echo "<H1> Mise à jour - " . $table . "</H1>";
else
	echo "<H1> Création - " . $table . "</H1>";
	
// Création du formulaire
$f = new Formulaire ("POST", $table."_get.php");
$f->debutTable();
$f->champCache ("id", $donnee[0]);
$f->champTexte ("Login :", "flogin", $donnee[1], 50, 32);
// $f->champMotDePasse ("Mot de passe :", "mdp",  $donnee[2], 50, 32);
$f->champTexte ("Prénom :", "fprenom", $donnee[3], 50, 64);
$f->champTexte ("Nom :", "fnom", $donnee[4], 50, 64);
$f->champTexte ("Site :", "fsite", $donnee[6], 50);
$f->champTexte ("Email :", "femail", $donnee[7], 128);
// $f->champTexte ("Signature :", "fsignature", $donnee[8], 255);
$f->champFenetre ("Signature :", "fsignature", $donnee[8], 5, 60);
$f->champTexte ("Dernier login :", "fdateDernierLogin", dateMysqlVersTexte($donnee[9]), 50);
$f->champTexte ("Nbre de logins :", "fnbreLogins", $donnee[10], 50);
$listeImages = listeImages ("/utilisateur");
$f->champListeImages("Image : ", "fimage", $donnee[5], 1,$listeImages);
$pListe = array("utilisateur non validé", "abonné", "éditeur", "administrateur");
$f->champListe ("Privileges :", "fprivilege", $donnee[11], 1, $pListe);
$f->finTable();
$f->champCache ("mode", $mode);
$f->champValider ("Valider la saisie", "valider");
$f->fin();

// privilege
// 0 : utilisateur non validé
// 1 : abonné (consultation + évaluation + commentaires)
// 2 : éditeur (idem + possibilité de rédiger, envoyer des fichiers)
// 3 : administrateur (droits complets sur le site)

?>