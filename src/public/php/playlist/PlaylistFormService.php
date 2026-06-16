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
}
