<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/document/document.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/liens/lienurl.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/chanson/chanson.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/utilisateur/utilisateur.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/utilssi.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/configMysql.php";

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
        $lienurl = $_SESSION[self::MYSQL]->real_escape_string($lienurl);
        $requete = "SELECT id FROM media WHERE lien = '$lienurl' LIMIT 1";
        $result = $_SESSION[self::MYSQL]->query($requete);

        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['id'];
        }
        return null;
    }

    // Crée un média et renvoie l'id du média créé
    private function creeMediaBDD(): bool|int
    {
        // Conversion de la date au format MySQL avant l'insertion
        // Bug ? Doublon ?
        $this->_titre = $_SESSION[self::MYSQL]->real_escape_string($this->_titre);
        $this->_image = $_SESSION[self::MYSQL]->real_escape_string($this->_image);
        $this->_lien = $_SESSION[self::MYSQL]->real_escape_string($this->_lien);
        $this->_description = $_SESSION[self::MYSQL]->real_escape_string($this->_description);
        $this->_tags = $_SESSION[self::MYSQL]->real_escape_string($this->_tags);
        $this->_datePub = $_SESSION[self::MYSQL]->real_escape_string($this->_datePub);
        $maRequete = sprintf("INSERT INTO media (id, type, titre, image, auteur, lien, description, tags, datePub, hits)
            VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            $this->_type,
            $this->_titre,
            $this->_image,
            $this->_auteur,
            $this->_lien,
            $this->_description,
            $this->_tags,
            $this->_datePub,
            $this->_hits);

        $result = $_SESSION[self::MYSQL]->query($maRequete);
        if (!$result) {
            $this->_lastError = $_SESSION[self::MYSQL]->error;
            return false; // Erreur lors de l'insertion
        }


        $this->setId($_SESSION[self::MYSQL]->insert_id);
        return $this->getId();
    }

    private function modifieMediaBDD() : bool
    {
        // Échappement des chaînes
        $this->_titre = $_SESSION[self::MYSQL]->real_escape_string($this->_titre);
        $this->_image = $_SESSION[self::MYSQL]->real_escape_string($this->_image);
        $this->_lien = $_SESSION[self::MYSQL]->real_escape_string($this->_lien);
        $this->_description = $_SESSION[self::MYSQL]->real_escape_string($this->_description);
        $this->_tags = $_SESSION[self::MYSQL]->real_escape_string($this->_tags);

        $id = (int)$this->getId();

        $maRequete = sprintf(
            "UPDATE media SET type='%s', titre='%s', image='%s', auteur='%s', lien='%s', description='%s', tags='%s', datePub='%s', hits='%s' WHERE id=%d",
            $this->_type,
            $this->_titre,
            $this->_image,
            $this->_auteur,
            $this->_lien,
            $this->_description,
            $this->_tags,
            $this->_datePub,
            $this->_hits,
            $id
        );

        $result = $_SESSION[self::MYSQL]->query($maRequete) or die("Problème dans modifieMediaBDD : " . $_SESSION[self::MYSQL]->error);

        return $result; // true si ok, false sinon
    }


    // Supprime un média si il existe
    public function supprimeMediaBDD()
    {
        $maRequete = "DELETE FROM media WHERE id = '" . $this->getId() . "'";
        $_SESSION[self::MYSQL]->query($maRequete) or die("Problème dans supprimeMediaBDD : " . $_SESSION[self::MYSQL]->error);
    }

    // Renvoie une chaîne de description du média
    public function infosMedia(): string
    {
        return "Id : " . $this->_id . " Type : " . $this->_type . " Titre : " . $this->_titre .
            " Image : " . $this->_image . " Auteur : " . $this->_auteur .
            " Lien : " . $this->_lien . " Description : " . $this->_description .
            " Tags : " . $this->_tags . " Date de publication : " . $this->_datePub .
            " Hits : " . $this->_hits . "<br>\n";
    }

    // Cherche un média par ID
    public function chercheMedia($id): int
    {
        $maRequete = sprintf("SELECT * FROM media WHERE id = '%s'", $id);
        $result = $_SESSION[self::MYSQL]->query($maRequete) or die("Problème dans chercheMedia : " . $_SESSION[self::MYSQL]->error);
        if ($ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return 1;
        } else {
            return 0;
        }
    }

    // Charge une ligne MySQL vers un objet
    private function mysqlRowVersObjet($ligne)
    {
        $this->_id = $ligne[0];
        $this->_type = $ligne[1];
        $this->_titre = $ligne[2];
        $this->_image = $ligne[3];
        $this->_auteur = $ligne[4];
        $this->_lien = $ligne[5];
        $this->_description = $ligne[6];
        $this->_tags = $ligne[7];
        $this->_datePub = $ligne[8];
        $this->_hits = $ligne[9];
    }

    // Cherche des médias par type
    public static function chercheMediasParType($type): array
    {
        $type = $_SESSION[self::MYSQL]->real_escape_string($type);
        $maRequete = "SELECT id FROM media WHERE type = '$type'";
        $result = $_SESSION[self::MYSQL]->query($maRequete) or die("Problème dans chercheMediasParType : " . $_SESSION[self::MYSQL]->error);
        $tableau = [];
        while ($idMedia = $result->fetch_row()) {
            array_push($tableau, $idMedia[0]);
        }
        return $tableau;
    }

    // Cherche tos les medias
    public static function chercheTousLesMedias(): array
    {
        $maRequete = "SELECT id FROM media ORDER BY datePub DESC";
        $result = $_SESSION[self::MYSQL]->query($maRequete) or die("Problème dans chercheMediasParType : " . $_SESSION[self::MYSQL]->error);
        $tableau = [];
        while ($idMedia = $result->fetch_row()) {
            array_push($tableau, $idMedia[0]);
        }
        return $tableau;
    }

    // Cherche des médias par titre
    public static function chercheMediasParTitre($titre): array
    {
        $titre = $_SESSION[self::MYSQL]->real_escape_string($titre);
        $maRequete = "SELECT id FROM media WHERE titre LIKE '%$titre%'";
        $result = $_SESSION[self::MYSQL]->query($maRequete) or die("Problème dans chercheMediasParTitre : " . $_SESSION[self::MYSQL]->error);
        $tableau = [];
        while ($idMedia = $result->fetch_row()) {
            array_push($tableau, $idMedia[0]);
        }
        return $tableau;
    }

    public static function normalize($string): string
    {
        // Convertir en minuscules
        $string = mb_strtolower($string);
        echo $string;

        // Remplacer les caractères accentués par leurs équivalents non accentués
        $string = preg_replace('/[áàâãäå]/u', 'a', $string);
        $string = preg_replace('/[éèêë]/u', 'e', $string);
        $string = preg_replace('/[íìîï]/u', 'i', $string);
        $string = preg_replace('/[óòôõö]/u', 'o', $string);
        $string = preg_replace('/[úùûü]/u', 'u', $string);
        $string = preg_replace('/[ýÿ]/u', 'y', $string);
        $string = preg_replace('/ç/u', 'c', $string);
        $string = preg_replace('/ñ/u', 'n', $string);

        // Supprimer les caractères non alphanumériques (sauf les espaces)
        $string = preg_replace('/[^a-z0-9\s]/', ' ', $string);

        // Réduire les espaces multiples à un seul espace
        $string = preg_replace('/\s+/', ' ', $string);

        // Supprimer les espaces au début et à la fin
        return trim($string);
    }

    public static function moteurRecherche($recherche): string
    {
        $rechercheNormalisee = self::normalize($recherche);
        $maRequete = "SELECT id, titre FROM media WHERE titre LIKE '%$rechercheNormalisee%'";
        $result = $_SESSION[self::MYSQL]->query($maRequete) or die("Problème dans moteurRecherche : " . $_SESSION[self::MYSQL]->error);

        $retour = "";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $retour .= "Titre: " . $row["titre"] . " - ID: " . $row["id"] . "<br>\n";
            }
        } else {
            $retour = "0 résultats";
        }
        return $retour;
    }

    public function chercheNdernieresPartoches($nombrePartoches = 100): void
    {
        $compteur = 0;
        // lance la requete cherche documents avec tableNom = chanson
        $documents = chercheDocuments("nomTable", "chanson", "date", false);
        while ($compteur < $nombrePartoches) {
            $document = $documents->fetch_row();
            // On ne garde que les documents de type partoche
            if (str_ends_with($document[1], ".pdf")) {
                $this->ajoutePartoche($document[0]);
                // TODO n'ajouter le media que de la dernière version du doc !
                $compteur++;
            }
        }
    }

    public function chercheNdernieresVideos($nombreVideos = 50): void
    {
        $compteur = 0;
        // lance la requete cherche documents avec tableNom = chanson
        $nderniersLiens = chercheNderniersLiens("vidéo");
        while ($compteur < $nombreVideos) {
            $liensUrl = $nderniersLiens->fetch_row();

            $this->ajouteLienUrl($liensUrl[0]);
            // TODO n'ajouter le media que de la dernière version du doc !
            $compteur++;
        }
    }

    public
    function transformePartocheEnMedia($idDocPartoche): void
    {
        // partant de l'id du document de partoche, on cherche la chanson
        $document = chercheDocument($idDocPartoche);
        $idChanson = $document[6];

        $chanson = new Chanson();
        $chanson->chercheChanson($idChanson);
        $this->setTitre($chanson->getNom());
        $this->setDescription("Partoche pour la chanson de " . $chanson->getInterprete() . " - " . $chanson->getAnnee());
        $this->setAuteur($document[7]); // Identifiant de l'utilisateur
        $this->setDatePub($document[3]); // Date de publication du document
        $this->setType("partoche");
        $this->setTags("partoche " . $chanson->getAnnee());

        $this->setImage("./data/chansons/$idChanson/" . rawurlencode(imageTableId(self::TABLE_CHANSON, $idChanson)));
        $this->setLien("./php/document/" . lienUrlTelechargeDocument($idDocPartoche));
    }

    public
    function transformeLienUrlEnMedia($idLienurl): void
    {
        // partant de l'id du document de partoche, on cherche la chanson
        $lienUrl = chercheLienurlId($idLienurl);
        $idChanson = $lienUrl[2];
        echo $lienUrl;
        var_dump($lienUrl);
        $chanson = new Chanson();
        $chanson->chercheChanson($idChanson);
        $this->setTitre($chanson->getNom());
        $this->setDescription("Vidéo pour la chanson de " . $chanson->getInterprete() . " - " . $chanson->getAnnee());
        $this->setAuteur(intval($lienUrl[7])); // Identifiant de l'utilisateur
        $this->setDatePub($lienUrl[6]); // Date de publication du document
        $this->setType($lienUrl[4]);
        $this->setTags($lienUrl[4] . " " . $chanson->getAnnee());

        $this->setImage("./data/chansons/$idChanson/" . rawurlencode(imageTableId(self::TABLE_CHANSON, $idChanson)));
        $this->setLien($lienUrl[3]);
    }

// Ajoute une partoche par l'id de son document rattaché à la chanson
    public
    function ajoutePartoche($idPartoche): void
    {
        // Transformer la partoche en média
        $this->transformePartocheEnMedia($idPartoche); // Méthode fictive pour transformer
        $this->persist(); // Utilisation de la méthode existante pour créer le média
    }

// Ajoute ue vidéo par l'id de son lienurl rattaché
    public
    function ajouteLienurl($idPartoche): void
    {
        // Transformer le lien url en média
        $this->transformeLienUrlEnMedia($idPartoche); // Méthode fictive pour transformer
        $this->persist(); // Utilisation de la méthode existante pour créer le média
    }


    public
    function resetAvecDernieresPartoches(int $nb = 50) :bool
    {
        // Suppression de toutes les entrées
        $deleteQuery = "DELETE  FROM media";
        $resultDelete = $_SESSION[self::MYSQL]->query($deleteQuery);
        if (!$resultDelete) {
            die("Erreur lors de la suppression des médias : " . $_SESSION[self::MYSQL]->error);
        }

        // Création des $nb dernières partoches
        $this->chercheNdernieresPartoches($nb);
        return true;
    }

    public
    function resetAvecDernieresVideos(int $nb = 50) :bool
    {
        // Suppression de toutes les entrées
        $deleteQuery = "DELETE FROM media WHERE type LIKE 'video' COLLATE utf8mb4_general_ci";
        $resultDelete = $_SESSION[self::MYSQL]->query($deleteQuery);
        if (!$resultDelete) {
            die("Erreur lors de la suppression des médias vidéos : " . $_SESSION[self::MYSQL]->error);
        }

        // Création des $nb dernières videos
        $this->chercheNdernieresVideos($nb);
        return true;
    }

    public function afficheComposantMedia(): string
    {
        $data = $this->prepareData();

        return <<<HTML
            <a href="{$data['lien']}" target="_blank" class="text-decoration-none media-link">
                <article class="card media-card shadow-sm border m-2" style="width:220px;">
                    <div class="card-body d-flex flex-column align-items-center text-center">
                        <span class="badge bg-{$data['couleurBadge']} mb-2 fs-5">{$data['emoji']} {$data['type']}</span>
                        <h5 class="card-title mb-1 text-dark">{$data['titre']}</h5>
                        <img src="{$data['imageUrl']}{$data['imageCacheTag']}" alt="Illustration : {$data['titre']}"
                             class="card-img-top my-2"
                             loading="lazy"
                             style="height:140px;width:100%;object-fit:cover;max-width:200px;">
                        <p class="card-text small mt-2 mb-1">{$data['description']}</p>
                        <p class="meta-pub mb-1">Publié le {$data['datePub']} par <strong>{$data['auteurNom']}</strong></p>
                    </div>
                </article>
            </a>
            HTML;
    }

    /**
     * Prépare toutes les données nécessaires à l'affichage d'un média.
     */
    private function prepareData(): array
    {
        $type = strtolower($this->_type);
        $titre = htmlspecialchars($this->_titre);
        $imageRelative = htmlspecialchars($this->_image);
        $imagePath = "../../" . $imageRelative;
        $imageUrl = "../../" . $imageRelative;

        // Gestion du cache-busting
        $imageCacheTag = "";
        if (file_exists($imagePath)) {
            $mtime = filemtime($imagePath);
            $imageCacheTag = "?v={$mtime}";
        }

        // Lien
        $lien = ($type === "partoche")
            ? "../../" . htmlspecialchars($this->_lien)
            : htmlspecialchars($this->_lien);

        // Auteur
        $auteur = chercheUtilisateur($this->_auteur);
        $auteurNom = htmlspecialchars($auteur[3] ?? "Auteur inconnu");

        // Attributs vidéo / partoche
        $isVideo = in_array($type, ["vidéo", "video"]);
        $couleurBadge = $isVideo ? "primary" : "danger";
        $emoji = $isVideo ? "🎬" : "🎵";

        return [
            'type' => $type,
            'titre' => $titre,
            'imageUrl' => $imageUrl,
            'imageCacheTag' => $imageCacheTag,
            'lien' => $lien,
            'description' => htmlspecialchars($this->_description),
            'datePub' => htmlspecialchars($this->_datePub),
            'auteurNom' => $auteurNom,
            'couleurBadge' => $couleurBadge,
            'emoji' => $emoji
        ];
    }





    public function resetMediasDistribues(int $totalMedias): array
    {
        $medias = new Media();

        $mysqli = $_SESSION[Media::MYSQL];

        $resultVideos = $_SESSION[self::MYSQL]->query("SELECT COUNT(*) AS nb FROM lienurl WHERE type LIKE 'vidéo' OR type LIKE 'video' COLLATE utf8mb4_general_ci");
        $nbVideos = $resultVideos ? (int) $resultVideos->fetch_assoc()["nb"] : 0;

        $resultPartoches = $_SESSION[self::MYSQL]->query("SELECT COUNT(*) AS nb FROM document WHERE nomTable='chanson' AND (nom LIKE '%.pdf' OR nom LIKE '%.PDF')");
        $nbPartoches = $resultPartoches ? (int) $resultPartoches->fetch_assoc()["nb"] : 0;

        $totalExistants = $nbVideos + $nbPartoches;
        if ($totalExistants === 0) {
            // Évite division par zéro, traite par défaut moitié/moitié
            $nbVideosATraiter = (int) round($totalMedias / 2);
        } else {
            $pctVideos = $nbVideos / $totalExistants;
            $nbVideosATraiter = (int) round($pctVideos * $totalMedias);
        }
        $nbPartochesATraiter = $totalMedias - $nbVideosATraiter;

        // Appels aux fonctions reset
        $medias->resetAvecDernieresPartoches($nbPartochesATraiter);
        $medias->resetAvecDernieresVideos($nbVideosATraiter);

        return [$nbVideosATraiter, $nbPartochesATraiter];
    }

}

// TODO
// - ne pas pouvoir de créer de media ayant déjà un lien existant !
// - un media peut être un doc pdf, mp3, lien vidéo yt, une partoche, un soungbook

