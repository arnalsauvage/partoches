<?php
/**
 * PAGE : todo_admin.php
 * Interface d'édition de la To-Do List en Markdown pour l'administrateur.
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
require_once "../navigation/menu.php";

// Sécurité : Admin requis
if (!isset($_SESSION['user']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    redirection("../media/listeMedias.php");
    exit();
}

$fileTodo = dirname(__DIR__, 3) . "/data/todo/to-do-list.md";
$message = "";

// Sauvegarde du contenu
if (isset($_POST['content'])) {
    // On s'assure que le dossier existe (Django Style)
    $dirTodo = dirname($fileTodo);
    if (!is_dir($dirTodo)) {
        mkdir($dirTodo, 0777, true);
    }

    if (file_put_contents($fileTodo, $_POST['content']) !== false) {
        $message = "<div class='alert alert-success'>✅ To-Do List sauvegardée avec succès !</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Erreur lors de l'écriture dans le fichier.</div>";
    }
}

// Lecture du contenu actuel
$content = file_exists($fileTodo) ? file_get_contents($fileTodo) : "##bugfix : \n##newfeature : \n##update : ";

$html = envoieHead("Administration - To-Do List", "../../css/index.css");

$html .= <<<HTML
<div class="container" style="margin-top: 30px; margin-bottom: 50px;">
    <h1><i class="glyphicon glyphicon-list-alt"></i> Roadbook de l'Admin</h1>
    <p class="text-muted">Gérez vos tâches et évolutions au format Markdown. Utilisez les tags : <code>##bugfix</code>, <code>##newfeature</code>, <code>##update</code>.</p>
    
    $message

    <form method="post" style="margin-top: 20px; background: none; width: 100%; padding: 0;">
        <div class="form-group">
            <textarea name="content" id="markdownEditor" class="form-control" rows="20" style="font-family: 'Courier New', Courier, monospace; font-size: 14px; padding: 20px; border-radius: 10px; border: 2px solid #D2B48C; background-color: #fcfaf2;">$content</textarea>
        </div>
        <div class="text-right">
            <button type="submit" class="btn btn-lg btn-primary shadow" style="background-color: #8B4513; border: none;">
                <i class="glyphicon glyphicon-save"></i> SAUVEGARDER LE ROADBOOK
            </button>
        </div>
    </form>

    <hr>
    <h3><i class="glyphicon glyphicon-eye-open"></i> Aperçu rapide</h3>
    <div id="preview" style="background: white; padding: 30px; border-radius: 15px; box-shadow: inset 0 2px 10px rgba(0,0,0,0.05); min-height: 100px;">
        <!-- Rendu dynamique via JS -->
    </div>
</div>

<script>
$(function() {
    function updatePreview() {
        let raw = $('#markdownEditor').val();
        // Rendu basique pour les tags Django Style
        let html = raw.replace(/\\n/g, '<br>')
                      .replace(/##bugfix/g, '<span class=\"label label-danger\">BUGFIX</span>')
                      .replace(/##newfeature/g, '<span class=\"label label-success\">NEW FEATURE</span>')
                      .replace(/##update/g, '<span class=\"label label-info\">UPDATE</span>')
                      .replace(/^# (.*)/gm, '<h1>$1</h1>')
                      .replace(/^## (.*)/gm, '<h2>$1</h2>');
        $('#preview').html(html);
    }

    $('#markdownEditor').on('input', updatePreview);
    updatePreview(); // Lancer l'aperçu au chargement
});
</script>
HTML;

$html .= envoieFooter();
echo $html;
