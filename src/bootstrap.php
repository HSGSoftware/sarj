<?php
define('ROOT_PATH', dirname(__DIR__));
define('APP_NAME', 'ŞarjNet – Şarj İstasyonu Platformu');
define('APP_VERSION', '1.0.0');

spl_autoload_register(function (string $class) {
    $base = ROOT_PATH . '/src/';
    $file = $base . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
