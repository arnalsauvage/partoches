<?php

// Si c'est un appel Ajax pour récupérer la date
if (isset($_POST['action']) && $_POST['action'] === 'derniere_date_modif') {
    function trouverDerniereDateModif($dossier, $extensions = ['php', 'js', 'css', 'html']) {
        $derniereDate = 0;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dossier));
        foreach ($it as $fichier) {
            if ($fichier->isFile()) {
                $ext = strtolower(pathinfo($fichier->getFilename(), PATHINFO_EXTENSION));
                if (in_array($ext, $extensions)) {
                    $filemtime = $fichier->getMTime();
                    if ($filemtime > $derniereDate) {
                        $derniereDate = $filemtime;
                    }
                }
            }
        }
        return $derniereDate;
    }
    $repertoire = __DIR__; // changer selon dossier à analyser
    $timestampDerniereModif = trouverDerniereDateModif($repertoire);
    if ($timestampDerniereModif > 0) {
        echo date("d/m/Y H:i:s", $timestampDerniereModif);
    } else {
        echo "Aucun fichier trouvé.";
    }
    exit; // Terminer pour ajax uniquement
}

// Fin ajax

include_once "../lib/utilssi.php";
include_once("menu.php");
include_once "../navigation/Footer.php";

$fichier = "../../conf/params.ini";
$sortie = "<div class='container' style='padding:20px;'>";

// Vérifie les privilèges
if (!isset($_SESSION['user']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    include "../../html/menuLogin.html";
    exit();
}

/// Traitement de la reinitialisation des medias

// Vérifie si le paramètre resetmedias est présent dans l’URL
if (isset($_GET['resetmedias'])) {
    // Récupère la valeur du paramètre (par exemple 125)
    $nombreMedias = (int) $_GET['resetmedias'];

    // Appelle la fonction de réinitialisation
    resetMediasPartoches($nombreMedias);

    echo "<p>Les médias ont été réinitialisés avec succès ($nombreMedias éléments).</p>";
}

// Fonction pour lancer le reset
function resetMediasPartoches(int $nombreMedias) {
    require_once("../media/Media.php");
    $medias = new Media();
    $medias->resetMediasDistribues($nombreMedias);
}

///
/// Fin traitement reinitialisation medias
///



// Charge le fichier ini
$ini_objet = new FichierIni();
$ini_objet->m_load_fichier($fichier);

$bModif = false;

// Items à gérer
$itemsGeneral = [
    "loginParam" => "Login paramétrage",
    "urlSite" => "URL du site",
    "EmailAdmin" => "Email admin",
    "titreSite" => "Titre du site",
    "sousTitreSite" => "Sous-titre du site",
    "mailOubliMotDePasse" => "Email oubli mot de passe",
    "nomEmailOubliMotDePasse" => "Nom email oubli mot de passe",
    "cleGetSongBpm" => "Clé GetSongBpm",
    "GEMINI_API_KEY" => "Clé GEMINI",
    "MAMMOUTH_API_KEY" => "Cle Api Mammouth"
];

$itemsMysql = [
    "monServeur" => "Serveur MySQL",
    "maBase" => "Base MySQL",
    "login" => "Login MySQL",
    "motDePasse" => "Mot de passe MySQL"
];

// Création de l'objet Footer
$footer = new Footer();
$bModif = false;

// Traiter POST
foreach (array_merge(array_keys($itemsGeneral), array_keys($itemsMysql)) as $item) {
    if (isset($_POST[$item])) {
        $groupe = array_key_exists($item, $itemsGeneral) ? "general" : "mysql";
        $ini_objet->m_put($_POST[$item], $item, $groupe);
        $bModif = true;
    }
    // Traitement du pied de page
    if (isset($_POST['footerHtml'])) {
        // Autoriser un petit sous-ensemble de balises HTML
        $footerHtml = strip_tags($_POST['footerHtml'], '<a><br><img><strong><em><p>');
        $ini_objet->m_put($footerHtml, 'footerHtml', 'footer');
        $bModif = true;
    }
}
// Traitement POST pour le footer
if (isset($_POST['footerHtml'])) {
    // Autoriser un petit sous-ensemble de balises HTML
    $footerHtml = strip_tags($_POST['footerHtml'], '<a><br><img><strong><em><p>');
    $footer->setHtml($footerHtml);
    $bModif = true;
}

// Sauvegarde si modifié
if ($bModif) {
    $footer->sauveBdd(); // Enregistre dans l'ini via la classe Footer
}

// Récupération du contenu HTML pour le formulaire
$footerHtml = htmlspecialchars($footer->getHtml());


// Upload logo
$logoActuel = $ini_objet->m_valeur('logoSite', 'general');
$uploadDir = "../../images/navigation/";
$racineDir = "../../";

if (isset($_FILES['logoSite']) && $_FILES['logoSite']['error'] === UPLOAD_ERR_OK) {
    $filename = basename($_FILES['logoSite']['name']);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if (in_array($ext, $allowed)) {
        $newFilename = "logo_site." . $ext;
        $destination = $uploadDir . $newFilename;
        list($width, $height) = getimagesize($_FILES['logoSite']['tmp_name']);
        $srcImage = null;

        switch ($ext) {
            case 'jpg':
            case 'jpeg': $srcImage = imagecreatefromjpeg($_FILES['logoSite']['tmp_name']); break;
            case 'png':  $srcImage = imagecreatefrompng($_FILES['logoSite']['tmp_name']);  break;
            case 'webp': $srcImage = imagecreatefromwebp($_FILES['logoSite']['tmp_name']); break;
        }

        if ($srcImage) {
            // 1) Logo 300x300
            $dstImage = imagecreatetruecolor(300, 300);
            if ($ext === 'png' || $ext === 'webp') {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
            }
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, 300, 300, $width, $height);

            switch ($ext) {
                case 'jpg':
                case 'jpeg': imagejpeg($dstImage, $destination, 90); break;
                case 'png':  imagepng($dstImage, $destination); break;
                case 'webp': imagewebp($dstImage, $destination); break;
            }
            imagedestroy($dstImage);

            // 2) favicon.ico (simple ICO: un seul PNG renommé)
            $faviconSize = 32;
            $faviconImage = imagecreatetruecolor($faviconSize, $faviconSize);
            imagealphablending($faviconImage, false);
            imagesavealpha($faviconImage, true);
            imagecopyresampled($faviconImage, $srcImage, 0, 0, 0, 0, $faviconSize, $faviconSize, $width, $height);

            // On génère un PNG puis on le renomme en .ico (suffisant pour la plupart des navigateurs).
            $faviconPngPath = $uploadDir . "favicon_tmp.png";
            imagepng($faviconImage, $faviconPngPath);
            imagedestroy($faviconImage);

            // Renommage en .ico
            $faviconIcoPath = $racineDir . "favicon.ico";
            @unlink($faviconIcoPath);
            rename($faviconPngPath, $faviconIcoPath);

            // 3) Apple touch icon 120x120
            $apple120Size = 120;
            $apple120 = imagecreatetruecolor($apple120Size, $apple120Size);
            imagealphablending($apple120, false);
            imagesavealpha($apple120, true);
            imagecopyresampled($apple120, $srcImage, 0, 0, 0, 0, $apple120Size, $apple120Size, $width, $height);
            imagepng($apple120, $racineDir . "apple-touch-icon-120x120-precomposed.png");
            imagedestroy($apple120);

            // 4) Apple touch icon 152x152
            $apple152Size = 152;
            $apple152 = imagecreatetruecolor($apple152Size, $apple152Size);
            imagealphablending($apple152, false);
            imagesavealpha($apple152, true);
            imagecopyresampled($apple152, $srcImage, 0, 0, 0, 0, $apple152Size, $apple152Size, $width, $height);
            imagepng($apple152, $racineDir . "apple-touch-icon-152x152-precomposed.png");
            imagedestroy($apple152);

            // Nettoyage source
            imagedestroy($srcImage);

            // MAJ ini / message
            $ini_objet->m_put($newFilename, 'logoSite', 'general');
            $bModif = true;
            $logoActuel = $newFilename;
            $sortie .= "<div class='alert alert-success'>Logo téléchargé, redimensionné et icônes générées avec succès !</div>";
        }
    } else {
        $sortie .= "<div class='alert alert-danger'>Format invalide. Autorisé : jpg, jpeg, png, webp</div>";
    }

}

if ($bModif) $ini_objet->save();

// Helper pour les champs
function champInput(FichierIni $ini, $name, $label, $type, $groupe) {
    $val = htmlspecialchars($ini->m_valeur($name, $groupe));
    return "<div class='mb-3'>
        <label for='$name' class='form-label'>$label</label>
        <input type='$type' class='form-control' name='$name' id='$name' value='$val'>
    </div>";
}

// Formulaire avec onglets
$sortie .= "<form method='post' enctype='multipart/form-data'>";

$sortie .= <<<HTML
<ul class="nav nav-tabs" role="tablist">
  <li class="active"><a href="#general" role="tab" data-toggle="tab">Général</a></li>
  <li><a href="#mysql" role="tab" data-toggle="tab">MySQL</a></li>
  <li><a href="#footer" role="tab" data-toggle="tab">Pied de page</a></li>
</ul>


<div class="tab-content" style="margin-top:20px;">
  <div class="tab-pane fade in active" id="general">
HTML;

// Champs général
foreach ($itemsGeneral as $item => $label) $sortie .= champInput($ini_objet, $item, $label, "text", "general");

// Upload logo
$sortie .= <<<HTML
<div class="mb-3">
    <label for="logoSite" class="form-label">Logo du site (300x300 px, jpg/png/webp)</label>
    <input type="file" id="logoSite" name="logoSite" class="form-control">
</div>
HTML;

if ($logoActuel) {
    $sortie .= "<p>Logo actuel :</p><img src='../../images/navigation/$logoActuel' width='64' height='64'>";
}

$sortie .= "</div> <!-- fin onglet général -->";

$sortie .= "<div class='tab-pane fade' id='mysql'>";
foreach ($itemsMysql as $item => $label) {
    $type = ($item === "motDePasse") ? "password" : "text";
    $sortie .= champInput($ini_objet, $item, $label, $type, "mysql");
}
$sortie .= "</div> <!-- fin onglet mysql -->";

$sortie .= <<<HTML
<div class='tab-pane fade' id="footer">
    <div class="mb-3">
        <label for="footerHtml" class="form-label">Contenu du pied de page</label>
        <textarea class="form-control" name="footerHtml" id="footerHtml" rows="10" style="font-family: monospace;">$footerHtml</textarea>
        <small class="text-muted d-block mt-2">
            Vous pouvez insérer des liens, des images et du texte (balises autorisées : &lt;a&gt;, &lt;br&gt;, &lt;img&gt;, &lt;strong&gt;, &lt;em&gt;).
        </small>
    </div>
</div> <!-- fin onglet footer -->
HTML;

$sortie .= "</div> <!-- fin tab-content -->";

$sortie .= "<button type='submit' class='btn btn-primary mt-3'>Valider</button>";
$sortie .= "</form>";

// Medias
$sortie .= "<h2>Medias</h2>
<a href='../media/listeMedias.php'>Voir les médias</a> |
<a href='paramsEdit.php?resetmedias=125'>Réinitialiser les médias</a>";

// Bouton dernière modif AJAX
$sortie.= <<<HTML
<button id="btnDerniereModif">Voir dernière modif</button>
<div id="resultatDerniereModif" style="margin-top:10px; font-weight:bold;"></div>

<script>
$('#btnDerniereModif').click(function() {
    $('#resultatDerniereModif').text('Chargement...');
    $.post('', {action: 'derniere_date_modif'}, function(data) {
        $('#resultatDerniereModif').text('Dernière date de modification (ajouter 2h): ' + data);
    }).fail(function() {
        $('#resultatDerniereModif').text('Erreur lors de la récupération.');
    });
});
</script>
HTML;


// Script onglets Bootstrap
$sortie .= <<<HTML
<script>
$(document).ready(function(){
    $('.nav-tabs a').click(function (e) {
      e.preventDefault();
      $(this).tab('show');
    });
});
</script>
HTML;


// Footer
$sortie .= envoieFooter();
echo $sortie;
