<?php
// Directory to start scanning - change this to your root directory
$rootDir = __DIR__; // current directory

$files = [];

// Recursive iterator to traverse directory tree
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $fileInfo) {
    // Check if it is a file and has .mp3 extension (case insensitive)
    if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'mp3') {
        $files[] = [
            'path' => $fileInfo->getPathname(),
            'mtime' => $fileInfo->getMTime(),
        ];
    }
}

// Sort files by modification time descending
usort($files, function($a, $b) {
    return $b['mtime'] <=> $a['mtime'];
});

// Display results
echo "<h1>List of MP3 files sorted by date descending</h1>";
echo "<ul style='font-family: Arial, sans-serif;'>";
foreach ($files as $file) {
    $date = date("Y-m-d H:i:s", $file['mtime']);
    $path = htmlspecialchars($file['path']);
    echo "<li><strong>{$date}</strong> - {$path}</li>";
}
echo "</ul>";
?>

