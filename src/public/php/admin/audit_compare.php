<?php
/**
 * 🕵️‍♂️ DJANGO DIFF - Comparateur de rapports d'audit
 * Compare deux fichiers CSV générés par audit_fichiers.php
 */

require_once dirname(__DIR__, 3) . "/autoload.php";

if (!estAdmin()) {
    die("🎸 Accès réservé au chef d'orchestre !");
}

$resultats = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_local'], $_FILES['csv_prod'])) {
    $local = parseAuditCsv($_FILES['csv_local']['tmp_name']);
    $prod = parseAuditCsv($_FILES['csv_prod']['tmp_name']);

    if ($local === false || $prod === false) {
        $error = "Erreur lors de la lecture des fichiers CSV. Vérifiez le format.";
    } else {
        $resultats = comparerAudits($local, $prod);
    }
}

/**
 * Lit le CSV et le transforme en tableau indexé par le chemin du fichier
 */
function parseAuditCsv($filename) {
    $data = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ","); // Sauter l'entête
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($row) < 3) continue;
            $data[$row[0]] = [
                'taille' => $row[1],
                'hash'   => $row[2],
                'modif'  => $row[3] ?? ''
            ];
        }
        fclose($handle);
        return $data;
    }
    return false;
}

/**
 * Compare les deux tableaux et déduit les actions
 */
function comparerAudits($local, $prod) {
    $diff = [
        'ajouter'   => [],
        'maj'       => [],
        'supprimer' => []
    ];

    // On parcourt le local pour voir ce qui manque ou a changé en prod
    foreach ($local as $path => $info) {
        if (!isset($prod[$path])) {
            $diff['ajouter'][] = $path;
        } elseif ($prod[$path]['hash'] !== $info['hash']) {
            $diff['maj'][] = $path;
        }
    }

    // On parcourt la prod pour voir ce qui est en trop
    foreach ($prod as $path => $info) {
        if (!isset($local[$path])) {
            $diff['supprimer'][] = $path;
        }
    }

    return $diff;
}

$headHtml = envoieHead("Django Diff", "../../css/styles-communs.css");
$pasDeMenu = true;
require_once PHP_DIR . "/navigation/menu.php";
?>

<div class="container" style="margin-top: 80px;">
    <div class="starter-template">
        <h1>🕵️‍♂️ Django Diff</h1>
        <p class="lead text-muted">Comparez les empreintes pour harmoniser la Prod.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="well" style="background: #fdfaf5; border: 2px solid #D2B48C;">
            <form method="post" enctype="multipart/form-data" class="form-inline">
                <div class="form-group">
                    <label>CSV LOCAL :</label><br>
                    <input type="file" name="csv_local" class="form-control" required>
                </div>
                <div class="form-group" style="margin-left: 20px;">
                    <label>CSV PROD :</label><br>
                    <input type="file" name="csv_prod" class="form-control" required>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-transfer"></i> Comparer les partitions</button>
                    <a href="audit_fichiers.php" class="btn btn-default">Retour Audit</a>
                </div>
            </form>
        </div>

        <?php if ($resultats): ?>
            <div class="row text-left">
                <!-- À AJOUTER -->
                <div class="col-md-4">
                    <div class="panel panel-success">
                        <div class="panel-heading"><h3 class="panel-title">➕ À AJOUTER (<?= count($resultats['ajouter']) ?>)</h3></div>
                        <div class="panel-body" style="max-height: 400px; overflow-y: auto;">
                            <small><code><?= implode("<br>", $resultats['ajouter']) ?></code></small>
                        </div>
                    </div>
                </div>

                <!-- À METTRE À JOU -->
                <div class="col-md-4">
                    <div class="panel panel-warning">
                        <div class="panel-heading"><h3 class="panel-title">🔄 À MAJ (<?= count($resultats['maj']) ?>)</h3></div>
                        <div class="panel-body" style="max-height: 400px; overflow-y: auto;">
                            <small><code><?= implode("<br>", $resultats['maj']) ?></code></small>
                        </div>
                    </div>
                </div>

                <!-- À SUPPRIMER -->
                <div class="col-md-4">
                    <div class="panel panel-danger">
                        <div class="panel-heading"><h3 class="panel-title">❌ À SUPPRIMER (<?= count($resultats['supprimer']) ?>)</h3></div>
                        <div class="panel-body" style="max-height: 400px; overflow-y: auto;">
                            <small><code><?= implode("<br>", $resultats['supprimer']) ?></code></small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <strong>🎸 Note de Django :</strong> Copie les fichiers listés dans "À AJOUTER" et "À MAJ" vers ton serveur FTP pour accorder la prod !
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
echo $MENU_HTML;
echo envoieFooter();
