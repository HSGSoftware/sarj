<?php
$stationId = (int)($_GET['id'] ?? 0);
?>
<?php require __DIR__ . '/partials/header.php'; ?>

<header class="app-bar">
    <a href="<?= page_url('stations') ?>" class="app-bar-back"><i class="fa-solid fa-arrow-left"></i></a>
    <span class="app-bar-page-title">İstasyon Detayı</span>
</header>

<div class="page-content">

    <div class="detail-hero" id="detailHero">
        <div class="loading-spinner" style="padding:24px 0">
            <i class="fa-solid fa-spinner fa-spin"></i><span>Yükleniyor...</span>
        </div>
    </div>

    <div class="detail-map-card" id="detailMapCard" style="display:none">
        <div id="detailMap" class="detail-map"></div>
    </div>

    <div class="info-card" id="infoCard" style="display:none"></div>

    <!-- Soket kartı — her zaman render edilir -->
    <div class="sockets-card" id="socketsCard" style="display:none">
        <div class="card-header-row"><i class="fa-solid fa-plug"></i> Soketler</div>
        <div id="socketsList"></div>
    </div>

    <div class="nav-card" id="navCard" style="display:none">
        <div class="card-header-row"><i class="fa-solid fa-route" style="color:var(--blue)"></i> Navigasyon</div>
        <div id="navButtons"></div>
    </div>

    <div id="chargeCta" style="display:none;margin-bottom:8px">
        <a href="#" class="charge-cta" id="chargeLink">
            <div class="charge-cta-icon"><i class="fa-solid fa-bolt"></i></div>
            <div class="charge-cta-text"><h4>Şarj Et & Öde</h4><p>Demo ödeme ile şarj başlat</p></div>
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
    if (!stationId) { showError('Geçersiz istasyon ID.'); return; }

    const date = new Date().toISOString().replace('T',' ').substring(0,19);
    const detailUrl = `${PAGES.api}?action=detail&id=${stationId}&date=${encodeURIComponent(date)}`;

    fetch(detailUrl)
        .then(r => { if(!r.ok) throw new Error(r.status); return r.json(); })
        .then(s => {
            if (s.error) throw new Error('not found');
            renderDetail(s);
        })
        .catch(() => {
            // API'ye erişilemedi veya hata — stations listesinden bul
            fetch(PAGES.api + '?action=stations')
                .then(r => r.json())
                .then(list => {
                    const st = list.find(x => x.id === stationId);
                    if (st) renderDetail(st, true);
                    else showError('İstasyon bulunamadı.');
                })
                .catch(() => showError('Veri yüklenemedi.'));
        });
});

function showError(msg) {
    document.getElementById('detailHero').innerHTML =
        `<div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> ${msg}</div>`;
}

function renderDetail(s, isFallback) {
    document.title = s.title + ' – ŞarjNet';
    const avail   = s.available;
    const isGreen = s.green === 'EVET';

    // ── Hero ──
    document.getElementById('detailHero').innerHTML = `
        <div class="detail-title">${esc(s.title)}</div>
        <div class="detail-brand">
            <i class="fa-solid fa-tag" style="color:var(--text-dim)"></i> ${esc(s.brand)}
            ${s.operatortitle ? `<span style="color:var(--border2)">·</span> ${esc(s.operatortitle)}` : ''}
        </div>
        <div class="detail-badges">
            <span class="detail-badge ${avail ? 'db-green' : 'db-red'}">
                <span class="dot ${avail ? 'dot-green' : 'dot-red'}"></span>
                ${avail ? 'Müsait' : 'Dolu'}
            </span>
            ${isGreen ? '<span class="detail-badge db-leaf">🌿 Yeşil Enerji</span>' : ''}
            ${s.serviceType === 'HALKA_ACIK' ? '<span class="detail-badge db-orange"><i class="fa-solid fa-users"></i> Halka Açık</span>' : ''}
            ${s.sockets ? `<span class="detail-badge db-gray"><i class="fa-solid fa-plug"></i> ${s.sockets.length} Soket</span>` : ''}
            ${isFallback ? '<span class="detail-badge db-gray"><i class="fa-solid fa-wifi" style="opacity:.5"></i> Önbellek</span>' : ''}
        </div>`;

    // ── Info kartı ──
    const ic = document.getElementById('infoCard');
    ic.style.display = '';
    ic.innerHTML = [
        s.address  ? ['fa-location-dot', 'Adres',    esc(s.address)] : null,
        s.phone    ? ['fa-phone',         'Telefon',  esc(s.phone)]   : null,
        ['fa-hashtag',   'İstasyon ID',  `<span style="font-family:monospace">${s.id}</span>`],
    ].filter(Boolean).map(([icon,label,val]) => `
        <div class="info-row">
            <div class="info-icon"><i class="fa-solid ${icon}"></i></div>
            <div><div class="info-label">${label}</div><div class="info-value">${val}</div></div>
        </div>`).join('');

    // ── Harita ──
    if (s.lat && s.lng) {
        document.getElementById('detailMapCard').style.display = '';
        initMap(s.lat, s.lng, s.title);

        document.getElementById('navCard').style.display = '';
        document.getElementById('navButtons').innerHTML = [
            ['nb-gmaps',  'fa-brands fa-google', 'Google Maps', `https://www.google.com/maps/dir/?api=1&destination=${s.lat},${s.lng}`],
            ['nb-yandex', 'fa-solid fa-map',      'Yandex Maps', `https://yandex.com.tr/maps/?rtext=~${s.lat},${s.lng}&rtt=auto`],
            ['nb-waze',   'fa-solid fa-car',       'Waze',       `https://waze.com/ul?ll=${s.lat},${s.lng}&navigate=yes`],
            ['nb-apple',  'fa-brands fa-apple',   'Apple Maps',  `https://maps.apple.com/?daddr=${s.lat},${s.lng}`],
        ].map(([cls,ico,label,url]) =>
            `<a href="${url}" target="_blank" class="nav-btn ${cls}">
                <div class="nav-btn-icon"><i class="${ico}"></i></div>
                <span>${label}</span>
                <i class="nav-btn-arrow fa-solid fa-arrow-up-right-from-square"></i>
            </a>`
        ).join('');
    }

    // ── Soketler ──
    renderSockets(s.sockets || [], isFallback);

    // ── CTA ──
    document.getElementById('chargeCta').style.display = '';
    document.getElementById('chargeLink').href =
        `${PAGES.payment}?id=${s.id}&name=${encodeURIComponent(s.title)}&brand=${encodeURIComponent(s.brand||'')}`;
}

function renderSockets(sockets, isFallback) {
    const card = document.getElementById('socketsCard');
    const list = document.getElementById('socketsList');
    card.style.display = '';

    if (!sockets.length) {
        list.innerHTML = '<div style="padding:16px;color:var(--text-muted);font-size:.875rem;">Soket bilgisi bulunamadı.</div>';
        return;
    }

    // Soket detay bilgisi var mı? (API'den geldiğinde type alanı dolu olur)
    const hasDetail = sockets[0].type;

    if (!hasDetail) {
        // Sadece sayı bilgisi var (fallback list verisi)
        list.innerHTML = `
            <div class="socket-item">
                <div class="socket-icon ac"><i class="fa-solid fa-plug"></i></div>
                <div class="socket-info">
                    <div class="socket-name">${sockets.length} Soket</div>
                    <div class="socket-details">
                        <span class="socket-tag">Detay için bağlantı bekleniyor</span>
                    </div>
                </div>
            </div>`;
        return;
    }

    list.innerHTML = sockets.map(sk => {
        const avail  = sk.availability && sk.availability[0] ? sk.availability[0].status : null;
        const price  = sk.price != null ? sk.price : (sk.prices && sk.prices[0] ? sk.prices[0].price : null);
        const isDC   = sk.type === 'DC';

        let statusBadge = '';
        if (avail === 'FREE')     statusBadge = '<span class="socket-tag tag-free"><i class="fa-solid fa-circle-check"></i> Serbest</span>';
        else if (avail)           statusBadge = `<span class="socket-tag tag-busy"><i class="fa-solid fa-circle-xmark"></i> ${avail}</span>`;

        return `
        <div class="socket-item">
            <div class="socket-icon ${isDC ? 'dc' : 'ac'}">
                <i class="fa-solid fa-plug"></i>
            </div>
            <div class="socket-info" style="flex:1">
                <div class="socket-name">${esc(sk.subType || sk.type)}</div>
                <div class="socket-details">
                    <span class="socket-tag">${sk.type}</span>
                    ${sk.power ? `<span class="socket-power">${sk.power} kW</span>` : ''}
                    ${price != null ? `<span class="socket-price">₺${parseFloat(price).toFixed(2)}/kWh</span>` : ''}
                    ${statusBadge}
                </div>
                ${sk.socketNumber ? `<div style="font-size:.7rem;color:var(--text-dim);margin-top:4px;font-family:monospace">${esc(sk.socketNumber)}</div>` : ''}
            </div>
        </div>`;
    }).join('');
}

function initMap(lat, lng, title) {
    detailMap = L.map('detailMap').setView([lat,lng],15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OSM', maxZoom:19 }).addTo(detailMap);
    const icon = L.divIcon({ className:'', html:'<div class="map-pin green-pin large"><i class="fa-solid fa-charging-station"></i></div>', iconSize:[40,40], iconAnchor:[20,20] });
    L.marker([lat,lng],{icon}).addTo(detailMap).bindPopup(esc(title)).openPopup();
}

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
