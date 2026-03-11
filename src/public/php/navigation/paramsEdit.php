<?php

// === ACTIONS AJAX RÉCENTES ===
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'lecture_log' && isset($_POST['fichier'])) {
        $fichierLog = __DIR__ . "/../../../data/logs/" . basename($_POST['fichier']);
        if (file_exists($fichierLog)) {
            $ext = strtolower(pathinfo($fichierLog, PATHINFO_EXTENSION));
            $contenu = file_get_contents($fichierLog);
            
            // Correction encodage si besoin (si le log est en ISO)
            if (!mb_check_encoding($contenu, 'UTF-8')) {
                $contenu = mb_convert_encoding($contenu, 'UTF-8', 'ISO-8859-1');
            }

            if (in_array($ext, ['htm', 'html'])) {
                // Rendu HTML direct pour les fichiers .htm ou .html
                echo "<div class='render-html-dj'>$contenu</div>";
            } else {
                // Texte brut pour les autres
                echo "<pre style='max-height: 500px; overflow: auto; background: #f8f9fa; padding: 10px; border: 1px solid #ddd; font-size:12px;'>";
                echo htmlspecialchars($contenu);
                echo "</pre>";
            }
        } else {
            echo "Fichier non trouvé.";
        }
        exit;
    }
    
    if ($_POST['action'] === 'execute_sql' && isset($_POST['sql'])) {
        require_once __DIR__ . "/../lib/configMysql.php";
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
        require_once __DIR__ . "/../lib/configMysql.php";
        echo "<h4><i class='glyphicon glyphicon-info-sign'></i> Environnement</h4><ul>";
        echo "<li><strong>Version PHP :</strong> " . phpversion() . "</li>";
        echo "<li><strong>Version MySQL :</strong> " . $mysqli->server_info . "</li>";
        $res = $mysqli->query("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = '$mabase'");
        $row = $res->fetch_assoc();
        echo "<li><strong>Taille BDD :</strong> " . round($row['size'], 2) . " Mo</li>";
        echo "</ul>";
        exit;
    }

    if ($_POST['action'] === 'derniere_date_modif') {
        function trouverDerniereDateModif($dossier) {
            $derniereDate = 0;
            if (!is_dir($dossier)) return 0;
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dossier));
            foreach ($it as $fichier) {
                if ($fichier->isFile() && $fichier->getMTime() > $derniereDate) {
                    $derniereDate = $fichier->getMTime();
                }
            }
            return $derniereDate;
        }
        echo date("d/m/Y H:i:s", trouverDerniereDateModif(__DIR__ . "/../../php"));
        exit;
    }
}

require_once dirname(__DIR__) . "/lib/utilssi.php";
$headHtml = envoieHead("Paramétrage du site", "../../css/index.css");
echo $headHtml;
$pasDeMenu = true;
require_once "menu.php";
echo $MENU_HTML;

$fichier = __DIR__ . "/../../../data/conf/params.ini";
$alerts = "";

if (!isset($_SESSION['user']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    include __DIR__ . "/../../html/composants/menuLogin.html";
    exit();
}

$ini_objet = new FichierIni();
$ini_objet->m_load_fichier($fichier);
$bModif = false;

$itemsGeneral = [
    "loginParam" => "Login paramétrage", "urlSite" => "URL du site", "EmailAdmin" => "Email admin",
    "titreSite" => "Titre du site", "sousTitreSite" => "Sous-titre du site",
    "mailOubliMotDePasse" => "Email d'envoi", "nomEmailOubliMotDePasse" => "Nom d'affichage",
    "largeurMaxImageChanson" => "Largeur Max (px)", "hauteurMaxImageChanson" => "Hauteur Max (px)",
    "cleGetSongBpm" => "Clé GetSongBpm", "GEMINI_API_KEY" => "Clé Gemini", "MAMMOUTH_API_KEY" => "Clé Mammouth"
];
$itemsMysql = ["monServeur" => "Serveur MySQL", "maBase" => "Base MySQL", "login" => "Login MySQL", "motDePasse" => "Mot de passe MySQL"];
$itemsAdmin = ["display_errors" => "Afficher les erreurs PHP", "log_level" => "Niveau de log"];

$footer = new Footer();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    foreach (array_merge(array_keys($itemsGeneral), array_keys($itemsMysql), array_keys($itemsAdmin)) as $item) {
        if (isset($_POST[$item])) {
            $groupe = array_key_exists($item, $itemsGeneral) ? "general" : (array_key_exists($item, $itemsMysql) ? "mysql" : "admin");
            // Nettoyage UTF8 avant sauvegarde
            $valeur = $_POST[$item];
            $ini_objet->m_put($valeur, $item, $groupe);
            $bModif = true;
        }
    }
    if (isset($_POST['footerHtml'])) {
        $footerHtml = strip_tags($_POST['footerHtml'], '<a><br><img><strong><em><p>');
        $ini_objet->m_put($footerHtml, 'footerHtml', 'footer');
        $footer->setHtml($footerHtml);
        $bModif = true;
    }
    if ($bModif) { $footer->sauveBdd(); $ini_objet->save(); $alerts .= "<div class='alert alert-success'>Enregistré !</div>"; }
}

$footerHtml = htmlspecialchars($footer->getHtml());
$logoActuel = $ini_objet->m_valeur('logoSite', 'general');

function champInput($ini, $name, $label, $type, $groupe) {
    $val = $ini->m_valeur($name, $groupe) ?? '';
    
    // Correction encodage pour l'affichage (si le INI est en ISO)
    if (!empty($val) && !mb_check_encoding($val, 'UTF-8')) {
        $val = mb_convert_encoding($val, 'UTF-8', 'ISO-8859-1');
    }
    // Nettoyage final des entités pour éviter le double encodage
    $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');

    $html = "<div class='form-group-django'>";
    $html .= "<label class='label-django'>$label</label>";
    if ($type === "checkbox") {
        $checked = ($val == "1") ? "checked" : "";
        $html = "<div class='checkbox-django'><label><input type='checkbox' name='$name' value='1' $checked> $label</label></div>";
    } else {
        $isPwd = (str_contains(strtolower($name), 'key') || str_contains(strtolower($name), 'passe') || $name === 'cleGetSongBpm');
        $inputType = $isPwd ? "password" : $type;
        $html .= "<div class='input-group-django'>";
        $html .= "<input type='$inputType' class='input-django' name='$name' id='$name' value='$val'>";
        if ($isPwd) $html .= "<button type='button' class='btn-toggle-pwd' data-target='$name'><i class='glyphicon glyphicon-eye-open'></i></button>";
        $html .= "</div>";
    }
    $html .= "</div>";
    return $html;
}

echo "<div id='django-config-page' class='container'>";
echo $alerts;
?>

<div class="header-django">
    <h1><i class="glyphicon glyphicon-cog"></i> Paramétrage</h1>
    <div class="btn-group-django">
        <a href='../todo/todo_admin.php' class='btn-dj btn-dj-primary'><i class="glyphicon glyphicon-list-alt"></i> Roadbook</a>
        <a href='../audit/imagesCheck.php' class='btn-dj btn-dj-info'><i class="glyphicon glyphicon-eye-open"></i> Images</a>
        <a href='../media/listeMedias.php' class='btn-dj btn-dj-default'><i class="glyphicon glyphicon-picture"></i> Médias</a>
    </div>
</div>

<form method='post' enctype='multipart/form-data' class='form-dj-reset'>
    <ul class="tabs-django">
        <li class="tab-dj active" data-target="dj-gen"><i class="glyphicon glyphicon-home"></i> Général</li>
        <li class="tab-dj" data-target="dj-sql-db"><i class="glyphicon glyphicon-hdd"></i> Base</li>
        <li class="tab-dj" data-target="dj-foot"><i class="glyphicon glyphicon-edit"></i> Footer</li>
        <li class="tab-dj" data-target="dj-logs"><i class="glyphicon glyphicon-list"></i> Logs</li>
        <li class="tab-dj" data-target="dj-console"><i class="glyphicon glyphicon-console"></i> SQL</li>
    </ul>

    <div class="content-django">
        <div id="dj-gen" class="pane-dj active">
            <div class="row">
                <div class="col-md-6">
                    <div class="section-dj">
                        <div class="section-dj-title">Identité</div>
                        <div style="display:flex; align-items:center; margin-bottom:15px;">
                            <img src='../../images/navigation/<?php echo $logoActuel; ?>' class="img-thumbnail" style="height:60px; margin-right:15px;">
                            <input type="file" name="logoSite" class="input-django">
                        </div>
                        <?php echo champInput($ini_objet, "titreSite", "Nom du site", "text", "general"); ?>
                        <?php echo champInput($ini_objet, "sousTitreSite", "Slogan", "text", "general"); ?>
                        <?php echo champInput($ini_objet, "urlSite", "URL racine", "url", "general"); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="section-dj">
                        <div class="section-dj-title">Emails & API</div>
                        <?php echo champInput($ini_objet, "EmailAdmin", "Email admin", "email", "general"); ?>
                        <?php echo champInput($ini_objet, "cleGetSongBpm", "Clé GetSongBpm", "text", "general"); ?>
                        <?php echo champInput($ini_objet, "GEMINI_API_KEY", "Clé Gemini", "text", "general"); ?>
                        <?php echo champInput($ini_objet, "MAMMOUTH_API_KEY", "Clé Mammouth", "text", "general"); ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="dj-sql-db" class="pane-dj">
            <div class="section-dj" style="max-width:500px; margin:0 auto;">
                <div class="section-dj-title">Connexion MySQL</div>
                <?php foreach ($itemsMysql as $item => $label) echo champInput($ini_objet, $item, $label, ($item === "motDePasse" ? "password" : "text"), "mysql"); ?>
            </div>
        </div>

        <div id="dj-foot" class="pane-dj">
            <div class="section-dj">
                <div class="section-dj-title">HTML du Footer</div>
                <textarea name="footerHtml" rows="12" class="textarea-footer-dj"><?php echo $footerHtml; ?></textarea>
            </div>
        </div>

        <div id="dj-logs" class="pane-dj">
            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group">
                        <?php 
                        $logPath = __DIR__ . "/../../../data/logs/*.{txt,htm,log,html}";
                        foreach (glob($logPath, GLOB_BRACE) as $l) {
                            $b = basename($l); echo "<a href='#' class='list-group-item item-log-dj' data-file='$b'>$b</a>";
                        } ?>
                    </div>
                </div>
                <div class="col-sm-8"><div id="log-view-dj" class="well-log-dj">Sélectionnez un log...</div></div>
            </div>
        </div>

        <div id="dj-console" class="pane-dj">
            <div class="section-dj">
                <textarea id="sqlQueryDj" class="input-django" rows="5" placeholder="SELECT * FROM chanson LIMIT 10;" style="width:100%; font-family:monospace;"></textarea>
                <div style="text-align:right; margin-top:10px;"><button type="button" id="btnRunSqlDj" class="btn-dj btn-dj-info">Exécuter</button></div>
                <div id="sqlResDj" style="margin-top:20px;"></div>
            </div>
        </div>
    </div>

    <div class="footer-save-dj">
        <button type="submit" class="btn-dj btn-dj-primary btn-lg" style="width:100%;">ENREGISTRER TOUT</button>
    </div>
</form>

<style>
#django-config-page { width: 100% !important; max-width: 1200px !important; margin: 20px auto !important; position: static !important; }
.form-dj-reset { background: #f9f9f9 !important; border: 1px solid #ddd !important; width: 100% !important; position: static !important; margin: 0 !important; padding: 20px !important; box-sizing: border-box !important; }
.header-django { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.tabs-django { display: flex; list-style: none; padding: 0; margin: 0; border-bottom: 2px solid #D2B48C; flex-wrap: wrap; }
.tab-dj { padding: 10px 20px; cursor: pointer; border: 1px solid transparent; border-bottom: none; margin-bottom: -2px; font-weight: bold; color: #8B4513; }
.tab-dj.active { background: #fff; border-color: #D2B48C; border-top: 3px solid #8B4513; color: #2b1d1a; }
.content-django { background: #fff; border: 1px solid #D2B48C; border-top: none; padding: 20px; min-height: 400px; }
.pane-dj { display: none; }
.pane-dj.active { display: block !important; }
.section-dj { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius: 4px; }
.section-dj-title { font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #eee; color: #8B4513; }
.form-group-django { margin-bottom: 15px; }
.label-django { display: block !important; width: auto !important; float: none !important; margin-bottom: 5px !important; font-weight: bold !important; color: #333 !important; }
.input-django { display: block !important; width: 100% !important; padding: 8px !important; border: 1px solid #ccc !important; border-radius: 4px !important; box-sizing: border-box !important; background: #fff !important; color: #333 !important; }
.input-group-django { position: relative; display: flex; }
.btn-toggle-pwd { position: absolute; right: 5px; top: 5px; border: none; background: transparent; cursor: pointer; }
.btn-dj { padding: 8px 15px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer; text-decoration: none; display: inline-block; margin-bottom: 5px; }
.btn-dj-primary { background: #8B4513; color: #fff; }
.btn-dj-info { background: #D2B48C; color: #2b1d1a; }
.footer-save-dj { margin-top: 20px; padding: 20px; background: #F5F5DC; border: 1px solid #D2B48C; border-radius: 8px; }
.spin { animation: spin 2s infinite linear; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }

/* Mobile Adaptations */
@media (max-width: 768px) {
    .header-django { flex-direction: column; align-items: stretch; text-align: center; }
    .header-django h1 { margin-bottom: 15px; font-size: 24px; }
    .btn-group-django { display: flex; flex-direction: column; gap: 5px; }
    .btn-dj { width: 100%; margin: 0; text-align: left; }
    .tabs-django { justify-content: space-around; }
    .tab-dj { flex-grow: 1; text-align: center; padding: 10px 5px; font-size: 12px; }
    .section-dj { padding: 10px; }
    .input-django { font-size: 16px; }
}

/* Fix styles Footer Editor */
.textarea-footer-dj {
    display: block !important;
    width: 100% !important;
    height: 350px !important;
    padding: 15px !important;
    font-family: 'Courier New', Courier, monospace !important;
    font-size: 14px !important;
    background: #2b1d1a !important;
    color: #f5f5dc !important;
    border: 1px solid #1a1210 !important;
    border-radius: 8px !important;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.3) !important;
    box-sizing: border-box !important;
}

/* Fix styles Logs View */
.well-log-dj {
    background: #fff !important;
    min-height: 500px !important;
    max-height: 800px !important;
    overflow: auto !important;
    padding: 15px !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
}
.render-html-dj {
    background: white;
    padding: 10px;
}
</style>

<script>
$(document).ready(function(){
    $('.tab-dj').on('click', function(){
        var target = $(this).data('target');
        $('.tab-dj').removeClass('active');
        $(this).addClass('active');
        $('.pane-dj').removeClass('active');
        $('#' + target).addClass('active');
    });

    $('.btn-toggle-pwd').click(function(){
        var i = $('#' + $(this).data('target'));
        i.attr('type', i.attr('type') === 'password' ? 'text' : 'password');
        $(this).find('i').toggleClass('glyphicon-eye-open glyphicon-eye-close');
    });

    $('.item-log-dj').click(function(e){
        e.preventDefault();
        var f = $(this).data('file');
        $('.item-log-dj').removeClass('active');
        $(this).addClass('active');
        $('#log-view-dj').html('<div class="text-center" style="margin-top:50px;"><i class="glyphicon glyphicon-refresh spin" style="font-size:20px;"></i> Lecture...</div>');
        $.post('', {action: 'lecture_log', fichier: f}, function(d){ $('#log-view-dj').html(d); });
    });

    $('#btnRunSqlDj').click(function(){
        $('#sqlResDj').html('Exécution...');
        $.post('', {action: 'execute_sql', sql: $('#sqlQueryDj').val()}, function(d){ $('#sqlResDj').html(d); });
    });
});
</script>

<?php
echo "</div>"; // #django-config-page
echo envoieFooter();
?>
