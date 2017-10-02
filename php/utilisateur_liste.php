<?php
include_once("lib/utilssi.php");
include ("menu.php");

$utilisateurForm = "utilisateur_form.php";
$utilisateurGet = "utilisateur_get.php";
$utilisateurVoir = "utilisateur_voir.php";

//include_once ("params.php");
$sortie = envoieHead("Menu", "../css/index.css");
$table = "utilisateur";
entreBalise("Utilisateurs","H1");
TblDebut (0);

// Chargement de la liste des utilisateurs
$marequete = "select * from $table ORDER BY dateDernierLogin DESC";
//$connexion = Connexion($LOGIN,$MOTDEPASSE,$mabase,$monserveur);
$resultat = ExecRequete ( $marequete, $idconnect);
$numligne = 0;

// Affichage de la liste

////////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if($_SESSION['privilege']>1)
echo "<BR>" . Ancre("$utilisateurForm",Image($cheminImages.$iconeDossier,32,32) . "Créer un nouvel utilisateur");
////////////////////////////////////////////////////////////////////////ADMIN

echo Image ($iconeAttention,"100%",1,1);


while($ligne = lignesuivante($resultat)){
	$numligne++;
	TblDebutLigne ();

	if($ligne[5])
		// TblCellule(Ancre($utilisateurForm."?id=$ligne[0]",afficheVignette(($ligne[5]),$cheminImages,$cheminVignettes)));  // image
		if($_SESSION['privilege']>1)
			TblCellule(Ancre($utilisateurForm."?id=$ligne[0]",Image(($cheminImages.$ligne[5]),32,32)));  // image
		else
			TblCellule(Image(($cheminImages.$ligne[5]),32,32));  // image
	else
		TblCellule(Ancre($_SESSION['urlSite']."/index.php?id=$ligne[0]","voir"));

	TblCellule(entreBalise($ligne[1],"H2")); // Login


	////////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
	if($_SESSION['privilege']>2)
	TblCellule (Ancre("$utilisateurForm?id=$ligne[0]",Image($cheminImages.$iconeEdit,16,16)));
	////////////////////////////////////////////////////////////////////////ADMIN
	
	TblCellule($ligne[3] . " " . $ligne[4]);         // nom prenom
	TblCellule(statut($ligne[11]));
	TblCellule(dateMysqlVersTexte($ligne[9],0));       
	TblCellule(" &nbsp &nbsp &nbsp &nbsp");  // un petit blanc 
	TblCellule(" " . $ligne[10] . " logins");         // nbreLogins
	
	////////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
	if($_SESSION['privilege']>1){
		TblCellule(boutonSuppression($utilisateurGet."?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages));
	////////////////////////////////////////////////////////////////////////ADMIN

		TblFinLigne ();
	}
}
TblFin();

echo Image ($iconeAttention,"100%",1,1);
////////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if($_SESSION['privilege']>1)
echo "<BR>" . Ancre("?page=$utilisateurForm",Image($cheminImages.$iconeDossier,32,32) . "Créer un nouvel utilisateur");
////////////////////////////////////////////////////////////////////////ADMIN
echo envoieFooter("Bienvenue chez nous !");
?>