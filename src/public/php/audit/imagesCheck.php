<?php
/**
 * Outil d'inspection et de nettoyage des images (Django Style)
 * Compare la BDD (table document et chanson.cover) avec le filesystem.
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
require_once __DIR__ . "/../navigation/menu.php";

/**
 * Supprime récursivement un dossier
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                else
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
            }
        }
        return rmdir($dir);
    }
    return false;
}

// Sécurité : Admin requis
if (!isset($_SESSION['user']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    redirection("../media/listeMedias.php");
    exit();
}

$db = $_SESSION['mysql'];
$dossierChansons = __DIR__ . "/../../../data/chansons/";
$extensionsAutorisees = ['jpg'];

// --- 0. RÉCUPÉRATION DES IDS CHANSONS EXISTANTS ---
$chansonsExistantes = [];
$res = $db->query("SELECT id FROM chanson");
while ($row = $res->fetch_assoc()) {
    $chansonsExistantes[] = (int)$row['id'];
}

// --- ACTIONS ---
$message = "";
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $path = $_GET['path'] ?? '';
    $id = (int)($_GET['id'] ?? 0);

    if ($action === 'delete_file' && !empty($path)) {
        $fullPath = realpath($dossierChansons . $path);
        if ($fullPath && strpos($fullPath, realpath($dossierChansons)) === 0 && is_file($fullPath)) {
            unlink($fullPath);
            $message = "<div class='alert alert-success'>Fichier supprimé : $path</div>";
        }
    }

    if ($action === 'delete_dir' && !empty($path)) {
        $dirPart = explode('/', $path)[0];
        $fullDirPath = realpath($dossierChansons . $dirPart);
        // On vérifie que c'est bien un dossier dans data/chansons et que le nom est numérique (ID)
        if ($fullDirPath && is_dir($fullDirPath) && strpos($fullDirPath, realpath($dossierChansons)) === 0 && is_numeric($dirPart)) {
            if (rrmdir($fullDirPath)) {
                $message = "<div class='alert alert-success'>Dossier supprimé : $dirPart</div>";
            } else {
                $message = "<div class='alert alert-danger'>Erreur lors de la suppression du dossier : $dirPart</div>";
            }
        }
    }

    if ($action === 'delete_doc' && $id > 0) {
        Document::supprimeDocument($id);
        $message = "<div class='alert alert-success'>Entrée BDD (Document) supprimée : ID $id</div>";
    }
}

// --- 1. COLLECTE DES DONNÉES FILESYSTEM ---
$fichiersPhysiques = [];
if (is_dir($dossierChansons)) {
    $rootPath = realpath($dossierChansons);
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath));
    foreach ($it as $file) {
        if ($file->isFile()) {
            $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            if (in_array($ext, $extensionsAutorisees)) {
                // Chemin relatif par rapport à data/chansons/
                $filePath = realpath($file->getPathname());
                $relPath = str_replace('\\', '/', substr($filePath, strlen($rootPath) + 1));
                $fichiersPhysiques[$relPath] = [
                    'size' => $file->getSize(),
                    'mtime' => $file->getMTime()
                ];
            }
        }
    }
}

// --- 2. COLLECTE DES DONNÉES BDD (Table Document) ---
$docsBdd = [];
$maRequete = "SELECT id, nom, version, idTable FROM document WHERE nomTable = 'chanson' AND (nom LIKE '%.jpg' OR nom LIKE '%.jpeg')";
$result = $db->query($maRequete);
while ($row = $result->fetch_assoc()) {
    $nomVersion = Document::composeNomVersion($row['nom'], $row['version']);
    $relPath = $row['idTable'] . "/" . $nomVersion;
    $docsBdd[$relPath] = $row;
}

// --- 3. COLLECTE DES DONNÉES BDD (Table Chanson.cover) ---
$coversBdd = [];
$maRequete = "SELECT id, cover FROM chanson WHERE cover LIKE '%.jpg' OR cover LIKE '%.jpeg'";
$result = $db->query($maRequete);
while ($row = $result->fetch_assoc()) {
    $cover = $row['cover'];
    // On nettoie le chemin si besoin
    $relPath = basename($cover); 
    $coversBdd[$row['id'] . "/" . $relPath] = $row;
}

// --- ANALYSE ---
$manquantsBDD = []; // En BDD mais pas sur disque
$orphelinsDisque = []; // Sur disque mais pas en BDD

// Chercher les manquants (BDD -> Disque)
foreach ($docsBdd as $path => $info) {
    if (!isset($fichiersPhysiques[$path])) {
        // Tentative de secours : peut-être que le fichier sur disque n'a pas le -v1 ?
        $pathSansVersion = $info['idTable'] . "/" . $info['nom'];
        if (!isset($fichiersPhysiques[$pathSansVersion])) {
            $manquantsBDD[] = [
                'type' => 'Document',
                'path' => $path,
                'id' => $info['id'],
                'idChanson' => $info['idTable']
            ];
        }
    }
}

// Chercher les orphelins (Disque -> BDD)
foreach ($fichiersPhysiques as $path => $info) {
    $found = false;
    $idChanson = (int)explode('/', $path)[0];
    $songExists = in_array($idChanson, $chansonsExistantes);

    if (isset($docsBdd[$path]) || isset($coversBdd[$path])) {
        $found = true;
    } else {
        // Tentative de secours inverse : est-ce que le nom sur disque correspond au nom en BDD sans le composeNomVersion ?
        foreach ($docsBdd as $bddPath => $bddInfo) {
            $pathSansVersion = $bddInfo['idTable'] . "/" . $bddInfo['nom'];
            if ($path === $pathSansVersion) {
                $found = true;
                break;
            }
        }
    }

    // Un fichier est orphelin s'il n'est pas trouvé en BDD OU si sa chanson n'existe plus
    if (!$found || !$songExists) {
        $orphelinsDisque[] = [
            'path' => $path,
            'size' => round($info['size'] / 1024, 2),
            'date' => date("d/m/Y H:i", $info['mtime']),
            'songDeleted' => !$songExists
        ];
    }
}

// --- RENDU HTML ---
$html = envoieHead("Inspection des images", "../../css/index.css");

$nbPhysiques = count($fichiersPhysiques);
$nbBdd = count($docsBdd) + count($coversBdd);
$countManquants = count($manquantsBDD);
$countOrphelins = count($orphelinsDisque);

$html .= <<<HTML
<div class="container" style="margin-top: 20px;">
    <h1><i class="glyphicon glyphicon-eye-open"></i> Inspection des images Chansons (.jpg)</h1>
    $message

    <div class="row" style="margin-top: 30px;">
        <!-- COLONNE GAUCHE : MANQUANTS SUR DISQUE -->
        <div class="col-md-6">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong>BDD -> DISQUE</strong> ($countManquants manquants)</h3>
                </div>
                <div class="panel-body">
                    <p class="text-muted">Entrées en base de données (.jpg) dont le fichier physique est introuvable.</p>
HTML;

if (empty($manquantsBDD)) {
    $html .= "<div class='alert alert-success'><i class='glyphicon glyphicon-ok'></i> Tout est en ordre !</div>";
} else {
    $html .= "<table class='table table-condensed small'>";
    foreach ($manquantsBDD as $m) {
        $html .= "<tr>
            <td><span class='label label-default'>{$m['type']}</span></td>
            <td><code>{$m['path']}</code></td>
            <td class='text-right'>
                <a href='../chanson/chanson_form.php?id={$m['idChanson']}' class='btn btn-xs btn-default' title='Voir chanson'><i class='glyphicon glyphicon-music'></i></a>
                <a href='?action=delete_doc&id={$m['id']}' class='btn btn-xs btn-danger' onclick='return confirm(\"Supprimer l entrée BDD ?\")' title='Supprimer entrée'><i class='glyphicon glyphicon-remove'></i></a>
            </td>
        </tr>";
    }
    $html .= "</table>";
}

$html .= <<<HTML
                </div>
            </div>
        </div>

        <!-- COLONNE DROITE : ORPHELINS SUR DISQUE -->
        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong>DISQUE -> BDD</strong> ($countOrphelins orphelins)</h3>
                </div>
                <div class="panel-body">
                    <p class="text-muted">Fichiers physiques (.jpg) non référencés dans la table <code>document</code> ou <code>cover</code>.</p>
HTML;

if (empty($orphelinsDisque)) {
    $html .= "<div class='alert alert-success'><i class='glyphicon glyphicon-ok'></i> Aucun fichier inutile !</div>";
} else {
    $html .= "<table class='table table-condensed small'>";
    foreach ($orphelinsDisque as $o) {
        $urlImg = "../../data/chansons/" . $o['path'];
        $dirPart = explode('/', $o['path'])[0];
        $btnDossier = "";
        $labelDeleted = (isset($o['songDeleted']) && $o['songDeleted']) ? " <span class='label label-danger'>Chanson supprimée</span>" : "";

        if (is_numeric($dirPart)) {
            $btnDossier = "<a href='?action=delete_dir&path=" . urlencode($o['path']) . "' class='btn btn-xs btn-warning' onclick='return confirm(\"Supprimer TOUT le dossier $dirPart et son contenu ?\")' title='Supprimer tout le dossier'><i class='glyphicon glyphicon-folder-close'></i></a>";
        }

        $html .= "<tr>
            <td><img src='$urlImg' width='24' height='24' style='object-fit: cover;'></td>
            <td><code>{$o['path']}</code>$labelDeleted<br><small class='text-muted'>{$o['size']} ko - {$o['date']}</small></td>
            <td class='text-right'>
                <div class='btn-group'>
                    <a href='?action=delete_file&path=" . urlencode($o['path']) . "' class='btn btn-xs btn-danger' onclick='return confirm(\"Supprimer le fichier PHYSIQUE ?\")' title='Supprimer fichier'><i class='glyphicon glyphicon-trash'></i></a>
                    $btnDossier
                </div>
            </td>
        </tr>";
    }
    $html .= "</table>";
}

$html .= <<<HTML
                </div>
            </div>
        </div>
    </div>

    <hr>
    <div class="well">
        <h4>Statistiques</h4>
        <ul>
            <li><strong>Fichiers .jpg scannés sur disque :</strong> $nbPhysiques</li>
            <li><strong>Références .jpg en BDD (Documents + Covers) :</strong> $nbBdd</li>
            <li><strong>Total erreurs détectées :</strong> <span class="text-danger"><b>$countManquants manquants</b></span> / <span class="text-warning"><b>$countOrphelins orphelins</b></span></li>
            <li><strong>Dossier racine :</strong> <code>$dossierChansons</code></li>
        </ul>
    </div>
</div>
HTML;

$html .= envoieFooter();
echo $html;
