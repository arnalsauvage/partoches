<?php
if (!isset ($ModuleTable)) {
    $ModuleTable = 1;

    // Module de production de tableaux HTML
    function TblDebut()
    {
        return "<table>\n";
       }

    function TblFin()
    {
        return "</table>\n";
    }

    function TblEnteteDebut()
    {
        return "<thead>";
    }

    function TblEntete($contenu, $nbLig = 1, $nbCol = 1)
    {
        return "<th ROWSPAN='$nbLig' COLSPAN='$nbCol'>$contenu</th>\n";
    }

    function TblEnteteFin()
    {
        return "</thead>\n";
    }


    function TblCorpsDebut()
    {
        return "<tbody>";
    }

    function TblDebutLigne($classe = -1)
    {
        $optionClasse = "";
        if ($classe != -1)
            $optionClasse = " CLASS='$classe'";
        return "<tr" . $optionClasse . ">\n";
    }

    function TblFinLigne()
    {
        return "</tr>\n";
    }


    function TblDebutCellule($classe = -1)
    {
        $optionClasse = "";
        if ($classe != -1)
            $optionClasse = " CLASS='$classe'";
        return "<td" . $optionClasse . ">\n";
    }

    function TblFinCellule()
    {
        return "</td>\n";
    }

    function TblCellule($contenu, $nbLig = 1, $nbCol = 1, $classe = -1)
    {
        $optionClasse = "";
        if ($classe != -1)
            $optionClasse = " CLASS='$classe'";

        return "<td ROWSPAN='$nbLig' COLSPAN='$nbCol' " . $optionClasse . ">$contenu</td>\n";
    }

    function TblCorpsFin()
    {
        return "</tbody>";
    }
} // Fin du module Table

