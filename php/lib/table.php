<?php
if (!isset ($ModuleTable))
{
 $ModuleTable = 1;

 // Module de production de tableaux HTML

 function TblDebut ($bordure = '1', // La bordure
                    $largeur = -1, 
                    $espCell = '2', // CELLSPACING
                    $remplCell = '4', // CELLPADDING 
                    $classe=-1)
 {
  $optionClasse = ""; $optionLargeur="";
  if ($classe != -1) $optionClasse = " CLASS='$classe' ";
  if ($largeur != -1) $optionLargeur = " WIDTH='$largeur' ";

  echo "<TABLE BORDER='$bordure' "
      . " CELLSPACING='$espCell' CELLPADDING='$remplCell' " 
      . $optionLargeur .  $optionClasse . ">\n";
 }

 function TblFin ()
 {
  echo "</TABLE>\n";
 }

 function TblDebutLigne ($classe=-1)
 {
  $optionClasse = "";
  if ($classe != -1) $optionClasse = " CLASS='$classe'";
  echo "<TR" . $optionClasse . ">\n";
 } 

 function TblFinLigne ()
 {
  echo "</TR>\n";
 }

 function TblEntete ($contenu, $nbLig=1, $nbCol=1)
 {
  echo "<TH ROWSPAN='$nbLig' COLSPAN='$nbCol'>$contenu</TH>\n";
 }

 function TblDebutCellule ($classe=-1)
 {
  $optionClasse = "";
  if ($classe != -1) $optionClasse = " CLASS='$classe'";
  echo "<TD" . $optionClasse . ">\n";
 }

 function TblFinCellule ()
 {
  echo "</TD>\n";
 }

 function TblCellule ($contenu, $nbLig=1, $nbCol=1, $classe=-1)
 {
  $optionClasse = "";
  if ($classe != -1) $optionClasse = " CLASS='$classe'";

  echo "<TD ROWSPAN='$nbLig' COLSPAN='$nbCol' " 
       . $optionClasse . ">$contenu</TD>\n";
 }

} // Fin du module Table
?>
