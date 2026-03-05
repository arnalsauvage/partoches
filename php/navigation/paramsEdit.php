<?php

// === ACTIONS AJAX RÉCENTES ===
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'lecture_log' && isset($_POST['fichier'])) {
        $fichierLog = "../../logs/" . basename($_POST['fichier']);
        if (file_exists($fichierLog)) {
            echo "<pre style='max-height: 400px; overflow: auto; background: #f8f9fa; padding: 10px; border: 1px solid #ddd;'>";
            echo htmlspecialchars(file_get_contents($fichierLog));
            echo "</pre>";
        } else {
            echo "Fichier non trouvé.";
        }
        exit;
    }
    
    if ($_POST['action'] === 'execute_sql' && isset($_POST['sql'])) {
        require_once "../lib/configMysql.php";
        $sql = $_POST['sql'];
        $res = $mysqli->query($sql);
        if (!$res) {
            echo "<div class='alert alert-danger'>Erreur : " . $mysqli->error . "</div>";
        } elseif ($res === true) {
            echo "<div class='alert alert-success'>Requête exécutée avec succès (" . $mysqli->affected_rows . " lignes affectées).</div>";
        } else {
            echo "<div class='table-responsive'><table class='table table-condensed table-striped table-bordered'>";
            echo "<thead><tr class='info'>";
            while ($finfo = $res->fetch_field()) {
                echo "<th>" . $finfo->name . "</th>";
            }
            echo "</tr></thead><tbody>";
            while ($row = $res->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $val) echo "<td>" . htmlspecialchars($val) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table></div>";
        }
        exit;
    }

    if ($_POST['action'] === 'infos_systeme') {
        require_once "../lib/configMysql.php";
        
        // Version PHP
        echo "<h4><i class='glyphicon glyphicon-info-sign'></i> Environnement</h4>";
        echo "<ul>";
        echo "<li><strong>Version PHP :</strong> " . phpversion() . "</li>";
        echo "<li><strong>Version MySQL :</strong> " . $mysqli->server_info . "</li>";
        
        // Taille BDD
        $res = $mysqli->query("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = '$mabase'");
        $row = $res->fetch_assoc();
        echo "<li><strong>Taille Base de données :</strong> " . round($row['size'], 2) . " Mo</li>";

        // Taille Chansons
        function get_dir_size($directory) {
            $size = 0;
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                $size += $file->getSize();
            }
            return $size;
        }
        $dataSize = get_dir_size("../../data/chansons/");
        echo "<li><strong>Taille Dossier Chansons :</strong> " . round($dataSize / 1024 / 1024, 2) . " Mo</li>";
        echo "</ul>";
        exit;
    }

    if ($_POST['action'] === 'derniere_date_modif') {
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
        $repertoire = "../../php"; 
        $timestampDerniereModif = trouverDerniereDateModif($repertoire);
        if ($timestampDerniereModif > 0) {
            echo date("d/m/Y H:i:s", $timestampDerniereModif);
        } else {
            echo "Aucun fichier trouvé.";
        }
        exit;
    }
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
if (isset($_GET['resetmedias'])) {
    $nombreMedias = (int) $_GET['resetmedias'];
    require_once("../media/Media.php");
    $medias = new Media();
    $medias->resetMediasDistribues($nombreMedias);
    $sortie .= "<div class='alert alert-info'>Les médias ont été réinitialisés avec succès ($nombreMedias éléments).</div>";
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

$itemsAdmin = [
    "display_errors" => "Afficher les erreurs PHP (Debug)",
    "log_level" => "Niveau de log (0=off, 1=errors, 2=full)"
];

// Création de l'objet Footer
$footer = new Footer();

// Traiter POST
foreach (array_merge(array_keys($itemsGeneral), array_keys($itemsMysql), array_keys($itemsAdmin)) as $item) {
    if (isset($_POST[$item])) {
        if (array_key_exists($item, $itemsGeneral)) $groupe = "general";
        elseif (array_key_exists($item, $itemsMysql)) $groupe = "mysql";
        else $groupe = "admin";
        
        $ini_objet->m_put($_POST[$item], $item, $groupe);
        $bModif = true;
    }
}

// Traitement du pied de page
if (isset($_POST['footerHtml'])) {
    $footerHtml = strip_tags($_POST['footerHtml'], '<a><br><img><strong><em><p>');
    $ini_objet->m_put($footerHtml, 'footerHtml', 'footer');
    $footer->setHtml($footerHtml);
    $bModif = true;
}

// Sauvegarde si modifié
if ($bModif) {
    $footer->sauveBdd();
    $ini_objet->save();
    $sortie .= "<div class='alert alert-success'>Paramètres mis à jour avec succès !</div>";
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
            
            // Icônes & favicon
            $faviconImage = imagecreatetruecolor(32, 32);
            imagealphablending($faviconImage, false); imagesavealpha($faviconImage, true);
            imagecopyresampled($faviconImage, $srcImage, 0, 0, 0, 0, 32, 32, $width, $height);
            imagepng($faviconImage, $racineDir . "favicon.ico"); // Simple rename
            imagedestroy($faviconImage);

            $apple120 = imagecreatetruecolor(120, 120);
            imagealphablending($apple120, false); imagesavealpha($apple120, true);
            imagecopyresampled($apple120, $srcImage, 0, 0, 0, 0, 120, 120, $width, $height);
            imagepng($apple120, $racineDir . "apple-touch-icon-120x120-precomposed.png");
            imagedestroy($apple120);

            imagedestroy($srcImage);
            $ini_objet->m_put($newFilename, 'logoSite', 'general');
            $ini_objet->save();
            $logoActuel = $newFilename;
            $sortie .= "<div class='alert alert-success'>Logo et icônes mis à jour !</div>";
        }
    }
}

// Helper pour les champs
function champInput(FichierIni $ini, $name, $label, $type, $groupe) {
    $val = htmlspecialchars($ini->m_valeur($name, $groupe));
    if ($type === "checkbox") {
        $checked = ($val == "1") ? "checked" : "";
        return "<div class='checkbox'><label><input type='checkbox' name='$name' value='1' $checked> $label</label></div>";
    }
    return "<div class='form-group'>
        <label for='$name'>$label</label>
        <input type='$type' class='form-control' name='$name' id='$name' value='$val'>
    </div>";
}

// Formulaire
$sortie .= "<form method='post' enctype='multipart/form-data'>";
$sortie .= <<<HTML
<ul class="nav nav-tabs" role="tablist">
  <li class="active"><a href="#general" role="tab" data-toggle="tab">Général</a></li>
  <li><a href="#mysql" role="tab" data-toggle="tab">MySQL</a></li>
  <li><a href="#footer" role="tab" data-toggle="tab">Pied de page</a></li>
  <li><a href="#tabLogs" role="tab" data-toggle="tab">Logs</a></li>
  <li><a href="#tabSql" role="tab" data-toggle="tab">Console SQL</a></li>
  <li><a href="#tabSysteme" role="tab" data-toggle="tab">Système</a></li>
</ul>

<div class="tab-content" style="margin-top:20px; border: 1px solid #ddd; border-top: none; padding: 20px; background: #fff;">
  <div class="tab-pane fade in active" id="general">
HTML;

foreach ($itemsGeneral as $item => $label) $sortie .= champInput($ini_objet, $item, $label, "text", "general");
$sortie .= champInput($ini_objet, "display_errors", "Activer l'affichage des erreurs PHP (display_errors)", "checkbox", "admin");

$sortie .= <<<HTML
    <div class="form-group">
        <label for="logoSite">Logo du site</label>
        <input type="file" id="logoSite" name="logoSite" class="form-control">
        <small class="text-muted">Logo actuel :</small><br>
        <img src='../../images/navigation/$logoActuel' width='48' style='border:1px solid #ccc; padding:2px;'>
    </div>
  </div>

  <div class='tab-pane fade' id='mysql'>
HTML;
foreach ($itemsMysql as $item => $label) {
    $type = ($item === "motDePasse") ? "password" : "text";
    $sortie .= champInput($ini_objet, $item, $label, $type, "mysql");
}
$sortie .= "</div>";

$sortie .= <<<HTML
  <div class='tab-pane fade' id="footer">
    <div class="form-group">
        <label for="footerHtml">HTML du pied de page</label>
        <textarea class="form-control" name="footerHtml" id="footerHtml" rows="8" style="font-family: monospace;">$footerHtml</textarea>
    </div>
  </div>

  <div class='tab-pane fade' id="tabLogs">
    <div class="form-group">
        <label>Choisir un fichier de log :</label>
        <select id="selectLog" class="form-control">
            <option value="">-- Sélectionner --</option>
HTML;

$logs = glob("../../logs/*.{txt,htm,log,html}", GLOB_BRACE);
foreach ($logs as $l) {
    $basename = basename($l);
    $sortie .= "<option value='$basename'>$basename</option>";
}

$sortie .= <<<HTML
        </select>
    </div>
    <div id="resultatLog" style="margin-top:15px;"></div>
    <button type="button" class="btn btn-default btn-sm" onclick="$('#resultatLog').empty();">Vider l'affichage</button>
  </div>

  <div class='tab-pane fade' id="tabSql">
    <div class="alert alert-warning"><strong>Attention :</strong> Les requêtes sont exécutées directement sur la base.</div>
    <div class="form-group">
        <textarea id="sqlQuery" class="form-control" rows="5" placeholder="SELECT * FROM chanson LIMIT 10;"></textarea>
    </div>
    <button type="button" id="btnRunSql" class="btn btn-danger">Exécuter la requête</button>
    <div id="resultatSql" style="margin-top:20px;"></div>
  </div>

  <div class='tab-pane fade' id="tabSysteme">
    <div id="resultatSysteme">Chargement...</div>
    <hr>
    <button type="button" id="btnRefreshSysteme" class="btn btn-info btn-sm">Rafraîchir les infos</button>
  </div>
</div>

<div style="margin-top:20px;">
    <button type='submit' class='btn btn-primary btn-lg'>Enregistrer les paramètres</button>
    <button type="button" id="btnDerniereModif" class="btn btn-link">Voir dernière modif php/</button>
    <span id="resultatDerniereModif" class="text-muted small"></span>
</div>
</form>

<hr>
<h3>Outils</h3>
<a href='../media/listeMedias.php' class='btn btn-default'>Voir les médias</a>
<a href='paramsEdit.php?resetmedias=125' class='btn btn-warning' onclick='return confirm("Réinitialiser ?");'>Réinitialiser les médias</a>

<script>
$(document).ready(function(){
    // Gestion des onglets
    $('.nav-tabs a').click(function (e) { e.preventDefault(); $(this).tab('show'); });
    
    // Chargement auto du système quand on clique sur l'onglet
    $('a[href="#tabSysteme"]').on('shown.bs.tab', function (e) { loadSysteme(); });

    // AJAX Logs
    $('#selectLog').change(function() {
        var f = $(this).val();
        if (!f) return;
        $('#resultatLog').html('Chargement...');
        $.post('', {action: 'lecture_log', fichier: f}, function(data) {
            $('#resultatLog').html(data);
        });
    });

    // AJAX SQL
    $('#btnRunSql').click(function() {
        var query = $('#sqlQuery').val();
        if (!query) return;
        $('#resultatSql').html('Exécution...');
        $.post('', {action: 'execute_sql', sql: query}, function(data) {
            $('#resultatSql').html(data);
        });
    });

    // AJAX Système
    function loadSysteme() {
        $('#resultatSysteme').html('Récupération des données...');
        $.post('', {action: 'infos_systeme'}, function(data) {
            $('#resultatSysteme').html(data);
        });
    }
    $('#btnRefreshSysteme').click(function() { loadSysteme(); });

    // AJAX Date Modif
    $('#btnDerniereModif').click(function() {
        $('#resultatDerniereModif').text('(Chargement...)');
        $.post('', {action: 'derniere_date_modif'}, function(data) {
            $('#resultatDerniereModif').text('Dernière modif : ' + data);
        });
    });
});
</script>
HTML;

$sortie .= "</div> <!-- container -->";
$sortie .= envoieFooter();
echo $sortie;
