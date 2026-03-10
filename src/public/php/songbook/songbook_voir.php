<?php
require_once dirname(__DIR__) . "/lib/utilssi.php";
require_once PHP_DIR . "/songbook/Songbook.php";
require_once PHP_DIR . "/document/Document.php";
require_once PHP_DIR . "/chanson/Chanson.php";
require_once PHP_DIR . "/lib/Image.php";

// Palette Canopée
$c_marron_fonce = "#2b1d1a";
$c_marron_clair = "#D2B48C"; 
$c_accent = "#8B4513";
$c_ivoire = "#fcfaf2"; 
$c_orange = "#e67e22";

$id = $_GET['id'] ?? 0;
if ($id == 0) {
    header("Location: songbook_liste.php");
    exit();
}

require_once PHP_DIR . "/navigation/menu.php";

$sb = new Songbook((int)$id);
if ($sb->getId() == 0) {
    die("Songbook introuvable !");
}

// On augmente le compteur de hits
augmenteHits("songbook", $id);

// --- DONNÉES DU SONGBOOK ---
$nom = htmlspecialchars($sb->getNom());
$description = nl2br(htmlspecialchars($sb->getDescription()));
$date = dateMysqlVersTexte($sb->getDate());
$hits = $sb->getHits();
$typeLabel = $sb->getLabelType();
$badgeColor = match($sb->getType()) {
    1 => "#e67e22", // Orange (Anthologie)
    2 => "#d35400", // Orange Foncé (Concert)
    3 => "#27ae60", // Vert (Thème)
    default => "#777"
};

// Image de couverture
$imageFile = imageSongbook($id);
$imgUrl = "";
if ($imageFile) {
    // Utilisation de la vignette moderne via Image.php
    $imgUrl = Image::getThumbnailUrl($id . "/" . $imageFile, 'sd', 'songbooks');
}
$imgTag = $imgUrl ? "<img src='$imgUrl' alt='$nom' class='img-responsive img-thumbnail shadow' style='border-radius: 10px; max-width: 300px;'>" : "<div style='width:100%; height:300px; background:#eee; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#ccc;'><i class='glyphicon glyphicon-book' style='font-size:100px;'></i></div>";

// --- RÉCUPÉRATION DES CHANSONS ---
$lignes = LienDocSongbook::chercheLiensDocSongbook('idSongbook', $id, "ordre");
$chansons = [];
while ($ligne = $lignes->fetch_row()) {
    $ligneDoc = chercheDocument($ligne[1]);
    if ($ligneDoc) {
        $chansonId = $ligneDoc[6];
        if (!isset($chansons[$chansonId])) {
            $chansons[$chansonId] = new Chanson((int)$chansonId);
        }
    }
}

// --- RENDU HTML ---

$html = <<<HTML
<style>
    body { background-color: $c_ivoire !important; }
    .sb-header { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 30px; }
    .sb-title { color: $c_marron_fonce; font-weight: 900; font-size: 36px; margin-top: 0; margin-bottom: 15px; }
    .sb-meta { font-size: 13px; color: #999; margin-bottom: 20px; }
    .sb-meta span { margin-right: 20px; }
    .sb-desc { font-size: 16px; color: #555; line-height: 1.6; margin-bottom: 30px; border-left: 4px solid $c_marron_clair; padding-left: 20px; }
    .chanson-item { background: white; padding: 15px; border-radius: 12px; margin-bottom: 10px; transition: all 0.2s; border: 1px solid transparent; }
    .chanson-item:hover { transform: translateX(10px); border-color: $c_marron_clair; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .chanson-num { font-family: serif; font-style: italic; color: $c_marron_clair; font-size: 20px; margin-right: 20px; width: 30px; display: inline-block; }
    .btn-sb-action { border-radius: 20px; padding: 8px 25px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
</style>

<div class="container" style="padding-top: 30px; padding-bottom: 100px;">
    
    <!-- BARRE DE RETOUR -->
    <div style="margin-bottom: 20px;">
        <a href="songbook_liste.php" class="btn btn-link" style="color: $c_marron_fonce; text-decoration: none; font-weight: bold;">
            <i class="glyphicon glyphicon-menu-left"></i> RETOUR À LA BIBLIOTHÈQUE
        </a>
    </div>

    <!-- EN-TÊTE DU SONGBOOK -->
    <div class="sb-header row">
        <div class="col-md-4">
            $imgTag
        </div>
        <div class="col-md-8">
            <span class="label" style="background-color: $badgeColor; padding: 6px 15px; font-size: 12px; margin-bottom: 15px; display: inline-block; border-radius: 4px;">$typeLabel</span>
            <h1 class="sb-title">$nom</h1>
            
            <div class="sb-meta">
                <span><i class="glyphicon glyphicon-calendar"></i> $date</span>
                <span><i class="glyphicon glyphicon-eye-open"></i> $hits lectures</span>
                <span><i class="glyphicon glyphicon-music"></i> @NB_CHANSONS@ morceaux</span>
            </div>

            <div class="sb-desc">$description</div>

            <div class="sb-actions">
HTML;

// On cherche s'il existe un PDF déjà généré pour ce songbook
$docsPdf = chercheDocumentsTableId("songbook", $id);
$dernierPdf = null;
while ($doc = $docsPdf->fetch_row()) {
    if (strpos(strtolower($doc[1]), '.pdf') !== false) {
        $dernierPdf = $doc; 
    }
}

if ($dernierPdf) {
    $nomFichier = composeNomVersion($dernierPdf[1], $dernierPdf[4]);
    $urlPdf = "../../data/songbooks/$id/" . urlencode($nomFichier);
    $html .= <<<HTML
                <a href="$urlPdf" target="_blank" class="btn btn-sb-action btn-orange-sb shadow" style="background-color: $c_orange; border: none; color: white; text-decoration: none;">
                    <i class="glyphicon glyphicon-download-alt"></i> TÉLÉCHARGER LE PDF
                </a>
HTML;
}

if (aDroits($GLOBALS["PRIVILEGE_EDITEUR"])) {
    $html .= <<<HTML
                <a href="songbook_form.php?id=$id" class="btn btn-sb-action btn-default" style="border: 2px solid $c_marron_fonce; color: $c_marron_fonce; margin-left: 10px;">
                    <i class="glyphicon glyphicon-pencil"></i> ÉDITER
                </a>
HTML;
}

$html .= <<<HTML
            </div>
        </div>
    </div>

    <!-- LISTE DES MORCEAUX -->
    <h2 style="color: $c_marron_fonce; font-weight: bold; margin-bottom: 25px; margin-left: 10px;">
        <i class="glyphicon glyphicon-list" style="color: $c_marron_clair;"></i> SOMMAIRE
    </h2>

    <div class="row">
        <div class="col-xs-12">
HTML;

if (empty($chansons)) {
    $html .= '<div class="alert alert-info" style="border-radius: 15px;">Ce recueil ne contient pas encore de chansons.</div>';
} else {
    $i = 1;
    foreach ($chansons as $c) {
        $c_id = $c->getId();
        $c_nom = htmlspecialchars($c->getNom());
        $c_interprete = htmlspecialchars($c->getInterprete());
        $c_tonalite = $c->getTonalite();
        
        $c_img_name = imageTableId("chanson", $c_id);
        $c_pochette = affichePochette($c_img_name, $c_id, 50, 50);

        $html .= <<<HTML
            <div class="chanson-item d-flex align-items-center" style="display: flex; align-items: center;">
                <span class="chanson-num">$i.</span>
                <div style="width: 50px; height: 50px; margin-right: 20px;">$c_pochette</div>
                <div style="flex-grow: 1;">
                    <h4 style="margin: 0; font-weight: bold;"><a href="../chanson/chanson_voir.php?id=$c_id" style="color: $c_marron_fonce; text-decoration: none;">$c_nom</a></h4>
                    <small class="text-muted">$c_interprete</small>
                </div>
                <div class="text-right">
                    <span class="label" style="background: transparent; color: $c_accent; border: 1px solid $c_marron_clair;">$c_tonalite</span>
                    <a href="../chanson/chanson_voir.php?id=$c_id" class="btn btn-link" style="color: $c_marron_fonce;"><i class="glyphicon glyphicon-chevron-right"></i></a>
                </div>
            </div>
HTML;
        $i++;
    }
}

$nbChansons = count($chansons);
$html = str_replace('@NB_CHANSONS@', (string)$nbChansons, $html);

$html .= <<<HTML
        </div>
    </div>

    <div id="div1" style="margin-top: 30px;"></div>

</div>
HTML;

echo $html;
echo envoieFooter();
