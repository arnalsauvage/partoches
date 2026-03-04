<?php
/** @noinspection PhpUndefinedMethodInspection */

/**
 * PAGE : chanson_voir.php
 * Affiche le détail d'une chanson avec une UX moderne (badges, barre d'outils, lightbox).
 */

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

// --- CONSTANTES ET GLOBALES ---
const CHANSON = "chanson";
const DIV_ROW = "<div class='row'>";
const FIN_DIV = "</div>";
const FIN_SECTION = "</section>";

global $_DOSSIER_CHANSONS, $iconeEdit, $cheminImages, $iconePoubelle;

// --- INITIALISATION ET RÉCUPÉRATION DES DONNÉES ---
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Erreur : Identifiant de chanson invalide.");
}

$idChanson = (int)$_GET['id'];
$_chanson = new Chanson($idChanson);

// Augmenter le compteur de vues
augmenteHits(CHANSON, $idChanson);

// Données de base
$datePub = dateMysqlVersTexte($_chanson->getDatePub());
$utilisateur = chercheUtilisateur($_chanson->getIdUser())[1];
$hits = $_chanson->getHits() + 1;
$monImage = imageTableId(CHANSON, $idChanson);

// Récupération et filtrage des documents
$resultDocs = chercheDocumentsTableId(CHANSON, $idChanson);
$documents = [];
$nbImages = 0;

if (!empty($resultDocs)) {
    while ($ligne = $resultDocs->fetch_row()) {
        $documents[] = $ligne;
        $ext = strtolower(substr(strrchr($ligne[1], '.'), 1));
        if (in_array($ext, ['jpg', 'png', 'webp'])) {
            $nbImages++;
        }
    }
}

// --- CONSTRUCTION DU CONTENU HTML ---
$contenuHtml = "
<style>
    .badge-tech { font-size: 1.1em; padding: 6px 10px; margin-right: 5px; margin-bottom: 10px; display: inline-block; text-decoration: none !important; color: white !important; }
    .badge-tech:hover { opacity: 0.8; }
    .tempo-label { font-size: 0.7em; display: block; margin-top: 2px; font-weight: normal; font-style: italic; }
    
    .action-bar { margin-top: 15px; display: flex; align-items: center; flex-wrap: wrap; gap: 15px; background: #f9f9f9; padding: 10px; border-radius: 8px; border: 1px solid #eee; }
    .action-item { display: flex; align-items: center; text-decoration: none; color: #333; transition: transform 0.2s; cursor: pointer; border: none; background: transparent; padding: 0; }
    .action-item:hover { transform: translateY(-2px); text-decoration: none; color: #000; }
    .action-icon { height: 32px; width: auto; }
    .qr-icon { font-size: 28px; color: #333; }
    
    .pochette-container { cursor: zoom-in; transition: transform 0.2s; }
    .pochette-container:hover { transform: scale(1.02); }
    #copy-success { display: none; margin-left: 10px; color: #5cb85c; font-weight: bold; font-size: 0.8em; }
    
    /* Fix pour les modales qui restent bloquées sous le voile noir */
    .modal { z-index: 2000 !important; }
    .modal-backdrop { z-index: 1040 !important; }
</style>

<div class='container'>
    <div class='starter-template'>\n";

// --- BOUTONS NAVIGATION ---
$contenuHtml .= DIV_ROW;
$contenuHtml .= "  <div class='col-xs-12 text-left' style='margin-bottom: 15px;'>";
$contenuHtml .= "    <a href='chanson_liste.php' class='btn btn-default btn-sm'><i class='glyphicon glyphicon-arrow-left'></i> Retour</a>";
if (!empty($_SESSION['privilege']) && $_SESSION['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    $contenuHtml .= "    <a href='chanson_form.php?id=$idChanson' class='btn btn-primary btn-sm'><i class='glyphicon glyphicon-pencil'></i> Modifier</a>";
}
$contenuHtml .= "  </div>";
$contenuHtml .= FIN_DIV;

// 1. ENTÊTE
$contenuHtml .= DIV_ROW;

// Colonne Gauche : Titre, Badges, Barre d'outils
$contenuHtml .= "<section class='col-sm-8'>";
$contenuHtml .= "  <h2 style='margin-top:0;'>" . htmlentities($_chanson->getNom());
// Bouton Copier URL
$contenuHtml .= "    <button class='btn btn-xs btn-link' onclick='copyUrlToClipboard()' title='Copier le lien'>
                        <i class='glyphicon glyphicon-link'></i>
                     </button>
                     <span id='copy-success'><i class='glyphicon glyphicon-ok'></i> Copié !</span>
                   </h2>";

$urlChercheAn = "chanson_liste.php?filtre=annee&valFiltre=" . $_chanson->getAnnee();
$contenuHtml .= "  <h3 style='margin-top:0;'>" . htmlentities($_chanson->getInterprete()) . "</h3>";

// Badges Techniques
$tonalite = !empty($_chanson->getTonalite()) ? $_chanson->getTonalite() : '?';
$urlTona = "chanson_liste.php?filtre=tonalite&valFiltre=" . urlencode($tonalite);
$tempoBpm = $_chanson->getTempo();
$tempoInfo = getTempoInfo($tempoBpm);
$urlTempo = "chanson_liste.php?filtre=tempo_famille&valFiltre=" . urlencode($tempoInfo['name']);
$annee = $_chanson->getAnnee();

$contenuHtml .= "  <div style='margin-top: 15px;'>";
$contenuHtml .= "    <a href='$urlChercheAn' class='label label-success badge-tech'><i class='glyphicon glyphicon-calendar'></i> $annee</a>";
$contenuHtml .= "    <a href='$urlTona' class='label label-primary badge-tech'><i class='glyphicon glyphicon-music'></i> $tonalite</a>";
$contenuHtml .= "    <a href='$urlTempo' class='label label-info badge-tech'><i class='glyphicon glyphicon-time'></i> $tempoBpm <span class='tempo-label'>{$tempoInfo['label']}</span></a>";
$contenuHtml .= "    <span class='label label-default badge-tech'><i class='glyphicon glyphicon-equalizer'></i> " . $_chanson->getMesure() . "</span>";
$pulsationIcon = ($_chanson->getPulsation() === "ternaire") ? "glyphicon-refresh" : "glyphicon-option-vertical";
$contenuHtml .= "    <span class='label label-warning badge-tech'><i class='glyphicon $pulsationIcon'></i> " . $_chanson->getPulsation() . "</span>";
$contenuHtml .= "  </div>";

// --- BARRE D'ACTIONS HARMONISÉE ---
$contenuHtml .= "  <div class='action-bar'>";
// YouTube
$contenuHtml .= "    <a href='https://www.youtube.com/results?search_query=" . urlencode($_chanson->getNom() . " " . $_chanson->getInterprete()) . "' target='_blank' class='action-item' title='Rechercher sur YouTube'>";
$contenuHtml .= "       <img src='https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/YouTube_Logo_2017.svg/280px-YouTube_Logo_2017.svg.png' class='action-icon' alt='YouTube'>";
$contenuHtml .= "    </a>";

// Wikipedia
$rechercheWiki = "https://fr.wikipedia.org/w/index.php?search=" . urlencode($_chanson->getNom() . " " . $_chanson->getInterprete());
$contenuHtml .= "    <a href='$rechercheWiki' target='_blank' class='action-item' title='Rechercher sur Wikipedia'>";
$contenuHtml .= "       <img src='https://fr.wikipedia.org/static/images/project-logos/frwiki.png' class='action-icon' alt='Wikipedia'>";
$contenuHtml .= "    </a>";

// QR Code
$contenuHtml .= "    <button class='action-item' title='Afficher le QR Code' onclick='openQRModal()'>";
$contenuHtml .= "       <i class='glyphicon glyphicon-qrcode qr-icon'></i>";
$contenuHtml .= "    </button>";

// Votes (Espace réservé)
$contenuHtml .= "    <div class='action-item' style='flex-grow: 1; justify-content: flex-end; cursor: default;'>";
if (!empty($_SESSION['privilege']) && $_SESSION['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $contenuHtml .= UtilisateurNote::starBarUtilisateur(CHANSON, $idChanson, 5, 25);
}
$contenuHtml .= "       <div style='margin-left:10px;'>" . UtilisateurNote::starBar(CHANSON, $idChanson, 5, 25) . "</div>";
$contenuHtml .= "    </div>";
$contenuHtml .= "  </div>";

$contenuHtml .= "  <p class='text-muted' style='margin-top:10px;'>Publiée le $datePub par $utilisateur &bull; Vue $hits fois</p>";
$contenuHtml .= FIN_SECTION;

// Colonne Droite : Image Couverture
$contenuHtml .= "<section class='col-sm-4 text-center'>";
if (!empty($monImage)) {
    $urlImage = "../../" . $_DOSSIER_CHANSONS . $idChanson . "/" . $monImage;
    $contenuHtml .= "  <div class='pochette-container' onclick='openLightbox(\"$urlImage\")'>";
    $contenuHtml .= "    " . image($urlImage, 200, "", "pochette", "img-thumbnail img-responsive center-block");
    $contenuHtml .= "  </div>";
}
$contenuHtml .= FIN_SECTION;

$contenuHtml .= FIN_DIV;


// 2. DOCUMENTS ATTACHÉS
if (!empty($documents)) {
    $sectionDocs = "";
    foreach ($documents as $ligne) {
        $extension = strtolower(substr(strrchr($ligne[1], '.'), 1));
        if (in_array($extension, ['mp3', 'm4a', 'aac', 'mp4'])) continue;
        if ($nbImages === 1 && in_array($extension, ['jpg', 'png', 'webp'])) continue;

        $fichierSec = substr($ligne[1], 0, strrpos($ligne[1], '.'));
        $icone = image(ICONES . $extension . ".png", 32, 32, "icone");
        if (!file_exists(ICONES . $extension . ".png")) {
            $icone = image("../images/icones/fichier.png", 32, 32, "icone");
        }

        $sectionDocs .= "<div class='col-xs-6 col-sm-3 col-md-2 centrer' style='margin-bottom: 20px;'>";
        $sectionDocs .= "  <a href='" . lienUrlAffichageDocument($ligne[0]) . "' target='_blank' class='thumbnail' style='text-decoration:none; padding:10px;'>$icone<br><small>" . htmlentities($fichierSec) . "</small></a>";
        $sectionDocs .= "</div>";
    }

    if (!empty($sectionDocs)) {
        $contenuHtml .= "<hr><h2><i class='glyphicon glyphicon-file'></i> Documents attachés</h2><section class='row'>$sectionDocs</section>";
    }
}


// 3. MÉDIAS (Audio & Vidéo)
$sectionMedias = "";
if (!empty($documents)) {
    foreach ($documents as $ligne) {
        $extension = strtolower(substr(strrchr($ligne[1], '.'), 1));
        $fichierSec = substr($ligne[1], 0, strrpos($ligne[1], '.'));
        $urlDoc = lienUrlAffichageDocument($ligne[0]);

        if (in_array($extension, ['mp3', 'm4a', 'aac'])) {
            $typeAudio = ($extension === "aac") ? "audio/mpeg" : "audio/mp3";
            $sectionMedias .= "<div class='col-xs-12 col-sm-6 col-md-4 text-center' style='margin-bottom: 20px;'>";
            $sectionMedias .= "  <div class='well well-sm'><strong>" . htmlentities($fichierSec) . "</strong><br>";
            $sectionMedias .= "  <audio controls style='width:100%; margin-top:10px;'><source src='$urlDoc' type='$typeAudio'></audio></div>";
            $sectionMedias .= "</div>";
        } elseif ($extension === "mp4") {
            $sectionMedias .= "<div class='col-xs-12 col-sm-6 col-md-4 text-center' style='margin-bottom: 20px;'>";
            $sectionMedias .= "  <div class='well well-sm'><strong>" . htmlentities($fichierSec) . "</strong><br>";
            $sectionMedias .= "  <video width='100%' controls style='margin-top:10px;'><source src='$urlDoc' type='video/ogg'></video></div>";
            $sectionMedias .= "</div>";
        }
    }
}
if (!empty($sectionMedias)) {
    $contenuHtml .= "<hr><section class='row'>$sectionMedias</section>";
}


// 4. STRUMS
$contenuHtml .= renderStrumsSection($idChanson, $_chanson->getTempo(), $_chanson->getPulsation() === "ternaire");


// 5. LIENS ASSOCIÉS
$liens = $_chanson->chercheLiensChanson();
if (!empty($liens) && $liens->num_rows > 0) {
    $contenuHtml .= "<hr><h2><i class='glyphicon glyphicon-link'></i> Liens associés</h2><section class='row'>";
    while ($lien = $liens->fetch_row()) {
        $contenuHtml .= "<div class='col-sm-6'>" . afficheLien($lien) . "</div>";
        ajouteUnHit($lien[0]);
    }
    $contenuHtml .= FIN_SECTION;
}


// 6. SONGBOOKS ASSOCIÉS
$songbooks = $_chanson->chercheSongbooksDocuments();
if (!empty($songbooks) && $songbooks->num_rows > 0) {
    $contenuHtml .= "<hr><h2><i class='glyphicon glyphicon-book'></i> Songbooks associés</h2><section class='row'>";
    while ($songbook = $songbooks->fetch_row()) {
        $idSb = $songbook[0];
        $nomSb = $songbook[1];
        $imgSb = imageSongbook($idSb);
        $urlImgSb = "../../data/songbooks/$idSb/$imgSb";
        $contenuHtml .= "<div class='col-xs-4 col-sm-3 col-md-2 text-center'>";
        $contenuHtml .= "  <a href='../songbook/songbook_voir.php?id=$idSb' class='thumbnail'>";
        $contenuHtml .= "    <img src='$urlImgSb' style='height:120px;' alt='Couverture Songbook'><div class='caption'><small>$nomSb</small></div>";
        $contenuHtml .= "  </a>";
        $contenuHtml .= "</div>";
    }
    $contenuHtml .= FIN_SECTION;
}

$contenuHtml .= FIN_DIV . "</div>"; // Fin starter-template et container

// --- MODALES (PLACÉES À LA FIN POUR ÉVITER LES CONFLITS DE Z-INDEX) ---
$contenuHtml .= "
<div id='lightboxModal' class='modal fade' tabindex='-1' role='dialog' aria-hidden='true'>
  <div class='modal-dialog modal-lg'>
    <div class='modal-content text-center' style='background:transparent; border:none; box-shadow:none;'>
        <button type='button' class='close' data-dismiss='modal' style='color:white; font-size:40px; opacity:1;'>&times;</button>
        <img id='lightboxImg' src='' alt='' class='img-responsive center-block' style='max-height: 90vh; border: 5px solid white;'>
    </div>
  </div>
</div>

<div id='qrModal' class='modal fade' tabindex='-1' role='dialog' aria-hidden='true'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal'>&times;</button>
        <h4 class='modal-title'>Partager la chanson</h4>
      </div>
      <div class='modal-body text-center'>
        <p>Scannez ce code pour ouvrir la chanson sur votre smartphone :</p>
        <div style='padding: 20px; background: white; display: inline-block; border-radius: 10px;'>
            " . generateQRCode("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 250) . "
        </div>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-default' data-dismiss='modal'>Fermer</button>
      </div>
    </div>
  </div>
</div>
";

// --- SCRIPTS JS ---
$contenuHtml .= "
<script>
    function copyUrlToClipboard() {
        const dummy = document.createElement('input');
        document.body.appendChild(dummy);
        dummy.value = window.location.href;
        dummy.select();
        document.execCommand('copy');
        document.body.removeChild(dummy);
        $('#copy-success').fadeIn().delay(2000).fadeOut();
    }

    function openLightbox(url) {
        $('#lightboxImg').attr('src', url);
        $('#lightboxModal').modal('show');
    }

    function openQRModal() {
        $('#qrModal').modal('show');
    }
</script>
";

$contenuHtml .= envoieFooter();
echo $contenuHtml;

// --- FONCTIONS DE RENDU ---

/**
 * Retourne les informations sur la famille de tempo en fonction des BPM
 */
function getTempoInfo(int $bpm): array
{
    return match(true) {
        $bpm < 60  => ['name' => 'Largo',    'label' => 'Largo'],
        $bpm < 76  => ['name' => 'Adagio',   'label' => 'Adagio'],
        $bpm < 108 => ['name' => 'Andante',  'label' => 'Andante'],
        $bpm < 120 => ['name' => 'Moderato', 'label' => 'Moderato'],
        $bpm < 156 => ['name' => 'Allegro',  'label' => 'Allegro'],
        $bpm < 176 => ['name' => 'Vivace',   'label' => 'Vivace'],
        default    => ['name' => 'Presto',   'label' => 'Presto'],
    };
}

function renderStrumsSection($idChanson, $tempo, $isTernaire) {
    $urlBoiteAstrum = "../../html/boiteAstrum/index.html";
    $imageBoiteAstrum = "../../html/boiteAstrum/medias/img/boiteAstrum.png";
    $html = "";
    $_listeDesLiensStrums = chercheLiensStrumChanson("idChanson", $idChanson);
    if (!empty($_listeDesLiensStrums) && $_listeDesLiensStrums->num_rows > 0) {
        $titre = ($_listeDesLiensStrums->num_rows > 1) ? "Strums" : "Strum";
        $html .= "<hr><h2><i class='glyphicon glyphicon-music'></i> $titre</h2>";
        $monStrum = new Strum();
        while ($lienStrum = $_listeDesLiensStrums->fetch_row()) {
            $monStrum->chercheStrumParChaine($lienStrum[1]);
            $html .= "<div class='well well-sm'>";
            $html .= "  <h3>" . str_replace(" ", "-", $monStrum->getStrum()) . " <small>(" . $monStrum->getLongueur() . " " . $monStrum->renvoieUniteEnFrancais() . ")</small></h3>";
            $urlStrum = "$urlBoiteAstrum?strum=" . str_replace(" ", "-", $monStrum->getStrum()) . "&tempo=$tempo";
            if ($isTernaire) {
                $urlStrum .= "&ternaire=true";
            }
            $html .= "  <a class='btn btn-info btn-sm' title='Boîte à strum' href='$urlStrum'><img src='$imageBoiteAstrum' alt='Strum' height='20'> Boîte à Strum</a>";
            $html .= "  <p style='margin-top:10px;'>" . $monStrum->getDescription() . "</p>";
            $html .= $monStrum->chansonsDuStrum();
            $html .= "</div>";
        }
    }
    return $html;
}
