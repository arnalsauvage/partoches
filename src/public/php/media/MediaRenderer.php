<?php
require_once dirname(__DIR__, 3) . "/autoload.php";
require_once PHP_DIR . "/lib/utilssi.php";

class MediaRenderer
{
    /**
     * Affiche une vignette de média (HTML)
     */
    public static function afficheComposantMedia(Media $media): string
    {
        $data = self::prepareData($media);
        
        $songLinkHtml = '';
        if (!empty($data['id_chanson'])) {
            $songLinkHtml = <<<HTML
<a href="../../php/chanson/chanson_voir.php?id={$data['id_chanson']}" class="btn btn-sm btn-primary mt-2">Fiche chanson</a>
HTML;
        }

        return <<<HTML
        <div class="col-sm-6 col-md-4 col-lg-3" style="margin-bottom: 25px;">
            <a href="{$data['lien']}" target="_blank" class="text-decoration-none media-link" style="display:block;">
                <article class="media-card shadow-sm border" style="height: 100%; display: flex; flex-direction: column;">
                    <div class="card-body d-flex flex-column align-items-center text-center" style="padding: 15px; flex-grow: 1;">
                        <span class="badge bg-{$data['couleurBadge']} mb-2" style="font-size: 12px;">{$data['emoji']} {$data['type']}</span>
                        <h5 class="card-title mb-1 text-dark" style="font-weight: bold; height: 40px; overflow: hidden;">{$data['titre']}</h5>
                        <img src="{$data['imageUrl']}" alt="Illustration : {$data['titre']}"
                             class="card-img-top my-2"
                             loading="lazy"
                             style="height:140px; width:100%; object-fit:cover; border-radius: 8px;">
                        <p class="card-text small mt-2 mb-1 text-muted" style="height: 40px; overflow: hidden; font-size: 11px;">{$data['description']}</p>
                        <p class="meta-pub mb-1" style="font-size: 10px; color: #999;">Publié le {$data['datePub']} par <strong>{$data['auteurNom']}</strong></p>
                        {$songLinkHtml}
                    </div>
                </article>
            </a>
        </div>
HTML;
    }

    private static function prepareData(Media $media): array
    {
        $idChanson = MediaRepository::getIdChansonAssocie($media);
        $chansonTitre = '';

        if ($idChanson > 0) {
            $chanson = new Chanson($idChanson);
            $chansonTitre = $chanson->getNom();
        }

        $typeMedia = strtolower($media->getType());
        $titreMedia = htmlspecialchars($media->getTitre());
        
        $imageRelative = ltrim($media->getImage(), './data/chansons/');
        $imageUrl = Image::getThumbnailUrl($imageRelative, 'sd');

        $lienRaw = $media->getLien();
        if (str_contains($lienRaw, 'getdoc.php')) {
            $lienFinal = "../../" . ltrim(ltrim($lienRaw, '.'), '/');
        } else {
            $lienFinal = htmlspecialchars($lienRaw);
        }

        $auteurData = Utilisateur::chercheUtilisateur($media->getAuteur());
        $auteurNom = htmlspecialchars($auteurData[3] ?? "Auteur inconnu");

        $config = self::getConfigByType($typeMedia);

        return [
            'type' => $typeMedia,
            'titre' => $titreMedia,
            'imageUrl' => $imageUrl,
            'id_chanson' => $idChanson,
            'chanson_titre' => $chansonTitre,
            'lien' => $lienFinal,
            'description' => htmlspecialchars($media->getDescription()),
            'datePub' => htmlspecialchars($media->getDatePub()),
            'auteurNom' => $auteurNom,
            'couleurBadge' => $config['couleurBadge'],
            'emoji' => $config['emoji']
        ];
    }

    public static function getConfigByType(string $typeMedia): array
    {
        $isVideo = str_contains($typeMedia, 'vid');
        $isAudio = ($typeMedia === 'audio' || $typeMedia === 'mp3' || $typeMedia === 'm4a');
        $isPartoche = ($typeMedia === 'partoche' || $typeMedia === 'pdf');
        
        $config = [
            'couleurBadge' => 'default',
            'emoji' => '📄'
        ];

        if ($isVideo) {
            $config['couleurBadge'] = "primary";
            $config['emoji'] = "🎬";
        } elseif ($isAudio) {
            $config['couleurBadge'] = "warning";
            $config['emoji'] = "🔊";
        } elseif ($isPartoche) {
            $config['couleurBadge'] = "danger";
            $config['emoji'] = "🎵";
        } elseif ($typeMedia === 'musescore') {
            $config['couleurBadge'] = "success";
            $config['emoji'] = "🎼";
        } elseif ($typeMedia === 'mise en page') {
            $config['couleurBadge'] = "info";
            $config['emoji'] = "🎨";
        } elseif ($typeMedia === 'songpress') {
            $config['couleurBadge'] = "success";
            $config['emoji'] = "🎸";
        } elseif ($typeMedia === 'diapo') {
            $config['couleurBadge'] = "warning";
            $config['emoji'] = "📽️";
        }

        return $config;
    }
}
