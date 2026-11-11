<?php
require_once __DIR__ . "/../lib/configMysql.php";
require_once __DIR__ . "/../lib/utilssi.php";

// Fonctions de gestion des liens chanson playlist dans la table lienchansonplaylist

// Cherche les lienChansonPlaylists correspondant à un critère
function chercheLiensChansonPlaylist($critere, $valeur, $critereTri = 'ordre', $bTriAscendant = true)
{
    $db = $_SESSION['mysql'];
    $critere = $db->real_escape_string($critere);
    $valeur = $db->real_escape_string($valeur);
    $critereTri = $db->real_escape_string($critereTri);
    $direction = $bTriAscendant ? "ASC" : "DESC";

    $maRequete = "SELECT id, id_chanson, id_playlist, ordre FROM lienchansonplaylist WHERE $critere LIKE '$valeur' ORDER BY $critereTri $direction";
    $result = $db->query($maRequete) or die ("Problème chercheliensChansonPlaylist #1 : " . $db->error);
    return $result;
}

// Cherche un lienChansonPlaylist et le renvoie s'il existe
function chercheLienChansonPlaylist($id)
{
    $db = $_SESSION['mysql'];
    $id = (int)$id;
    $maRequete = "SELECT id, id_chanson, id_playlist, ordre FROM lienchansonplaylist WHERE id = '$id'";
    $result = $db->query($maRequete) or die ("Problème cherchelienChansonPlaylist #1 : " . $db->error);
    if (($ligne = $result->fetch_assoc())) {
        return array_merge(array_values($ligne), $ligne);
    }
    return 0;
}

// Renvoie le nombre de chansons dans une playlist
/**
 * @param $idPlaylist
 * @return nombre de lignes trouvées
 */
function nombreDeLiensDeLaPlaylist($idPlaylist)
{
    $db = $_SESSION['mysql'];
    $idPlaylist = (int)$idPlaylist;
    $maRequete = "SELECT COUNT(*) FROM lienchansonplaylist WHERE id_playlist = '$idPlaylist'";
    $result = $db->query($maRequete) or die ("Problème nombreDeLiensDeLaPlaylist #1 : " . $db->error);
    $row = $result->fetch_row();
    return (int)($row[0] ?? 0);
}

// Cherche un lienChansonPlaylist et le renvoie s'il existe
function chercheLienParIdPlaylistIdChanson($idPlaylist, $idChanson)
{
    $db = $_SESSION['mysql'];
    $idPlaylist = (int)$idPlaylist;
    $idChanson = (int)$idChanson;
    $maRequete = "SELECT id, id_chanson, id_playlist, ordre FROM lienchansonplaylist WHERE id_chanson = '$idChanson' AND id_playlist = '$idPlaylist'";
    $result = $db->query($maRequete) or die ("Problème chercheIdPlaylistIdChanson #1 : " . $db->error);
    if (($ligne = $result->fetch_assoc())) {
        return array_merge(array_values($ligne), $ligne);
    }
    return 0;
}

// Cherche le nieme lienChansonPlaylist d'une playlist et le renvoie s'il existe
function chercheLienParIdPlaylistOrdre($idPlaylist, $ordre)
{
    $db = $_SESSION['mysql'];
    $idPlaylist = (int)$idPlaylist;
    $ordre = (int)$ordre;
    $maRequete = "SELECT id, id_chanson, id_playlist, ordre FROM lienchansonplaylist WHERE ordre = '$ordre' AND id_playlist = '$idPlaylist'";
    $result = $db->query($maRequete) or die ("Problème chercheLienParIdPlaylistOrdre #1 : " . $db->error);
    if (($ligne = $result->fetch_assoc())) {
        return array_merge(array_values($ligne), $ligne);
    }
    return 0;
}

// Crée un lienChansonPlaylist
function creelienChansonPlaylist($idChanson, $idPlaylist)
{
    $db = $_SESSION['mysql'];
    $idChanson = (int)$idChanson;
    $idPlaylist = (int)$idPlaylist;
    
    // On compte proprement en SQL
    $res = $db->query("SELECT COUNT(*) FROM lienchansonplaylist WHERE id_playlist = '$idPlaylist'");
    $row = $res->fetch_row();
    $nb = (int)$row[0] + 1;

    $maRequete = "INSERT INTO lienchansonplaylist (id, id_chanson, id_playlist, ordre) VALUES (NULL, '$idChanson', '$idPlaylist', '$nb')";
    $db->query($maRequete) or die ("Problème creelienChansonPlaylist#1 : " . $db->error);
}

// Modifie en base le lienChansonPlaylist
function modifielienChansonPlaylist($id, $idChanson, $idPlaylist, $ordre)
{
    $db = $_SESSION['mysql'];
    $id = (int)$id;
    $idChanson = (int)$idChanson;
    $idPlaylist = (int)$idPlaylist;
    $ordre = (int)$ordre;

    $maRequete = "UPDATE lienchansonplaylist
	              SET id_chanson = '$idChanson', id_playlist = '$idPlaylist', ordre = '$ordre'
	              WHERE id = '$id'";
    $db->query($maRequete) or die ("Problème modifielienChansonPlaylist #1 : " . $db->error);
}

// Cette fonction supprime un lienChansonPlaylist s'il existe
function supprimelienChansonPlaylist($idlienChansonPlaylist)
{
    $db = $_SESSION['mysql'];
    $idlienChansonPlaylist = (int)$idlienChansonPlaylist;

    // Récupérer l'id_playlist avant suppression pour réordonner ensuite
    $lien = chercheLienChansonPlaylist($idlienChansonPlaylist);
    $idPlaylist = $lien ? (int)($lien['id_playlist'] ?? $lien[2]) : 0;

    $maRequete = "DELETE FROM lienchansonplaylist WHERE id = '$idlienChansonPlaylist'";
    $db->query($maRequete) or die ("Problème #1 dans supprimelienChansonPlaylist : " . $db->error);

    if ($idPlaylist > 0) {
        ordonneLiensPlaylist($idPlaylist);
    }
}

// Cette fonction supprime un lienChansonPlaylist si il existe
function supprimelienIdChansonIdPlaylist($idChanson, $idPlaylist)
{
    $db = $_SESSION['mysql'];
    $idChanson = (int)$idChanson;
    $idPlaylist = (int)$idPlaylist;

    $maRequete = "DELETE FROM lienchansonplaylist WHERE id_chanson = '$idChanson' AND id_playlist = '$idPlaylist'";
    $db->query($maRequete) or die ("Problème #1 dans supprimelienIdChansonIdPlaylist : " . $db->error);

    ordonneLiensPlaylist($idPlaylist);
}

// Cette fonction supprime tous les liens vers une chanson
function supprimeliensChansonPlaylistDeLaChanson($idChanson)
{
    $db = $_SESSION['mysql'];
    $idChanson = (int)$idChanson;

    $maRequete = "DELETE FROM lienchansonplaylist WHERE id_chanson = '$idChanson'";
    $db->query($maRequete) or die ("Problème #1 dans supprimeliensChansonPlaylistDeLaChanson : " . $db->error);
}

// Cette fonction supprime tous les liens de la playlist
function supprimeliensChansonPlaylistDeLaPlaylist($idPlaylist)
{
    $db = $_SESSION['mysql'];
    $idPlaylist = (int)$idPlaylist;

    $maRequete = "DELETE FROM lienchansonplaylist WHERE id_playlist = '$idPlaylist'";
    $db->query($maRequete) or die ("Problème #1 dans supprimeliensChansonPlaylistDeLaPlaylist : " . $db->error);
}

// Cette fonction modifie ou crée un lienChansonPlaylist si besoin
function creeModifielienChansonPlaylist($id, $idChanson, $idPlaylist, $ordre = 0)
{
    if (chercheLienChansonPlaylist($id))
        modifielienChansonPlaylist($id, $idChanson, $idPlaylist, $ordre);
    else
        creelienChansonPlaylist($idChanson, $idPlaylist);
}

// Cette fonction renvoie une chaîne de description du lienChansonPlaylist
function infoslienChansonPlaylist($id)
{
    $enr = chercheLienChansonPlaylist($id);
    if (!$enr) return "";
    $retour = "Id : " . ($enr['id'] ?? $enr[0]) . 
              " idChanson : " . ($enr['id_chanson'] ?? $enr[1]) . 
              " IdPlaylist : " . ($enr['id_playlist'] ?? $enr[2]) . 
              " Ordre : " . ($enr['ordre'] ?? $enr[3]);
    return $retour . "<BR>\n";
}

// Cette fonction s'assure que les chansons d'une playlist soient numérotées de 1 à n
function ordonneLiensPlaylist($idPlaylist)
{
    $idPlaylist = (int)$idPlaylist;
    $lignes = chercheLiensChansonPlaylist('id_playlist', $idPlaylist, "ordre", true);

    $mesLiens = [];
    $numero = 1;
    while ($ligne = $lignes->fetch_assoc()) {
        $mesLiens[$numero] = $ligne;
        $numero++;
    }

    $parcours = 1;
    while ($parcours < $numero) {
        modifielienChansonPlaylist(
            (int)$mesLiens[$parcours]['id'], 
            (int)$mesLiens[$parcours]['id_chanson'], 
            (int)$mesLiens[$parcours]['id_playlist'], 
            $parcours
        );
        $parcours++;
    }
}

function remonteTitrePlaylist($idPlaylist, $rang, $longueurSaut)
{
    $idPlaylist = (int)$idPlaylist;
    $rang = (int)$rang;
    $longueurSaut = (int)$longueurSaut;

    if ($rang <= $longueurSaut) {
        return false;
    }

    $lienAmonter = chercheLienParIdPlaylistOrdre($idPlaylist, $rang);
    if (!$lienAmonter) {
        return false;
    }

    $lienAbaisser = chercheLienParIdPlaylistOrdre($idPlaylist, $rang - $longueurSaut);
    if (!$lienAbaisser) {
        return false;
    }

    $idLienMonter = (int)($lienAmonter['id'] ?? $lienAmonter[0]);
    $idChansonMonter = (int)($lienAmonter['id_chanson'] ?? $lienAmonter[1]);
    $idPlMonter = (int)($lienAmonter['id_playlist'] ?? $lienAmonter[2]);
    $ordreMonter = (int)($lienAmonter['ordre'] ?? $lienAmonter[3]);

    $idLienBaisser = (int)($lienAbaisser['id'] ?? $lienAbaisser[0]);
    $idChansonBaisser = (int)($lienAbaisser['id_chanson'] ?? $lienAbaisser[1]);
    $idPlBaisser = (int)($lienAbaisser['id_playlist'] ?? $lienAbaisser[2]);
    $ordreBaisser = (int)($lienAbaisser['ordre'] ?? $lienAbaisser[3]);

    modifielienChansonPlaylist($idLienMonter, $idChansonMonter, $idPlMonter, $ordreMonter - $longueurSaut);
    modifielienChansonPlaylist($idLienBaisser, $idChansonBaisser, $idPlBaisser, $ordreBaisser + $longueurSaut);

    ordonneLiensPlaylist($idPlaylist);
    return true;
}

function descendTitrePlaylist($idPlaylist, $rang, $longueurSaut)
{
    $idPlaylist = (int)$idPlaylist;
    $rang = (int)$rang;
    $longueurSaut = (int)$longueurSaut;

    $lienAdescendre = chercheLienParIdPlaylistOrdre($idPlaylist, $rang);
    if (!$lienAdescendre) {
        return false;
    }

    $lienAmonter = chercheLienParIdPlaylistOrdre($idPlaylist, $rang + $longueurSaut);
    if (!$lienAmonter) {
        return false;
    }

    $idLienDesc = (int)($lienAdescendre['id'] ?? $lienAdescendre[0]);
    $idChansonDesc = (int)($lienAdescendre['id_chanson'] ?? $lienAdescendre[1]);
    $idPlDesc = (int)($lienAdescendre['id_playlist'] ?? $lienAdescendre[2]);
    $ordreDesc = (int)($lienAdescendre['ordre'] ?? $lienAdescendre[3]);

    $idLienMonter = (int)($lienAmonter['id'] ?? $lienAmonter[0]);
    $idChansonMonter = (int)($lienAmonter['id_chanson'] ?? $lienAmonter[1]);
    $idPlMonter = (int)($lienAmonter['id_playlist'] ?? $lienAmonter[2]);
    $ordreMonter = (int)($lienAmonter['ordre'] ?? $lienAmonter[3]);

    modifielienChansonPlaylist($idLienDesc, $idChansonDesc, $idPlDesc, $ordreDesc + $longueurSaut);
    modifielienChansonPlaylist($idLienMonter, $idChansonMonter, $idPlMonter, $ordreMonter - $longueurSaut);

    ordonneLiensPlaylist($idPlaylist);
    return true;
}
