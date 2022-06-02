<?php
include_once("../lib/utilssi.php");
include_once("../lib/configMysql.php");

// Fonctions de gestion de la lienDocSongbook

// Cherche les lienDocSongbooks correspondant à un critère
function chercheLiensDocSongbook($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
{
    $maRequete = "SELECT * FROM liendocsongbook WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    if ($bTriAscendant == false)
        $maRequete .= " DESC";
    else
        $maRequete .= " ASC";
    // echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheliensDocSongbook #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Cherche un lienDocSongbook et le renvoie s'il existe
function chercheLienDocSongbook($id)
{
    $maRequete = "SELECT * FROM liendocsongbook WHERE id = '$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchelienDocSongbook #1 : " . $_SESSION ['mysql']->error);
    // renvoie la lisgne sélectionnée : id, nom, interprète, année
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Renvoie le nombre de docs dans un songbook
/**
 * @param $idSongBook
 * @return mixed
 */
function nombreDeLiensDuSongbook($idSongBook)
{
    $maRequete = "SELECT * FROM liendocsongbook WHERE idSongbook = '$idSongBook'";
    $result = $_SESSION['mysql']->query($maRequete) or die ("Problème nombreDeLiensDuSongbook #1 : " . $_SESSION ['mysql']->error);
    $row_cnt = $result->num_rows;
    return ($row_cnt);
}

// Cherche un lienDocSongbook et le renvoie s'il existe
function chercheLienParIdSongbookIdDoc($idSongbook, $idDoc)
{
    $maRequete = "SELECT * FROM liendocsongbook WHERE idDocument = '$idDoc' AND idSongbook = '$idSongbook'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheIdSongbookIdDoc #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, interprète, année
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Cherche le nieme lienDocSongbook  d'un Songbooket le renvoie s'il existe
function chercheLienParIdSongbookOrdre($idSongbook, $ordre)
{
    $maRequete = "SELECT * FROM liendocsongbook WHERE ordre = '$ordre' AND idSongbook = '$idSongbook'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienParIdSongbookOrdre #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, interprète, année
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Crée un lienDocSongbook
function creelienDocSongbook($idDocument, $idSongbook)
{
    chercheLiensDocSongbook("idSongbook", $idSongbook, "id");
    $nb = $_SESSION ['mysql']->affected_rows + 1;
    $maRequete = "INSERT INTO liendocsongbook VALUES (NULL, '$idDocument', '$idSongbook', '$nb')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creelienDocSongbook#1 : " . $_SESSION ['mysql']->error);
}

// Modifie en base la lienDocSongbook
function modifielienDocSongbook($id, $idDocument, $idSongbook, $ordre)
{
    $maRequete = "UPDATE  liendocsongbook
	SET idDocument = '$idDocument', idSongbook = '$idSongbook', ordre = '$ordre'
	WHERE id='$id'";
// 	echo $maRequete . "<br>";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème modifielienDocSongbook #1 : " . $_SESSION ['mysql']->error);
}

// Modifie en base l'ordre du lienDocSongbook
function modifieOrdreLienDocSongbook( $idDocument, $idSongbook, $ordre)
{
    $maRequete = "UPDATE  liendocsongbook
	SET  ordre = '$ordre'
	WHERE idDocument = '$idDocument' AND idSongbook = '$idSongbook'";
// 	echo $maRequete . "<br>";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème modifielienDocSongbook #1 : " . $_SESSION ['mysql']->error);
}

// Cette fonction supprime un lienDocSongbook si il existe
function supprimelienDocSongbook($idlienDocSongbook)
{
    // On supprime les enregistrements dans lienDocSongbook
    $maRequete = "DELETE FROM liendocsongbook
	WHERE id='$idlienDocSongbook'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimelienDocSongbook : " . $_SESSION ['mysql']->error);
    // On réordonne la liste
    ordonneLiensSongbook($idlienDocSongbook);
}

// Cette fonction supprime un lienDocSongbook si il existe
function supprimelienIdDocIdSongbook($idDoc, $idSongbook)
{
    // On supprime les enregistrements dans lienDocSongbook
    $maRequete = "DELETE FROM liendocsongbook
	WHERE idDocument = '$idDoc' AND idSongBook = '$idSongbook'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimelienDocSongbook : " . $_SESSION ['mysql']->error);
    // On réordonne la liste
    ordonneLiensSongbook($idSongbook);
}

// Cette fonction supprime un lienDocSongbook si il existe
function supprimeliensDocSongbookDuDocument($idDocument)
{
    // On supprime les enregistrements dans lienDocSongbook
    $maRequete = "DELETE FROM liendocsongbook
	WHERE idDocument ='$idDocument'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimeliensDocSongbookDuDocument : " . $_SESSION ['mysql']->error);
}

// Cette fonction supprime un lienDocSongbook si il existe
function supprimeliensDocSongbookDuSongbook($idSongbook)
{
    // On supprime les enregistrements dans lienDocSongbook
    $maRequete = "DELETE FROM liendocsongbook
	WHERE idSongbook ='$idSongbook'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimeliensDocSongbookDuSongbook : " . $_SESSION ['mysql']->error);
}

// Cette fonction modifie ou crée un lienDocSongbook si besoin
function creeModifielienDocSongbook($id, $idDocument, $idSongbook, $ordre = 0)
{
    if (chercheLienDocSongbook($id))
        modifielienDocSongbook($id, $idDocument, $idSongbook, $ordre);
    else
        creelienDocSongbook($idDocument, $idSongbook);
}

// Cette fonction renvoie une chaine de description de la lienDocSongbook
function infoslienDocSongbook($id)
{
    $enr = chercheLienDocSongbook($id);
    // id_journee id_joueur poste statut
    $retour = "Id : " . $enr [0] . " idDocument : " . $enr [1] . " IdSongbook : " . $enr [2] . " Ordre : " . $enr [3];
    return $retour . "<BR>\n";
}

function ordonneLiensSongbook($idSongbook)
{
    // Récupérer la liste des liens triés par ordre
    $lignes = chercheLiensDocSongbook('idSongbook', $idSongbook, "ordre", true);

    // Les stocker dans un tableau
    $numero = 1;
    while ($ligne = $lignes->fetch_row()) {
        $mesLiens [$numero] = $ligne;
        $numero++;
    }

    // Faire une boucle de 1 à n pour les renuméroter
    $parcours = 1;
    while ($parcours < $numero) {
        modifielienDocSongbook($mesLiens [$parcours] [0], $mesLiens [$parcours] [1], $mesLiens [$parcours] [2], $parcours);
        $parcours++;
    }
}

/**
 * @param $idSongbook
 * @param $rang
 * @param $longueurSaut
 * @return bool
 */
function remonteTitre($idSongbook, $rang, $longueurSaut)
{
    $quelqueChoseAeteFait = false;

    while (0 < $longueurSaut) {
        $coupleTrouve = true;

        // cherche le doc à monter
        $lienAmonter = chercheLienParIdSongbookOrdre($idSongbook, $rang);
        if ($lienAmonter == 0)
            $coupleTrouve = false;

        // cherche le doc à baisser
        $lienAbaisser = chercheLienParIdSongbookOrdre($idSongbook, $rang - 1);
        if ($lienAbaisser == 0)
            $coupleTrouve = false;

        if ($coupleTrouve) {
            //  changer l'ordre et enregistrer
            modifielienDocSongbook($lienAmonter[0], $lienAmonter[1], $lienAmonter[2], $lienAmonter[3] - 1);
            modifielienDocSongbook($lienAbaisser[0], $lienAbaisser[1], $lienAbaisser[2], $lienAbaisser[3] + 1);
            $quelqueChoseAeteFait = true;
        }
        $longueurSaut--;
        $rang--;
    }
    ordonneLiensSongbook($idSongbook);
    return $quelqueChoseAeteFait;
}

// Fonction de test
function testelienDocSongbook()
{
    creelienDocSongbook(1, 1);
    creelienDocSongbook(2, 1);
    // creeModifielienDocSongbook ( 2, 1,1,3 );
    creelienDocSongbook(3, 1);
    // echo infoslienDocSongbook ( $id );
}

// testelienDocSongbook ();
// ordonneLiensSongbook(24);
// TODO ajouter des logs pour tracer l'activité du site
