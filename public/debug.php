<?php
header('Content-Type: application/json');
echo json_encode([
    'php_version' => PHP_VERSION,
    'curl' => extension_loaded('curl'),
    'memory_limit' => ini_get('memory_limit'),
    'max_exec' => ini_get('max_execution_time'),
    'error_reporting' => ini_get('display_errors'),
    'root' => __DIR__,
    'inc_bootstrap' => file_exists(__DIR__.'/inc/bootstrap.php'),
    'stations_json' => file_exists(__DIR__.'/assets/stations_data.json'),
    'sys_temp' => sys_get_temp_dir(),
    'temp_writable' => is_writable(sys_get_temp_dir()),
], JSON_PRETTY_PRINT);
