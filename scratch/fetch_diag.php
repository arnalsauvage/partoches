<?php
$u = 'https://partoches.canopee-musique.fr/php/audit/debug_covers_prod.php';
$ctx = stream_context_create([
    'http' => [
        'ignore_errors' => true,
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
    ]
]);
$content = file_get_contents($u, false, $ctx);
echo "=== PROD DIAGNOSTIC RESULT ===\n";
echo $content;
