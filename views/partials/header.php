<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="<?= $bodyClass ?? '' ?>">

<nav class="navbar">
    <div class="nav-container">
        <a href="/" class="nav-brand">
            <div class="brand-icon"><i class="fa-solid fa-bolt"></i></div>
            <span class="brand-text">ŞarjNet</span>
        </a>
        <div class="nav-links">
            <a href="/" class="nav-link <?= ($activePage ?? '') === 'home' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> Ana Sayfa
            </a>
            <a href="/?page=map" class="nav-link <?= ($activePage ?? '') === 'map' ? 'active' : '' ?>">
                <i class="fa-solid fa-map-location-dot"></i> Harita
            </a>
            <a href="/?page=stations" class="nav-link <?= ($activePage ?? '') === 'stations' ? 'active' : '' ?>">
                <i class="fa-solid fa-charging-station"></i> İstasyonlar
            </a>
        </div>
        <button class="nav-toggle" id="navToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</nav>
