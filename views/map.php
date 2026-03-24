<?php
$pageTitle  = 'Harita – ŞarjNet';
$activePage = 'map';
$bodyClass  = 'map-page';
require __DIR__ . '/partials/header.php';
?>

<header class="app-bar">
    <div class="app-bar-brand">
        <div class="app-bar-icon"><i class="fa-solid fa-bolt"></i></div>
        <span class="app-bar-title">Harita</span>
    </div>
    <button class="app-bar-action" id="locateBtn"><i class="fa-solid fa-location-crosshairs"></i></button>
</header>

<div class="page-content" style="padding-bottom:0; overflow:hidden;">
    <div class="map-full-container">

        <div id="fullMap" class="full-map"></div>

        <div class="map-fabs">
            <button class="map-fab" id="fitBtn" title="Tüm istasyonları göster">
                <i class="fa-solid fa-expand"></i>
            </button>
        </div>

        <!-- Bottom Sheet -->
        <div class="map-sheet collapsed" id="mapSheet">
            <div class="sheet-handle-wrap" id="sheetHandle">
                <div class="sheet-handle"></div>
            </div>

            <div class="sheet-search">
                <div class="sheet-search-input">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="İstasyon veya marka ara...">
                    <i class="fa-solid fa-xmark" id="clearSearch" style="cursor:pointer; display:none;"></i>
                </div>
            </div>

            <div class="sheet-filters">
                <button class="filter-pill active" data-filter="all">Tümü</button>
                <button class="filter-pill" data-filter="avail">Müsait</button>
                <button class="filter-pill" data-filter="green">Yeşil Enerji</button>
                <button class="filter-pill" data-filter="AC">AC</button>
                <button class="filter-pill" data-filter="DC">DC</button>
            </div>

            <div class="sheet-count" id="sheetCount">Yükleniyor...</div>
            <div class="sheet-list" id="sheetList">
                <div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i><span>İstasyonlar yükleniyor...</span></div>
            </div>
        </div>

    </div>
</div>

<?php require __DIR__ . '/partials/tab_bar.php'; ?>

<script>
let allStations = [], markers = [], map, userMarker = null;
let sheetOpen = false;
let activeFilter = 'all';
let searchVal = '';

document.addEventListener('DOMContentLoaded', () => {
    map = L.map('fullMap', { zoomControl: false }).setView([39.0, 35.0], 6);
    L.control.zoom({ position: 'bottomright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(map);

    // Open sheet on map load
    setTimeout(() => openSheet(), 600);

    fetch(BASE_URL + '/api.php?action=stations')
        .then(r => r.json())
        .then(data => {
            allStations = data;
            applyFilters();
        });

    // Sheet toggle
    document.getElementById('sheetHandle').addEventListener('click', () => {
        sheetOpen ? closeSheet() : openSheet();
    });

    // Search
    const si = document.getElementById('searchInput');
    si.addEventListener('input', () => {
        searchVal = si.value.toLowerCase();
        document.getElementById('clearSearch').style.display = searchVal ? '' : 'none';
        applyFilters();
    });
    document.getElementById('clearSearch').addEventListener('click', () => {
        si.value = ''; searchVal = '';
        document.getElementById('clearSearch').style.display = 'none';
        applyFilters();
    });

    // Filter pills
    document.querySelectorAll('.filter-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.dataset.filter;
            applyFilters();
        });
    });

    document.getElementById('locateBtn').addEventListener('click', locateUser);
    document.getElementById('fitBtn').addEventListener('click', () => {
        if (markers.length) {
            map.fitBounds(L.featureGroup(markers).getBounds(), { padding: [40, 40] });
        }
    });
});

function openSheet()  { document.getElementById('mapSheet').classList.remove('collapsed'); sheetOpen = true; }
function closeSheet() { document.getElementById('mapSheet').classList.add('collapsed');    sheetOpen = false; }

function applyFilters() {
    const filtered = allStations.filter(s => {
        if (activeFilter === 'avail' && !s.available) return false;
        if (activeFilter === 'green' && s.green !== 'EVET') return false;
        if (activeFilter === 'AC') {
            const socks = s.sockets || [];
            if (!socks.some(sk => sk.type === 'AC')) return false;
        }
        if (activeFilter === 'DC') {
            const socks = s.sockets || [];
            if (!socks.some(sk => sk.type === 'DC')) return false;
        }
        if (searchVal) {
            const h = ((s.title||'') + ' ' + (s.brand||'')).toLowerCase();
            if (!h.includes(searchVal)) return false;
        }
        return true;
    });
    renderMarkers(filtered);
    renderSheetList(filtered);
}

function renderSheetList(stations) {
    document.getElementById('sheetCount').textContent = stations.length.toLocaleString('tr') + ' istasyon';
    const list = document.getElementById('sheetList');
    if (!stations.length) {
        list.innerHTML = '<div class="no-results"><i class="fa-solid fa-circle-xmark"></i><p>Sonuç bulunamadı</p></div>';
        return;
    }
    list.innerHTML = stations.slice(0, 100).map(s => {
        const avail = s.available;
        const green = s.green === 'EVET';
        const colorClass = !avail ? 'red' : (green ? 'green' : 'orange');
        return `
        <div class="sheet-station-item" onclick="focusStation(${s.lat},${s.lng},${s.id})">
            <div class="ssi-icon ${colorClass}">
                <i class="fa-solid fa-charging-station"></i>
            </div>
            <div class="ssi-info">
                <div class="ssi-name">${escapeHtml(s.title)}</div>
                <div class="ssi-brand">${escapeHtml(s.brand)}</div>
                <div class="ssi-status">
                    <span class="dot ${avail ? 'dot-green' : 'dot-red'}"></span>
                    ${avail ? 'Müsait' : 'Dolu'}
                    ${green ? ' · <i class="fa-solid fa-leaf" style="color:var(--green);font-size:0.65rem;"></i> Yeşil' : ''}
                </div>
            </div>
            <div class="ssi-right">
                <div class="ssi-sockets">${s.sockets ? s.sockets.length : 0} soket</div>
                <div class="ssi-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </div>
        </div>`;
    }).join('');
}

function renderMarkers(stations) {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    const makeIcon = c => L.divIcon({ className: '', html: `<div class="map-pin ${c}-pin"><i class="fa-solid fa-bolt"></i></div>`, iconSize:[26,26], iconAnchor:[13,13] });
    const gIcon = makeIcon('green'), oIcon = makeIcon('orange'), rIcon = makeIcon('red');
    stations.forEach(s => {
        if (!s.lat || !s.lng) return;
        const icon = s.available ? (s.green==='EVET' ? gIcon : oIcon) : rIcon;
        const m = L.marker([s.lat, s.lng], { icon }).addTo(map).bindPopup(buildPopup(s));
        markers.push(m);
    });
}

function buildPopup(s) {
    const sockCount = s.sockets ? s.sockets.length : 0;
    return `<div class="popup-card">
        <div class="popup-badges">
            <span class="popup-badge ${s.available ? 'pb-green' : 'pb-red'}">${s.available ? '● Müsait' : '● Dolu'}</span>
            ${s.green==='EVET' ? '<span class="popup-badge pb-leaf">🌿 Yeşil</span>' : ''}
        </div>
        <div class="popup-title">${escapeHtml(s.title)}</div>
        <div class="popup-brand">${escapeHtml(s.brand)} · ${sockCount} soket</div>
        <div class="popup-actions">
            <a href="${BASE_URL}/?page=station&id=${s.id}" class="popup-btn pb-primary"><i class="fa-solid fa-circle-info"></i> Detay</a>
            <a href="https://www.google.com/maps/dir/?api=1&destination=${s.lat},${s.lng}" target="_blank" class="popup-btn pb-nav"><i class="fa-solid fa-route"></i> Git</a>
        </div>
    </div>`;
}

function focusStation(lat, lng) {
    map.setView([lat, lng], 16);
    closeSheet();
    markers.forEach(m => {
        const ll = m.getLatLng();
        if (Math.abs(ll.lat - lat) < 0.0001 && Math.abs(ll.lng - lng) < 0.0001) m.openPopup();
    });
}

function locateUser() {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(pos => {
        const { latitude: lat, longitude: lng } = pos.coords;
        if (userMarker) map.removeLayer(userMarker);
        userMarker = L.marker([lat, lng], {
            icon: L.divIcon({ className: '', html: '<div class="map-pin user-pin"><i class="fa-solid fa-person"></i></div>', iconSize:[32,32], iconAnchor:[16,16] })
        }).addTo(map).bindPopup('Konumunuz').openPopup();
        map.setView([lat, lng], 13);
    });
}

function escapeHtml(s) { if(!s) return ''; return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
