<?php
require_once __DIR__ . '/../src/bootstrap.php';

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'home':
        require __DIR__ . '/../views/home.php';
        break;
    case 'map':
        require __DIR__ . '/../views/map.php';
        break;
    case 'stations':
        require __DIR__ . '/../views/stations.php';
        break;
    case 'station':
        require __DIR__ . '/../views/station_detail.php';
        break;
    case 'payment':
        require __DIR__ . '/../views/payment.php';
        break;
    case 'payment_success':
        require __DIR__ . '/../views/payment_success.php';
        break;
    default:
        require __DIR__ . '/../views/home.php';
}
