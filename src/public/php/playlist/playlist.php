<?php
require_once __DIR__ . "/../lib/utilssi.php";
require_once __DIR__ . "/../lib/configMysql.php";

$playlistForm = "playlist_form.php";
$playlistGet = "playlist_get.php";
$playlistVoir = "playlist_voir.php";
$playlistListe = "playlist_liste.php";
$cheminImagesplaylist = "../data/playlists/";

// Fonctions de gestion du playlist

// Cherche les playlists correspondant à un critère
function cherchePlaylists($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
{
    $db = $_SESSION['mysql'];
    $critere = $db->real_escape_string($critere);
    $valeur = $db->real_escape_string($valeur);
    $critereTri = $db->real_escape_string($critereTri);
    
    // Mapping pour la compatibilité avec le renommage de colonnes
    if ($critereTri === 'date') $critereTri = 'date_creation';
    
    $maRequete = "SELECT * FROM playlist WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    $maRequete .= $bTriAscendant ? " ASC" : " DESC";
    
    $result = $db->query($maRequete) or die ("Problème cherchePlaylists #1 : " . $db->error);
    return $result;
}

// Cherche une playlist et le renvoie s'il existe
function cherchePlaylist($id)
{
    $db = $_SESSION['mysql'];
    $id = (int)$id;
    $maRequete = "SELECT * FROM playlist WHERE id = '$id'";
    $result = $db->query($maRequete) or die ("Problème cherchePlaylist #1 : " . $db->error);
    return ($ligne = $result->fetch_assoc()) ? $ligne : 0;
}

// Cherche une playlist et la renvoie si elle existe
function cherchePlaylistParLeNom($nom)
{
    $db = $_SESSION['mysql'];
    $nom = $db->real_escape_string($nom);
    $maRequete = "SELECT * FROM playlist WHERE nom = '$nom'";
    $result = $db->query($maRequete) or die ("Problème cherchePlaylistParLeNom #1 : " . $db->error);
    return ($ligne = $result->fetch_assoc()) ? $ligne : 0;
}

// Crée une playlist
function creePlaylist($nom, $description, $date, $image, $hits, $type = 0, $criteres = "")
{
    $db = $_SESSION['mysql'];
    $nom = $db->real_escape_string($nom);
    $description = $db->real_escape_string($description);
    $date = convertitDateJJMMAAAAversMySql($date);
    $image = $db->real_escape_string($image);
    $hits = (int)$hits;
    $idUser = (int)$_SESSION['id'];
    $type = (int)$type;
    $criteres = $db->real_escape_string($criteres);
    
    $maRequete = "INSERT INTO playlist (id, nom, description, date_creation, image, hits, id_utilisateur, type, criteres) 
                  VALUES (NULL, '$nom', '$description', '$date', '$image', '$hits', '$idUser', '$type', '$criteres')";
    $db->query($maRequete) or die ("Problème creePlaylist #1 : " . $db->error);
}

// Modifie en base la playlist
function modifiePlaylist($id, $nom, $description, $date, $image, $hits, $type = 0, $criteres = "")
{
    $db = $_SESSION['mysql'];
    $id = (int)$id;
    $nom = $db->real_escape_string($nom);
    $description = $db->real_escape_string($description);
    $date = convertitDateJJMMAAAAversMySql($date);
    $image = $db->real_escape_string($image);
    $hits = (int)$hits;
    $type = (int)$type;
    $criteres = $db->real_escape_string($criteres);
    
    $maRequete = "UPDATE playlist
	              SET nom = '$nom', description = '$description', date_creation = '$date' , image = '$image', hits = '$hits', type = '$type', criteres = '$criteres'
	              WHERE id='$id'";
    $db->query($maRequete) or die ("Problème modifiePlaylist #1 : " . $db->error);
}

// Cette fonction supprime une playlist si il existe
function supprimePlaylist($idplaylist)
{
    $db = $_SESSION['mysql'];
    $idplaylist = (int)$idplaylist;
    
    // On supprime les enregistrements dans playlist
    $maRequete = "DELETE FROM playlist WHERE id='$idplaylist'";
    $db->query($maRequete) or die ("Problème #1 dans supprimePlaylist : " . $db->error);
}

// Cette fonction modifie ou crée une playlist si besoin
function creemodifiePlaylist($id, $nom, $description, $date, $image, $hits)
{
    if (cherchePlaylist($id))
        modifiePlaylist($id, $nom, $description, $date, $image, $hits);
    else
        creePlaylist($nom, $description, $date, $image, $hits);
}

// Cette fonction renvoie l'image vignette d'une playlist
function imagePlaylist($idplaylist)
{

    $maRequete = "SELECT * FROM document WHERE document.idTable = '$idplaylist' AND document.nomTable='playlist' ";
    $maRequete .= " AND ( document.nom LIKE '%.png' OR document.nom LIKE '%.jpg')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème imagePlaylist #1 : " . $_SESSION ['mysql']->error);
    if (empty($result)) {
        return ("");
    }

    // Choisit une vignette au hasard parmi les images
    // renvoie la ligne sélectionnée : id, nom, description, date , image, hits
    if (($ligne = $result->fetch_row())) {
        $nom = composeNomVersion($ligne[1], $ligne[4]);
        return ($nom);
    } else
        return ("");
}

// Cette fonction renvoie une chaine de description de la playlist
function infosPlaylist($id)
{
    $enr = chercheplaylist($id);
    // id_journee id_joueur poste statut
    $retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Description : " . $enr [2] . " Date : " . $enr [3] . " image : " . $enr [4] . " Hits : " . $enr [5];
    return $retour . "<BR>\n";
}

/**
 * Affiche une carte moderne (thumbnail Bootstrap 3) pour la playlist
 * @param array $ligne Données de la playlist (associatif)
 * @return string HTML de la carte
 */
function afficheCartePlaylist($ligne): string
{
    $id = $ligne['id'] ?? $ligne[0] ?? 0;
    $nom = htmlspecialchars($ligne['nom'] ?? $ligne[1] ?? '');
    $description = htmlspecialchars(limiteLongueur($ligne['description'] ?? $ligne[2] ?? '', 60));
    $date = dateMysqlVersTexte($ligne['date_creation'] ?? $ligne['date'] ?? $ligne[3] ?? '');
    $hits = $ligne['hits'] ?? $ligne[8] ?? $ligne[5] ?? 0;
    
    $imagePochette = imagePlaylist($id);
    
    $urlVoir = "playlist_voir.php?id=$id";
    
    $htmlImage = "";
    if ($imagePochette != "") {
        $htmlImage = "<img src='../data/playlists/$imagePochette' alt='Pochette'>";
    } else {
        $htmlImage = "<i class='glyphicon glyphicon-music' style='font-size: 64px;'></i>";
    }

    // Sous-titre (Description)
    $sousTitre = "<p class='text-muted small' style='height: 40px; overflow: hidden; margin-bottom: 10px;'>$description</p>";

    // Badges (Date, Hits)
    $badges = "
        <span class='small'><i class='glyphicon glyphicon-calendar'></i> $date</span>
        <span style='margin: 0 10px; opacity: 0.3;'>|</span>
        <span class='small'><i class='glyphicon glyphicon-eye-open'></i> $hits</span>";

    // Actions (Écouter, Modifier)
    $actions = "
        <a href='$urlVoir' class='btn btn-canopee-ecouter'><i class='glyphicon glyphicon-play'></i> Écouter</a>";
    
    if ($_SESSION['privilege'] >= $GLOBALS["PRIVILEGE_EDITEUR"]) {
        $actions .= "  <a href='playlist_form.php?id=$id' class='btn btn-canopee-modifier-outline' title='Modifier'><i class='glyphicon glyphicon-pencil'></i></a>";
    }

    return ComposantsUI::afficheCarteCanopee($nom, $sousTitre, $htmlImage, $urlVoir, $badges, $actions, ['hauteur' => '380px']);
}

/**
 * Récupère la liste des chansons pour une playlist donnée
 * @param int $idPlaylist
 * @param string $tri Critère de tri (nom, datePub, hits, tonalite, annee, tempo)
 * @return mysqli_result|array
 */
function getMorceauxPlaylist($idPlaylist, $tri = 'ordre')
{
    $db = $_SESSION['mysql'];
    $idPlaylist = (int)$idPlaylist;
    $donnee = cherchePlaylist($idPlaylist);
    
    // Mapping des tris
    $sortMap = [
        'nom' => 'c.nom ASC',
        'date' => 'c.datePub DESC',
        'hits' => 'c.hits DESC',
        'tona' => 'c.tonalite ASC',
        'annee' => 'c.annee DESC',
        'bpm' => 'c.tempo DESC',
        'ordre' => 'lcp.ordre ASC'
    ];
    $orderBy = $sortMap[$tri] ?? 'c.nom ASC';

    // Si la playlist est dynamique
    $type = $donnee['type'] ?? $donnee[7] ?? 0;
    if ($type == 1 || $type == 'dynamique') {
        $criteresStr = $donnee['criteres'] ?? $donnee[8] ?? "[]";
        $criteres = json_decode($criteresStr, true);
        $conditions = ["1=1"]; // Condition de base
        $jointures = "";

        if (!empty($criteres)) {
            // Filtre par Tonalité
            if (!empty($criteres['tonalite'])) {
                $conditions[] = "tonalite = '" . $db->real_escape_string($criteres['tonalite']) . "'";
            }
            // Filtre par Tempo (Famille)
            if (!empty($criteres['tempo_famille'])) {
                $conditions[] = "tempo_famille = '" . $db->real_escape_string($criteres['tempo_famille']) . "'";
            }
            // Filtre par Strum
            if (!empty($criteres['idStrum'])) {
                $jointures .= " INNER JOIN lienstrumchanson lsc ON c.id = lsc.id_chanson ";
                $conditions[] = "lsc.id_strum = " . (int)$criteres['idStrum'];
            }
            // Filtre par Saison Musicale (01/08 au 31/07)
            if (!empty($criteres['saison'])) {
                $anneeStart = (int)$criteres['saison'];
                $dateStart = "$anneeStart-08-01";
                $dateEnd = ($anneeStart + 1) . "-07-31";
                $conditions[] = "datePub BETWEEN '$dateStart' AND '$dateEnd'";
            }
        }

        $sql = "SELECT c.* FROM chanson c $jointures WHERE " . implode(" AND ", $conditions) . " ORDER BY $orderBy";
        return $db->query($sql);
    } 
    
    // Sinon, playlist manuelle classique (via la table de liens)
    // Note: Si on trie manuellement, on ignore l'ordre défini par l'utilisateur
    $currentOrder = ($tri == 'ordre') ? 'lcp.ordre ASC' : $orderBy;
    $sql = "SELECT c.*, lcp.ordre 
            FROM chanson c 
            INNER JOIN lienchansonplaylist lcp ON c.id = lcp.id_chanson 
            WHERE lcp.id_playlist = $idPlaylist 
            ORDER BY $currentOrder";
    return $db->query($sql);
}

// Cette fonction renvoie la liste des fichiers attachés à la playlist
function fichiersPlaylist($id)
{
    $enr = chercheplaylist($id);
    $retour = []; // repertoire, nom, extension
    $repertoire = "../data/playlists/";
    if (is_dir($repertoire)) {
        foreach (new DirectoryIterator ($repertoire) as $fileInfo) {
            if ($fileInfo->isDot() || strpos($fileInfo->getFilename(), ".") == 0)
                continue;
            array_push($retour, [$repertoire, $fileInfo->getFilename(), $fileInfo->getextension()]);
        }
    }
    return $retour;
}

// Fonction de test
function testePlaylist()
{
    creePlaylist("playlist #1", "Chansons d été", "31/07/2017", "cover.jpg", 0);
    $id = cherchePlaylistParLeNom("playlist #1");
    $id = $id [0];
    echo infosPlaylist($id);

    $enr = chercheplaylist($id);
    $id = $id [0];
    echo infosPlaylist($id);

    creePlaylist("playlist #2", "Chansons d automne", "30/11/2017", "cover.jpg", 0);
    $id = cherchePlaylistParLeNom("playlist #2");
    $id = $id [0];
    echo infosPlaylist($id);

    creemodifiePlaylist($id, "playlist #2", "Chansons d automne !", "28/11/2017", "cover.jpg", 0);
    $id = cherchePlaylistParLeNom("playlist #2");
    $id = $id [0];
    echo infosPlaylist($id);

    $id = cherchePlaylistParLeNom("playlist #2");
    $id = $id [0];
    // supprimePlaylist($id);
    echo infosPlaylist($id);

    $id = cherchePlaylistParLeNom("playlist #1");
    supprimePlaylist($id[0]);
    $id = cherchePlaylistParLeNom("playlist #2");
    supprimePlaylist($id[0]);

}

// testePlaylist ();
// TODO ajouter des logs pur tracer l'activité du site
