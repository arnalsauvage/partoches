<?php
include_once "lib/utilssi.php";
include_once ("menu.php");
$fichier = "../conf/params.ini";
$sortie = "";
$sortie .="<table align='center'><tr><td>";

// Si l'utilisateur n'est pas logué
if (!isset ($_SESSION['user']) || $_SESSION ['privilege'] <= 2) {
	// Affichage du formulaire de login
	$sortie .=$sortie;
	include "../html/menuLogin.html";
	exit();
}
// On lit les données dans le fichier ini
$ini_objet=new ini();

$sortie .= ("Fichier params.ini<br>"); 
$ini_objet->m_fichier($fichier);

// Traitement du formulaire si besoin
$bModif = false;

if(isset ($_POST['urlSite'])){
	$ini_objet->m_put($_POST['urlSite'],"urlSite","general",$fichier);
	$bModif = true;
	$sortie .="urlsite ok ";
}

if(isset ($_POST['EmailAdmin'])){
	$ini_objet->m_put($_POST['EmailAdmin'],"EmailAdmin","general",$fichier);
	$bModif = true;
	$sortie .="emailAdmin ok ";
}

if(isset ($_POST['loginParam'])){
	$ini_objet->m_put($_POST['loginParam'],"loginParam","general",$fichier);
	$bModif = true;
	$sortie .="loginParam ok ";
}

if ($bModif){
	$ini_objet->save();
}
$ini_objet->print_fichier();

$sortie .= "
<form action='paramsEdit.php' method='post' ENCTYPE='x-www-form-urlencoded'>
<fieldset>
 <legend> Attributs modifiables : </legend>
<br> <label for='emailAdmin'>Email de l'admin : </label>
<input value='".$ini_objet->m_valeur("EmailAdmin","general")."' name='EmailAdmin' id='EmailAdmin' type='email'>
<br><label for='urlSite'>Url du site : </label>
<input value='" . $ini_objet->m_valeur("urlSite","general") . "' name='urlSite' id='urlSite' type='text' >
<br><label for='loginParam'>login de paramétrage  : </label>
<input value='" . $ini_objet->m_valeur("loginParam","general") . "' name='loginParam' id='loginParam' type='text'> 
<button type='submit' value='Valider' name='valider'>Valider</button>

</fieldset></form> ";

$sortie .= "</td></tr></table>";
$sortie .= envoieFooter("Bienvenue chez nous !");
echo $sortie;
?>