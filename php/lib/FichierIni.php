<?php

// http://sdz.tdct.org/sdz/enregistrer-et-lire-des-donnees-de-fichiers-ini.html
class FichierIni
{
    const SUPPRIM_EACUTE = ") supprim&eacute;.";
    const B_BR = "</b><br />";
    const SPAN_BR = "</span><br />";
    var string $fichier = "";
    var array $tableauDesValeursDansItemsDansGroupe = array();

    // Cette méthode prend le nom d'un fichier en argument et le charge dans $fichier_ini
    function m_load_fichier($arg)
    {
        $this->fichier = $arg;
        $this->tableauDesValeursDansItemsDansGroupe = array();
        $groupe_curseur = "general"; // Par défaut
        if (file_exists($arg) && $fichier_lecture = file($arg)) {
            foreach ($fichier_lecture as $ligne) // Parcourt chaque ligne du fichier
            {
                $ligne_propre = trim($ligne); // efface les espaces
                if (preg_match("#^\[(.+)\]$#", $ligne_propre, $matches)) // Si la ligne est un groupe
                {
                    $groupe_curseur = $matches [1];
//                    echo 'groupe :' . $groupe_curseur;
                    $this->groupe = $groupe_curseur; // ajout sinon warning
                } else {
                    if ($ligne_propre [0] != ';' && $tableau = explode("=", $ligne, 2)) // Sinon, c'est un item / valeur
                    {
                        $this->tableauDesValeursDansItemsDansGroupe [$groupe_curseur] [trim($tableau [0])] = trim($tableau [1], "\n\r ");
//                        echo ("Fichier [$groupe_curseur][$tableau[0]] =$tableau[1] ");
                        $this->item = $tableau [0]; // ajout sinon warnings
                    }
                }
            }
        }
    }

    // Changer une valeur (valeur sélectionnée, ou préciser item, groupe, fichier)
    function m_put(string $arg_valeur, string $arg_item, string $arg_groupe )
    {
        $this->tableauDesValeursDansItemsDansGroupe [$arg_groupe] [$arg_item] = $arg_valeur;
        // Pour debug echo $this->fichier . " ==> [" . $arg_groupe . "] " . $arg_item . "=" . $arg_valeur . "<br>";
        return $this->fichier . " ==> [" . $arg_groupe . "] " . $arg_item . "=" .$arg_valeur;
    }

    // Sans paramètres : nb elements / Nb groupes / nb items (tableau)
    // Parametre : nb d'items du groupe en paramètre
    function m_count($arg_gr = false)
    {
        if ($arg_gr === false) {
            return array(
                1 => $gr_cou = count($this->tableauDesValeursDansItemsDansGroupe),
                0 => $itgr_cou = count($this->tableauDesValeursDansItemsDansGroupe, COUNT_RECURSIVE),
                2 => $itgr_cou - $gr_cou
            );
        } else {
            return count($this->tableauDesValeursDansItemsDansGroupe [$arg_gr]);
        }
    }

    // Renvoie les items d'un groupe passé en paramètre
    function m_array_get_items_groupe($arg_gr)
    {
        return $this->tableauDesValeursDansItemsDansGroupe [$arg_gr];
    }

    // Enregistre l'objet dans le fichier ini
    function save()
    {
        $fichier_save = "";
        foreach ($this->tableauDesValeursDansItemsDansGroupe as $keyGroupe => $groupe_n) // Pour chaque groupe
        {
            $fichier_save .= "[" . $keyGroupe . "]\r\n";
            foreach ($groupe_n as $keyCle => $item_n) // Pour chaque valeur
            {
                $fichier_save .= "" . $keyCle . " = " . $item_n . "\r\n";
                // pour debug echo "sauvegarde[$keyGroupe]: $keyCle = $item_n<br>";
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
    private function m_clear()
    {
        $this->fichier = "";
        $this->tableauDesValeursDansItemsDansGroupe = array();
    }

    // Supprime le fichier
    function m_delete_fichier()
    {
        $return = $this->fichier;
        if (file_exists($this->fichier)) {
            unlink($this->fichier);
        }
        $this->fichier = "";
        return "fichier(" . $return . self::SUPPRIM_EACUTE;
    }

    // Supprime l'item sélectionné
    function s_item($_argItem, $arg_groupe)
    {
        $return = $this->item;
        if (isset ($this->tableauDesValeursDansItemsDansGroupe [$arg_groupe] [$_argItem])) {
            unset ($this->tableauDesValeursDansItemsDansGroupe [$arg_groupe] [$_argItem]);
        }
    }

    // Si $fichier contient le nom d'un dossier, affiche le liste des fichiers ini que contient ce dossier
   /* function print_dossier()
    {
        $retour = "";
        if (is_dir($this->fichier)) {
            $retour .= "<img src='dir.png' alt='Dossier' /><span style='position:relative; top:-10px;font-size:20px; font-weight:bold;'>" . $this->fichier . self::SPAN_BR;
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
*/
    // Renvoie une chaîne html pour afficher le contenu du fichier ini
    function print_fichier() : string
    {
        $chaineHtml = "Fichier : $this->fichier <br>";

        if (file_exists($this->fichier) && is_file($this->fichier) && $fichier_lecture = file($this->fichier)) {
            // Pour chaque ligne
            foreach ($fichier_lecture as $ligne) {
                $ligne = preg_replace("#\s$#", "", $ligne);

                // variable pour le reset dans le elseif
                $var = explode("=", $ligne);
                // Titre de rubrique en bleu
                if (preg_match("/^\[[a-zA-Z]*\]$/", $ligne)) {
                    $chaineHtml .= "<span style='background-color:aqua;'>" . htmlspecialchars($ligne) . self::SPAN_BR;

                } // première ligne en jaune

                else {
                    $chaineHtml .= htmlspecialchars($ligne) . "<br />";
                }
            }
            return $chaineHtml;
        } else {
            return "Le fichier " . $this->fichier . " n'existe pas ou est incompatible";
        }
        // $this->valeur=$this->fichier_ini[$this->groupe][$this->item];
    }

    // Prend deux paramètres : l'item, le groupe, et renvoie la valeur
    function m_valeur($arg_item, $arg_groupe)
    {
        // echo "Cherche item : " . $arg_item . " dans groupe : " . $arg_groupe . "\n";
        return $this->tableauDesValeursDansItemsDansGroupe [$arg_groupe] [$arg_item];
    }
}