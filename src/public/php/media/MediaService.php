<?php
require_once dirname(__DIR__, 3) . "/autoload.php";
require_once PHP_DIR . "/lib/utilssi.php";

class MediaService
{
    const MYSQL = 'mysql';

    /**
     * Réinitialise complètement la table des médias
     */
    public static function resetMediaTable(): array
    {
        MediaRepository::truncateTable();
        return self::resetMediasDistribues();
    }

    /**
     * Reconstruit l'index des médias selon les quotas
     */
    public static function resetMediasDistribues(int $limit = 500): array
    {
        $nbVideosATraiter = $limit;
        $nbAudiosATraiter = $limit;
        $nbAutresDocsATraiter = $limit;
        $nbPartochesATraiter = $limit;

        self::chercheNdernieresPartoches($nbPartochesATraiter);
        self::chercheNdernieresVideos($nbVideosATraiter);
        self::chercheNderniersAudios($nbAudiosATraiter);
        self::chercheAutresDocuments($nbAutresDocsATraiter);

        return [$nbVideosATraiter, $nbPartochesATraiter, $nbAudiosATraiter, $nbAutresDocsATraiter];
    }

    public static function chercheNdernieresPartoches(int $limit = 500): void
    {
        $db = $_SESSION[self::MYSQL];
        $maRequete = "SELECT id FROM document WHERE nomTable = 'chanson' AND nom LIKE '%.pdf' ORDER BY date DESC LIMIT $limit";
        $result = $db->query($maRequete);
        if ($result) {
            while ($row = $result->fetch_row()) {
                self::ajouteDocument((int)$row[0], "partoche");
            }
        }
    }

    public static function chercheNderniersAudios(int $limit = 500): void
    {
        $db = $_SESSION[self::MYSQL];
        $maRequete = "SELECT id FROM document WHERE nomTable='chanson' AND (nom LIKE '%.mp3' OR nom LIKE '%.m4a' OR nom LIKE '%.aac') ORDER BY date DESC LIMIT $limit";
        $result = $db->query($maRequete);
        if ($result) {
            while ($row = $result->fetch_row()) {
                self::ajouteDocument((int)$row[0], "audio");
            }
        }
        $nderniersLiens = LienUrl::chercheNderniersLiens("Audio");
        if ($nderniersLiens) {
            while ($liensUrl = $nderniersLiens->fetch_row()) {
                self::ajouteLienurl((int)$liensUrl[0]);
            }
        }
    }

    public static function chercheNdernieresVideos(int $limit = 500): void
    {
        $db = $_SESSION[self::MYSQL];
        $nderniersLiens = LienUrl::chercheNderniersLiens("vid%"); 
        if ($nderniersLiens) {
            while ($liensUrl = $nderniersLiens->fetch_row()) {
                self::ajouteLienurl((int)$liensUrl[0]);
            }
        }
        $maRequete = "SELECT id FROM document WHERE nomTable='chanson' AND nom LIKE '%.mp4' ORDER BY date DESC LIMIT $limit";
        $result = $db->query($maRequete);
        if ($result) {
            while ($row = $result->fetch_row()) {
                self::ajouteDocument((int)$row[0], "vidéo");
            }
        }
    }

    public static function chercheAutresDocuments(int $limit = 1000): void
    {
        $db = $_SESSION[self::MYSQL];
        $maRequete = "SELECT id, nom FROM document WHERE nomTable='chanson' 
                      AND nom NOT LIKE '%.pdf' AND nom NOT LIKE '%.mp3' AND nom NOT LIKE '%.m4a' 
                      AND nom NOT LIKE '%.aac' AND nom NOT LIKE '%.mp4' 
                      AND nom NOT LIKE '%.jpg' AND nom NOT LIKE '%.png' AND nom NOT LIKE '%.webp'
                      ORDER BY date DESC LIMIT $limit";
        $result = $db->query($maRequete);
        if ($result) {
            while ($row = $result->fetch_row()) {
                $ext = strtolower(pathinfo($row[1], PATHINFO_EXTENSION));
                $type = match($ext) {
                    'mscz' => 'musescore',
                    'crd'  => 'songpress',
                    'ppt', 'pptx', 'doc', 'docx', 'svg' => 'document',
                    default => 'fichier'
                };
                self::ajouteDocument((int)$row[0], $type);
            }
        }
    }

    public static function ajouteDocument(int $idDoc, ?string $typeForce = null): bool|int
    {
        $media = new Media();
        self::transformeDocumentEnMedia($media, $idDoc, $typeForce);
        return MediaRepository::persist($media);
    }

    public static function ajouteLienurl(int $idLien): bool|int
    {
        $media = new Media();
        self::transformeLienUrlEnMedia($media, $idLien);
        return MediaRepository::persist($media);
    }

    public static function transformeDocumentEnMedia(Media $media, int $idDoc, ?string $typeForce = null): void
    {
        $document = Document::chercheDocument($idDoc);
        $idChanson = (int)$document[6];
        $chanson = new Chanson($idChanson);
        
        $extension = strtolower(pathinfo($document[1], PATHINFO_EXTENSION));
        $typeDoc = $typeForce ?? ($extension === 'pdf' ? 'partoche' : 'audio');

        $media->setTitre($chanson->getNom());
        $descPrefix = ($typeDoc === 'partoche') ? "Partoche" : "Audio";
        $media->setDescription("$descPrefix pour la chanson de " . $chanson->getInterprete() . " - " . $chanson->getAnnee());
        $media->setAuteur((int)$document[7]);
        $media->setDatePub($document[3]);
        $media->setType($typeDoc);
        $media->setTags("$typeDoc " . $chanson->getAnnee());
        $media->setImage("./data/chansons/$idChanson/" . rawurlencode(Document::imageTableId('chanson', $idChanson)));
        $media->setLien("./php/document/" . Document::lienUrlTelechargeDocument($idDoc));
    }

    public static function transformeLienUrlEnMedia(Media $media, int $idLienurl): void
    {
        $lienUrl = LienUrl::chercheLienurlId($idLienurl);
        $idChanson = (int)$lienUrl[2];
        $chanson = new Chanson($idChanson);
        
        $media->setTitre($chanson->getNom());
        $typeLien = (string)$lienUrl[4];
        $descPrefix = (str_contains(strtolower($typeLien), 'vid')) ? "Vidéo" : "Audio";
        $media->setDescription("$descPrefix pour la chanson de " . $chanson->getInterprete() . " - " . $chanson->getAnnee());
        $media->setAuteur((int)($lienUrl[7] ?? 1));
        $media->setDatePub($lienUrl[6]);
        $media->setType($typeLien);
        $media->setTags($typeLien . " " . $chanson->getAnnee());
        $media->setImage("./data/chansons/$idChanson/" . rawurlencode(Document::imageTableId('chanson', $idChanson)));
        $media->setLien((string)$lienUrl[3]);
    }
}
