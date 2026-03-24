<?php
define('ROOT_PATH', dirname(__DIR__));
define('APP_NAME', 'ŞarjNet – Şarj İstasyonu Platformu');
define('APP_VERSION', '1.0.0');

// Apache subdirectory desteği: /sarj, /sarj/, vs.
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $base   = rtrim(dirname($script), '/\\');
        // Eğer public/ içinden serve ediliyorsa dirname boş olur
        if ($base === '.') $base = '';
    }
    return $base . '/' . ltrim($path, '/');
}

spl_autoload_register(function (string $class) {
    $file = ROOT_PATH . '/src/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
