<?php
/**
 * Script de comparaison d'audit Prod vs Local
 * Avec détection de la conversion de fins de lignes CRLF -> LF
 */

$racine = dirname(__DIR__);
$localPublicDir = $racine . '/src/public';

$prodCsvFile = $racine . '/src/data/temp/audit_prod.csv';
if (!file_exists($prodCsvFile)) {
    die("Fichier audit_prod.csv introuvable dans $prodCsvFile\n");
}

$prodData = [];
$handle = fopen($prodCsvFile, 'r');
$header = fgetcsv($handle);

while (($row = fgetcsv($handle)) !== FALSE) {
    if (count($row) < 3) continue;
    $rawPath = trim($row[0]);
    $cleanPath = preg_replace('/^public_html\//', '', str_replace('\\', '/', $rawPath));
    
    $prodData[$cleanPath] = [
        'raw_path' => $rawPath,
        'taille'   => (int)trim($row[1]),
        'hash'     => trim($row[2]),
        'modif'    => trim($row[3] ?? '')
    ];
}
fclose($handle);

$localData = [];
$it = new RecursiveDirectoryIterator($localPublicDir, RecursiveDirectoryIterator::SKIP_DOTS);
foreach (new RecursiveIteratorIterator($it) as $file) {
    if ($file->isDir()) continue;
    
    $filePath = str_replace('\\', '/', $file->getPathname());
    $relativePath = str_replace(str_replace('\\', '/', $localPublicDir) . '/', '', $filePath);

    $content = file_get_contents($filePath);
    $rawHash = md5($content);
    $lfContent = str_replace("\r\n", "\n", $content);
    $lfHash = md5($lfContent);

    $localData[$relativePath] = [
        'full_path' => $filePath,
        'taille'    => $file->getSize(),
        'raw_hash'  => $rawHash,
        'lf_hash'   => $lfHash,
        'lf_size'   => strlen($lfContent),
        'modif'     => date("Y-m-d H:i:s", $file->getMTime())
    ];
}

$devAbsentsEnProd = [];
$prodAbsentsEnDev = [];
$diffVraies = [];
$diffCrlfSeulement = [];
$identiques = [];

foreach ($localData as $relPath => $localInfo) {
    if (!isset($prodData[$relPath])) {
        $devAbsentsEnProd[$relPath] = $localInfo;
    } else {
        $prodInfo = $prodData[$relPath];
        if ($localInfo['raw_hash'] === $prodInfo['hash']) {
            $identiques[$relPath] = $localInfo;
        } elseif ($localInfo['lf_hash'] === $prodInfo['hash']) {
            $diffCrlfSeulement[$relPath] = [
                'local' => $localInfo,
                'prod'  => $prodInfo
            ];
        } else {
            $diffVraies[$relPath] = [
                'local' => $localInfo,
                'prod'  => $prodInfo
            ];
        }
    }
}

foreach ($prodData as $relPath => $prodInfo) {
    if (!isset($localData[$relPath])) {
        $prodAbsentsEnDev[$relPath] = $prodInfo;
    }
}

$output = [];
$output[] = "==================================================";
$output[] = "📊 RAPPORT D'AUDIT COMPARATIF PROD VS DEV";
$output[] = "==================================================\n";
$output[] = "Total Fichiers Prod (CSV) : " . count($prodData);
$output[] = "Total Fichiers Dev (src/public) : " . count($localData);
$output[] = "Fichiers à Hash Raw strictement identical : " . count($identiques);
$output[] = "Fichiers identiques après conversion CRLF->LF (Hostinger) : " . count($diffCrlfSeulement);
$output[] = "Fichiers avec VRAIE différence de contenu : " . count($diffVraies);
$output[] = "Fichiers Dev ABSENTS en Prod : " . count($devAbsentsEnProd);
$output[] = "Fichiers Prod ABSENTS en Dev : " . count($prodAbsentsEnDev) . "\n";

$output[] = "==================================================";
$output[] = "1. FICHIERS DE LA DEV ABSENTS EN PROD (" . count($devAbsentsEnProd) . ")";
$output[] = "==================================================";
foreach ($devAbsentsEnProd as $path => $info) {
    $output[] = " - $path (Taille: {$info['taille']} octets)";
}
if (empty($devAbsentsEnProd)) $output[] = " (Aucun)";

$output[] = "\n==================================================";
$output[] = "2. FICHIERS DE LA PROD ABSENTS EN DEV (" . count($prodAbsentsEnDev) . ")";
$output[] = "==================================================";
foreach ($prodAbsentsEnDev as $path => $info) {
    $output[] = " - $path (Taille: {$info['taille']} octets, Hash: {$info['hash']})";
}
if (empty($prodAbsentsEnDev)) $output[] = " (Aucun)";

$output[] = "\n==================================================";
$output[] = "3. FICHIERS AVEC DIFFERENCE DE CONTENU REELLE (" . count($diffVraies) . ")";
$output[] = "==================================================";
foreach ($diffVraies as $path => $diff) {
    $output[] = " - $path";
    $output[] = "     Dev  : Hash {$diff['local']['raw_hash']} (LF Hash {$diff['local']['lf_hash']}) | Taille {$diff['local']['taille']} B | Modif {$diff['local']['modif']}";
    $output[] = "     Prod : Hash {$diff['prod']['hash']} | Taille {$diff['prod']['taille']} B | Modif {$diff['prod']['modif']}";
}
if (empty($diffVraies)) $output[] = " (Aucune différence réelle - 100% des fichiers concordent au niveau du code !)";

$output[] = "\n==================================================";
$output[] = "4. FICHIERS AVEC DIFFERENCE CRLF/LF SEULEMENT (" . count($diffCrlfSeulement) . ")";
$output[] = "==================================================";
foreach ($diffCrlfSeulement as $path => $diff) {
    $output[] = " - $path (Dev CRLF {$diff['local']['taille']}B => Prod LF {$diff['prod']['taille']}B)";
}

$finalText = implode("\n", $output);
file_put_contents($racine . '/src/data/temp/rapport_audit_result.txt', $finalText);
echo $finalText;
