<?php
const PRIVILEGE = 'privilege';
const NOM_FIC = 'nomFic';
const RESTAUREDOC = "RESTAUREDOC";
const RENDOC = "RENDOC";
const SUPPRFIC = "SUPPRFIC";
const SUPPR = "SUPPR";
const REGEN_THUMBS = "REGEN_THUMBS";
const CHANSON = "chanson";
$nomTable = CHANSON;
require_once dirname(__DIR__, 3) . "/autoload.php";
require_once __DIR__ . "/../lib/utilssi.php";

// ecritFichierLog("ajaxlog.htm", "entrée dans chanson_post");

if ($_SESSION [PRIVILEGE] <= $GLOBALS["PRIVILEGE_INVITE"]) {
    redirection($nomTable . "_liste.php");
}

global $_DOSSIER_CHANSONS;

$nomTable = "chanson";
$_chanson = new Chanson();

/**
 * Télécharge une image depuis une URL, la redimensionne (max 400x400),
 * la convertit en WebP et l'enregistre comme document de la chanson.
 */
function telechargeImageFromUrl($monUrl, $nomFichier, $id, $dossierDest): string
{
    require_once __DIR__ . "/../lib/Image.php";
    
    $repertoire = $dossierDest . $id . "/";
    if (!file_exists($repertoire)) {
        mkdir($repertoire, 0755, true);
    }

    // On télécharge le contenu
    $fileData = @file_get_contents($monUrl);
    if (!$fileData) return "";

    // On utilise un fichier temporaire pour le traitement
    $tmpFile = tempnam(sys_get_temp_dir(), 'chanson_cover_');
    file_put_contents($tmpFile, $fileData);

    $img = Image::load($tmpFile);
    if (!$img) {
        unlink($tmpFile);
        return "";
    }

    // Redimensionnement (Max 400x400)
    $resized = Image::resizeToLimit($img, 400, 400);
    if ($resized) {
        imagedestroy($img);
        $img = $resized;
    }

    // Nom de fichier propre (forcé en webp)
    $nomBase = simplifieNomFichier($nomFichier);
    $nomFichierFinal = $nomBase . ".webp";
    
    // Sauvegarde en WebP (qualité 75)
    $cheminFinalTmp = $tmpFile . ".webp";
    $success = Image::save($img, $cheminFinalTmp, 'webp', 75);
    imagedestroy($img);
    unlink($tmpFile);

    if ($success) {
        $size = filesize($cheminFinalTmp);
        $version = Document::creeModifieDocument($nomFichierFinal, $size, "chanson", $id);
        
        $nomVersionne = Document::composeNomVersion($nomFichierFinal, $version);
        $destinationDefinitive = $repertoire . $nomVersionne;
        
        if (rename($cheminFinalTmp, $destinationDefinitive)) {
            chmod($destinationDefinitive, 0644);
            // Retourne le chemin relatif web pour la colonne 'cover' de la table chanson
            return "../../data/chansons/" . $id . "/" . $nomVersionne;
        }
    }

    if (file_exists($cheminFinalTmp)) unlink($cheminFinalTmp);
    return "";
}

if (isset ($_GET ['id']) && is_numeric($_GET ['id'])) {
    $id = $_GET ['id'];
    $mode = $_GET ['mode'];
}

// Appel ajax RENDOC
if (isset ($_POST ['idDoc']) && is_numeric($_POST ['idDoc'])) {
    $mode = $_POST ['mode'];
}

if (isset ($_POST ['id']) && is_numeric($_POST ['id'])) {
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
    $fdate = $_POST['fdate'] ?? date("Y-m-d");
    // NOUVEAU : Convertir la date du formulaire au format MySQL
    $fdate = dateTexteVersMysql($fdate);
    // NOUVEAU : Récupérer le champ cover
    $fcover = $_POST['fcover'] ?? null;
    // NOUVEAU : Récupérer le champ publication
    $fpublication = isset($_POST['fpublication']) ? 1 : 0;
}

//  1- création d'une chanson,
if ($mode == "INS") {
    $fhits = 0;
    $_chanson = new Chanson($fnom, $finterprete, $fannee, $fidUser, $ftempo, $fmesure, $fpulsation, $fhits, $ftonalite);
    $_chanson->setPublication($fpublication);
    $id = $_chanson->creeChansonBDD();

    // Si une cover URL est fournie, on la télécharge maintenant qu'on a l'ID
    if ($fcover && str_starts_with($fcover, 'http')) {
        $localCover = telechargeImageFromUrl($fcover, "pochette-" . $fnom, $id, $_DOSSIER_CHANSONS);
        if ($localCover) $_chanson->setCover($localCover);
    } else {
        $_chanson->setCover($fcover);
    }
    
    $_chanson->creeModifieChansonBDD(); // Seconde passe pour l'ID et la cover locale
    
    $repertoire = $_DOSSIER_CHANSONS . $id . "/";
    if (!file_exists($repertoire)) {
        mkdir($repertoire, 0755, true);
    }
}

//  2 - modif,
if ($mode == "MAJ") {
    if ($_SESSION [PRIVILEGE] < 3) {
        $_chanson->chercheChanson($id);
        $fhits = $_chanson->getHits();
    }
    
    // Si c'est une nouvelle URL externe, on la télécharge et on l'optimise
    if ($fcover && str_starts_with($fcover, 'http')) {
        $localCover = telechargeImageFromUrl($fcover, "pochette-" . $fnom, $id, $_DOSSIER_CHANSONS);
        if ($localCover) $fcover = $localCover;
    }

    $_chanson->__construct($id, $fnom, $finterprete, $fannee, $fidUser, $ftempo, $fmesure, $fpulsation, $fdate, $fhits, $ftonalite);
    $_chanson->setCover($fcover);
    $_chanson->setPublication($fpublication);
    $_chanson->creeModifieChansonBDD();
}

// On actualise la table des médias automatiquement
actualiseMedias();

//  3 - suppression chanson
if ($id && $mode == SUPPR && $_SESSION [PRIVILEGE] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $_chanson = new Chanson($id);
    $_chanson->supprimeChansonBddFile();
    redirection($nomTable . "_liste.php");
}

//  4 - MAJ des infos depuis une API
if ($mode == "MAJ_SONGBPM") {
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
    echo "télécharge fichier " . $fnom . "-" . $finterprete . " depuis url " . $fimage;
    telechargeImageFromUrl($fimage, $fnom . "-" . $finterprete , $id, $_DOSSIER_CHANSONS);
}

//  5 - gestion des docs de la chanson
if ($mode == "SUPPRDOC" && $_SESSION [PRIVILEGE] > 1) {
    Document::supprimeDocument($_GET ['idDoc']);
}

if ($mode == RENDOC && $_SESSION [PRIVILEGE] > 1) {
    $retour = renommeDocument($_POST ['idDoc'], $_POST ['nomDoc']);
    if ($retour == 1) {
        echo "Tout s'est bien passé";
    } else {
        echo "La demande n'a pas été traitée... Erreur " . $retour;
    }
}

// Régénération forcée des vignettes
if ($mode == REGEN_THUMBS) {
    require_once "../lib/Image.php";
    $res = Document::chercheDocumentsTableId(CHANSON, $id);
    $count = 0;
    while ($doc = $res->fetch_row()) {
        $nomDoc = $doc[1];
        $version = $doc[4];
        $ext = strtolower(pathinfo($nomDoc, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $relPath = $id . "/" . Document::composeNomVersion($nomDoc, $version);
            Image::getThumbnailUrl($relPath, 'mini', 'chansons', true);
            Image::getThumbnailUrl($relPath, 'sd', 'chansons', true);
            $count++;
        }
    }
    redirection("chanson_form.php?id=$id&msg=OK_REGEN&count=$count");
}

if ($mode == SUPPRFIC  && $_SESSION [PRIVILEGE] > 1) {
    unlink($_GET[NOM_FIC]);
}

if ($mode == RESTAUREDOC) {
    $repertoire = $_DOSSIER_CHANSONS . $_POST ['id'] . "/";
    $size = filesize($repertoire . $_POST [NOM_FIC]);
    $version = Document::creeModifieDocument($_POST [NOM_FIC], $size, $nomTable, $id);
    rename($repertoire . $_POST [NOM_FIC], $repertoire . composeNomVersion($_POST [NOM_FIC], $version));
}

// On actualise l'index des médias
resetMediasPartoches(99);

// On fait une redirection dans tous les cas, sauf la demande de restauration d'un fichier - appel ajax
if ($mode != RESTAUREDOC && $mode !=  RENDOC ) {
    redirection($nomTable . "_form.php?id=$id");
}

/**
 * Réinitialise l'index des médias (SOLID)
 */
function resetMediasPartoches(int $nombreMedias): void
{
    // Utilisation du Service au lieu du POPO
    require_once __DIR__ . "/../media/MediaService.php";
    MediaService::resetMediasDistribues($nombreMedias);
}
