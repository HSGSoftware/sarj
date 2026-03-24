<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Static file serving - return false to let PHP handle it
$staticPath = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($staticPath) && is_file($staticPath)) {
    return false;
}

// Route /api.php
if (strpos($uri, '/api.php') === 0) {
    require __DIR__ . '/public/api.php';
    return;
}

// All other routes -> index.php
require __DIR__ . '/public/index.php';
