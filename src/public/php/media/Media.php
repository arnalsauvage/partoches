<?php
require_once dirname(__DIR__, 2) . "/autoload.php";
require_once PHP_DIR . "/lib/utilssi.php";

class Media
{
    const D_M_Y = "d/m/Y";
    const MYSQL = 'mysql';
    const TABLE_CHANSON = "chanson";
    const CONFIG_MYSQL = "/lib/configMysql.php";

    private int $id = 0; // identifiant en BDD
    private string $type = ""; // type de média (mp3, pdf, vidéo YouTube)
    private string $titre = ""; // titre du média
    private string $image = ""; // URL de l'image associée
    private int $auteur = 1; // identifiant de l'utilisateur ayant proposé le média
    private string $lien = ""; // URL de la ressource
    private string $description = ""; // description du média
    private string $tags = ""; // tags associés au média
    private string $datePub = ""; // date de publication en AAAA-MM-JJ
    private int $hits = 0; // compteur de visites
    private string $lastError = ""; // pour stocker la dernière erreur

    /**
     * Constructeur unique et flexible
     */
    public function __construct(array $data = [])
    {
        $this->datePub = convertitDateJJMMAAAAversMySql(date(self::D_M_Y));
        
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Remplit l'objet à partir d'un tableau
     */
    private function hydrate(array $data): void
    {
        $mapping = [
            'id'          => fn(mixed $v) => $this->setId((int) $v),
            'type'        => fn(mixed $v) => $this->setType((string) $v),
            'titre'       => fn(mixed $v) => $this->setTitre((string) $v),
            'image'       => fn(mixed $v) => $this->setImage((string) $v),
            'auteur'      => fn(mixed $v) => $this->setAuteur((int) $v),
            'lien'        => fn(mixed $v) => $this->setLien((string) $v),
            'description' => fn(mixed $v) => $this->setDescription((string) $v),
            'tags'        => fn(mixed $v) => $this->setTags((string) $v),
            'datePub'     => fn(mixed $v) => $this->setDatePub((string) $v),
            'hits'        => fn(mixed $v) => $this->setHits((int) $v),
        ];

        foreach ($mapping as $key => $setter) {
            if (array_key_exists($key, $data)) {
                $setter($data[$key]);
            }
        }
    }


    // Getters et Setters

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        if ($id > 0) {
            $this->id = $id;
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getAuteur(): int
    {
        return $this->auteur;
    }

    public function setAuteur(int $auteur): void
    {
        if ($auteur > 0) {
            $this->auteur = $auteur;
        }
    }

    public function getLien(): string
    {
        return $this->lien;
    }

    public function setLien(string $lien): void
    {
        $this->lien = $lien;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getTags(): string
    {
        return $this->tags;
    }

    public function setTags(string $tags): void
    {
        $this->tags = $tags;
    }

    public function getDatePub(): string
    {
        return $this->datePub;
    }

    public function setDatePub(string $datePub): void
    {
        $this->datePub = $datePub;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function setHits(int $hits): void
    {
        if ($hits >= 0) {
            $this->hits = $hits;
        }
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }


    // Méthode pour créer ou modifier un média en BDD
    public function persist()
    {
        $this->checkDbConnection();
        $idExistant = self::verifieExistenceMedia($this->lien);
        if ($idExistant !== null) {
            $this->setId($idExistant);
            return $this->modifieMediaBDD();
        } else {
            return $this->creeMediaBDD();
        }
    }

    private static function verifieExistenceMedia(string $lienurl): ?int
    {
        if (!isset($_SESSION[self::MYSQL]) || !($_SESSION[self::MYSQL] instanceof mysqli) || $_SESSION[self::MYSQL]->connect_error) {
            require_once PHP_DIR . self::CONFIG_MYSQL;
        }
        $lienurl = $_SESSION[self::MYSQL]->real_escape_string($lienurl);
        $requete = "SELECT id FROM media WHERE lien = '$lienurl' LIMIT 1";
        $result = $_SESSION[self::MYSQL]->query($requete);

        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['id'];
        }
        return null;
    }

    private function creeMediaBDD(): bool|int
    {
        $this->checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $maRequete = sprintf("INSERT INTO media (type, titre, image, auteur, lien, description, tags, datePub, hits)
            VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            $db->real_escape_string($this->type),
            $db->real_escape_string($this->titre),
            $db->real_escape_string($this->image),
            $db->real_escape_string((string)$this->auteur),
            $db->real_escape_string($this->lien),
            $db->real_escape_string($this->description),
            $db->real_escape_string($this->tags),
            $db->real_escape_string($this->datePub),
            $db->real_escape_string((string)$this->hits));

        $result = $db->query($maRequete);
        if (!$result) {
            $this->lastError = $db->error;
            return false;
        }

        $this->setId($db->insert_id);
        return $this->getId();
    }

    private function modifieMediaBDD() : bool
    {
        $this->checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $maRequete = sprintf(
            "UPDATE media SET type='%s', titre='%s', image='%s', auteur='%s', lien='%s', description='%s', tags='%s', datePub='%s', hits='%s' WHERE id=%d",
            $db->real_escape_string($this->type),
            $db->real_escape_string($this->titre),
            $db->real_escape_string($this->image),
            $db->real_escape_string((string)$this->auteur),
            $db->real_escape_string($this->lien),
            $db->real_escape_string($this->description),
            $db->real_escape_string($this->tags),
            $db->real_escape_string($this->datePub),
            $db->real_escape_string((string)$this->hits),
            (int)$this->getId()
        );

        return $db->query($maRequete);
    }

    public function supprimeMediaBDD(): void
    {
        $this->checkDbConnection();
        $maRequete = "DELETE FROM media WHERE id = " . (int)$this->getId();
        $_SESSION[self::MYSQL]->query($maRequete);
    }

    public function infosMedia(): string
    {
        return "Id : " . $this->id . " Titre : " . $this->titre . "<br>\n";
    }

    public function chercheMedia($id): int
    {
        $this->checkDbConnection();
        $maRequete = sprintf("SELECT * FROM media WHERE id = %d", (int)$id);
        $result = $_SESSION[self::MYSQL]->query($maRequete);
        if ($result && $ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return 1;
        }
        return 0;
    }

    private function mysqlRowVersObjet($ligne): void
    {
        $this->id = (int)$ligne[0];
        $this->type = (string)$ligne[1];
        $this->titre = (string)$ligne[2];
        $this->image = (string)$ligne[3];
        $this->auteur = (int)$ligne[4];
        $this->lien = (string)$ligne[5];
        $this->description = (string)$ligne[6];
        $this->tags = (string)$ligne[7];
        $this->datePub = (string)$ligne[8];
        $this->hits = (int)$ligne[9];
    }

    public static function chercheMediasParType($type): array
    {
        if (!isset($_SESSION[self::MYSQL]) || !($_SESSION[self::MYSQL] instanceof mysqli) || $_SESSION[self::MYSQL]->connect_error) {
            require_once PHP_DIR . self::CONFIG_MYSQL;
        }
        $db = $_SESSION[self::MYSQL];
        $type = $db->real_escape_string($type);
        $maRequete = "SELECT id FROM media WHERE type = '$type'";
        $result = $db->query($maRequete);
        $tableau = [];
        if ($result) {
            while ($row = $result->fetch_row()) {
                $tableau[] = $row[0];
            }
        }
        return $tableau;
    }

    public static function chercheTousLesMedias(int $limit = 50, int $offset = 0, array $filtres = []): array
    {
        if (!isset($_SESSION[self::MYSQL]) || !($_SESSION[self::MYSQL] instanceof mysqli) || $_SESSION[self::MYSQL]->connect_error) {
            require_once PHP_DIR . self::CONFIG_MYSQL;
        }
        $db = $_SESSION[self::MYSQL];
        $where = "";
        
        if (!empty($filtres) && !in_array('tous', $filtres)) {
            $escapedFiltres = array_map(fn($f) => "'" . $db->real_escape_string($f) . "'", $filtres);
            $where = "WHERE type IN (" . implode(',', $escapedFiltres) . ")";
        }

        $maRequete = "SELECT id FROM media $where ORDER BY datePub DESC LIMIT $limit OFFSET $offset";
        $result = $db->query($maRequete);
        $tableau = [];
        if ($result) {
            while ($row = $result->fetch_row()) {
                $tableau[] = $row[0];
            }
        }
        return $tableau;
    }

    public static function compteTousLesMedias(array $filtres = []): int
    {
        if (!isset($_SESSION[self::MYSQL]) || !($_SESSION[self::MYSQL] instanceof mysqli) || $_SESSION[self::MYSQL]->connect_error) {
            require_once PHP_DIR . self::CONFIG_MYSQL;
        }
        $db = $_SESSION[self::MYSQL];
        $where = "";
        
        if (!empty($filtres) && !in_array('tous', $filtres)) {
            $escapedFiltres = array_map(fn($f) => "'" . $db->real_escape_string($f) . "'", $filtres);
            $where = "WHERE type IN (" . implode(',', $escapedFiltres) . ")";
        }
        
        $res = $db->query("SELECT COUNT(*) FROM media $where");
        if ($res) {
            $row = $res->fetch_row();
            return (int)$row[0];
        }
        return 0;
    }

    public function chercheNdernieresPartoches($nombrePartoches = 500): void
    {
        $this->checkDbConnection();
        $maRequete = "SELECT id FROM document WHERE nomTable = 'chanson' AND nom LIKE '%.pdf' ORDER BY date DESC LIMIT $nombrePartoches";
        $result = $_SESSION[self::MYSQL]->query($maRequete);
        if ($result) {
            while ($document = $result->fetch_row()) {
                $this->ajouteDocument($document[0], "partoche");
            }
        }
    }

    public function chercheNderniersAudios($nombreAudios = 500): void
    {
        $this->checkDbConnection();
        $maRequete = "SELECT id FROM document WHERE nomTable='chanson' AND (nom LIKE '%.mp3' OR nom LIKE '%.m4a' OR nom LIKE '%.aac') ORDER BY date DESC LIMIT $nombreAudios";
        $result = $_SESSION[self::MYSQL]->query($maRequete);
        if ($result) {
            while ($document = $result->fetch_row()) {
                $this->ajouteDocument($document[0], "audio");
            }
        }
        $nderniersLiens = chercheNderniersLiens("Audio");
        if ($nderniersLiens) {
            while ($liensUrl = $nderniersLiens->fetch_row()) {
                $this->ajouteLienurl($liensUrl[0]);
            }
        }
    }

    public function chercheNdernieresVideos($nombreVideos = 500): void
    {
        $this->checkDbConnection();
        $nderniersLiens = chercheNderniersLiens("vid%"); 
        if ($nderniersLiens) {
            while ($liensUrl = $nderniersLiens->fetch_row()) {
                $this->ajouteLienurl($liensUrl[0]);
            }
        }
        $maRequete = "SELECT id FROM document WHERE nomTable='chanson' AND nom LIKE '%.mp4' ORDER BY date DESC LIMIT $nombreVideos";
        $result = $_SESSION[self::MYSQL]->query($maRequete);
        if ($result) {
            while ($document = $result->fetch_row()) {
                $this->ajouteDocument($document[0], "vidéo");
            }
        }
    }

    public function chercheAutresDocuments($nombreDocs = 1000): void
    {
        $this->checkDbConnection();
        $maRequete = "SELECT id, nom FROM document WHERE nomTable='chanson' 
                      AND nom NOT LIKE '%.pdf' AND nom NOT LIKE '%.mp3' AND nom NOT LIKE '%.m4a' 
                      AND nom NOT LIKE '%.aac' AND nom NOT LIKE '%.mp4' 
                      AND nom NOT LIKE '%.jpg' AND nom NOT LIKE '%.png' AND nom NOT LIKE '%.webp'
                      ORDER BY date DESC LIMIT $nombreDocs";
        $result = $_SESSION[self::MYSQL]->query($maRequete);
        if ($result) {
            while ($document = $result->fetch_row()) {
                $ext = strtolower(pathinfo($document[1], PATHINFO_EXTENSION));
                $type = match($ext) {
                    'mscz' => 'musescore',
                    'crd'  => 'songpress',
                    'ppt', 'pptx', 'doc', 'docx', 'svg' => 'document',
                    default => 'fichier'
                };
                $this->ajouteDocument($document[0], $type);
            }
        }
    }

    public function transformeDocumentEnMedia($idDoc, $typeForce = null): void
    {
        $this->checkDbConnection();
        $document = chercheDocument($idDoc);
        $idChanson = $document[6];
        $chanson = new Chanson($idChanson);
        
        $extension = strtolower(pathinfo($document[1], PATHINFO_EXTENSION));
        $typeDoc = $typeForce ?? ($extension === 'pdf' ? 'partoche' : 'audio');

        $this->setTitre($chanson->getNom());
        $descPrefix = ($typeDoc === 'partoche') ? "Partoche" : "Audio";
        $this->setDescription("$descPrefix pour la chanson de " . $chanson->getInterprete() . " - " . $chanson->getAnnee());
        $this->setAuteur((int)$document[7]);
        $this->setDatePub($document[3]);
        $this->setType($typeDoc);
        $this->setTags("$typeDoc " . $chanson->getAnnee());
        $this->setImage("./data/chansons/$idChanson/" . rawurlencode(imageTableId(self::TABLE_CHANSON, $idChanson)));
        $this->setLien("./php/document/" . lienUrlTelechargeDocument($idDoc));
    }

    public function transformeLienUrlEnMedia($idLienurl): void
    {
        $this->checkDbConnection();
        $lienUrl = chercheLienurlId($idLienurl);
        $idChanson = $lienUrl[2];
        $chanson = new Chanson($idChanson);
        
        $this->setTitre($chanson->getNom());
        $typeLien = (string)$lienUrl[4];
        $descPrefix = (str_contains(strtolower($typeLien), 'vid')) ? "Vidéo" : "Audio";
        $this->setDescription("$descPrefix pour la chanson de " . $chanson->getInterprete() . " - " . $chanson->getAnnee());
        $this->setAuteur((int)($lienUrl[7] ?? 1));
        $this->setDatePub($lienUrl[6]);
        $this->setType($typeLien);
        $this->setTags($typeLien . " " . $chanson->getAnnee());
        $this->setImage("./data/chansons/$idChanson/" . rawurlencode(imageTableId(self::TABLE_CHANSON, $idChanson)));
        $this->setLien((string)$lienUrl[3]);
    }

    public function ajouteDocument($idDoc, $typeForce = null): void
    {
        $this->checkDbConnection();
        $this->transformeDocumentEnMedia($idDoc, $typeForce);
        $this->persist();
    }

    public function ajouteLienurl($idLien): void
    {
        $this->checkDbConnection();
        $this->transformeLienUrlEnMedia($idLien);
        $this->persist();
    }

    public function afficheComposantMedia(): string
    {
        $data = $this->prepareData();
        
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

    private function prepareData(): array
    {
        $this->checkDbConnection();
        $idChanson = $this->getIdChansonAssocie();
        $chansonTitre = '';

        if ($idChanson > 0) {
            $chanson = new Chanson($idChanson);
            $chansonTitre = $chanson->getNom();
        }

        $typeMedia = strtolower($this->type);
        $titreMedia = htmlspecialchars($this->titre);
        
        $imageRelative = ltrim($this->image, './data/chansons/');
        $imageUrl = Image::getThumbnailUrl($imageRelative, 'sd');

        $lienRaw = $this->lien;
        if (str_contains($lienRaw, 'getdoc.php')) {
            $lienFinal = "../../" . ltrim(ltrim($lienRaw, '.'), '/');
        } else {
            $lienFinal = htmlspecialchars($lienRaw);
        }

        $auteurData = chercheUtilisateur($this->auteur);
        $auteurNom = htmlspecialchars($auteurData[3] ?? "Auteur inconnu");

        $config = $this->getConfigByType($typeMedia);

        return [
            'type' => $typeMedia,
            'titre' => $titreMedia,
            'imageUrl' => $imageUrl,
            'id_chanson' => $idChanson,
            'chanson_titre' => $chansonTitre,
            'lien' => $lienFinal,
            'description' => htmlspecialchars($this->description),
            'datePub' => htmlspecialchars($this->datePub),
            'auteurNom' => $auteurNom,
            'couleurBadge' => $config['couleurBadge'],
            'emoji' => $config['emoji']
        ];
    }

    private function getConfigByType(string $typeMedia): array
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

    private function getIdChansonAssocie(): ?int
    {
        $this->checkDbConnection();
        $requete = "";
        $typeMedia = $this->getType();
        
        $typesFichiers = ['partoche', 'audio', 'musescore', 'songpress', 'document', 'pdf', 'fichier'];
        
        if (in_array($typeMedia, $typesFichiers)) {
            if (preg_match('/doc=(\d+)/', $this->getLien(), $matches)) {
                $idDocument = (int)$matches[1];
                $requete = "SELECT idTable FROM document WHERE id = $idDocument AND nomTable = 'chanson' LIMIT 1";
            }
        } else {
            $lienEscaped = $_SESSION[self::MYSQL]->real_escape_string($this->getLien());
            $requete = "SELECT idtable FROM lienurl WHERE nomtable = 'chanson' AND url = '$lienEscaped' LIMIT 1";
        }

        if (!empty($requete)) {
            $result = $_SESSION[self::MYSQL]->query($requete);
            if ($result && $row = $result->fetch_row()) {
                return (int)$row[0];
            }
        }
        return null;
    }

    public function resetAvecDernieresPartoches(int $nb = 50) :bool
    {
        $this->checkDbConnection();
        $this->chercheNdernieresPartoches($nb);
        return true;
    }

    public function resetAvecDernieresVideos(int $nb = 50) :bool
    {
        $this->checkDbConnection();
        $this->chercheNdernieresVideos($nb);
        return true;
    }

    public function resetAvecDerniersAudios(int $nb = 50) :bool
    {
        $this->checkDbConnection();
        $this->chercheNderniersAudios($nb);
        return true;
    }

    public function resetMediaTable(): void
    {
        $this->checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $db->query("TRUNCATE TABLE media");
        $this->resetMediasDistribues();
    }

    public function resetMediasDistribues(): array
    {
        $this->checkDbConnection();
        
        $nbVideosATraiter = 500;
        $nbAudiosATraiter = 500;
        $nbAutresDocsATraiter = 500;
        $nbPartochesATraiter = 500;

        $this->resetAvecDernieresPartoches($nbPartochesATraiter);
        $this->resetAvecDernieresVideos($nbVideosATraiter);
        $this->resetAvecDerniersAudios($nbAudiosATraiter);
        $this->chercheAutresDocuments($nbAutresDocsATraiter);

        return [$nbVideosATraiter, $nbPartochesATraiter, $nbAudiosATraiter, $nbAutresDocsATraiter];
    }

    private function checkDbConnection(): void
    {
        if (!isset($_SESSION[self::MYSQL]) || !($_SESSION[self::MYSQL] instanceof mysqli) || $_SESSION[self::MYSQL]->connect_error) {
            require_once PHP_DIR . self::CONFIG_MYSQL;
        }
    }
}
