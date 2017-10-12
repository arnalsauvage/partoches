<?php
include_once "lib/utilssi.php";
include_once ("menu.php");
$sortie = "";
$sortie .= "<table align='center'><tr><td>";
$ficher = "..\data\params.ini";

// On lit les données dans le fichier ini
$ini_objet=new ini();

$sortie .= ("Fichier params.ini<br>"); 
$ini_objet->m_fichier($ficher);
$ini_objet->print_fichier();
   
// Traitement du formulaire si besoin
if(isset ($_POST['urlSite'])){
	$ini_objet->m_put($_POST['urlSite'],"urlSite","general","params.ini");
	$ini_objet->save();
}

if(isset ($_POST['EmailAdmin'])){
	$ini_objet->m_put($_POST['EmailAdmin'],"EmailAdmin","general","params.ini");
	$ini_objet->save();
}

if(isset ($_POST['loginParam'])){
	$ini_objet->m_put($_POST['loginParam'],"loginParam","general","params.ini");
	$ini_objet->save();
}
//print_r($_POST);
//$ini_objet->print_fichier();
$ini_objet->save();

// Si l'utilisateur n'est pas logué
if(!isset ($_SESSION['user']) ){
	// Affichage du formulaire de login
	echo $sortie;
	include "menuLogin.html";
	exit();
}
$sortie .= "  <FORM action='paramsEdit.php' method='post' ENCTYPE='x-www-form-urlencoded'>
<br> Email de l'admin :    <input value='".$ini_objet->m_valeur("EmailAdmin","general")."' name='EmailAdmin'  type='text'>
<br>Url du site : <input type='text' value='".$ini_objet->m_valeur("urlSite","general")."' name='urlSite'>
<br>login de paramétrage <input type='text' value='".$ini_objet->m_valeur("loginParam","general")."' name='loginParam'> 
<button type='submit' value='Valider' name='valider'>Valider</button>
</form> ";

$sortie .= "</td></tr></table>";
$sortie .= envoieFooter("Bienvenue chez nous !");
echo $sortie;
?>