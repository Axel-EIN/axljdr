<?php

$token = $_GET['token'] ?? '';
if ($token !== 'SQa4xcWKiTvwkjgDyCLwAoZfOdIQpo0QtcyUTw8eCaIjcoPkw9Llk0y4zW8SYZiy') {
    http_response_code(403);
    exit('Forbidden');
}

function rrmdir($dir) {
    if (!is_dir($dir)) return;

    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            rrmdir($path);
        } else {
            unlink($path);
        }
    }
}

// 📁 chemin vers le cache Symfony
$cacheDir = __DIR__ . '/var/cache';

rrmdir($cacheDir);

echo "Cache Symfony vidé ✅";