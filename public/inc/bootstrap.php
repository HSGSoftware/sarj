<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_NAME', 'ŞarjNet – Şarj İstasyonu Platformu');

/**
 * Apache /sarj/ veya kök / her iki durumda çalışır.
 * SCRIPT_NAME = /sarj/index.php  → base = /sarj
 * SCRIPT_NAME = /index.php       → base = (boş)
 */
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $dir    = dirname($script);
        $base   = rtrim($dir === '.' ? '' : $dir, '/');
    }
    if ($path === '') return $base === '' ? '/' : $base . '/';
    return $base . '/' . ltrim($path, '/');
}

spl_autoload_register(function (string $class) {
    // App\Api\EpdkApi → inc/EpdkApi.php  (namespace kısmını at, sadece sınıf adını al)
    $parts    = explode('\\', $class);
    $basename = end($parts);
    $file     = ROOT_PATH . '/inc/' . $basename . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
