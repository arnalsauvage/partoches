<?php
include_once("lib/utilssi.php");
include ("menu.php");

$chansonForm = "chanson_form.php";
$chansonGet = "chanson_get.php";
$chansonVoir = "chanson_voir.php";

$table = "chanson";

$retour = "";

$retour .= entreBalise("chansons","H1");
$retour .= TblDebut (0);

// Chargement de la liste des chansons
$marequete = "select * from $table ORDER BY annee DESC";
//$connexion = Connexion($LOGIN,$MOTDEPASSE,$mabase,$monserveur);
$resultat = ExecRequete ( $marequete, $idconnect);
$numligne = 0;

// Affichage de la liste

////////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if($_SESSION['privilege']>1)
echo "<BR>" . Ancre("$chansonForm",Image($cheminImages.$iconeDossier,32,32) . "Créer une nouvelle chanson");
////////////////////////////////////////////////////////////////////////ADMIN

$retour .= Image ($iconeAttention,"100%",1,1);

while($ligne = lignesuivante($resultat)){
	$numligne++;
	$retour .= TblDebutLigne ();

/* TO Gestion d'une image pour  une chanson'
	if($ligne[5])
		TblCellule(Ancre($chansonForm."?id=$ligne[0]",afficheVignette(($ligne[5]),$cheminImages,$cheminVignettes)));  // image
	else

		TblCellule(Ancre($_SESSION['urlSite']."/index.php?id=$ligne[0]","voir"));
*/
	
	////////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
	if($_SESSION['privilege']>1)
		$retour .= TblCellule (Ancre("$chansonForm?id=$ligne[0]",entreBalise($ligne[1],"H2"))); // Nom));
	////////////////////////////////////////////////////////////////////////ADMIN
	else
		$retour .= TblCellule(entreBalise($ligne[1],"H2")); // Nom
	$retour .= TblCellule($ligne[2] . " - " . $ligne[3]);         //  interprete annee
	
	////////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
	if($_SESSION['privilege']>1){
		$retour .= TblCellule(boutonSuppression($chansonGet."?id=$ligne[0]&mode=SUPPR", $iconePoubelle, $cheminImages));
	////////////////////////////////////////////////////////////////////////ADMIN
		$retour .= TblFinLigne ();
	}
}
$retour .= TblFin();

echo Image ($iconeAttention,"100%",1,1);
////////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if($_SESSION['privilege']>1)
	$retour .= "<BR>" . Ancre("?page=$chansonForm",Image($cheminImages.$iconeDossier,32,32) . "Créer une nouvelle chanson");
////////////////////////////////////////////////////////////////////////ADMIN
	$retour .= envoieFooter("Bienvenue chez nous !");
	echo $retour;
?>