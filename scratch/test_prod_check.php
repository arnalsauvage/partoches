<?php
$u = 'https://partoches.canopee-musique.fr/php/songbook/songbook-portfolio.php';
$ctx = stream_context_create([
    'http' => [
        'ignore_errors' => true,
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
    ]
]);
$content = file_get_contents($u, false, $ctx);
$status = $http_response_header[0] ?? 'UNKNOWN';
echo "STATUS: $status\n";
if (str_contains($content, 'Fatal error') || str_contains($content, '500 Internal')) {
    echo "ERROR BODY: " . substr(strip_tags($content), 0, 300) . "\n";
} else {
    echo "SUCCESS: Page rendered cleanly without Fatal Error!\n";
}
