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
    // ON REVIENT AU FETCH_ROW POUR COMPATIBILITÉ INDEX NUMÉRIQUES [1], [2]...
    return ($ligne = $result->fetch_row()) ? $ligne : 0;
}

// Cherche une playlist et la renvoie si elle existe
function cherchePlaylistParLeNom($nom)
{
    $db = $_SESSION['mysql'];
    $nom = $db->real_escape_string($nom);
    $maRequete = "SELECT * FROM playlist WHERE nom = '$nom'";
    $result = $db->query($maRequete) or die ("Problème cherchePlaylistParLeNom #1 : " . $db->error);
    return ($ligne = $result->fetch_row()) ? $ligne : 0;
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
 * @param array $ligne Données de la playlist (id, nom, description, date, image, hits)
 * @return string HTML de la carte
 */
function afficheCartePlaylist($ligne): string
{
    $id = $ligne[0];
    $nom = htmlspecialchars($ligne[1]);
    $description = htmlspecialchars(limiteLongueur($ligne[2], 60));
    $date = dateMysqlVersTexte($ligne[3]);
    $hits = $ligne[5];
    
    $imagePochette = imagePlaylist($id);
    
    // Palette Canopée
    $c_marron_fonce = "#2b1d1a";
    $c_marron_clair = "#D2B48C"; // Bois clair
    $c_accent = "#8B4513";
    $c_beige = "#F5F5DC";

    $urlVoir = "playlist_voir.php?id=$id";
    
    $htmlImage = "";
    if ($imagePochette != "") {
        $htmlImage = "<img src='../data/playlists/$imagePochette' style='max-height: 100%; max-width: 100%; object-fit: cover;' alt='Pochette'>";
    } else {
        $htmlImage = "<i class='glyphicon glyphicon-music' style='font-size: 64px; color: $c_marron_fonce;'></i>";
    }

    $html = "
    <div class='col-sm-6 col-md-4 col-lg-3' style='margin-bottom: 25px;'>
        <div class='thumbnail shadow-hover' style='height: 380px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease; padding: 0; border: 1px solid $c_marron_clair; background-color: $c_marron_fonce; position: relative;'>
            <a href='$urlVoir' style='text-decoration: none;'>
                <div style='height: 180px; overflow: hidden; background-color: $c_marron_clair; display: flex; align-items: center; justify-content: center; border-bottom: 3px solid $c_accent;'>
                    $htmlImage
                </div>
            </a>
            <div class='caption' style='padding: 15px; text-align: center; color: $c_beige;'>
                <h4 style='margin-top: 0; margin-bottom: 5px; color: $c_marron_clair; height: 44px; overflow: hidden; font-weight: bold;'>$nom</h4>
                <p style='height: 40px; overflow: hidden; margin-bottom: 10px; font-size: 0.9em; color: #9e8d8a;'>$description</p>
                <div style='margin-bottom: 15px; border-top: 1px solid rgba(210, 180, 140, 0.2); padding-top: 10px;'>
                    <span style='font-size: 0.85em;'><i class='glyphicon glyphicon-calendar'></i> $date</span>
                    <span style='margin: 0 10px; opacity: 0.3;'>|</span>
                    <span style='font-size: 0.85em;'><i class='glyphicon glyphicon-eye-open'></i> $hits</span>
                </div>
                <div class='btn-group btn-group-justified'>
                    <a href='$urlVoir' class='btn btn-primary' style='background-color: $c_accent; border: none; border-radius: 0 0 0 12px;'><i class='glyphicon glyphicon-play'></i> Écouter</a>";
    
    if ($_SESSION['privilege'] >= $GLOBALS["PRIVILEGE_EDITEUR"]) {
        $html .= "  <a href='playlist_form.php?id=$id' class='btn btn-default' style='background-color: transparent; color: $c_marron_clair; border: 1px solid $c_marron_clair; border-radius: 0 0 12px 0;' title='Modifier'><i class='glyphicon glyphicon-pencil'></i></a>";
    }
    
    $html .= "
                </div>
            </div>
        </div>
    </div>";

    return $html;
}

/**
 * Récupère la liste des chansons pour une playlist donnée
 * @param int $idPlaylist
 * @return mysqli_result|array
 */
function getMorceauxPlaylist($idPlaylist)
{
    $db = $_SESSION['mysql'];
    $idPlaylist = (int)$idPlaylist;
    $donnee = cherchePlaylist($idPlaylist);
    
    // Si la playlist est dynamique (type = 1 ou 'dynamique')
    if (isset($donnee[7]) && ($donnee[7] == 1 || $donnee[7] == 'dynamique')) {
        $criteres = json_decode($donnee[8], true);
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

        $sql = "SELECT c.* FROM chanson c $jointures WHERE " . implode(" AND ", $conditions) . " ORDER BY c.nom ASC";
        return $db->query($sql);
    } 
    
    // Sinon, playlist manuelle classique (via la table de liens)
    $sql = "SELECT c.*, lcp.ordre 
            FROM chanson c 
            INNER JOIN lienchansonplaylist lcp ON c.id = lcp.id_chanson 
            WHERE lcp.id_playlist = $idPlaylist 
            ORDER BY lcp.ordre ASC";
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
