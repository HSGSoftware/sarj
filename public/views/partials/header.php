<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <script>
        const BASE_URL = '<?= rtrim(base_url(), '/') ?>';
        const PAGES = {
            home:            BASE_URL + '/index.php',
            map:             BASE_URL + '/map.php',
            stations:        BASE_URL + '/stations.php',
            station:         BASE_URL + '/station.php',
            payment:         BASE_URL + '/payment.php',
            payment_success: BASE_URL + '/payment_success.php',
            api:             BASE_URL + '/api.php',
        };
    </script>
</head>
<body class="<?= $bodyClass ?? '' ?>">
<div class="app-shell">
