<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
$mode ="";
$table = "utilisateur";
$sortie = "";

// Chargement des donnees de l'utilisateur si l'identifiant est fourni
if ((isset ( $_GET ['id'] ) && $_GET ['id'] != "")) {
	$donnee = chercheUtilisateur ( $id );
	if (($_SESSION ['privilege'] > 2) || $_SESSION ['user'] == $donnee [1]) {
		$mode = "MAJ";
		$donnee [2] = Chiffrement::decrypt ( $donnee [2] );
		$donnee [1] = htmlspecialchars($donnee [1]);
		$donnee [3] = htmlspecialchars($donnee [3]);
		$donnee [4] = htmlspecialchars($donnee [4]);
		$donnee [6] = htmlspecialchars($donnee [6]);
		$donnee [7] = htmlspecialchars($donnee [7]);
		$donnee [8] = htmlspecialchars($donnee [8]);
	}
} 
else if ($_SESSION ['privilege'] > 2) {
	$mode = "INS";
	$donnee [0] = 0; // id
	$donnee [1] = ""; // login
	$donnee [2] = ""; // mdp
	$donnee [3] = ""; // prenom
	$donnee [4] = ""; // nom
	$donnee [5] = ""; // image
	$donnee [6] = "http://"; // site
	$donnee [7] = "@"; // Adresse
	$donnee [8] = "Devise ou citation..."; // signature
	$donnee [9] = "1970-01-01"; // Date dernier login
	$donnee [10] = 0; // nbrelogins
	$donnee [11] = 0; // privilege
}

if ($mode == "MAJ")
	$sortie .= "<H1> Mise à jour - " . $table . "</H1>";
else if ($mode == "INS")
	$sortie . "<H1> Création - " . $table . "</H1>";
else
	return;

// Création du formulaire
$f = new Formulaire ( "POST", $table . "_get.php", $sortie );
$f->champCache ( "id", $donnee [0] );
$listeImages = listeImages ( "/utilisateur" );
$f->champListeImages ( "Image : ", "fimage", str_replace ( "/utilisateur/", "", $donnee [5] ), 1, $listeImages );

$f->champTexte ( "Login :", "flogin", $donnee [1], 50, 32 );
$f->champMotDePasse ( "Mot de passe :", "fmdp", $donnee [2], 50, 32 );
$f->champTexte ( "Prénom :", "fprenom", $donnee [3], 50, 64 );
$f->champTexte ( "Nom :", "fnom", $donnee [4], 50, 64 );
$f->champTexte ( "Site :", "fsite", $donnee [6], 50 );
$f->champTexte ( "Email :", "femail", $donnee [7], 128 );
$f->champFenetre ( "Signature :", "fsignature", $donnee [8], 5, 60 );
$f->champTexte ( "Dernier login :", "fdateDernierLogin", dateMysqlVersTexte ( $donnee [9] ), 50 );
$f->champTexte ( "Nbre de logins :", "fnbreLogins", $donnee [10], 50 );

$pListe = array (
		"utilisateur non validé",
		"abonné",
		"éditeur",
		"administrateur" 
);
$f->champListe ( "Privileges :", "fprivilege", $donnee [11], 1, $pListe );

$f->champCache ( "mode", $mode );
$f->champValider ( "Valider la saisie", "valider" );
$sortie .= $f->fin ();
$sortie .= envoieFooter ( "Bienvenue chez nous !" );
echo $sortie;
// privilege
// 0 : utilisateur non validé
// 1 : abonné (consultation + évaluation + commentaires)
// 2 : éditeur (idem + possibilité de rédiger, envoyer des fichiers)
// 3 : administrateur (droits complets sur le site)
?>