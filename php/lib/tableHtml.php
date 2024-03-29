<?php
if (!isset ($ModuleTable)) {
    $ModuleTable = 1;

    // Module de production de tableaux HTML
    function TblDebut(): string
    {
        return "<table>\n";
       }

    function TblFin(): string
    {
        return "</table>\n";
    }

    function TblEnteteDebut(): string
    {
        return "<thead>";
    }

    function TblEntete($contenu, $nbLig = 1, $nbCol = 1): string
    {
        //echo "Appel de tbl entete avec contenu = $contenu, nbLig = $nbLig et nbCol = $nbCol";
        $_entete = "<th ";
        if ($nbLig <> 1){
            $_entete .= "rowspan='$nbLig' ";
        }
        if ($nbCol <> 1){
            $_entete .= "colspan='$nbCol' ";
        }
        $_entete .= "> " . $contenu;
        $_entete .= "</th>\n";

        return $_entete;
    }

    function TblEnteteFin(): string
    {
        return "</thead>\n";
    }


    function TblCorpsDebut(): string
    {
        return "<tbody>";
    }

    function TblDebutLigne($classe = -1): string
    {
        $optionClasse = "";
        if ($classe != -1) {
            $optionClasse = " CLASS='$classe'";
        }
        return "<tr" . $optionClasse . ">\n";
    }

    function TblFinLigne(): string
    {
        return "</tr>\n";
    }

    function TblCellule($contenu, $nbLig = 1, $nbCol = 1, $classe = -1): string
    {
        $optionClasse = "";
        if ($classe != -1) {
            $optionClasse = " CLASS='$classe' ";
        }

        $rowSpan = " ";
        if ($nbLig <> 1)
        {
            $rowSpan = " rowspan='$nbLig' ";
        }

        $colSpan = " ";
        if ($nbCol <> 1)
        {
            $colSpan = " rowspan='$nbCol' ";
        }
        return "<td " . $rowSpan . $colSpan. $optionClasse . ">$contenu</td>\n";
    }

    function TblCorpsFin(): string
    {
        return "</tbody>";
    }
} // Fin du module Table

