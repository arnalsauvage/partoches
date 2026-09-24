<?php
$ids = [780, 779, 778, 777, 776, 775, 774, 769, 749, 745, 100, 103];

foreach ($ids as $id) {
    $u = "https://partoches.canopee-musique.fr/php/chanson/chanson_voir.php?id=$id";
    $ctx = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
        ]
    ]);
    $content = file_get_contents($u, false, $ctx);
    
    // Find all img tags in the output
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
    echo "Song ID $id:\n";
    if (!empty($matches[1])) {
        foreach ($matches[1] as $src) {
            echo "   - img src: $src\n";
        }
    } else {
        echo "   - No img tags found!\n";
    }
}
