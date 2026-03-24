<?php
require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$api = new \App\Api\EpdkApi();

switch ($action) {
    case 'stations':
        $stations = $api->getAllStations();
        echo json_encode($stations);
        break;

    case 'sockets':
        $stationNo = $_GET['no'] ?? '';
        if (!$stationNo) {
            http_response_code(400);
            echo json_encode(['error' => 'station no required']);
            break;
        }
        $sockets = $api->getStationSockets($stationNo);
        echo json_encode($sockets);
        break;

    case 'geocode':
        $address = $_GET['address'] ?? '';
        if (!$address) {
            echo json_encode(['lat' => 39.0, 'lng' => 35.0]);
            break;
        }
        $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($address) . '&countrycodes=tr&limit=1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SarjPlatformu/1.0');
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($res, true);
        if (!empty($data)) {
            echo json_encode(['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']]);
        } else {
            echo json_encode(['lat' => 39.0, 'lng' => 35.0]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'action not found']);
}
