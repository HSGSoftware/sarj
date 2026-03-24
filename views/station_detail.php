<?php
$stationId = (int)($_GET['id'] ?? 0);
$pageTitle = 'İstasyon Detayı – ŞarjNet';
$activePage = 'stations';
require __DIR__ . '/partials/header.php';
?>

<div class="page-hero mini">
    <div class="page-hero-content">
        <a href="<?= base_url('?page=stations') ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> İstasyonlara Dön</a>
        <h1 id="stationTitle">Şarj İstasyonu</h1>
        <p id="stationSubtitle">Yükleniyor...</p>
    </div>
</div>

<div class="detail-page">
    <div class="detail-main">
        <div class="detail-info-card" id="infoCard">
            <div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i><span>Yükleniyor...</span></div>
        </div>
        <div class="sockets-card" id="socketsCard" style="display:none">
            <h3><i class="fa-solid fa-plug"></i> Soketler</h3>
            <div id="socketsList"></div>
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
const stationId = <?= (int)$stationId ?>;
let detailMap = null;

document.addEventListener('DOMContentLoaded', () => {
    const date = new Date().toISOString().replace('T', ' ').substring(0, 19);
    fetch(`${BASE_URL}/api.php?action=detail&id=${stationId}&date=${encodeURIComponent(date)}`)
        .then(r => r.json())
        .then(s => {
            if (s.error) {
                // fallback: stations listesinden bul
                return fetch(BASE_URL + '/api.php?action=stations')
                    .then(r => r.json())
                    .then(stations => {
                        const st = stations.find(st => st.id === stationId);
                        if (st) renderDetail(st);
                        else document.getElementById('infoCard').innerHTML = '<div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> İstasyon bulunamadı.</div>';
                    });
            }
            renderDetail(s);
        });
});

function renderDetail(s) {
    document.getElementById('stationTitle').textContent = s.title;
    document.getElementById('stationSubtitle').textContent = (s.brand || '') + (s.operatortitle ? ' · ' + s.operatortitle : '');
    document.title = s.title + ' – ŞarjNet';

    const isGreen = s.green === 'EVET';
    const serviceLabel = s.serviceType === 'HALKA_ACIK' ? 'Halka Açık' : (s.serviceType || '—');

    document.getElementById('infoCard').innerHTML = `
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-charging-station"></i> İstasyon Adı</span>
                <span class="info-value">${escapeHtml(s.title)}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-tag"></i> Marka</span>
                <span class="info-value">${escapeHtml(s.brand)}</span>
            </div>
            ${s.operatortitle ? `<div class="info-row"><span class="info-label"><i class="fa-solid fa-building"></i> İşletmeci</span><span class="info-value">${escapeHtml(s.operatortitle)}</span></div>` : ''}
            ${s.address ? `<div class="info-row"><span class="info-label"><i class="fa-solid fa-location-dot"></i> Adres</span><span class="info-value">${escapeHtml(s.address)}</span></div>` : ''}
            ${s.phone ? `<div class="info-row"><span class="info-label"><i class="fa-solid fa-phone"></i> Telefon</span><span class="info-value">${escapeHtml(s.phone)}</span></div>` : ''}
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-hashtag"></i> ID</span>
                <span class="info-value badge-no">${s.id}</span>
            </div>
            ${s.serviceType ? `<div class="info-row"><span class="info-label"><i class="fa-solid fa-users"></i> Hizmet Şekli</span><span class="info-value"><span class="badge badge-green">${serviceLabel}</span></span></div>` : ''}
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-circle-dot"></i> Durum</span>
                <span class="info-value">
                    <span class="badge ${s.available ? 'badge-green' : 'badge-red'}">
                        <span class="dot ${s.available ? 'dot-green' : 'dot-red'}"></span>
                        ${s.available ? 'Müsait' : 'Dolu'}
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-leaf"></i> Enerji Tipi</span>
                <span class="info-value">
                    <span class="badge ${isGreen ? 'badge-leaf' : 'badge-gray'}">${isGreen ? '🌿 Yeşil Enerji' : 'Standart'}</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-plug"></i> Soket Sayısı</span>
                <span class="info-value">${s.sockets ? s.sockets.length : 0} soket</span>
            </div>
        </div>
    `;

    if (s.sockets && s.sockets.length > 0 && s.sockets[0].type) {
        renderSockets(s.sockets);
    }

    if (s.lat && s.lng) {
        initDetailMap(s.lat, s.lng, s.title);
        setupNavButtons(s.lat, s.lng);
    }

    document.getElementById('chargeBtn').href = `${BASE_URL}/?page=payment&id=${s.id}&name=${encodeURIComponent(s.title)}&brand=${encodeURIComponent(s.brand || '')}`;
}

function renderSockets(sockets) {
    const card = document.getElementById('socketsCard');
    card.style.display = '';
    document.getElementById('socketsList').innerHTML = sockets.map(sk => {
        const statusInfo = sk.availability && sk.availability[0] ? sk.availability[0].status : '';
        const price = sk.prices && sk.prices[0] ? sk.prices[0].price : null;
        return `
        <div class="socket-item">
            <div class="socket-icon ${sk.type === 'DC' ? 'dc' : 'ac'}">
                <i class="fa-solid fa-plug"></i>
            </div>
            <div class="socket-info">
                <div class="socket-name">${escapeHtml(sk.subType || sk.type || '—')}</div>
                <div class="socket-details">
                    <span class="socket-tag">${sk.type || ''}</span>
                    ${sk.power ? `<span class="socket-power">${sk.power} kW</span>` : ''}
                    ${price !== null ? `<span class="socket-price">₺${parseFloat(price).toFixed(2)}/kWh</span>` : ''}
                    ${statusInfo ? `<span class="socket-tag ${statusInfo === 'FREE' ? 'tag-free' : 'tag-busy'}">${statusInfo === 'FREE' ? 'Serbest' : statusInfo}</span>` : ''}
                    ${sk.socketNumber ? `<span class="socket-no">${escapeHtml(sk.socketNumber)}</span>` : ''}
                </div>
            </div>
        </div>`;
    }).join('');
}

function initDetailMap(lat, lng, title) {
    detailMap = L.map('detailMap').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(detailMap);
    const icon = L.divIcon({ className: '', html: '<div class="map-pin green-pin large"><i class="fa-solid fa-charging-station"></i></div>', iconSize: [40,40], iconAnchor: [20,20] });
    L.marker([lat, lng], { icon }).addTo(detailMap).bindPopup(escapeHtml(title)).openPopup();
}

function setupNavButtons(lat, lng) {
    document.getElementById('navButtons').innerHTML = `
        <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="nav-btn gmaps"><i class="fa-brands fa-google"></i> Google Maps</a>
        <a href="https://yandex.com.tr/maps/?rtext=~${lat},${lng}&rtt=auto" target="_blank" class="nav-btn yandex"><i class="fa-solid fa-map"></i> Yandex Maps</a>
        <a href="https://waze.com/ul?ll=${lat},${lng}&navigate=yes" target="_blank" class="nav-btn waze"><i class="fa-solid fa-car"></i> Waze</a>
        <a href="https://maps.apple.com/?daddr=${lat},${lng}" target="_blank" class="nav-btn apple"><i class="fa-brands fa-apple"></i> Apple Maps</a>
    `;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
