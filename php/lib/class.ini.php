<?php

// http://sdz.tdct.org/sdz/enregistrer-et-lire-des-donnees-de-fichiers-ini.html
class ini
{
    var $fichier = "";
    var $groupe = "";
    var $item = "";
    var $valeur = "";
    var $fichier_ini = array();

    // Cette méthode prend le contenuFiltrer d'un fichier en argument et le charge dans $fichier_ini
    function m_fichier($arg)
    {
        $this->fichier = $arg;
        $this->fichier_ini = null;
        $this->fichier_ini = array();
        if (file_exists($arg) && $fichier_lecture = file($arg)) {
            foreach ($fichier_lecture as $ligne) // Parcourt chaque ligne du fichier
            {
                $ligne_propre = trim($ligne); // efface les espaces
                if (preg_match("#^\[(.+)\]$#", $ligne_propre, $matches)) // Si la ligne est un groupe
                {
                    $groupe_curseur = $matches [1];
                    // echo 'groupe :' . $groupe_curseur;
                    $this->groupe = $groupe_curseur; // ajout sinon warning
                } else {
                    if ($ligne_propre [0] != ';' && $tableau = explode("=", $ligne, 2)) // Sinon, c'est un item / valeur
                    {
                        $this->fichier_ini [$groupe_curseur] [trim($tableau [0])] = trim($tableau [1], "\n\r ");
                        // echo ("Fichier [$groupe_curseur][$tableau[0]] =$tableau[1] ");
                        $this->item = $tableau [0]; // ajout sinon warnings
                    }
                }
            }
        }

        // Valeur courante = derniere valeur passée
        // $this->valeur=$this->fichier_ini[$this->groupe][$this->item];
        // print_r($this->fichier_ini);
        /*
         * echo ("groupe :".$this->groupe);
         * echo("item :" . $this->item);
         * echo $this->fichier_ini[$this->groupe][$this->item];
         */
    }

    // Sélectionner un groupe dans le fichier ini
    function m_groupe($arg)
    {
        $this->groupe = $arg;
        return true;
    }

    // Selectionner un item
    function m_item($arg)
    {
        $this->item = $arg;
        return true;
    }

    // Changer une valeur (valeur sélectionnée, ou préciser item, groupe, fichier)
    function m_put($arg, $arg_i = false, $arg_g = false, $arg_f = false)
    {
        if ($arg_f !== false)
            $this->m_fichier($arg_f);
        if ($arg_g !== false)
            $this->m_groupe($arg_g);
        if ($arg_i !== false)
            $this->m_item($arg_i);
        $this->fichier_ini [$this->groupe] [$this->item] = $arg;
        $this->valeur = $arg;
        echo $this->fichier . " ==> [" . $this->groupe . "] " . $this->item . "=" . $this->valeur . "<br>";
        return $this->fichier . " ==> [" . $this->groupe . "] " . $this->item . "=" . $this->valeur;
    }

    // Sans paramètres : nb elements / Nb groupes / nb items (tableau)
    // Parqmetre : nb d'items du groupe en paramètre
    function m_count($arg_gr = false)
    {
        if ($arg_gr === false)
            return array(
                1 => $gr_cou = count($this->fichier_ini),
                0 => $itgr_cou = count($this->fichier_ini, COUNT_RECURSIVE),
                2 => $itgr_cou - $gr_cou
            );
        else
            return count($this->fichier_ini [$arg_gr]);
    }

    // Renvoie les items d'un groupe passé en paramètre
    function array_groupe($arg_gr = false)
    {
        if ($arg_gr === false)
            $arg_gr = $this->groupe;
        return $this->fichier_ini [$arg_gr];
    }

    // Enregistre l'objet dans le fichier ini
    function save()
    {
        $fichier_save = "";
        foreach ($this->fichier_ini as $keyGroupe => $groupe_n) // Pour chaque groupe
        {
            $fichier_save .= "[" . $keyGroupe . "]\r\n";
            foreach ($groupe_n as $keyCle => $item_n) // Pour chaque valeur
            {
                $fichier_save .= "" . $keyCle . " = " . $item_n . "\r\n";
                echo("sauvegarde[$keyGroupe]: $keyCle = $item_n<br>");
            }
        }
        // $fichier_save=substr($fichier_save, 1);
        $monTab = explode('.', phpversion());
        // echo "version :".reset($monTab);
        if (file_exists($this->fichier) && reset($monTab) >= 5) {
            echo "Ecriture du fichier : $this->fichier";
            if (false === file_put_contents($this->fichier, $fichier_save)) {
                die ("Impossible d'&eacute;crire dans ce fichier (mais le fichier existe).");
            }
        } else {
            $fichier_ouv = fopen($this->fichier, "w+");
            if (false === fwrite($fichier_ouv, $fichier_save)) {
                die ("Impossible d'&eacute;crire dans ce fichier (Le fichier n'existe pas).");
            }
            fclose($fichier_ouv);
        }
        return true;
    }

    // Réinitialise toutes les variables de l'objet
    function clear()
    {
        $this->fichier = "";
        $this->groupe = "";
        $this->item = "";
        $this->valeur = "";
        $this->fichier_ini = null;
        $this->fichier_ini = array();
    }

    // Supprime le fichier
    function s_fichier()
    {
        $return = $this->fichier;
        if (file_exists($this->fichier))
            unlink($this->fichier);
        $this->fichier = "";
        $this->valeur = "";
        return "fichier(" . $return . ") supprim&eacute;.";
    }

    // Supprime le groupe selectionné
    function s_groupe()
    {
        $return = $this->groupe;
        if (isset ($this->fichier_ini [$this->groupe]))
            unset ($this->fichier_ini [$this->groupe]);
        $this->groupe = "";
        $this->valeur = "";
        return "groupe(" . $return . ") supprim&eacute;.";
    }

    // Supprime l'item sélectionné
    function s_item()
    {
        $return = $this->item;
        if (isset ($this->fichier_ini [$this->groupe] [$this->item]))
            unset ($this->fichier_ini [$this->groupe] [$this->item]);
        $this->item = "";
        $this->valeur = "";
        return "item(" . $return . ") supprim&eacute;.";
    }

    // Imprime les coordonnées du curseur et la valeur courante
    function print_curseur()
    {
        $retour = "";
        $retour .= "Fichier : <b>" . $this->fichier . "</b><br />";
        $retour .= "Groupe : <b>" . $this->groupe . "</b><br />";
        $retour .= "Item : <b>" . $this->item . "</b><br />";
        $retour .= "Valeur : <b>" . $this->valeur . "</b><br />";
        return $retour;
    }

    // Si $fichier contient le contenuFiltrer d'un dossier, affiche le liste des fichiers ini que contient ce dossier
    function print_dossier()
    {
        $retour = "";
        if (is_dir($this->fichier)) {
            $retour .= "<img src='dir.png' alt='Dossier' /><span style='position:relative; top:-10px;font-size:20px; font-weight:bold;'>" . $this->fichier . "</span><br />";
            if ($handle = opendir($this->fichier)) {
                while (false !== ($file = readdir($handle))) {
                    if (substr($file, -4, 4) == ".ini") {
                        echo "&nbsp;&nbsp;<a href='?fichier=" . $file . "'><img src='iniicone.png' alt='Ini' style='border:none;' /></a>&nbsp;" . $file . "<br />";
                    }
                }
                closedir($handle);
            }
            return true;
        } else {
            echo "L'élément sélectionné n'est pas un dossier";
            return false;
        }
    }

    // Affiche le contenu du fichier ini
    function print_fichier()
    {
        $echo = "Fichier : $this->fichier <br>";
        $groupe = false;
        if (file_exists($this->fichier) && is_file($this->fichier) && $fichier_lecture = file($this->fichier)) {
            // Pour chaque ligne
            foreach ($fichier_lecture as $ligne) {
                $ligne = preg_replace("#\s$#", "", $ligne);
                if (preg_match("#^\[.+\]\s?$#", $ligne))
                    $groupe = false;
                // variable pour le reset dans le elseif
                $var = explode("=", $ligne);
                // Titre de rubrique en bleu
                if (preg_match("#^\[" . preg_quote($this->groupe, "#") . "\]$#", $ligne)) {
                    $echo .= "<span style='background-color:aqua;'>" . htmlspecialchars($ligne) . "</span><br />";
                    $groupe = true;
                } // première ligne en jaune
                elseif ($groupe == true && $this->item == reset($var))
                    $echo .= "<span style='background-color:yellow;'>" . htmlspecialchars($ligne) . "</span><br />";
                else
                    $echo .= htmlspecialchars($ligne) . "<br />";
            }
            echo $echo;
        } else {
            echo "Le fichier " . $this->fichier . " n'existe pas ou est incompatible";
        }
        // $this->valeur=$this->fichier_ini[$this->groupe][$this->item];
        return true;
    }

    // Prend deux paramètres : l'item, le groupe, et renvoie la valeur
    function m_valeur($arg_item, $arg_groupe)
    {
        // echo "Cherche item : " . $arg_item . " dans groupe : " . $arg_groupe . "\n";
        return $this->fichier_ini [$arg_groupe] [$arg_item];
    }
}

