<?php
require_once __DIR__ . '/inc/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$action   = $_GET['action'] ?? '';
$epdkApi  = new \App\Api\EpdkApi();
$astorApi = new \App\Api\AstorApi();

switch ($action) {

    case 'stations':
        // EPDK'dan astor/beefull HARİÇ tüm istasyonlar
        $staticFile = __DIR__ . '/assets/stations_data.json';
        if (file_exists($staticFile)) {
            $allEpdk = json_decode(file_get_contents($staticFile), true) ?: [];
        } else {
            $allEpdk = $epdkApi->getAllStations();
        }
        $epdk = array_values(array_filter($allEpdk, function($s) {
            $brand = strtolower($s['brand'] ?? '');
            return !in_array($brand, ['astor', 'beefull']);
        }));

        // Beefull API'sinden astor+beefull istasyonları
        $beefullStations = $astorApi->getAllStations();

        $combined = array_merge($epdk, $beefullStations);
        echo json_encode($combined, JSON_UNESCAPED_UNICODE);
        break;

    case 'detail':
        $id   = $_GET['id'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d H:i:s');

        // Beefull istasyonu (id = "bf_XXXX")
        if (strpos($id, 'bf_') === 0) {
            $beefullId  = (int)substr($id, 3);
            $allBf      = $astorApi->getAllStations();
            $station    = null;
            foreach ($allBf as $s) {
                if ($s['beefull_id'] === $beefullId) { $station = $s; break; }
            }
            if (!$station) {
                http_response_code(404);
                echo json_encode(['error' => 'not found']);
                break;
            }
            $sockets = $astorApi->getSocketsByBeefullId($beefullId, $station['tenantCode'] ?? 'astor');
            $station['sockets']      = $sockets ?: [];
            $station['socketSource'] = 'beefull';
            echo json_encode($station, JSON_UNESCAPED_UNICODE);
            break;
        }

        // EPDK istasyonu
        $epdkId = (int)$id;
        if (!$epdkId) {
            http_response_code(400);
            echo json_encode(['error' => 'id required']);
            break;
        }
        $detail = $epdkApi->getStationDetail($epdkId, $date);
        if ($detail === null) {
            http_response_code(404);
            echo json_encode(['error' => 'not found']);
            break;
        }
        echo json_encode($detail, JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'unknown action']);
}
