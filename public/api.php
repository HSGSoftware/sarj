<?php
require_once __DIR__ . '/inc/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$api    = new \App\Api\EpdkApi();

switch ($action) {
    case 'stations':
        echo json_encode($api->getAllStations(), JSON_UNESCAPED_UNICODE);
        break;

    case 'detail':
        $id   = (int)($_GET['id'] ?? 0);
        $date = $_GET['date'] ?? date('Y-m-d H:i:s');
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'id required']);
            break;
        }
        $detail = $api->getStationDetail($id, $date);
        if ($detail === null) {
            http_response_code(404);
            echo json_encode(['error' => 'not found']);
        } else {
            // Astor istasyonuysa Beefull'dan koordinat bazlı soket+SoC verisi al
            if (strtoupper($detail['brand'] ?? '') === 'ASTOR') {
                $lat = $detail['lat'] ?? null;
                $lng = $detail['lng'] ?? null;
                if ($lat && $lng) {
                    $astorApi     = new \App\Api\AstorApi();
                    $astorSockets = $astorApi->getSocketsByCoord((float)$lat, (float)$lng);
                    if ($astorSockets !== null) {
                        $detail['sockets']      = $astorSockets;
                        $detail['socketSource'] = 'astor_beefull';
                    }
                }
            }
            echo json_encode($detail, JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'astor_sockets':
        // Direkt Beefull ID ile sorgu
        $beefullId = (int)($_GET['beefull_id'] ?? 0);
        $epdkId    = (int)($_GET['epdk_id'] ?? 0);
        $astorApi  = new \App\Api\AstorApi();

        if ($beefullId) {
            $sockets = $astorApi->getSocketsByBeefullId($beefullId);
        } elseif ($epdkId) {
            $sockets = $astorApi->getSocketsByEpdkId($epdkId);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'beefull_id or epdk_id required']);
            break;
        }

        if ($sockets === null) {
            http_response_code(404);
            echo json_encode(['error' => 'not found or no match']);
        } else {
            echo json_encode($sockets, JSON_UNESCAPED_UNICODE);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'unknown action']);
}
