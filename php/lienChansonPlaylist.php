<?php
require_once("lib/utilssi.php");
require_once "lib/configMysql.php";

// Fonctions de gestion des liens chanson playlist dans la table lienchansonplaylist

// Cherche les lienChansonPlaylists correspondant à un critère
function chercheLiensChansonPlaylist($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
{
    $maRequete = "SELECT * FROM lienchansonplaylist WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    if ($bTriAscendant == false)
        $maRequete .= " DESC";
    else
        $maRequete .= " ASC";
    // echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheliensChansonPlaylist #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Cherche un lienChansonPlaylist et le renvoie s'il existe
function chercheLienChansonPlaylist($id)
{
    $maRequete = "SELECT * FROM lienchansonplaylist WHERE id = '$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchelienChansonPlaylist #1 : " . $_SESSION ['mysql']->error);
    // renvoie la lisgne sélectionnée : id, nom, interprète, année
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Renvoie le nombre de chansons dans une playlist
/**
 * @param $idPlaylist
 * @return nombre de lignes trouvées
 */
function nombreDeLiensDeLaPlaylist($idPlaylist)
{
    $maRequete = "SELECT * FROM lienchansonplaylist WHERE idPlaylist = '$idPlaylist'";
    $result = $_SESSION['mysql']->query($maRequete) or die ("Problème nombreDeLiensDeLaPlaylist #1 : " . $_SESSION ['mysql']->error);
    $row_cnt = $result->num_rows;
    return ($row_cnt);
}

// Cherche un lienChansonPlaylist et le renvoie s'il existe
function chercheLienParIdPlaylistIdChanson($idPlaylist, $idChanson)
{
    $maRequete = "SELECT * FROM lienchansonplaylist WHERE idChanson = '$idChanson' AND idPlaylist = '$idPlaylist'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheIdPlaylistIdChanson #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, interprète, année
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Cherche le nieme lienChansonPlaylist  d'un Playlistet le renvoie s'il existe
function chercheLienParIdPlaylistOrdre($idPlaylist, $ordre)
{
    $maRequete = "SELECT * FROM lienchansonplaylist WHERE ordre = '$ordre' AND idPlaylist = '$idPlaylist'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienParIdPlaylistOrdre #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, interprète, année
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Crée un lienChansonPlaylist
function creelienChansonPlaylist($idChanson, $idPlaylist)
{
    chercheLiensChansonPlaylist("idPlaylist", $idPlaylist, "id");
    $nb = $_SESSION ['mysql']->affected_rows + 1;
    $maRequete = "INSERT INTO lienchansonplaylist VALUES (NULL, '$idChanson', '$idPlaylist', '$nb')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creelienChansonPlaylist#1 : " . $_SESSION ['mysql']->error);
}

// Modifie en base la lienChansonPlaylist
function modifielienChansonPlaylist($id, $idChanson, $idPlaylist, $ordre)
{
    $maRequete = "UPDATE  lienchansonplaylist
	SET idChanson = '$idChanson', idPlaylist = '$idPlaylist', ordre = '$ordre'
	WHERE id='$id'";
// 	echo $maRequete . "<br>";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème modifielienChansonPlaylist #1 : " . $_SESSION ['mysql']->error);
}

// Cette fonction supprime un lienChansonPlaylist si il existe
function supprimelienChansonPlaylist($idlienChansonPlaylist)
{
    // On supprime les enregistrements dans lienChansonPlaylist
    $maRequete = "DELETE FROM lienchansonplaylist
	WHERE id='$idlienChansonPlaylist'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimelienChansonPlaylist : " . $_SESSION ['mysql']->error);
    // On réordonne la liste
    ordonneLiensPlaylist($idlienChansonPlaylist);
}

// Cette fonction supprime un lienChansonPlaylist si il existe
function supprimelienIdChansonIdPlaylist($idChanson, $idPlaylist)
{
    // On supprime les enregistrements dans lienChansonPlaylist
    $maRequete = "DELETE FROM lienchansonplaylist
	WHERE idChanson = '$idChanson' AND idPlaylist = '$idPlaylist'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimelienChansonPlaylist : " . $_SESSION ['mysql']->error);
    // On réordonne la liste
    ordonneLiensPlaylist($idPlaylist);
}

// Cette fonction supprime un lienChansonPlaylist si il existe
function supprimeliensChansonPlaylistDeLaChanson($idChanson)
{
    // On supprime les enregistrements dans lienChansonPlaylist
    $maRequete = "DELETE FROM lienchansonplaylist
	WHERE idChanson ='$idChanson'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimeliensChansonPlaylistDeLaChanson : " . $_SESSION ['mysql']->error);
}

// Cette fonction supprime tous les liens de la playlist
function supprimeliensChansonPlaylistDeLaPlaylist($idPlaylist)
{
    // On supprime les enregistrements dans lienChansonPlaylist
    $maRequete = "DELETE FROM lienchansonplaylist
	WHERE idPlaylist ='$idPlaylist'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimeliensChansonPlaylistDeLaPlaylist : " . $_SESSION ['mysql']->error);
}

// Cette fonction modifie ou crée un lienChansonPlaylist si besoin
function creeModifielienChansonPlaylist($id, $idChanson, $idPlaylist, $ordre = 0)
{
    if (chercheLienChansonPlaylist($id))
        modifielienChansonPlaylist($id, $idChanson, $idPlaylist, $ordre);
    else
        creelienChansonPlaylist($idChanson, $idPlaylist);
}

// Cette fonction renvoie une chaine de description de la lienChansonPlaylist
function infoslienChansonPlaylist($id)
{
    $enr = chercheLienChansonPlaylist($id);
    // id_journee id_joueur poste statut
    $retour = "Id : " . $enr [0] . " idChanson : " . $enr [1] . " IdPlaylist : " . $enr [2] . " Ordre : " . $enr [3];
    return $retour . "<BR>\n";
}


// Cette fonction s'assure que les chansons d'une playlist soient numérotées de 1 à n
function ordonneLiensPlaylist($idPlaylist)
{
    // Récupérer la liste des liens triés par ordre
    $lignes = chercheLiensChansonPlaylist('idPlaylist', $idPlaylist, "ordre", true);

    // Les stocker dans un tableau
    $numero = 1;
    while ($ligne = $lignes->fetch_row()) {
        $mesLiens [$numero] = $ligne;
        $numero++;
    }

    // Faire une boucle de 1 à n pour les renuméroter
    $parcours = 1;
    while ($parcours < $numero) {
        modifielienChansonPlaylist($mesLiens [$parcours] [0], $mesLiens [$parcours] [1], $mesLiens [$parcours] [2], $parcours);
        $parcours++;
    }
}

function remonteTitrePlaylist($idPlaylist, $rang, $longueurSaut)
{
    if ($rang < $longueurSaut)
        return false;

    // cherche le doc à monter
    $lienAmonter = chercheLienParIdPlaylistOrdre($idPlaylist, $rang);
    if ($lienAmonter == 0)
        return false;

    // cherche le doc à baisser
    $lienAbaisser = chercheLienParIdPlaylistOrdre($idPlaylist, $rang - $longueurSaut);
    if ($lienAbaisser == 0)
        return false;

    //  changer l'ordre et enregistrer
    modifielienChansonPlaylist($lienAmonter[0], $lienAmonter[1], $lienAmonter[2], $lienAmonter[3] - $longueurSaut);
    modifielienChansonPlaylist($lienAbaisser[0], $lienAbaisser[1], $lienAbaisser[2], $lienAbaisser[3] + $longueurSaut);

    //  changer l'ordre et enregistrer
    ordonneLiensPlaylist($idPlaylist);
    return true;
}

// Fonction de test
function testelienChansonPlaylist()
{
    creelienChansonPlaylist(1, 1);
    creelienChansonPlaylist(2, 1);
    // creeModifielienChansonPlaylist ( 2, 1,1,3 );
    creelienChansonPlaylist(3, 1);
    // echo infoslienChansonPlaylist ( $id );
}

// testelienChansonPlaylist ();
// ordonneLiensPlaylist(24);
// TODO ajouter des logs pur tracer l'activité du site
