<?php
$stationNo = $_GET['no'] ?? '';
$pageTitle = 'İstasyon Detayı – ŞarjNet';
$activePage = 'stations';
require __DIR__ . '/partials/header.php';
?>

<div class="page-hero mini">
    <div class="page-hero-content">
        <a href="/?page=stations" class="back-link"><i class="fa-solid fa-arrow-left"></i> İstasyonlara Dön</a>
        <h1 id="stationTitle">Şarj İstasyonu</h1>
        <p id="stationSubtitle">Yükleniyor...</p>
    </div>
</div>

<div class="detail-page">
    <div class="detail-main">
        <div class="detail-info-card" id="infoCard">
            <div class="loading-spinner">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Yükleniyor...</span>
            </div>
        </div>

        <div class="sockets-card">
            <h3><i class="fa-solid fa-plug"></i> Soketler</h3>
            <div id="socketsList">
                <div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>

    <div class="detail-sidebar">
        <div id="detailMap" class="detail-map"></div>

        <div class="nav-card">
            <h3><i class="fa-solid fa-route"></i> Navigasyon</h3>
            <p>İstasyona git:</p>
            <div class="nav-buttons" id="navButtons">
                <div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i></div>
            </div>
        </div>

        <div class="charge-card">
            <h3><i class="fa-solid fa-credit-card"></i> Şarj Başlat</h3>
            <p>Demo şarj ödemesi yapın</p>
            <a href="#" class="btn btn-primary btn-block" id="chargeBtn">
                <i class="fa-solid fa-bolt"></i> Şarj Et & Öde
            </a>
        </div>
    </div>
</div>

<script>
const stationNo = '<?= htmlspecialchars($stationNo, ENT_QUOTES) ?>';
let stationData = null;
let detailMap = null;

document.addEventListener('DOMContentLoaded', () => {
    fetch('/api.php?action=stations')
        .then(r => r.json())
        .then(stations => {
            stationData = stations.find(s => s.sarjIstasyonuNo === stationNo);
            if (!stationData) {
                document.getElementById('infoCard').innerHTML = '<div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> İstasyon bulunamadı.</div>';
                return;
            }
            renderDetail(stationData);
            loadSockets(stationNo);
        });
});

function renderDetail(s) {
    document.getElementById('stationTitle').textContent = s.sarjIstasyonuAdi;
    document.getElementById('stationSubtitle').textContent = s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi + ' · ' + (s.hizmetSekli === 'HALKA_ACIK' ? 'Halka Açık' : 'Özel');
    document.title = s.sarjIstasyonuAdi + ' – ŞarjNet';

    document.getElementById('infoCard').innerHTML = `
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-charging-station"></i> İstasyon Adı</span>
                <span class="info-value">${escapeHtml(s.sarjIstasyonuAdi)}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-tag"></i> Marka</span>
                <span class="info-value">${escapeHtml(s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi)}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-building"></i> İşletmeci</span>
                <span class="info-value">${escapeHtml(s.sarjAgiIsletmecisiUnvan)}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-location-dot"></i> Adres</span>
                <span class="info-value">${escapeHtml(s.adresMahalleCaddeSokak)}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-hashtag"></i> İstasyon No</span>
                <span class="info-value badge-no">${escapeHtml(s.sarjIstasyonuNo)}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-users"></i> Hizmet Şekli</span>
                <span class="info-value"><span class="badge ${s.hizmetSekli === 'HALKA_ACIK' ? 'badge-green' : 'badge-orange'}">${s.hizmetSekli === 'HALKA_ACIK' ? 'Halka Açık' : 'Özel'}</span></span>
            </div>
        </div>
    `;

    if (s.lat && s.lng) {
        initDetailMap(s.lat, s.lng, s);
        setupNavButtons(s.lat, s.lng);
    }

    document.getElementById('chargeBtn').href = `/?page=payment&no=${encodeURIComponent(s.sarjIstasyonuNo)}&name=${encodeURIComponent(s.sarjIstasyonuAdi)}`;
}

function initDetailMap(lat, lng, s) {
    detailMap = L.map('detailMap').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(detailMap);

    const icon = L.divIcon({
        className: '',
        html: '<div class="map-pin green-pin large"><i class="fa-solid fa-charging-station"></i></div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });
    L.marker([lat, lng], { icon }).addTo(detailMap).bindPopup(escapeHtml(s.sarjIstasyonuAdi)).openPopup();
}

function setupNavButtons(lat, lng) {
    document.getElementById('navButtons').innerHTML = `
        <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="nav-btn gmaps">
            <i class="fa-brands fa-google"></i> Google Maps
        </a>
        <a href="https://yandex.com.tr/maps/?rtext=~${lat},${lng}&rtt=auto" target="_blank" class="nav-btn yandex">
            <i class="fa-solid fa-map"></i> Yandex Maps
        </a>
        <a href="https://waze.com/ul?ll=${lat},${lng}&navigate=yes" target="_blank" class="nav-btn waze">
            <i class="fa-solid fa-car"></i> Waze
        </a>
        <a href="https://maps.apple.com/?daddr=${lat},${lng}" target="_blank" class="nav-btn apple">
            <i class="fa-brands fa-apple"></i> Apple Maps
        </a>
    `;
}

function loadSockets(no) {
    fetch(`/api.php?action=sockets&no=${encodeURIComponent(no)}`)
        .then(r => r.json())
        .then(sockets => {
            if (!sockets || sockets.length === 0) {
                document.getElementById('socketsList').innerHTML = '<p class="no-data">Soket bilgisi bulunamadı.</p>';
                return;
            }
            document.getElementById('socketsList').innerHTML = sockets.map(sk => `
                <div class="socket-item">
                    <div class="socket-icon ${sk.soketTipi === 'DC' ? 'dc' : 'ac'}">
                        <i class="fa-solid fa-plug"></i>
                    </div>
                    <div class="socket-info">
                        <div class="socket-name">${escapeHtml(sk.soketTuru || sk.soketTipi)}</div>
                        <div class="socket-details">
                            <span class="socket-tag">${sk.soketTipi}</span>
                            <span class="socket-power">${sk.soketGucu} kW</span>
                            <span class="socket-no">${escapeHtml(sk.soketNo)}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
