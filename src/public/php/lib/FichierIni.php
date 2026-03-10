<?php

class FichierIni
{
    // Constantes
    public const SUPPRIM_EACUTE = ") supprim&eacute;";
    public const B_BR = "</b><br />";
    public const SPAN_BR = "</span><br />";

    // Propriétés
    public string $fichier = "";
    public array $tableauDesValeursDansItemsDansGroupe = [];
    public ?string $item = null;
    public ?string $groupe = null;

    /**
     * Charge un fichier INI dans l'objet
     */
    public function m_load_fichier(string $chemin): void
    {
        $this->fichier = $chemin;
        $this->tableauDesValeursDansItemsDansGroupe = [];
        $groupe_curseur = "general";
        $this->groupe = $groupe_curseur;

        if (!file_exists($chemin)) {
            return;
        }

        $lignes = file($chemin, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lignes as $ligne) {
            $ligne_propre = trim($ligne);

            // Ignorer les commentaires
            if ($ligne_propre === '' || $ligne_propre[0] === ';') {
                continue;
            }

            // Groupe
            if (preg_match('/^\[(.+)\]$/', $ligne_propre, $matches)) {
                $groupe_curseur = trim($matches[1]);
                $this->groupe = $groupe_curseur;
                continue;
            }

            // Item = valeur
            if ($tableau = explode('=', $ligne_propre, 2)) {
                $cle = trim($tableau[0]);
                $valeur = isset($tableau[1]) ? trim($tableau[1]) : '';
                $this->tableauDesValeursDansItemsDansGroupe[$groupe_curseur][$cle] = $valeur;
                $this->item = $cle;
            }
        }
    }

    /**
     * Récupère la valeur d'un item
     */
    public function m_valeur(string $arg_item, string $arg_groupe): ?string
    {
        return $this->tableauDesValeursDansItemsDansGroupe[$arg_groupe][$arg_item] ?? null;
    }

    /**
     * Modifie ou ajoute un item dans un groupe
     */
    public function m_put(string $valeur, string $item, string $groupe): void
    {
        $this->tableauDesValeursDansItemsDansGroupe[$groupe][$item] = $valeur;
    }

    /**
     * Compte les éléments
     */
    public function m_count(string|false $groupe = false): int|array
    {
        if ($groupe === false) {
            $nb_groupes = count($this->tableauDesValeursDansItemsDansGroupe);
            $nb_items_total = count($this->tableauDesValeursDansItemsDansGroupe, COUNT_RECURSIVE) - $nb_groupes;
            return [1 => $nb_groupes, 0 => $nb_items_total, 2 => $nb_items_total];
        }

        return count($this->tableauDesValeursDansItemsDansGroupe[$groupe] ?? []);
    }

    /**
     * Retourne tous les items d'un groupe
     */
    public function m_array_get_items_groupe(string $groupe): array
    {
        return $this->tableauDesValeursDansItemsDansGroupe[$groupe] ?? [];
    }

    /**
     * Sauvegarde le fichier INI
     */
    public function save(): bool
    {
        $contenu = '';
        foreach ($this->tableauDesValeursDansItemsDansGroupe as $groupe => $items) {
            $contenu .= "[$groupe]\r\n";
            foreach ($items as $cle => $valeur) {
                $contenu .= "$cle = $valeur\r\n";
            }
        }

        if (@file_put_contents($this->fichier, $contenu) === false) {
            throw new RuntimeException("Impossible d'écrire dans le fichier: $this->fichier");
        }

        return true;
    }

    /**
     * Supprime le fichier
     */
    public function m_delete_fichier(): string
    {
        $retour = $this->fichier;
        if (file_exists($this->fichier)) {
            unlink($this->fichier);
        }
        $this->fichier = '';
        return "fichier($retour" . self::SUPPRIM_EACUTE;
    }

    /**
     * Supprime un item d'un groupe
     */
    public function s_item(string $item, string $groupe): void
    {
        if (isset($this->tableauDesValeursDansItemsDansGroupe[$groupe][$item])) {
            unset($this->tableauDesValeursDansItemsDansGroupe[$groupe][$item]);
        }
    }

    /**
     * Affiche le contenu du fichier en HTML
     */
    public function print_fichier(): string
    {
        $html = "Fichier : $this->fichier <br>";

        if (!file_exists($this->fichier)) {
            return "Le fichier $this->fichier n'existe pas ou est incompatible";
        }

        $lignes = file($this->fichier, FILE_IGNORE_NEW_LINES);
        foreach ($lignes as $ligne) {
            $ligne = rtrim($ligne);
            if (preg_match('/^\[[a-zA-Z0-9_]+\]$/', $ligne)) {
                $html .= "<span style='background-color:aqua;'>" . htmlspecialchars($ligne) . self::SPAN_BR;
            } else {
                $html .= htmlspecialchars($ligne) . "<br />";
            }
        }

        return $html;
    }
}
