<?php
$stationId  = (int)($_GET['id'] ?? 0);
$pageTitle  = 'İstasyon – ŞarjNet';
$activePage = 'stations';
require __DIR__ . '/partials/header.php';
?>

<header class="app-bar">
    <a href="<?= base_url('?page=stations') ?>" class="app-bar-back">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <span class="app-bar-page-title">İstasyon Detayı</span>
</header>

<div class="page-content">

    <!-- Hero -->
    <div class="detail-hero" id="detailHero">
        <div class="loading-spinner" style="padding:24px 0"><i class="fa-solid fa-spinner fa-spin"></i><span>Yükleniyor...</span></div>
    </div>

    <!-- Map -->
    <div class="detail-map-card" id="detailMapCard" style="display:none">
        <div id="detailMap" class="detail-map"></div>
    </div>

    <!-- Info -->
    <div class="info-card" id="infoCard" style="display:none"></div>

    <!-- Sockets -->
    <div class="sockets-card" id="socketsCard" style="display:none">
        <div class="card-header-row"><i class="fa-solid fa-plug"></i> Soketler</div>
        <div id="socketsList"></div>
    </div>

    <!-- Navigation -->
    <div class="nav-card" id="navCard" style="display:none">
        <div class="card-header-row"><i class="fa-solid fa-route" style="color:var(--blue)"></i> Navigasyon</div>
        <div id="navButtons"></div>
    </div>

    <!-- Charge CTA -->
    <div id="chargeCta" style="display:none; margin-bottom:8px">
        <a href="#" class="charge-cta" id="chargeLink">
            <div class="charge-cta-icon"><i class="fa-solid fa-bolt"></i></div>
            <div class="charge-cta-text">
                <h4>Şarj Et & Öde</h4>
                <p>Demo ödeme ile şarj başlat</p>
            </div>
            <div class="charge-cta-btn"><i class="fa-solid fa-arrow-right"></i></div>
        </a>
    </div>

    <div style="height:8px"></div>

</div>

<?php require __DIR__ . '/partials/tab_bar.php'; ?>

<script>
const stationId = <?= (int)$stationId ?>;
let detailMap = null;

document.addEventListener('DOMContentLoaded', () => {
    const date = new Date().toISOString().replace('T',' ').substring(0,19);
    fetch(`${BASE_URL}/api.php?action=detail&id=${stationId}&date=${encodeURIComponent(date)}`)
        .then(r => r.json())
        .then(s => {
            if (s.error) {
                return fetch(BASE_URL+'/api.php?action=stations')
                    .then(r=>r.json())
                    .then(list => {
                        const st = list.find(x => x.id === stationId);
                        st ? renderDetail(st) : showError();
                    });
            }
            renderDetail(s);
        })
        .catch(showError);
});

function showError() {
    document.getElementById('detailHero').innerHTML = '<div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> İstasyon bulunamadı.</div>';
}

function renderDetail(s) {
    document.title = s.title + ' – ŞarjNet';
    const avail   = s.available;
    const isGreen = s.green === 'EVET';

    // Hero
    document.getElementById('detailHero').innerHTML = `
        <div class="detail-title">${escapeHtml(s.title)}</div>
        <div class="detail-brand">
            <i class="fa-solid fa-tag" style="color:var(--text-dim)"></i> ${escapeHtml(s.brand)}
            ${s.operatortitle ? `<span style="color:var(--border2)">·</span> ${escapeHtml(s.operatortitle)}` : ''}
        </div>
        <div class="detail-badges">
            <span class="detail-badge ${avail ? 'db-green' : 'db-red'}">
                <span class="dot ${avail ? 'dot-green' : 'dot-red'}"></span>
                ${avail ? 'Müsait' : 'Dolu'}
            </span>
            ${isGreen ? '<span class="detail-badge db-leaf">🌿 Yeşil Enerji</span>' : ''}
            ${s.serviceType === 'HALKA_ACIK' ? '<span class="detail-badge db-orange"><i class="fa-solid fa-users"></i> Halka Açık</span>' : ''}
            ${s.sockets ? `<span class="detail-badge db-gray"><i class="fa-solid fa-plug"></i> ${s.sockets.length} Soket</span>` : ''}
        </div>
    `;

    // Info card
    const infoCard = document.getElementById('infoCard');
    infoCard.style.display = '';
    const rows = [
        s.address ? ['fa-location-dot', 'Adres', s.address] : null,
        s.phone   ? ['fa-phone', 'Telefon', s.phone] : null,
        ['fa-hashtag', 'İstasyon ID', `<span style="font-family:monospace;font-size:0.85rem">${s.id}</span>`],
    ].filter(Boolean);

    infoCard.innerHTML = rows.map(([icon, label, val]) => `
        <div class="info-row">
            <div class="info-icon"><i class="fa-solid ${icon}"></i></div>
            <div>
                <div class="info-label">${label}</div>
                <div class="info-value">${escapeHtml(typeof val === 'string' && val.startsWith('<') ? null : val) || val}</div>
            </div>
        </div>
    `).join('');

    // Map
    if (s.lat && s.lng) {
        document.getElementById('detailMapCard').style.display = '';
        initMap(s.lat, s.lng, s.title);

        // Nav
        document.getElementById('navCard').style.display = '';
        document.getElementById('navButtons').innerHTML = [
            ['nb-gmaps',  'fa-brands fa-google', 'Google Maps', `https://www.google.com/maps/dir/?api=1&destination=${s.lat},${s.lng}`],
            ['nb-yandex', 'fa-solid fa-map',      'Yandex Maps', `https://yandex.com.tr/maps/?rtext=~${s.lat},${s.lng}&rtt=auto`],
            ['nb-waze',   'fa-solid fa-car',       'Waze',        `https://waze.com/ul?ll=${s.lat},${s.lng}&navigate=yes`],
            ['nb-apple',  'fa-brands fa-apple',    'Apple Maps',  `https://maps.apple.com/?daddr=${s.lat},${s.lng}`],
        ].map(([cls, ico, label, url]) => `
            <a href="${url}" target="_blank" class="nav-btn ${cls}">
                <div class="nav-btn-icon"><i class="${ico}"></i></div>
                <span>${label}</span>
                <i class="nav-btn-arrow fa-solid fa-arrow-up-right-from-square"></i>
            </a>
        `).join('');
    }

    // Sockets
    if (s.sockets && s.sockets.length > 0 && s.sockets[0].type) {
        renderSockets(s.sockets);
    }

    // CTA
    document.getElementById('chargeCta').style.display = '';
    document.getElementById('chargeLink').href = `${BASE_URL}/?page=payment&id=${s.id}&name=${encodeURIComponent(s.title)}&brand=${encodeURIComponent(s.brand||'')}`;
}

function renderSockets(sockets) {
    document.getElementById('socketsCard').style.display = '';
    document.getElementById('socketsList').innerHTML = sockets.map(sk => {
        const status = sk.availability && sk.availability[0] ? sk.availability[0].status : '';
        const price  = sk.prices && sk.prices[0] ? sk.prices[0].price : null;
        return `
        <div class="socket-item">
            <div class="socket-icon ${sk.type === 'DC' ? 'dc' : 'ac'}"><i class="fa-solid fa-plug"></i></div>
            <div class="socket-info">
                <div class="socket-name">${escapeHtml(sk.subType || sk.type)}</div>
                <div class="socket-details">
                    <span class="socket-tag">${sk.type}</span>
                    ${sk.power ? `<span class="socket-power">${sk.power} kW</span>` : ''}
                    ${price !== null ? `<span class="socket-price">₺${parseFloat(price).toFixed(2)}/kWh</span>` : ''}
                    ${status ? `<span class="socket-tag ${status==='FREE'?'tag-free':'tag-busy'}">${status==='FREE'?'Serbest':'Meşgul'}</span>` : ''}
                </div>
            </div>
        </div>`;
    }).join('');
}

function initMap(lat, lng, title) {
    detailMap = L.map('detailMap').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OSM', maxZoom:19 }).addTo(detailMap);
    const icon = L.divIcon({ className:'', html:'<div class="map-pin green-pin large"><i class="fa-solid fa-charging-station"></i></div>', iconSize:[40,40], iconAnchor:[20,20] });
    L.marker([lat, lng], { icon }).addTo(detailMap).bindPopup(escapeHtml(title)).openPopup();
}

function escapeHtml(s) { if(!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
