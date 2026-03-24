<?php
$pageTitle  = 'ŞarjNet';
$activePage = 'home';
require __DIR__ . '/partials/header.php';
?>

<header class="app-bar">
    <div class="app-bar-brand">
        <div class="app-bar-icon"><i class="fa-solid fa-bolt"></i></div>
        <span class="app-bar-title">ŞarjNet</span>
    </div>
</header>

<div class="page-content">

    <div class="hero">
        <div class="hero-badge"><i class="fa-solid fa-bolt"></i> EPDK Lisanslı Veriler</div>
        <h1 class="hero-title">Tüm Şarj<br>İstasyonları <span class="gradient-text">Tek Yerde</span></h1>
        <p class="hero-sub">12.000+ istasyonu haritada görün, navigasyon başlatın, demo ödeme yapın.</p>
        <div class="hero-actions">
            <a href="<?= base_url('?page=map') ?>" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-map-location-dot"></i> Haritayı Aç
            </a>
            <a href="<?= base_url('?page=stations') ?>" class="btn btn-outline btn-lg">
                <i class="fa-solid fa-list"></i> Listele
            </a>
        </div>
        <div class="stat-chips">
            <div class="stat-chip">
                <span class="stat-chip-num" id="heroTotal">—</span>
                <span class="stat-chip-label">İstasyon</span>
            </div>
            <div class="stat-chip">
                <span class="stat-chip-num" id="heroAvail">—</span>
                <span class="stat-chip-label">Müsait</span>
            </div>
            <div class="stat-chip">
                <span class="stat-chip-num" id="heroBrands">—</span>
                <span class="stat-chip-label">Marka</span>
            </div>
            <div class="stat-chip">
                <span class="stat-chip-num" id="heroGreen">—</span>
                <span class="stat-chip-label">Yeşil Enerji</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-head">
            <span class="section-title">Yakındaki İstasyonlar</span>
            <a href="<?= base_url('?page=map') ?>" class="section-link">Tümü <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="home-map-wrap">
            <div id="homeMap" class="home-map"></div>
        </div>
    </div>

    <div class="section">
        <div class="section-head">
            <span class="section-title">Özellikler</span>
        </div>
        <div class="feature-scroll">
            <div class="feature-card">
                <div class="feature-icon fi-green"><i class="fa-solid fa-map-location-dot"></i></div>
                <h3>Harita</h3>
                <p>12.000+ istasyon interaktif haritada</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-blue"><i class="fa-solid fa-route"></i></div>
                <h3>Navigasyon</h3>
                <p>Google Maps, Yandex, Waze ile yol tarifi</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-purple"><i class="fa-solid fa-plug-circle-check"></i></div>
                <h3>Anlık Durum</h3>
                <p>Müsaitlik, fiyat ve soket bilgileri</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-orange"><i class="fa-solid fa-credit-card"></i></div>
                <h3>Demo Ödeme</h3>
                <p>Şarj ödeme akışını test edin</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-head">
            <span class="section-title">Markalar</span>
        </div>
        <div class="brand-scroll" id="brandScroll">
            <div class="brand-skeleton"></div>
            <div class="brand-skeleton"></div>
            <div class="brand-skeleton"></div>
            <div class="brand-skeleton"></div>
        </div>
    </div>

</div>

<?php require __DIR__ . '/partials/tab_bar.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    fetch(BASE_URL + '/api.php?action=stations')
        .then(r => r.json())
        .then(stations => {
            document.getElementById('heroTotal').textContent  = stations.length.toLocaleString('tr');
            document.getElementById('heroAvail').textContent  = stations.filter(s => s.available).length.toLocaleString('tr');
            const brands = [...new Set(stations.map(s => s.brand).filter(Boolean))];
            document.getElementById('heroBrands').textContent = brands.length;
            document.getElementById('heroGreen').textContent  = stations.filter(s => s.green === 'EVET').length.toLocaleString('tr');

            const scroll = document.getElementById('brandScroll');
            scroll.innerHTML = '';
            brands.slice(0, 15).forEach(b => {
                const d = document.createElement('div');
                d.className = 'brand-chip';
                d.innerHTML = `<i class="fa-solid fa-bolt-lightning"></i>${escapeHtml(b)}`;
                scroll.appendChild(d);
            });

            initHomeMap(stations.slice(0, 400));
        });
});

function initHomeMap(stations) {
    const map = L.map('homeMap', { zoomControl: false, scrollWheelZoom: false, dragging: true })
                 .setView([39.0, 35.0], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM', maxZoom: 18 }).addTo(map);
    const gIcon = L.divIcon({ className: '', html: '<div class="map-pin green-pin"><i class="fa-solid fa-bolt"></i></div>', iconSize:[26,26], iconAnchor:[13,13] });
    const oIcon = L.divIcon({ className: '', html: '<div class="map-pin orange-pin"><i class="fa-solid fa-bolt"></i></div>', iconSize:[26,26], iconAnchor:[13,13] });
    stations.forEach(s => {
        if (!s.lat || !s.lng) return;
        L.marker([s.lat, s.lng], { icon: s.green==='EVET' ? gIcon : oIcon }).addTo(map)
         .bindPopup(`<div class="popup-card"><div class="popup-title">${escapeHtml(s.title)}</div><div class="popup-brand">${escapeHtml(s.brand)}</div><div class="popup-actions"><a href="${BASE_URL}/?page=station&id=${s.id}" class="popup-btn pb-primary"><i class="fa-solid fa-arrow-right"></i> Detay</a></div></div>`);
    });
}

function escapeHtml(s) { if(!s) return ''; return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
