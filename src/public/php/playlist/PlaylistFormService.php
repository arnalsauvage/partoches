<?php
/**
 * Service de préparation du formulaire de Playlist.
 */

class PlaylistFormService
{
    /**
     * Prépare les données pour le formulaire.
     */
    public static function prepareData(int $id): array
    {
        $mode = ($id > 0) ? "MAJ" : "INS";

        if ($mode === "MAJ") {
            $donnee = chercheplaylist($id);
            $typePl = $donnee['type'] ?? 0;
            $criteres = json_decode($donnee['criteres'] ?? "[]", true);
        } else {
            $donnee = [
                'id' => 0, 
                'nom' => '', 
                'description' => '', 
                'date_creation' => date("Y-m-d"), 
                'image' => '', 
                'hits' => 0, 
                'type' => 0, 
                'criteres' => ''
            ];
            $typePl = 0;
            $criteres = [];
        }

        return [
            'id' => $id,
            'mode' => $mode,
            'playlist' => $donnee,
            'typePl' => $typePl,
            'criteres' => $criteres
        ];
    }

    /**
     * Gère les actions (up, down, del) sur les morceaux de la playlist.
     */
    public static function handleActions(int $id, array $get): void
    {
        if (isset($get['action']) && isset($get['rang'])) {
            $rang = (int)$get['rang'];
            if ($get['action'] === "up") remonteTitrePlaylist($id, $rang, 1);
            if ($get['action'] === "down") descendTitrePlaylist($id, $rang, 1);
            if ($get['action'] === "del" && isset($get['idLien'])) {
                supprimelienChansonPlaylist((int)$get['idLien']);
                ordonneLiensPlaylist($id);
            }
            redirection("playlist_form.php?id=$id&msg=OK_ACTION");
        }
    }

    /**
     * Ajoute un morceau à la playlist.
     */
    public static function addSong(int $id, int $chansonId): string
    {
        creelienChansonPlaylist($chansonId, $id);
        ordonneLiensPlaylist($id);
        return "Morceau ajouté avec succès !";
    }

    /**
     * Importe toutes les chansons d'un songbook dans la playlist.
     */
    public static function importFromSongbook(int $idPlaylist, int $idSongbook): string
    {
        $db = $_SESSION['mysql'];
        
        // 1. Récupérer les documents du songbook
        $resDocs = $db->query("SELECT idDocument FROM liendocsongbook WHERE idSongbook = $idSongbook");
        if (!$resDocs) return "Erreur lors de la récupération des documents.";

        $count = 0;
        $addedIds = [];

        while ($row = $resDocs->fetch_assoc()) {
            $idDoc = (int)$row['idDocument'];
            
            // 2. Trouver la chanson rattachée à ce document
            $resCh = $db->query("SELECT idChanson FROM document WHERE id = $idDoc");
            if ($resCh && ($ch = $resCh->fetch_assoc())) {
                $idChanson = (int)$ch['idChanson'];
                
                // 3. Éviter les doublons dans la playlist
                $check = $db->query("SELECT id FROM lienchansonplaylist WHERE id_playlist = $idPlaylist AND id_chanson = $idChanson");
                if ($check && $check->num_rows == 0 && !in_array($idChanson, $addedIds)) {
                    creelienChansonPlaylist($idChanson, $idPlaylist);
                    $addedIds[] = $idChanson;
                    $count++;
                }
            }
        }

        if ($count > 0) {
            ordonneLiensPlaylist($idPlaylist);
            return "Succès : $count chanson(s) importée(s) du songbook !";
        }
        
        return "Aucune nouvelle chanson ajoutée (déjà présentes ou songbook vide).";
    }
}
