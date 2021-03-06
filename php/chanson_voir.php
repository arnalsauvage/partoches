<?php
/** @noinspection PhpUndefinedMethodInspection */
const CHANSON = "chanson";
const DIV_CLASS_ROW = "<div class='row'>";
const FIN_DIV = "</div>";
const FIN_SECTION = "</section>";
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("chanson.php");
require_once("document.php");
require_once("songbook.php");
require_once("UtilisateurNote.php");

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

if ($_SESSION ['privilege'] > 1) {
    $contenuHtml .= Ancre("chanson_form.php?id=" . $idChanson, Image($cheminImages . $iconeEdit, 32, 32)); // Nom));
}
$contenuHtml .= "</div>" . FIN_DIV;

$contenuHtml .= DIV_CLASS_ROW;
$contenuHtml .= "<div class='col-sm-11'><h3> " . htmlentities($_chanson->getInterprete()) . " - " . $_chanson->getAnnee() . " </h3>" . FIN_DIV . "\n";
$contenuHtml .= FIN_DIV;
$contenuHtml .= DIV_CLASS_ROW;
$contenuHtml .= " <div class='col-sm-8'>Tonalité : " . $_chanson->getTonalite() . ", Tempo : " . $_chanson->getTempo();
$contenuHtml .= ", mesure : " . $_chanson->getMesure() . ", pulsation : " . $_chanson->getPulsation() . " <br>\n";
$contenuHtml .= " Publiée le  :$datePub, par $utilisateur, affichée $hits fois. <br>\n" . FIN_DIV . "\n";

// Propose des recherches sur la chanson
$contenuHtml .= "<div class='col-sm-4'><a href='https://www.youtube.com/results?search_query=" . urlencode($_chanson->getNom() . " " . $_chanson->getInterprete()) . "' target='_blank'><img src='https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/YouTube_Logo_2017.svg/280px-YouTube_Logo_2017.svg.png' alt = 'recherche youtube' width='64'></a>\n";
$rechercheWikipedia = "https://fr.wikipedia.org/w/index.php?search=" . urlencode(($_chanson->getNom() . " " . $_chanson->getInterprete()));
$contenuHtml .= "<a href='$rechercheWikipedia' target='_blank'><img src='https://fr.wikipedia.org/static/images/project-logos/frwiki.png' alt='recherche wikipedia' width='64'></a><br>\n" . FIN_DIV . "\n";
$contenuHtml .= FIN_DIV;
$contenuHtml .= FIN_SECTION;
$contenuHtml .= "<section class='col-sm-4'>";

if ("" != $monImage) {
    $contenuHtml .= Image("../" . $_DOSSIER_CHANSONS . $idChanson . "/" . $monImage, 200, "", "pochette", "img-thumbnail");
}

if ($_SESSION['privilege'] > 0) {
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
        $contenuHtml .= "<div class='col-xs-4 col-sm-3 col-md-2 centrer'>\n";
        $fichierCourt = composeNomVersion($ligne [1], $ligne [4]);
        // $fichier = "../".$_DOSSIER_CHANSONS" . $idChanson . "/" . composeNomVersion ( $ligne [1], $ligne [4] );
        $fichierSec = substr($ligne [1], 0, strrpos($ligne [1], '.'));
        $extension = substr(strrchr($ligne [1], '.'), 1);
        $icone = Image("../images/icones/" . $extension . ".png", 32, 32, "icone");
        if (!file_exists("../images/icones/" . $extension . ".png")) {
            $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
        }
        $contenuHtml .= "<a href= '" . lienUrlAffichageDocument($ligne [0]) . "' target='_blank'> $icone  <br>" . htmlentities($fichierSec) . "</a> <br>\n";
        $contenuHtml .= FIN_DIV;
    }
    $contenuHtml .= FIN_SECTION . "\n";

/// Affichage des audios mp3 avec un outil de lecture audio

    $contenuHtml .= "<br><section class='row'>\n";
    $result = chercheDocumentsTableId(CHANSON, $idChanson);

// Pour chaque fichier audio
    while ($ligne = $result->fetch_row()) {
        $fichierCourt = composeNomVersion($ligne [1], $ligne [4]);
        $fichierSec = substr($ligne [1], 0, strrpos($ligne [1], '.'));
        $extension = substr(strrchr($ligne [1], '.'), 1);
        if (($extension == "mp3") || ($extension == "m4a")) {
            $contenuHtml .= "<div class='col-xs-12 col-sm-6 col-md-4 centrer'>\n";
            $baliseAudio = htmlentities($fichierSec) . "<br><audio controls='controls'>   <source src='" . lienUrlAffichageDocument($ligne [0]) . "' type='audio/mp3'>
            Votre navigateur ne prend pas en charge l'élément <code>audio</code></audio>";
            $contenuHtml .= $baliseAudio . "\n";
            $contenuHtml .= FIN_DIV;
        }
    }
    $contenuHtml .= " </section>\n";
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
<a href = 'songbook_voir.php?id=$id'>
<img src = '../data/songbooks/$id/$image' height='128' alt = 'couverture songbook'>
<p>  $nom</p>
</a>
</div>

";
    }
    $contenuHtml .= "</section>";
}
$contenuHtml .= FIN_DIV . "<!-- /.starter-template -->\n
</div><!-- /.container -->\n";
$contenuHtml .= envoieFooter();
echo $contenuHtml;