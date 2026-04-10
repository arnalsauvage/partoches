<?php
/**
 * Script de rotation automatique des logs Gemini.
 * Rôle : Archiver les sessions passées dans documentation/gemini-log/
 * et ne garder que la session en cours + le résumé de la précédente.
 */

$logFile = __DIR__ . '/../gemini-log.md';
$archiveBaseDir = __DIR__ . '/../documentation/gemini-log';

if (!file_exists($logFile)) {
    exit("Log file not found.\n");
}

$content = file_get_contents($logFile);

// On ignore tout ce qui est avant la première session "## Session du "
$parts = preg_split('/^## Session du /m', $content, 2);
if (count($parts) < 2) {
    exit("Aucune session trouvée pour la rotation.\n");
}

// On récupère uniquement la partie avec les sessions
$sessionsRaw = "## Session du " . $parts[1];
$sessions = preg_split('/^## Session du /m', $sessionsRaw, -1, PREG_SPLIT_NO_EMPTY);

$todayFr = date_fr(time()); 
$activeSessions = [];
$archivedSessions = [];
$lastSessionToArchive = null;

foreach ($sessions as $session) {
    $lines = explode("\n", trim($session));
    $dateLine = trim(array_shift($lines)); // La première ligne est la date
    $sessionBody = implode("\n", $lines);
    $fullSession = "## Session du " . $dateLine . "\n" . $sessionBody . "\n\n";

    // Si la session n'est pas celle d'aujourd'hui, on archive
    if ($dateLine !== $todayFr && $dateLine !== date('d/m/Y')) {
        $archiveFile = getArchiveFileName($dateLine, $archiveBaseDir);
        
        if (!is_dir($archiveBaseDir)) {
            mkdir($archiveBaseDir, 0777, true);
        }

        file_put_contents($archiveFile, $fullSession, FILE_APPEND);
        
        // On mémorise la dernière session archivée pour en faire un résumé
        if ($lastSessionToArchive === null) {
            $lastSessionToArchive = [
                'date' => $dateLine,
                'content' => $fullSession
            ];
        }
    } else {
        // Session du jour, on la garde
        $activeSessions[] = "## Session du " . $dateLine . "\n" . $sessionBody . "\n";
    }
}

// Construction du nouveau log actif
if ($lastSessionToArchive !== null) {
    $summary = extractSummary($lastSessionToArchive['content']);
    $recap = "### 📖 Résumé de la session précédente (" . $lastSessionToArchive['date'] . ")\n" . $summary . "\n";
    
    $newLogContent = "# 📝 Journal de Bord Gemini (Projet Partoches)\n\n" . $recap . "\n" . implode("\n", $activeSessions);
    file_put_contents($logFile, $newLogContent);
    echo "Rotation effectuée : Sessions passées déplacées vers les archives.\n";
} else {
    echo "Aucune session à archiver (tout est à jour).\n";
}

/**
 * Extrait le résumé d'une session
 */
function extractSummary($content) {
    if (preg_match('/### ✅ Ce qui a été fait(.*?)(?=###|$)/s', $content, $matches)) {
        return trim($matches[1]);
    }
    return "- (Aucun résumé détaillé trouvé)";
}

/**
 * Détermine le nom du fichier d'archive YYYY-MM-gemini-log.md
 */
function getArchiveFileName($dateLine, $dir) {
    // Parsing "10 Avril 2026"
    $parts = explode(' ', trim($dateLine));
    if (count($parts) >= 3) {
        $year = $parts[count($parts) - 1];
        $monthName = mb_strtolower($parts[count($parts) - 2]);
        $months = [
            'janvier'=>'01', 'février'=>'02', 'mars'=>'03', 'avril'=>'04', 'mai'=>'05', 'juin'=>'06',
            'juillet'=>'07', 'août'=>'08', 'septembre'=>'09', 'octobre'=>'10', 'novembre'=>'11', 'décembre'=>'12',
            'april'=>'04'
        ];
        $month = $months[$monthName] ?? date('m');
        // Nettoyage sécurité du nom de fichier
        $year = preg_replace('/[^0-9]/', '', $year);
        $month = preg_replace('/[^0-9]/', '', $month);
        return $dir . "/$year-$month-gemini-log.md";
    }
    return $dir . "/" . date('Y-m') . "-gemini-log.md";
}

function date_fr($time) {
    $mois = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    return date('j', $time) . ' ' . $mois[date('n', $time)] . ' ' . date('Y', $time);
}
