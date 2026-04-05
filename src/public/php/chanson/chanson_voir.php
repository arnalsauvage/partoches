<?php
/** @noinspection PhpUndefinedMethodInspection */

/**
 * PAGE : chanson_voir.php
 * Affiche le détail d'une chanson avec une UX moderne (badges, barre d'outils, lightbox).
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
require_once PHP_DIR . "/navigation/menu.php";
require_once PHP_DIR . "/liens/lienurl_voir.php";

// --- CONSTANTES ET GLOBALES ---
const CHANSON = "chanson";

global $_DOSSIER_CHANSONS, $iconeEdit, $cheminImages, $iconePoubelle;

// --- INITIALISATION ET RÉCUPÉRATION DES DONNÉES ---
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Erreur : Identifiant de chanson invalide.");
}

$idChanson = (int)$_GET['id'];
$_chanson = new Chanson($idChanson);

// --- SÉCURITÉ PUBLICATION ---
if ($_chanson->getPublication() == 0 && !estAdmin()) {
    if (!isset($_SESSION['id']) || $_SESSION['id'] != $_chanson->getIdUser()) {
        header('Location: chanson_liste.php');
        exit();
    }
}

// Augmenter le compteur de vues
augmenteHits(CHANSON, $idChanson);

// Données de base
$datePub = dateMysqlVersTexte($_chanson->getDatePub());
$utilisateur = Utilisateur::chercheUtilisateur($_chanson->getIdUser())[1];
$hits = $_chanson->getHits() + 1;
$monImage = Document::imageTableId(CHANSON, $idChanson);

// Récupération et filtrage des documents
$resultDocs = Document::chercheDocumentsTableId(CHANSON, $idChanson);
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
    
    .modal { z-index: 2050 !important; }
    .modal-backdrop { z-index: 2040 !important; }
</style>

<div class='container'>
    <div class='starter-template'>\n";

// --- BOUTONS NAVIGATION ---
$contenuHtml .= "<div class='row'>";
$contenuHtml .= "  <div class='col-xs-12 text-left' style='margin-bottom: 15px;'>";
$contenuHtml .= "    <a href='chanson_liste.php' class='btn btn-default btn-sm'><i class='glyphicon glyphicon-arrow-left'></i> Retour</a>";
if (!empty($_SESSION['privilege']) && $_SESSION['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    $contenuHtml .= "    <a href='chanson_form.php?id=$idChanson' class='btn btn-primary btn-sm'><i class='glyphicon glyphicon-pencil'></i> Modifier</a>";
}
$contenuHtml .= "  </div>";
$contenuHtml .= "</div>";

// 1. ENTÊTE
$contenuHtml .= "<div class='row'>";
$contenuHtml .= "<section class='col-sm-8'>";
$contenuHtml .= "  <h2 style='margin-top:0;'>" . htmlentities($_chanson->getNom());
$contenuHtml .= "    <button class='btn btn-xs btn-link' onclick='copyUrlToClipboard()' title='Copier le lien'>
                        <i class='glyphicon glyphicon-link'></i>
                     </button>
                     <span id='copy-success'><i class='glyphicon glyphicon-ok'></i> Copié !</span>
                   </h2>";

$urlChercheAn = "chanson_liste.php?filtre=annee&amp;valFiltre=" . $_chanson->getAnnee();
$contenuHtml .= "  <h3 style='margin-top:0;'>" . htmlentities($_chanson->getInterprete()) . "</h3>";

// Badges Techniques
$tonalite = !empty($_chanson->getTonalite()) ? $_chanson->getTonalite() : '?';
$urlTona = "chanson_liste.php?filtre=tonalite&amp;valFiltre=" . urlencode($tonalite);
$tempoBpm = $_chanson->getTempo();
$tempoInfo = getTempoInfo($tempoBpm);
$urlTempo = "chanson_liste.php?filtre=tempo_famille&amp;valFiltre=" . urlencode($tempoInfo['name']);
$annee = $_chanson->getAnnee();
$mesure = $_chanson->getMesure();
$urlMesure = "chanson_liste.php?filtre=mesure&amp;valFiltre=" . urlencode($mesure);
$pulsation = $_chanson->getPulsation();
$urlPulsation = "chanson_liste.php?filtre=pulsation&amp;valFiltre=" . urlencode($pulsation);

$contenuHtml .= "  <div style='margin-top: 15px;'>";
$contenuHtml .= "    <a href='$urlChercheAn' class='label label-success badge-tech'><i class='glyphicon glyphicon-calendar'></i> $annee</a>";
$contenuHtml .= "    <a href='$urlTona' class='label label-primary badge-tech'><i class='glyphicon glyphicon-music'></i> $tonalite</a>";
$contenuHtml .= "    <a href='$urlTempo' class='label label-info badge-tech'><i class='glyphicon glyphicon-time'></i> $tempoBpm <span class='tempo-label'>{$tempoInfo['label']}</span></a>";
$contenuHtml .= "    <a href='$urlMesure' class='label label-default badge-tech'><i class='glyphicon glyphicon-equalizer'></i> $mesure</a>";
$pulsationIcon = ($pulsation === "ternaire") ? "glyphicon-refresh" : "glyphicon-option-vertical";
$contenuHtml .= "    <a href='$urlPulsation' class='label label-warning badge-tech'><i class='glyphicon $pulsationIcon'></i> $pulsation</a>";
$contenuHtml .= "  </div>";

// Barre d'actions
$urlYoutube = "https://www.youtube.com/results?search_query=" . urlencode($_chanson->getNom() . " " . $_chanson->getInterprete());
$rechercheWiki = "https://fr.wikipedia.org/w/index.php?search=" . urlencode($_chanson->getNom() . " " . $_chanson->getInterprete());

$contenuHtml .= "  <div class='action-bar'>";
$contenuHtml .= "    <a href='$urlYoutube' target='_blank' class='action-item' title='Rechercher sur YouTube'><img src='../../images/icones/youtube.png' class='action-icon' alt='YouTube'></a>";
$contenuHtml .= "    <a href='$rechercheWiki' target='_blank' class='action-item' title='Rechercher sur Wikipedia'><img src='../../images/icones/wikipedia.png' class='action-icon' alt='Wikipedia'></a>";
$contenuHtml .= "    <button class='action-item' title='Afficher le QR Code' onclick='openQRModal()'><i class='glyphicon glyphicon-qrcode qr-icon'></i></button>";
$contenuHtml .= "    <div class='action-item' style='flex-grow: 1; justify-content: flex-end; cursor: default;'>";
if (!empty($_SESSION['privilege']) && $_SESSION['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $contenuHtml .= UtilisateurNote::starBarUtilisateur(CHANSON, $idChanson, 5, 25);
}
$contenuHtml .= "       <div style='margin-left:10px;'>" . UtilisateurNote::starBar(CHANSON, $idChanson, 5, 25) . "</div>";
$contenuHtml .= "    </div>";
$contenuHtml .= "  </div>";

$contenuHtml .= "  <p class='text-muted' style='margin-top:10px;'>Publiée le $datePub par $utilisateur &#8226; Vue $hits fois</p>";
$contenuHtml .= "</section>";

// Colonne Droite
$contenuHtml .= "<section class='col-sm-4 text-center'>";
if (!empty($monImage)) {
    $urlOriginale = "../../" . $_DOSSIER_CHANSONS . $idChanson . "/" . $monImage;
    $urlThumbnail = Image::getThumbnailUrl($idChanson . "/" . $monImage, 'sd');
    $contenuHtml .= "  <div class='pochette-container' onclick='openLightbox(\"$urlOriginale\")'><img src='$urlThumbnail' alt='pochette' class='img-thumbnail img-responsive center-block' style='max-width: 300px;'></div>";
}
$contenuHtml .= "</section>";
$contenuHtml .= "</div>"; // Fin Entête row

// 2. DOCUMENTS
if (!empty($documents)) {
    $sectionDocs = "";
    foreach ($documents as $ligne) {
        $extension = strtolower(substr(strrchr($ligne[1], '.'), 1));
        if (in_array($extension, ['mp3', 'm4a', 'aac', 'mp4'])) continue;
        if ($nbImages === 1 && in_array($extension, ['jpg', 'png', 'webp'])) continue;

        $fichierSec = substr($ligne[1], 0, strrpos($ligne[1], '.'));
        $icone = image(ICONES . $extension . ".png", 32, 32, "icone");
        if (!file_exists(ICONES . $extension . ".png")) $icone = image("../images/icones/fichier.png", 32, 32, "icone");

        $sectionDocs .= "<div class='col-xs-6 col-sm-3 col-md-2 centrer' style='margin-bottom: 20px;'>";
        $sectionDocs .= "  <a href='" . Document::lienUrlAffichageDocument($ligne[0]) . "' target='_blank' class='thumbnail' style='text-decoration:none; padding:10px;'>$icone<br><small>" . htmlentities($fichierSec) . "</small></a>";
        $sectionDocs .= "</div>";
    }
    if (!empty($sectionDocs)) $contenuHtml .= "<hr><h2><i class='glyphicon glyphicon-file'></i> Documents attachés</h2><div class='row'>$sectionDocs</div>";
}

// 3. MÉDIAS
$sectionMedias = "";
$hasMedias = false;
// $isGuest = (!isset($_SESSION['user']) || $_SESSION['user'] === 'invite'); // Désactivé temporairement

if (!empty($documents)) {
    foreach ($documents as $ligne) {
        $extension = strtolower(substr(strrchr($ligne[1], '.'), 1));
        $fichierSec = substr($ligne[1], 0, strrpos($ligne[1], '.'));
        $urlDoc = Document::lienUrlAffichageDocument($ligne[0]);

        if (in_array($extension, ['mp3', 'm4a', 'aac', 'mp4'])) {
            $hasMedias = true;
            // if ($isGuest) continue; // On laisse passer tout le monde pour le moment
            
            if (in_array($extension, ['mp3', 'm4a', 'aac'])) {
                $typeAudio = ($extension === "aac") ? "audio/mpeg" : "audio/mp3";
                $sectionMedias .= "<div class='col-xs-12 col-sm-6 col-md-4 text-center' style='margin-bottom: 20px;'>";
                $sectionMedias .= "  <div class='well well-sm'><strong>" . htmlentities($fichierSec) . "</strong><br><audio controls style='width:100%; margin-top:10px;'><source src='$urlDoc' type='$typeAudio'></audio></div>";
                $sectionMedias .= "</div>";
            } elseif ($extension === "mp4") {
                $sectionMedias .= "<div class='col-xs-12 col-sm-6 col-md-4 text-center' style='margin-bottom: 20px;'>";
                $sectionMedias .= "  <div class='well well-sm'><strong>" . htmlentities($fichierSec) . "</strong><br><video width='100%' controls style='margin-top:10px;'><source src='$urlDoc' type='video/mp4'></video></div>";
                $sectionMedias .= "</div>";
            }
        }
    }
}

if ($hasMedias && !empty($sectionMedias)) {
    /* 
    if ($isGuest) {
        $contenuHtml .= "<hr><div class='well text-center' style='background:#F5F5DC; border:2px dashed #D2B48C; padding:30px;'>";
        $contenuHtml .= "<h3><i class='glyphicon glyphicon-lock'></i> Contenu réservé</h3><p>Connectez-vous pour voir les médias !</p></div>";
    } else {
        $contenuHtml .= "<hr><div class='row'>$sectionMedias</div>";
    }
    */
    $contenuHtml .= "<hr><div class='row'>$sectionMedias</div>";
}

// 4. STRUMS
$contenuHtml .= renderStrumsSection($idChanson, $_chanson->getTempo(), $_chanson->getPulsation() === "ternaire");

// 5. LIENS
$liens = $_chanson->chercheLiensChanson();
if (!empty($liens) && $liens->num_rows > 0) {
    $contenuHtml .= "<hr><h2><i class='glyphicon glyphicon-link'></i> Liens associés</h2><div class='row'>";
    while ($lien = $liens->fetch_row()) {
        $contenuHtml .= "<div class='col-sm-6'>" . afficheLien($lien) . "</div>";
        LienUrl::ajouteUnHit($lien[0]);
    }
    $contenuHtml .= "</div>";
}

// 6. SONGBOOKS
$songbooks = $_chanson->chercheSongbooksDocuments();
if (!empty($songbooks) && $songbooks->num_rows > 0) {
    $contenuHtml .= "<hr><h2><i class='glyphicon glyphicon-book'></i> Songbooks associés</h2><div class='row'>";
    while ($songbook = $songbooks->fetch_row()) {
        $idSb = $songbook[0];
        $nomSb = $songbook[1];
        $imgSb = Songbook::imageSongbook($idSb);
        $urlImgVignette = Image::getThumbnailUrl($idSb . "/" . $imgSb, 'sd', 'songbooks');
        $contenuHtml .= "<div class='col-xs-4 col-sm-3 col-md-2 text-center'><a href='../songbook/songbook_voir.php?id=$idSb' class='thumbnail'>";
        $contenuHtml .= "<img src='$urlImgVignette' style='height:120px; object-fit:cover;' alt='SB'><div class='caption'><small>$nomSb</small></div></a></div>";
    }
    $contenuHtml .= "</div>";
}

$contenuHtml .= "</div></div>"; // Fin starter-template et container

// Modales
$contenuHtml .= "
<div id='lightboxModal' class='modal fade' tabindex='-1' role='dialog' aria-hidden='true'>
  <div class='modal-dialog modal-lg'><div class='modal-content text-center' style='background:transparent; border:none; box-shadow:none;'>
    <button type='button' class='close' data-dismiss='modal' style='color:white; font-size:40px; opacity:1;'>&times;</button>
    <img id='lightboxImg' src='' alt='' class='img-responsive center-block' style='max-height:90vh; border:5px solid white;'>
  </div></div>
</div>
<div id='qrModal' class='modal fade' tabindex='-1' role='dialog' aria-hidden='true'>
  <div class='modal-dialog'><div class='modal-content' style='border-radius:15px;'><div class='modal-body text-center'>
    " . generateQRCode("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 200) . "
  </div></div></div>
</div>
<script>
    function copyUrlToClipboard() {
        const dummy = document.createElement('input');
        document.body.appendChild(dummy);
        dummy.value = window.location.href; dummy.select(); document.execCommand('copy');
        document.body.removeChild(dummy); \$('#copy-success').fadeIn().delay(2000).fadeOut();
    }
    function openLightbox(url) { \$('#lightboxImg').attr('src', url); \$('#lightboxModal').modal('show'); }
    function openQRModal() { \$('#qrModal').modal('show'); }
    function voirChansonsStrum(id, nom) { 
        \$('#modalStrumNom').text(nom); 
        \$('#modalChansonsBody').load('../strum/chansons_par_strum_ajax.php?idStrum='+id, function(){ \$('#modalChansonsStrum').modal('show'); });
    }
</script>
";

$headHtml = envoieHead("Partoches - " . $_chanson->getNom(), "../../css/index.css");
echo $headHtml;
echo $MENU_HTML;
echo $contenuHtml;
echo envoieFooter();

function getTempoInfo(int $bpm): array {
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
    $liens = LienStrumChanson::chercheLiensStrumChanson("idChanson", $idChanson);
    
    if (!empty($liens) && $liens->num_rows > 0) {
        $html .= "<hr><h2 style='color: #2b1d1a; font-weight: bold; margin-bottom: 20px;'>";
        $html .= "<i class='glyphicon glyphicon-music'></i> Rythmique</h2>";
        
        while ($l = $liens->fetch_row()) {
            $s = new Strum(); 
            $s->chercheStrumParChaine($l[1]);
            $nomStrum = str_replace(" ", "-", $s->getStrum());
            $ternaireValue = $isTernaire ? "true" : "false";
            $urlStrum = "$urlBoiteAstrum?strum=$nomStrum&amp;tempo=$tempo&amp;ternaire=$ternaireValue";
            
            $html .= "
            <div class='well' style='background: white; border: 1px solid #D2B48C; border-left: 5px solid #8B4513; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;'>
                <!-- Partie Gauche : Badge du Strum -->
                <div style='background: #fdfaf5; border: 2px solid #D2B48C; padding: 10px 20px; border-radius: 12px; text-align: center; min-width: 150px; margin-right: 20px;'>
                    <span style='text-transform: uppercase; font-size: 12px; font-weight: 900; color: #8B4513; letter-spacing: 2px; display: block; border-bottom: 1px solid #D2B48C; margin-bottom: 5px; padding-bottom: 2px;'>Strum</span>
                    <span style='font-family: monospace; font-size: 28px; font-weight: 900; color: #2b1d1a;'>$nomStrum</span>
                </div>

                <!-- Partie Droite : Description et Bouton -->
                <div style='flex: 1; min-width: 250px; text-align: right;'>
                    <p style='font-size: 16px; color: #555; font-style: italic; margin-bottom: 15px;'>" . $s->getDescription() . "</p>
                    <a href='$urlStrum' target='_blank' class='btn btn-info' style='background-color: #2980b9; border: none; padding: 12px 25px; border-radius: 30px; font-size: 18px; font-weight: bold; display: inline-flex; align-items: center; transition: all 0.2s;'>
                        <img src='$imageBoiteAstrum' alt='Strum' height='32' style='margin-right: 15px;'> 
                        écoute-le sur la boîte à strum !
                    </a>
                </div>
            </div>";
        }
    }
    return $html;
}
