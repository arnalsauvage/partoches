<?php
const PRIVILEGE = 'privilege';
const NOM_FIC = 'nomFic';
const RESTAUREDOC = "RESTAUREDOC";
const RENDOC = "RENDOC";
const SUPPRFIC = "SUPPRFIC";
const SUPPR = "SUPPR";
$nomTable = "chanson";
require_once("../chanson/chanson.php");
require_once("../document/document.php");
require_once("../lib/utilssi.php");

// ecritFichierLog("ajaxlog.htm", "entrée dans chanson_post");

if ($_SESSION [PRIVILEGE] <= $GLOBALS["PRIVILEGE_INVITE"]) {
    redirection($nomTable . "_liste.php");
}

global $_DOSSIER_CHANSONS;

$nomTable = "chanson";
$_chanson = new Chanson();

function telechargeImageFromUrl($monUrl, $nomFichier, $id, $dossierDest)
{
    $repertoire = "../" . $dossierDest . $id . "/";
    $file = file_get_contents($monUrl);
    $cheminFichier = "vide";
    $nomFichier = simplifieNomFichier($nomFichier);

    $isImageJpg = (bin2hex($file[0]) == 'ff' && bin2hex($file[1]) == 'd8');
    if ($isImageJpg){
        $nomFichier .= ".jpg";
        $cheminFichier = $repertoire . $nomFichier;
    }

    $isImagePng = (bin2hex($file[0]) == '89' && $file[1] == 'P' && $file[2] == 'N' && $file[3] == 'G');
    if ($isImagePng){
        $nomFichier .= ".png";
        $cheminFichier = $repertoire . $nomFichier;
    }

    if ($cheminFichier <> "vide")
    {
        // On  enregistre le fichier sur disque
        file_put_contents ($cheminFichier, $file);
        // On met à jour en base de données
        $size = filesize($cheminFichier);
        $version = creeModifieDocument($nomFichier, $size, "chanson", $id);
        // Il faut renommer le doc en lui accolant son numéro de version
        rename($repertoire . $nomFichier, $repertoire . composeNomVersion($nomFichier, $version));
    }
/*
    echo "téléchargement de $monUrl<br>";
    echo " contenu : $file <br>";
    echo "vers fichier : $cheminFichier";
    exit();
*/
}

if (isset ($_GET ['id']) && is_numeric($_GET ['id'])) {
    $id = $_GET ['id'];
    $mode = $_GET ['mode'];
    //echo "On est en get <br> " ;
}

// Appel ajax RENDOC
if (isset ($_POST ['idDoc']) && is_numeric($_POST ['idDoc'])) {
    $mode = $_POST ['mode'];
}

if (isset ($_POST ['id']) && is_numeric($_POST ['id'])) {
    //echo "On est en post !!!  \n\n\n ";
    $id = $_POST ['id'];
    $fnom = $_POST ['fnom'];
    $finterprete = $_POST ['finterprete'];
    $fannee = $_POST ['fannee'];
    $ftempo = $_POST ['ftempo'];
    $fmesure = $_POST ['fmesure'];
    $fpulsation = $_POST ['fpulsation'];
    if (isset($_POST ['fhits']) && is_numeric($_POST ['fhits'])) {
        $fhits = $_POST ['fhits'];
    }
    $ftonalite = $_POST ['ftonalite'];
    $mode = $_POST ['mode'];
    if (isset($_POST ['fidUser'])) {
        $fidUser = $_POST ['fidUser'];
    }
    $fdate = $_POST['fdate'];
}

$chaine = "Mode  : " . $mode;
//ecritFichierLog("ajaxlog.htm", $chaine);


// On gère 5 cas :
//  1- création d'une chanson,
//  2 - modif,
//  3 - suppression chanson
//  4 - MAJ des infos depuis une API
//  5 - suppression d'un doc de la chanson

//  1- création d'une chanson,
if ($mode == "INS") {
    // echo "FHits = " . $fhits;
    $fhits = 0;
    $_chanson = new Chanson($fnom, $finterprete, $fannee, $fidUser, $ftempo, $fmesure, $fpulsation, $fhits, $ftonalite);
    $id = $_chanson->creeChansonBDD();
    $repertoire = "../". $_DOSSIER_CHANSONS . $id . "/";
    if (!file_exists($repertoire)) {
        mkdir($repertoire, 0755);
        echo " -=> Création du repertoire $repertoire réussi<br>";
    }
}

//  2 - modif,
if ($mode == "MAJ") {
    if ($_SESSION [PRIVILEGE] < 3) {
        // On doit recharger les hits pour qu'ils ne soient remis à zéro
        $_chanson->chercheChanson($id);
        $fhits = $_chanson->getHits();
    }
    $fdate = dateTexteVersMysql($fdate);

    $_chanson->__construct($id, $fnom, $finterprete, $fannee, $fidUser, $ftempo, $fmesure, $fpulsation, $fdate, $fhits, $ftonalite);
    $_chanson->creeModifieChansonBDD();
}

//  3 - suppression chanson
// Gestion de la demande de suppression
if ($id && $mode == SUPPR && $_SESSION [PRIVILEGE] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $_chanson = new Chanson($id);
    $_chanson->supprimeChansonBddFile();
    redirection($nomTable . "_liste.php");
}

//  4 - MAJ des infos depuis une API
if ($mode == "MAJ_SONGBPM") {
    // On doit recharger les hits, le user et la date pour qu'ils ne soient remis à zéro
    $_chanson->chercheChanson($id);
    $fhits = $_chanson->getHits();
    $fidUser = $_chanson->getIdUser();
    $fdate = $_chanson->getDatePub();
    $fnom = $_chanson->getNom();
    $finterprete = $_chanson->getInterprete();
    $fpulsation = $_chanson->getPulsation();
    $fannee = $_GET ['annee'];
    $ftempo = $_GET ['tempo'];
    $fmesure = $_GET ['mesure'];
    $ftonalite = $_GET ['tonalite'];
    $_chanson->__construct($id, $fnom, $finterprete, $fannee, $fidUser, $ftempo, $fmesure, $fpulsation, $fdate, $fhits, $ftonalite);
    $_chanson->creeModifieChansonBDD();
    $fimage = $_GET ['image'];
    echo "télécharge fichier" . $fnom . "-" . $finterprete . " depuis url " .$fimage;
    telechargeImageFromUrl($fimage, $fnom . "-" . $finterprete , $id, $_DOSSIER_CHANSONS);
}

//  5 - gestion des docs de la chanson
// Gestion de la demande de suppression de document dans la chanson
if ($mode == "SUPPRDOC" && $_SESSION [PRIVILEGE] > 1) {
    // 	echo "Appel avec mode = $mode, id = $id, idDoc = " . $_GET ['idDoc'] . " idSongbook = " . $_GET ['idSongbook'];
    supprimeDocument($_GET ['idDoc']);
}

// Gestion de la demande de renommage de document dans la chanson
if ($mode == RENDOC && $_SESSION [PRIVILEGE] > 1) {
//    $log =  "Appel avec idDoc = " . $_POST ['idDoc'] . " nomDoc = " . $_POST ['nomDoc'];
//    ecritFichierLog("ajaxlog.htm", $log);
    $retour = renommeDocument($_POST ['idDoc'], $_POST ['nomDoc']);
    if ($retour == 1) {
        echo "Tout s'est bien passé";
    } else {
        {
            echo "La demande n'a pas été traitée... Erreur " . $retour;
        }
    }
}

// Gestion de la demande de suppression de fichier dans la chanson
if ($mode == SUPPRFIC  && $_SESSION [PRIVILEGE] > 1) {
    // echo "Appel avec mode = $mode, id = $id, nomFic = " . $_GET ['nomFic'];
    unlink($_GET[NOM_FIC]);
}

if ($mode == RESTAUREDOC) {

    $repertoire = "../".$_DOSSIER_CHANSONS . $_POST ['id'] . "/";
    $size = filesize($repertoire . $_POST [NOM_FIC]);
    $version = creeModifieDocument($_POST [NOM_FIC], $size, $nomTable, $id);
    // Il faut renommer le doc en lui accolant son numéro de version
    rename($repertoire . $_POST [NOM_FIC], $repertoire . composeNomVersion($_POST [NOM_FIC], $version));
}
    resetMediasPartoches(99);

// On fait une redirection dans tous les cas, sauf la demande de restauration d'un fichier - appel ajax
if ($mode != RESTAUREDOC && $mode !=  RENDOC ) {
    redirection($nomTable . "_form.php?id=$id");
}

function resetMediasPartoches($nombreMedias){
    require_once ("../media/Media.php");
    $medias = new Media();
    $medias->resetAvecDernieresPartoches($nombreMedias);
}
