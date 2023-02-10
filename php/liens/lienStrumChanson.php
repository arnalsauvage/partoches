<?php
include_once("../lib/utilssi.php");
include_once("../lib/configMysql.php");

// Fonctions de gestion de la lienStrumChanson

// Cherche les lienStrumChansons correspondant à un critère
function chercheLiensStrumChanson($critere, $valeur, $critereTri = 'ordre', $bTriAscendant = true)
{
    $maRequete = "SELECT * FROM lienstrumchanson WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    if ($bTriAscendant == false)
        $maRequete .= " DESC";
    else
        $maRequete .= " ASC";
    // echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheliensStrumChanson #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Cherche un lienStrumChanson et le renvoie s'il existe
function chercheLienStrumChanson($id)
{
    $maRequete = "SELECT * FROM lienstrumchanson WHERE id = '$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchelienStrumChanson #1 : " . $_SESSION ['mysql']->error);
    // renvoie la lisgne sélectionnée : id, nom, interprète, année
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Cherche un lienStrumChanson et le renvoie s'il existe
function chercheLienStrumChansonOrdre($strum, $chanson, $ordre)
{
    $maRequete = "SELECT * FROM lienstrumchanson WHERE strum = '$strum' AND idChanson='$chanson' AND ordre='$ordre'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienStrumChansonOrdre #1 : " . $_SESSION ['mysql']->error);
    // renvoie la lisgne sélectionnée : id, strum, idChanson, ordre
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Renvoie le nombre de strum dans un chanson
/**
 * @param $idChanson
 * @return mixed
 */
function nombreDeStrumsDuneChanson($idChanson)
{
    $maRequete = "SELECT * FROM lienstrumchanson WHERE idchanson = '$idChanson'";
    $result = $_SESSION['mysql']->query($maRequete) or die ("Problème nombreDeStrumsDuneChanson #1 : " . $_SESSION ['mysql']->error);
    $row_cnt = $result->num_rows;
    return ($row_cnt);
}

// Cherche un lienStrumChanson et le renvoie s'il existe
function chercheLienParIdchansonIdDoc($_strum, $id_Chanson)
{
    $maRequete = "SELECT * FROM lienstrumchanson WHERE strum = '$_strum' AND idchanson = '$id_Chanson'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienParIdchansonIdDoc #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, strum, idChanson, ordre
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Cherche le nieme lienStrumChanson  d'un strum  le renvoie s'il existe
function chercheLienParIdchansonOrdre($_idChanson, $ordre)
{
    $maRequete = "SELECT * FROM lienstrumchanson WHERE ordre = '$ordre' AND idChanson = '$_idChanson'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheLienParIdchansonOrdre #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, interprète, année
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Crée un lienStrumChanson
function creelienStrumChanson($_strum, $idchanson)
{
    chercheLiensStrumChanson("idchanson", $idchanson, "ordre");
    $nb = $_SESSION ['mysql']->affected_rows + 1;
    $maRequete = "INSERT INTO lienstrumchanson VALUES (NULL, '$_strum', '$idchanson', '$nb')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creelienStrumChanson #1 : " . $_SESSION ['mysql']->error);
}

// Modifie en base la lienStrumChanson
function modifielienStrumChanson($_strum, $idchanson, $ordre)
{
    $maRequete = "UPDATE  lienstrumchanson
	SET strum = '$_strum', idchanson = '$idchanson', ordre = '$ordre'
	WHERE idChanson='$idchanson' AND strum = '$_strum'";
// 	echo $maRequete . "<br>";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème modifielienStrumChanson #1 : " . $_SESSION ['mysql']->error);
}

// Modifie en base l'ordre du lienStrumChanson
function modifieOrdreLienStrumChanson( $_strum, $idchanson, $ordre)
{
    $maRequete = "UPDATE  lienstrumchanson
	SET  ordre = '$ordre'
	WHERE strum = '$_strum' AND idchanson = '$idchanson'";
// 	echo $maRequete . "<br>";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème modifieOrdreLienStrumChanson #1 : " . $_SESSION ['mysql']->error);
}

// Cette fonction supprime un lienStrumChanson si il existe
function supprimelienStrumChanson($idlienStrumChanson)
{
    $_lien_cherche = chercheLienStrumChanson($idlienStrumChanson);
    if (!$_lien_cherche)
            return false;
    $_idChanson = $_lien_cherche[2];
    // On supprime les enregistrements dans lienStrumChanson
    $maRequete = "DELETE FROM lienstrumchanson
	WHERE id='$idlienStrumChanson'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimelienStrumChanson : " . $_SESSION ['mysql']->error);
    // On réordonne la liste
    ordonneLiensStrumChanson($_idChanson);
}

// Cette fonction supprime un lienStrumChanson si il existe
function supprimelienIdDocIdchanson($_strum, $idchanson)
{
    // On supprime les enregistrements dans lienStrumChanson
    $maRequete = "DELETE FROM lienstrumchanson
	WHERE strum = '$_strum' AND idchanson = '$idchanson'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimelienStrumChanson : " . $_SESSION ['mysql']->error);
    // On réordonne la liste
    ordonneLiensStrumChanson($idchanson);
}

// Cette fonction modifie ou crée un lienStrumChanson si besoin
function creeModifielienStrumChanson($id, $_strum, $idchanson, $ordre = 0)
{
    if (chercheLienStrumChanson($_strum, $idchanson))
        modifielienStrumChanson($id, $_strum, $idchanson, $ordre);
    else
        creelienStrumChanson($_strum, $idchanson);
}

// Cette fonction renvoie une chaine de description de la lienStrumChanson
function infoslienStrumChanson($id)
{
    $enr = chercheLienStrumChanson($id);
    // id_journee id_joueur poste statut
    $retour = "Id : " . $enr [0] . " strum : " . $enr [1] . " Idchanson : " . $enr [2] . " Ordre : " . $enr [3];
    return $retour . "<BR>\n";
}

function ordonneLiensStrumChanson($idchanson)
{
    // Récupérer la liste des liens triés par ordre
    $lignes = chercheLiensStrumChanson('idchanson', $idchanson, "ordre", true);

    // Les stocker dans un tableau
    $numero = 1;
    while ($ligne = $lignes->fetch_row()) {
        $mesLiens [$numero] = $ligne;
        $numero++;
    }

    // Faire une boucle de 1 à n pour les renuméroter
    $parcours = 1;
    while ($parcours < $numero) {
        modifielienStrumChanson($mesLiens [$parcours] [0], $mesLiens [$parcours] [1], $mesLiens [$parcours] [2], $parcours);
        $parcours++;
    }
}

/**
 * @param $idchanson
 * @param $rang
 * @param $longueurSaut
 * @return bool
 */
function remonteStrum($strum, $idchanson, $rang, $longueurSaut)
{
    $quelqueChoseAeteFait = false;

    while (0 < $longueurSaut) {
        $coupleTrouve = true;

        // cherche le strum à monter
        $_strumAmonter = chercheLienStrumChansonOrdre($strum, $idchanson, $rang);
        if ($_strumAmonter == 0)
            $coupleTrouve = false;

        // cherche le doc à baisser
        $lienAbaisser = chercheLienStrumChansonOrdre($strum, $idchanson,$rang - 1);
        if ($lienAbaisser == 0)
            $coupleTrouve = false;

        if ($coupleTrouve) {
            //  changer l'ordre et enregistrer
            modifielienStrumChanson( $_strumAmonter[1], $_strumAmonter[2], $_strumAmonter[3] - 1);
            modifielienStrumChanson( $lienAbaisser[1], $lienAbaisser[2], $lienAbaisser[3] + 1);
            $quelqueChoseAeteFait = true;
        }
        $longueurSaut--;
        $rang--;
    }
    ordonneLiensStrumChanson($idchanson);
    return $quelqueChoseAeteFait;
}

// Fonction de test
function testelienStrumChanson()
{
    creelienStrumChanson(1, 1);
    creelienStrumChanson(2, 1);
    // creeModifielienStrumChanson ( 2, 1,1,3 );
    creelienStrumChanson(3, 1);
    // echo infoslienStrumChanson ( $id );
}

// testelienStrumChanson ();
// ordonneLienschanson(24);
// TODO ajouter des logs pour tracer l'activité du site
