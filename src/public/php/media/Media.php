<?php
require_once PHP_DIR . "/document/Document.php";
require_once PHP_DIR . "/liens/LienUrl.php";
require_once PHP_DIR . "/chanson/Chanson.php";
require_once PHP_DIR . "/utilisateur/Utilisateur.php";
require_once PHP_DIR . "/lib/utilssi.php";
require_once PHP_DIR . "/lib/configMysql.php";
require_once PHP_DIR . "/lib/Image.php";

class Media
{
    const D_M_Y = "d/m/Y";
    const MYSQL = 'mysql';
    const TABLE_CHANSON = "chanson";
    private int $_id; // identifiant en BDD
    private string $_type; // type de média (mp3, pdf, vidéo YouTube)
    private string $_titre; // titre du média
    private string $_image; // URL de l'image associée
    private int $_auteur; // identifiant de l'utilisateur ayant proposé le média
    private string $_lien; // URL de la ressource
    private string $_description; // description du média
    private string $_tags; // tags associés au média
    private string $_datePub; // date de publication en AAAA-MM-JJ
    private int $_hits; // compteur de visites
    private $_lastError = ""; // pour stocker la dernière erreur

    function __construct()
    {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = '__construct' . $i)) {
            call_user_func_array(array($this, $f), $a);
        }
    }

    public function __construct0()
    {
        $this->_id = 0;
        $this->setType("");
        $this->setTitre("");
        $this->setImage("");
        $this->setAuteur(1);
        $this->setLien("");
        $this->setDescription("");
        $this->setTags("");
        $this->setDatePub(convertitDateJJMMAAAAversMySql(date(self::D_M_Y)));
        $this->setHits(0);
    }

    public function __construct7($_type, $_titre, $_image, $_auteur, $_lien, $_description, $_tags)
    {
        $this->setId(0);
        $this->setType($_type);
        $this->setTitre($_titre);
        $this->setImage($_image);
        $this->setAuteur($_auteur);
        $this->setLien($_lien);
        $this->setDescription($_description);
        $this->setTags($_tags);
        $this->setDatePub(convertitDateJJMMAAAAversMySql(date(self::D_M_Y)));
        $this->setHits(0);
    }

    public function __construct8($_id, $_type, $_titre, $_image, $_auteur, $_lien, $_description, $_tags)
    {
        $this->__construct7($_type, $_titre, $_image, $_auteur, $_lien, $_description, $_tags);
        $this->setId($_id);
    }

    // Getters et Setters

    public function getId(): int
    {
        return $this->_id;
    }

    public function setId(int $id): void
    {
        if ($id > 0) {
            $this->_id = $id;
        }
    }

    public function getType(): string
    {
        return $this->_type;
    }

    public function setType(string $type): void
    {
        $this->_type = $type;
    }

    public function getTitre(): string
    {
        return $this->_titre;
    }

    public function setTitre(string $titre): void
    {
        $this->_titre = $titre;
    }

    public function getImage(): string
    {
        return $this->_image;
    }

    public function setImage(string $image): void
    {
        $this->_image = $image;
    }

    public function getAuteur(): int
    {
        return $this->_auteur;
    }

    public function setAuteur(int $auteur): void
    {
        if ($auteur > 0) {
            $this->_auteur = $auteur;
        }
    }

    public function getLien(): string
    {
        return $this->_lien;
    }

    public function setLien(string $lien): void
    {
        $this->_lien = $lien;
    }

    public function getDescription(): string
    {
        return $this->_description;
    }

    public function setDescription(string $description): void
    {
        $this->_description = $description;
    }

    public function getTags(): string
    {
        return $this->_tags;
    }

    public function setTags(string $tags): void
    {
        $this->_tags = $tags;
    }

    public function getDatePub(): string
    {
        return $this->_datePub;
    }

    public function setDatePub(string $datePub): void
    {
        $this->_datePub = $datePub;
    }

    public function getHits(): int
    {
        return $this->_hits;
    }

    public function setHits(int $hits): void
    {
        if ($hits >= 0) {
            $this->_hits = $hits;
        }
    }

    public function getLastError()
    {
        return $this->_lastError;
    }


    // Méthode pour créer ou modifier un média en BDD
    public function persist()
    {
        $this->checkDbConnection();
        $idExistant = self::verifieExistenceMedia($this->_lien);
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
            require_once PHP_DIR . "/lib/configMysql.php";
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
            $db->real_escape_string($this->_type),
            $db->real_escape_string($this->_titre),
            $db->real_escape_string($this->_image),
            $db->real_escape_string((string)$this->_auteur),
            $db->real_escape_string($this->_lien),
            $db->real_escape_string($this->_description),
            $db->real_escape_string($this->_tags),
            $db->real_escape_string($this->_datePub),
            $db->real_escape_string((string)$this->_hits));

        $result = $db->query($maRequete);
        if (!$result) {
            $this->_lastError = $db->error;
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
            $db->real_escape_string($this->_type),
            $db->real_escape_string($this->_titre),
            $db->real_escape_string($this->_image),
            $db->real_escape_string((string)$this->_auteur),
            $db->real_escape_string($this->_lien),
            $db->real_escape_string($this->_description),
            $db->real_escape_string($this->_tags),
            $db->real_escape_string($this->_datePub),
            $db->real_escape_string((string)$this->_hits),
            (int)$this->getId()
        );

        return $db->query($maRequete);
    }

    public function supprimeMediaBDD()
    {
        $this->checkDbConnection();
        $maRequete = "DELETE FROM media WHERE id = " . (int)$this->getId();
        $_SESSION[self::MYSQL]->query($maRequete);
    }

    public function infosMedia(): string
    {
        return "Id : " . $this->_id . " Titre : " . $this->_titre . "<br>\n";
    }

    public function chercheMedia($id): int
    {
        $this->checkDbConnection();
        $maRequete = sprintf("SELECT * FROM media WHERE id = %d", (int)$id);
        $result = $_SESSION[self::MYSQL]->query($maRequete);
        if ($ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return 1;
        }
        return 0;
    }

    private function mysqlRowVersObjet($ligne)
    {
        $this->_id = (int)$ligne[0];
        $this->_type = (string)$ligne[1];
        $this->_titre = (string)$ligne[2];
        $this->_image = (string)$ligne[3];
        $this->_auteur = (int)$ligne[4];
        $this->_lien = (string)$ligne[5];
        $this->_description = (string)$ligne[6];
        $this->_tags = (string)$ligne[7];
        $this->_datePub = (string)$ligne[8];
        $this->_hits = (int)$ligne[9];
    }

    public static function chercheMediasParType($type): array
    {
        if (!isset($_SESSION[self::MYSQL]) || !($_SESSION[self::MYSQL] instanceof mysqli) || $_SESSION[self::MYSQL]->connect_error) {
            require_once PHP_DIR . "/lib/configMysql.php";
        }
        $db = $_SESSION[self::MYSQL];
        $type = $db->real_escape_string($type);
        $maRequete = "SELECT id FROM media WHERE type = '$type'";
        $result = $db->query($maRequete);
        $tableau = [];
        while ($row = $result->fetch_row()) {
            $tableau[] = $row[0];
        }
        return $tableau;
    }

    public static function chercheTousLesMedias(): array
    {
        if (!isset($_SESSION[self::MYSQL]) || !($_SESSION[self::MYSQL] instanceof mysqli) || $_SESSION[self::MYSQL]->connect_error) {
            require_once PHP_DIR . "/lib/configMysql.php";
        }
        $maRequete = "SELECT id FROM media ORDER BY datePub DESC";
        $result = $_SESSION[self::MYSQL]->query($maRequete);
        $tableau = [];
        while ($row = $result->fetch_row()) {
            $tableau[] = $row[0];
        }
        return $tableau;
    }

    public static function normalize($string): string
    {
        $string = mb_strtolower($string);
        $string = preg_replace('/[áàâãäå]/u', 'a', $string);
        $string = preg_replace('/[éèêë]/u', 'e', $string);
        $string = preg_replace('/[íìîï]/u', 'i', $string);
        $string = preg_replace('/[óòôõö]/u', 'o', $string);
        $string = preg_replace('/[úùûü]/u', 'u', $string);
        $string = preg_replace('/[ýÿ]/u', 'y', $string);
        $string = preg_replace('/ç/u', 'c', $string);
        $string = preg_replace('/ñ/u', 'n', $string);
        $string = preg_replace('/[^a-z0-9\s]/', ' ', $string);
        $string = preg_replace('/\s+/', ' ', $string);
        return trim($string);
    }

    public function chercheNdernieresPartoches($nombrePartoches = 100): void
    {
        $this->checkDbConnection();
        $compteur = 0;
        $documents = chercheDocuments("nomTable", "chanson", "date", false);
        while ($compteur < $nombrePartoches && $document = $documents->fetch_row()) {
            if (str_ends_with(strtolower($document[1]), ".pdf")) {
                $this->ajouteDocument($document[0], "partoche");
                $compteur++;
            }
        }
    }

    public function chercheNderniersAudios($nombreAudios = 50): void
    {
        $this->checkDbConnection();
        $compteur = 0;
        
        // 1. Chercher dans les documents (fichiers mp3, m4a, aac)
        $exts = ["mp3", "m4a", "aac"];
        foreach ($exts as $ext) {
            $documents = chercheDocuments("nom", "%.$ext", "date", false);
            while ($compteur < $nombreAudios && $document = $documents->fetch_row()) {
                if ($document[6] > 0 && $document[2] == 'chanson') { // idTable > 0 et nomTable == chanson
                    $this->ajouteDocument($document[0], "audio");
                    $compteur++;
                }
            }
        }

        // 2. Chercher dans les liens (type "Audio")
        $nderniersLiens = chercheNderniersLiens("Audio");
        while ($compteur < $nombreAudios && $liensUrl = $nderniersLiens->fetch_row()) {
            $this->ajouteLienurl($liensUrl[0]);
            $compteur++;
        }
    }

    public function chercheNdernieresVideos($nombreVideos = 50): void
    {
        $this->checkDbConnection();
        $compteur = 0;
        $nderniersLiens = chercheNderniersLiens("vid%"); // "vid%" pour attraper Vidéo, Vidéo, video, Video
        while ($compteur < $nombreVideos && $liensUrl = $nderniersLiens->fetch_row()) {
            $this->ajouteLienurl($liensUrl[0]);
            $compteur++;
        }
        
        // On cherche aussi les types "Video" explicitement au cas où
        $nderniersLiens = chercheNderniersLiens("Video");
        while ($compteur < $nombreVideos && $liensUrl = $nderniersLiens->fetch_row()) {
            $this->ajouteLienurl($liensUrl[0]);
            $compteur++;
        }
    }

    public function transformeDocumentEnMedia($idDoc, $typeForce = null): void
    {
        $this->checkDbConnection();
        $document = chercheDocument($idDoc);
        $idChanson = $document[6];
        $chanson = new Chanson($idChanson);
        
        $extension = strtolower(pathinfo($document[1], PATHINFO_EXTENSION));
        $type = $typeForce ?? ($extension === 'pdf' ? 'partoche' : 'audio');

        $this->setTitre($chanson->getNom());
        $descPrefix = ($type === 'partoche') ? "Partoche" : "Audio";
        $this->setDescription("$descPrefix pour la chanson de " . $chanson->getInterprete() . " - " . $chanson->getAnnee());
        $this->setAuteur((int)$document[7]);
        $this->setDatePub($document[3]);
        $this->setType($type);
        $this->setTags("$type " . $chanson->getAnnee());
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
        $type = (string)$lienUrl[4];
        $descPrefix = (str_contains(strtolower($type), 'vid')) ? "Vidéo" : "Audio";
        $this->setDescription("$descPrefix pour la chanson de " . $chanson->getInterprete() . " - " . $chanson->getAnnee());
        $this->setAuteur((int)($lienUrl[7] ?? 1));
        $this->setDatePub($lienUrl[6]);
        $this->setType($type);
        $this->setTags($type . " " . $chanson->getAnnee());
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

        $type = strtolower($this->_type);
        $titre = htmlspecialchars($this->_titre);
        
        // On récupère le chemin relatif propre (ex: 354/cover.jpg)
        $imageRelative = ltrim($this->_image, './data/chansons/');
        $imageUrl = Image::getThumbnailUrl($imageRelative, 'sd');

        $lien = ($type === "partoche")
            ? "../../" . ltrim(htmlspecialchars($this->_lien), './')
            : htmlspecialchars($this->_lien);

        $auteur = chercheUtilisateur($this->_auteur);
        $auteurNom = htmlspecialchars($auteur[3] ?? "Auteur inconnu");

        $isVideo = str_contains($type, 'vid');
        $isAudio = ($type === 'audio' || $type === 'mp3' || $type === 'm4a');
        
        $couleurBadge = "danger"; // Par défaut Partoche
        $emoji = "🎵";

        if ($isVideo) {
            $couleurBadge = "primary";
            $emoji = "🎬";
        } elseif ($isAudio) {
            $couleurBadge = "warning";
            $emoji = "🔊";
        }

        return [
            'type' => $type,
            'titre' => $titre,
            'imageUrl' => $imageUrl,
            'id_chanson' => $idChanson,
            'chanson_titre' => $chansonTitre,
            'lien' => $lien,
            'description' => htmlspecialchars($this->_description),
            'datePub' => htmlspecialchars($this->_datePub),
            'auteurNom' => $auteurNom,
            'couleurBadge' => $couleurBadge,
            'emoji' => $emoji
        ];
    }

    private function getIdChansonAssocie(): ?int
    {
        $this->checkDbConnection();
        $requete = "";
        if ($this->getType() === 'partoche' || $this->getType() === 'audio') {
            if (preg_match('/doc=(\d+)/', $this->getLien(), $matches)) {
                $idDocument = (int)$matches[1];
                $requete = "SELECT idTable FROM document WHERE id = $idDocument AND nomTable = 'chanson' LIMIT 1";
            }
        } else {
            $lien = $_SESSION[self::MYSQL]->real_escape_string($this->getLien());
            $requete = "SELECT idtable FROM lienurl WHERE nomtable = 'chanson' AND url = '$lien' LIMIT 1";
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

    public function resetMediaTable(int $totalMedias = 50): void
    {
        $this->checkDbConnection();
        $db = $_SESSION[self::MYSQL];
        $db->query("DELETE FROM media"); // On vide TOUT avant de reconstruire
        $this->resetMediasDistribues($totalMedias);
    }

    public function resetMediasDistribues(int $totalMedias = 50): array
    {
        $this->checkDbConnection();
        $db = $_SESSION[self::MYSQL];

        // On définit des quotas plus équilibrés pour être sûr de voir de tout
        // Même si une catégorie est moins représentée en BDD, on veut ses derniers éléments.
        $nbVideosATraiter = 15;
        $nbAudiosATraiter = 15;
        $nbPartochesATraiter = $totalMedias - $nbVideosATraiter - $nbAudiosATraiter; // 20 par défaut

        $this->resetAvecDernieresPartoches($nbPartochesATraiter);
        $this->resetAvecDernieresVideos($nbVideosATraiter);
        $this->resetAvecDerniersAudios($nbAudiosATraiter);

        return [$nbVideosATraiter, $nbPartochesATraiter, $nbAudiosATraiter];
    }

    private function checkDbConnection(): void
    {
        if (!isset($_SESSION[self::MYSQL]) || !($_SESSION[self::MYSQL] instanceof mysqli) || $_SESSION[self::MYSQL]->connect_error) {
            require_once PHP_DIR . "/lib/configMysql.php";
        }
    }
}
