<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_NAME', 'ŞarjNet – Şarj İstasyonu Platformu');

/**
 * .htaccess gerektirmeyen URL yardımcısı.
 * Hangi klasörde (/, /sarj/, /myapp/) olursa olsun çalışır.
 */
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $dir    = dirname($script);
        $base   = rtrim($dir === '.' ? '' : $dir, '/');
    }
    if ($path === '') return $base . '/';
    return $base . '/' . ltrim($path, '/');
}

/** Sayfa URL'leri — mod_rewrite yok, doğrudan .php dosyaları */
function page_url(string $page, array $params = []): string
{
    $files = [
        'home'            => 'index.php',
        'map'             => 'map.php',
        'stations'        => 'stations.php',
        'station'         => 'station.php',
        'payment'         => 'payment.php',
        'payment_success' => 'payment_success.php',
    ];
    $file = $files[$page] ?? 'index.php';
    $url  = base_url($file);
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

spl_autoload_register(function (string $class) {
    $parts    = explode('\\', $class);
    $basename = end($parts);
    $file     = ROOT_PATH . '/inc/' . $basename . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
