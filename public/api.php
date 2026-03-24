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
            echo json_encode($detail, JSON_UNESCAPED_UNICODE);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'unknown action']);
}
