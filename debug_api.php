<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$results = [];

// Test 1: sarjtr API
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://sarjtr.epdk.gov.tr/sarjet/api/stations',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => 'gzip',
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['User-Agent: Dart/3.1 (dart:io)', 'Accept: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);
$r = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$results['sarjtr'] = [
    'http_code' => $code,
    'error' => $err ?: null,
    'response_length' => $r ? strlen($r) : 0,
    'valid_json' => $r ? (json_decode($r) !== null) : false,
];

// Test 2: Beefull API
$ch2 = curl_init();
curl_setopt_array($ch2, [
    CURLOPT_URL => 'https://api-gw.beefull.io/api/beefull-charge-management/grouped-charge-points-alternative?columns=id,coordinate&serviceTypeCode=ev&isVisible=true&tenantCode=beefull,astor',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => 'gzip',
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        'inavitas-tenant: astor',
        'authorization: Basic 5fe5db79fccb804dcc9e548272948ec901d36655c06fc618c87d0f148fca7374',
        'accept: application/json',
        'user-agent: okhttp/4.9.2',
    ],
    CURLOPT_SSL_VERIFYPEER => false,
]);
$r2 = curl_exec($ch2);
$err2 = curl_error($ch2);
$code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

$results['beefull'] = [
    'http_code' => $code2,
    'error' => $err2 ?: null,
    'response_length' => $r2 ? strlen($r2) : 0,
];

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
