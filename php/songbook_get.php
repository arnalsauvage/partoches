<?php
include_once("lib/utilssi.php");
include ("menu.php");
include ("songbook.php");

$nomTable = "songbook";

$fnom = $_SESSION ['mysql']->real_escape_string($fnom);
$description = $_SESSION ['mysql']->real_escape_string($fdescription);
$fdate = $fdate;
$fimage = $fimage;
$fhits = $fhits;

// On gère 3 cas :  création d'une songbook, modif ou suppression
  
if($mode=="MAJ"){
	modifiesongbook($id, $fnom, $description, $fdate, $fimage, $fhits);
}
  
// Gestion de la demande de suppression
if($id && $mode == "SUPPR" && $_SESSION['privilege'] > 1 ){
		supprimesongbook($id);
	}

if($mode=="INS"){
	creesongbook( $fnom, $description, $fdate, $fimage, $fhits);
}
redirection ($nomTable . "_liste.php");
?>