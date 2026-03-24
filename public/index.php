<?php
require_once __DIR__ . '/inc/bootstrap.php';

$page = $_GET['page'] ?? 'home';

$views = [
    'home'            => __DIR__ . '/views/home.php',
    'map'             => __DIR__ . '/views/map.php',
    'stations'        => __DIR__ . '/views/stations.php',
    'station'         => __DIR__ . '/views/station_detail.php',
    'payment'         => __DIR__ . '/views/payment.php',
    'payment_success' => __DIR__ . '/views/payment_success.php',
];

$file = $views[$page] ?? $views['home'];
require $file;
