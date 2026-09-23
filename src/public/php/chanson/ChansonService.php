<?php
/**
 * Service pour la logique métier de la page chanson_voir.
 */
class ChansonService
{
    /**
     * Rassemble toutes les données nécessaires à l'affichage d'une chanson.
     */
    public static function getChansonViewData(Chanson $chanson): array
    {
        $id = $chanson->getId();
        
        // 1. Utilisateur (Auteur)
        $userRow = Utilisateur::chercheUtilisateur($chanson->getIdUser());
        $utilisateur = $userRow[1] ?? 'Inconnu';

        // 2. Documents et filtrage (Pochettes vs Médias)
        $resultDocs = Document::chercheDocumentsTableId("chanson", $id);
        $documents = [];
        $medias = [];
        $nbImages = 0;
        $hasAudio = false;
        $canAccessAudio = MediaService::estAudioAccessible();

        if (!empty($resultDocs)) {
            while ($ligne = $resultDocs->fetch_row()) {
                $ext = strtolower(pathinfo($ligne[1], PATHINFO_EXTENSION));
                
                if (in_array($ext, ['mp3', 'm4a', 'aac', 'mp4'])) {
                    if (in_array($ext, ['mp3', 'm4a', 'aac'])) {
                        $hasAudio = true;
                    }
                    $medias[] = $ligne;
                } else {
                    $documents[] = $ligne;
                    if (in_array($ext, ['jpg', 'png', 'webp'])) $nbImages++;
                }
            }
        }

        // 3. Strums (Rythmiques)
        $strums = [];
        if (class_exists('LienStrumChanson')) {
            $resStrum = LienStrumChanson::chercheLiensStrumChanson("idChanson", $id);
            if ($resStrum) {
                while ($l = $resStrum->fetch_row()) {
                    $s = new Strum();
                    $s->chercheStrumParChaine($l[1]);
                    $strums[] = $s;
                }
            }
        }

        // 4. Liens URL
        $liens = ChansonRepository::getLinks($id);

        // 5. Songbooks
        $songbooks = ChansonRepository::getSongbooks($id);

        return [
            'chanson' => $chanson,
            'id' => $id,
            'auteur' => $utilisateur,
            'documents' => $documents,
            'medias' => $medias,
            'hasAudio' => $hasAudio,
            'canAccessAudio' => $canAccessAudio,
            'nbImages' => $nbImages,
            'strums' => $strums,
            'liens' => $liens,
            'songbooks' => $songbooks,
            'tempoInfo' => self::getTempoInfo($chanson->getTempo())
        ];
    }

    public static function getTempoInfo(int $bpm): array 
    {
        return match(true) {
            $bpm < 60  => ['name' => 'Largo',    'label' => 'Largo'],
            $bpm < 76  => ['name' => 'Adagio',   'label' => 'Adagio'],
            $bpm < 108 => ['name' => 'Andante',  'label' => 'Andante'],
            $bpm < 120 => ['name' => 'Moderato', 'label' => 'Moderato'],
            $bpm < 156 => ['name' => 'Allegro',  'label' => 'Allegro'],
            $bpm < 176 => ['name' => 'Vivace',   'label' => 'Vivace'],
            default    => ['name' => 'Presto',   'label' => 'Presto'],
        };
    }
}
