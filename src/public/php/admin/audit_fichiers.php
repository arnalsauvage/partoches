<?php
/**
 * 🕵️‍♂️ DJANGO AUDIT - Comparateur Prod vs Local
 * Génère un rapport d'empreinte (MD5) de tous les fichiers du projet.
 */

require_once dirname(__DIR__, 3) . "/autoload.php";

// Sécurité : Seul l'admin peut lancer l'audit
if (!estAdmin()) {
    die("🎸 Désolé Arnal, seul le patron du club peut lancer l'audit !");
}

// Configuration
$racine = dirname(__DIR__, 3); // C:\Users\medin\PhpstormProjects\partoches
$extensions_autorisees = ['php', 'css', 'js', 'html', 'phtml', 'sql', 'ini'];
$fichiers_a_ignorer = ['.git', '.idea', 'vendor', 'node_modules', 'rendered_html'];

// Si on demande le téléchargement CSV
if (isset($_GET['download'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=audit-partoches-' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Chemin', 'Taille (octets)', 'MD5 Hash', 'Dernière Modif']);

    $it = new RecursiveDirectoryIterator($racine);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->isDir()) continue;
        
        $relative_path = str_replace($racine . DIRECTORY_SEPARATOR, '', $file->getPathname());
        
        // On ignore les dossiers sensibles
        foreach ($fichiers_a_ignorer as $ignore) {
            if (str_contains($relative_path, $ignore)) continue 2;
        }

        $ext = strtolower(pathinfo($relative_path, PATHINFO_EXTENSION));
        if (in_array($ext, $extensions_autorisees)) {
            fputcsv($output, [
                $relative_path,
                $file->getSize(),
                md5_file($file->getPathname()),
                date("Y-m-d H:i:s", $file->getMTime())
            ]);
        }
    }
    fclose($output);
    exit;
}

// Sinon, on affiche une petite interface sympa
$headHtml = envoieHead("Django Audit", "../../css/styles-communs.css");
$pasDeMenu = true;
require_once PHP_DIR . "/navigation/menu.php";

$html = <<<HTML
<div class="container" style="margin-top: 80px;">
    <div class="starter-template">
        <h1>🕵️‍♂️ Django Audit (Comparateur Prod/Local)</h1>
        <p class="lead text-muted">Générez une empreinte digitale de vos fichiers pour vérifier l'intégrité de la prod.</p>
        
        <div class="well" style="background: #fdfaf5; border: 2px solid #D2B48C;">
            <h3>Instructions :</h3>
            <ol style="text-align: left; display: inline-block;">
                <li>Lancez ce script en <strong>Local</strong> et téléchargez le CSV.</li>
                <li>Lancez ce script en <strong>Prod</strong> et téléchargez le CSV.</li>
                <li>Comparez les deux fichiers (Excel ou Diff) pour dénicher les écarts !</li>
            </ol>
            <br><br>
            <a href="?download=1" class="btn btn-lg btn-primary">
                <i class="glyphicon glyphicon-download-alt"></i> Télécharger l'Empreinte (CSV)
            </a>
        </div>
        
        <p><a href="params.php" class="btn btn-default"><i class="glyphicon glyphicon-chevron-left"></i> Retour Admin</a></p>
    </div>
</div>
HTML;

echo $headHtml;
echo $MENU_HTML;
echo $html;
echo envoieFooter();
