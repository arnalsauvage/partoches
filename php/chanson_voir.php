<?php
include_once ("lib/utilssi.php");
include ("menu.php");
include("chanson.php");
$table = "chanson";
$sortie = "";

$donnee = chercheChanson($_GET['id']);
$sortie .= "<h2>$donnee[1]</h2>";
$sortie .= $donnee[2] ."-" . $donnee[3] . "<br>";
$sortie .= fichiersChanson($_GET['id']);
$sortie .= envoieFooter ( "Bienvenue chez nous !" );
echo $sortie;
?>