<?php
include_once "../lib/utilssi.php";
include_once("menu.php");

$fichier = "../../conf/params.ini";
$sortie = "<div class='container' style='padding:20px;'>";

// Vérifie les privilèges
if (!isset($_SESSION['user']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    include "../../html/menuLogin.html";
    exit();
}

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
    "cleGetSongBpm" => "Clé GetSongBpm"
];

$itemsMysql = [
    "monServeur" => "Serveur MySQL",
    "maBase" => "Base MySQL",
    "login" => "Login MySQL",
    "motDePasse" => "Mot de passe MySQL"
];

// Traiter POST
foreach (array_merge(array_keys($itemsGeneral), array_keys($itemsMysql)) as $item) {
    if (isset($_POST[$item])) {
        $groupe = array_key_exists($item, $itemsGeneral) ? "general" : "mysql";
        $ini_objet->m_put($_POST[$item], $item, $groupe);
        $bModif = true;
    }
}

// Upload logo
$logoActuel = $ini_objet->m_valeur('logoSite', 'general');
$uploadDir = "../../images/navigation/";

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
            case 'png': $srcImage = imagecreatefrompng($_FILES['logoSite']['tmp_name']); break;
            case 'webp': $srcImage = imagecreatefromwebp($_FILES['logoSite']['tmp_name']); break;
        }

        if ($srcImage) {
            $dstImage = imagecreatetruecolor(300, 300);
            if ($ext === 'png' || $ext === 'webp') {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
            }
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, 300, 300, $width, $height);

            switch ($ext) {
                case 'jpg':
                case 'jpeg': imagejpeg($dstImage, $destination, 90); break;
                case 'png': imagepng($dstImage, $destination); break;
                case 'webp': imagewebp($dstImage, $destination); break;
            }

            imagedestroy($srcImage);
            imagedestroy($dstImage);

            $ini_objet->m_put($newFilename, 'logoSite', 'general');
            $bModif = true;
            $logoActuel = $newFilename;
            $sortie .= "<div class='alert alert-success'>Logo téléchargé et redimensionné avec succès !</div>";
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
$sortie .= "</div> <!-- fin tab-content -->";

$sortie .= "<button type='submit' class='btn btn-primary mt-3'>Valider</button>";
$sortie .= "</form>";

// Medias
$sortie .= "<h2>Medias</h2>
<a href='../media/listeMedias.php'>Voir les médias</a> |
<a href='paramsEdit.php?resetmedias=125'>Réinitialiser les médias</a>";

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
