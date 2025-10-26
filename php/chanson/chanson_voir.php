<?php
/** @noinspection PhpUndefinedMethodInspection */
const CHANSON = "chanson";
const DIV_CLASS_ROW = "<div class='row'>";
const FIN_DIV = "</div>";
const FIN_SECTION = "</section>";
global $_DOSSIER_CHANSONS;
global $iconeEdit;
global $cheminImages;
require_once "../lib/utilssi.php";
require_once "../chanson/chanson.php";
require_once "../document/document.php";
require_once "../liens/lienStrumChanson.php";
require_once "../liens/lienurl.php";
require_once "../liens/lienurl_voir.php";
require_once "../navigation/menu.php";
require_once "../note/UtilisateurNote.php";
require_once "../songbook/songbook.php";
require_once "../strum/strum.php";

$_strumForm = "strum_form.php";
$_strumPost = "strum_post.php";
$_lienStrumChansonPost = "lienStrumChanson_post.php";
global $iconePoubelle;

$urlBoiteAstrum = "../../html/boiteAstrum/index.html";
$imageBoiteAstrum = "../../html/boiteAstrum/medias/img/boiteAstrum.png";

$table = CHANSON;
$contenuHtml = "<div class='container'>
  <div class='starter-template'> \n";
$monImage = "";

if (!is_numeric($_GET['id'])) {
    echo "Erreur #1 dans chanson_voir.php";
    return;
}

$idChanson = $_GET['id'];
$_chanson = new Chanson ($idChanson);
$fichiersDuSongbook = $_chanson->fichiersChanson($_DOSSIER_CHANSONS);

// On choisit une des images du songbook
$monImage = imageTableId(CHANSON, $idChanson);

$datePub = dateMysqlVersTexte($_chanson->getDatePub()); // datePub
$utilisateur = chercheUtilisateur($_chanson->getIdUser())[1];
$hits = $_chanson->getHits() + 1; // hits


$contenuHtml .= DIV_CLASS_ROW;
$contenuHtml .= "<section class='col-sm-8'>";

$contenuHtml .= DIV_CLASS_ROW;
$contenuHtml .= " <div class='col-sm-10'><h2>" . htmlentities($_chanson->getNom()) . "</h2>" . FIN_DIV; // Titre
$contenuHtml .= "<div class='col-sm-2'>";

if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    $contenuHtml .= ancre("chanson_form.php?id=" . $idChanson, image($cheminImages . $iconeEdit, 32, 32)); // Nom));
}
$contenuHtml .= "</div>" . FIN_DIV;

$contenuHtml .= DIV_CLASS_ROW;
$urlChercheAnChanson = "chanson_liste.php?filtre=annee&valFiltre=". $_chanson->getAnnee()."'>" . $_chanson->getAnnee();
$contenuHtml .= "<div class='col-sm-11'><h3> " . htmlentities($_chanson->getInterprete())  . " </h3>" . FIN_DIV .
    " - <a href='" . $urlChercheAnChanson . "</a>\n";
$contenuHtml .= FIN_DIV;
$contenuHtml .= DIV_CLASS_ROW;
$contenuHtml .= " <div class='col-sm-6'>Tonalité : " . $_chanson->getTonalite() . ", Tempo : " . $_chanson->getTempo();
$contenuHtml .= ", mesure : " . $_chanson->getMesure() . ", pulsation : " . $_chanson->getPulsation() . " <br>\n";
$contenuHtml .= " Publiée le  :$datePub, par $utilisateur, affichée $hits fois. <br>\n" . FIN_DIV . "\n";

$ICONEWIIKIPEDIA = "https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/";
$ICONEWIIKIPEDIA.="YouTube_Logo_2017.svg/280px-YouTube_Logo_2017.svg.png";
// Propose des recherches sur la chanson
$contenuHtml .= "<div class='col-sm-6'><a href='https://www.youtube.com/results?search_query=" .
    urlencode($_chanson->getNom() . " " . $_chanson->getInterprete()) . "' target='_blank'>
<img src='$ICONEWIIKIPEDIA' alt = 'recherche youtube' width='64'></a>\n";
$rechercheWikipedia = "https://fr.wikipedia.org/w/index.php?search=" .
    urlencode(($_chanson->getNom() . " " . $_chanson->getInterprete()));
$contenuHtml .= "<a href='$rechercheWikipedia' target='_blank'>
    <img src='https://fr.wikipedia.org/static/images/project-logos/frwiki.png'
    alt='recherche wikipedia' width='64'></a>\n" ;

$urlPageEnCours = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$contenuHtml .= generateQRCode ($urlPageEnCours, 128);
$contenuHtml .= FIN_DIV;
$contenuHtml .= FIN_SECTION;
$contenuHtml .= "<section class='col-sm-4'>";

if ("" != $monImage) {
    $urlImage = "../../" . $_DOSSIER_CHANSONS . $idChanson . "/" . $monImage;
    $contenuHtml .= image($urlImage, 200, "", "pochette", "img-thumbnail");
}

if ($_SESSION['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $contenuHtml .= UtilisateurNote::starBarUtilisateur(CHANSON, $idChanson, 5, 25);
} // 5 stars, Media ID 201, 25px star image
$contenuHtml .= UtilisateurNote::starBar(CHANSON, $idChanson, 5, 25); // 5 stars, Media ID 201, 25px star image

$contenuHtml .= FIN_SECTION;
$contenuHtml .= FIN_DIV;

augmenteHits($table, $idChanson);
// Cherche un document et le renvoie s'il existe
$result = chercheDocumentsTableId(CHANSON, $idChanson);

if ($result->num_rows > 0) {
    $contenuHtml .= "<h2> Documents attachés à cette chanson</h2>";
    $contenuHtml .= "<section class='row'>\n";

// Pour chaque document
    /** @noinspection PhpUndefinedMethodInspection */
    /** @noinspection PhpUndefinedMethodInspection */
    while ($ligne = $result->fetch_row()) {
        $fichierCourt = composeNomVersion($ligne [1], $ligne [4]);
        $fichierSec = substr($ligne [1], 0, strrpos($ligne [1], '.'));
        $extension = substr(strrchr($ligne [1], '.'), 1);
        if ($extension <> "mp3" && $extension <> "m4a" && $extension <> "mp4" && $extension <> "aac") {
            $contenuHtml .= "<div class='col-xs-4 col-sm-3 col-md-2 centrer'>\n";
            // $fichier = "../".$_DOSSIER_CHANSONS" . $idChanson . "/" . composeNomVersion ( $ligne [1], $ligne [4] );
            $icone = image(ICONES . $extension . ".png", 32, 32, "icone");
            if (!file_exists(ICONES . $extension . ".png")) {
                $icone = image("../images/icones/fichier.png", 32, 32, "icone");
            }
            $contenuHtml .= "<a href= '" . lienUrlAffichageDocument($ligne [0]) . "' target='_blank'> $icone  <br>" .
                htmlentities($fichierSec) . "</a> <br>\n";
            $contenuHtml .= FIN_DIV;
        }
    }
    $contenuHtml .= FIN_SECTION . "\n";

/// Affichage des audios mp3 avec un outil de lecture audio

    $contenuHtml .= "<br><section class='row'>\n";
    $result = chercheDocumentsTableId(CHANSON, $idChanson);

// Pour chaque fichier audio ou vidéo
    while ($ligne = $result->fetch_row()) {
        $fichierCourt = composeNomVersion($ligne [1], $ligne [4]);
        $fichierSec = substr($ligne [1], 0, strrpos($ligne [1], '.'));
        $extension = substr(strrchr($ligne [1], '.'), 1);
        if (($extension == "mp3") || ($extension == "m4a")|| ($extension == "aac")) {
            $contenuHtml .= "<div class='col-xs-12 col-sm-6 col-md-4 centrer'>\n";
            $baliseAudio = htmlentities($fichierSec) . "<br><audio controls='controls'>
                <source src='" . lienUrlAffichageDocument($ligne [0]) . "' type='";
            if ($extension == "aac") {
                $baliseAudio .= "audio/mpeg";
            }

            else{
                $baliseAudio .= "audio/mp3";
            }
                $baliseAudio .= "'>
            Votre navigateur ne prend pas en charge l'élément <code>audio</code></audio>";
            $contenuHtml .= $baliseAudio . "\n";
            $contenuHtml .= FIN_DIV;
        }
        elseif ($extension == "mp4"){
            $contenuHtml .= "<div class='col-xs-12 col-sm-6 col-md-4 centrer'>\n";
            $baliseVideo = htmlentities($fichierSec) . "<br><video width='320' controls='controls'>
            <source src='" . lienUrlAffichageDocument($ligne [0]) . "' type='video/ogg'>
            Votre navigateur ne prend pas en charge l'élément <code>audio</code></video>";
            $contenuHtml .= $baliseVideo . "\n";
            $contenuHtml .= FIN_DIV;
        }
    }
    $contenuHtml .= FIN_SECTION . " \n";
}

$contenuHtml .= afficheStrums($idChanson, $_chanson->getTempo(), $_chanson->getPulsation()=="ternaire");

//Voir les liens associés à cette chanson
//  id	table	idtable	url	type	description
$liens = $_chanson->chercheLiensChanson();

// Voir les songbooks associés à cette chanson

if ($liens->num_rows > 0) {
    $contenuHtml .= "<h2> Liens associés à cette chanson</h2>";
    $contenuHtml .= "<br><section class='row'>";

    while ($lien = $liens->fetch_row()) {
        $contenuHtml .= afficheLien($lien);
        ajouteUnHit($lien[0]);
    }

    $contenuHtml .= FIN_SECTION;
}

$songbooks = $_chanson->chercheSongbooksDocuments();

if ($songbooks->num_rows > 0) {

    $contenuHtml .= "<h2> Songbooks associés à cette chanson</h2>";
    $contenuHtml .= "<br><section class='row'>";

    while ($songbook = $songbooks->fetch_row()) {
        $nom = $songbook[1];
        $id = $songbook[0];
        $image = imageSongbook($id);
        $contenuHtml .= "

        <div class=\"col-xs-4 col-sm-3 col-md-2 centrer\">
        <a href = '../songbook/songbook_voir.php?id=$id'>
        <img src = '../../data/songbooks/$id/$image' height='128' alt = 'couverture songbook'>
        <p>  $nom</p>
        </a>
        </div>
        ";

    }
    $contenuHtml .= "</section>";
}
/**
 * @param int $idChanson
 * @return string
 */
function afficheStrums(int $idChanson, int $tempo, bool $ternaire): string
{
    global $urlBoiteAstrum;
    global $imageBoiteAstrum;
    $contenuHtml = "";
// Affiche les strums de la chanson
    $_listeDesLiensStrums = chercheLiensStrumChanson("idChanson", $idChanson);
    // Chargement de la liste des strums
    $marequete = "SELECT strum.id, 
        lienstrumchanson.strum, 
        strum.longueur , 
        strum.unite, 
        strum.description 
FROM lienstrumchanson    JOIN 
        strum ON lienstrumchanson.strum = strum.strum
    WHERE 
        lienstrumchanson.idChanson = $idChanson
";
    $_SESSION ['mysql']->query($marequete);
    if ($_listeDesLiensStrums->num_rows > 0) {
        $titre = "Strum";
        if ($_listeDesLiensStrums->num_rows > 1) {
            $titre .= "s";
        }
        $contenuHtml .= "<h2>$titre</h2>";
        $monStrum = new Strum();
        while ($lienStrum = $_listeDesLiensStrums->fetch_row()) {
            $monStrum->chercheStrumParChaine($lienStrum[1]);
            $contenuHtml .= entreBalise(str_replace(" ", "-", $monStrum->getStrum()), "H3"); // Login

            $contenuHtml .= $monStrum->getLongueur() . " " . $monStrum->renvoieUniteEnFrancais(); //  longueur / unité

            $urlDuStrum = $urlBoiteAstrum . "?strum=";
            $urlDuStrum .= str_replace(" ", "-",$monStrum->getStrum()) . "&tempo=" . $tempo;
            if ($ternaire){
                $urlDuStrum .= "&ternaire=true";
            }
            $contenuHtml .= "<a title='ouvrir dans la boîte à strum' href='$urlDuStrum'>
            <img src='$imageBoiteAstrum' alt='Ouvrir dans la boite a strum' height='50vw'>  </a>";

            $contenuHtml .= " - " . $monStrum->getDescription(); // description
            $contenuHtml .= $monStrum->chansonsDuStrum();
        }
    }
    return $contenuHtml;
}

$contenuHtml .= FIN_DIV . "<!-- /.starter-template -->\n
</div><!-- /.container -->\n";
$contenuHtml .= envoieFooter();
echo $contenuHtml;
