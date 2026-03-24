<?php
$pageTitle = 'Harita – ŞarjNet';
$activePage = 'map';
$bodyClass = 'map-page';
require __DIR__ . '/partials/header.php';
?>

<div class="map-layout">
    <div class="map-sidebar" id="mapSidebar">
        <div class="sidebar-header">
            <h2><i class="fa-solid fa-map-location-dot"></i> İstasyonlar</h2>
            <button class="sidebar-toggle-btn" id="sidebarToggle">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </div>

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="İstasyon adı veya marka ara...">
        </div>

        <div class="filter-row">
            <select id="filterGreen" class="filter-select">
                <option value="">Tüm Tipler</option>
                <option value="EVET">Yeşil Enerji</option>
                <option value="HAYIR">Standart</option>
            </select>
            <select id="filterBrand" class="filter-select">
                <option value="">Tüm Markalar</option>
            </select>
        </div>

        <div class="filter-row">
            <select id="filterAvail" class="filter-select">
                <option value="">Tüm Durumlar</option>
                <option value="1">Müsait</option>
                <option value="0">Dolu</option>
            </select>
            <select id="filterSockets" class="filter-select">
                <option value="">Soket Sayısı</option>
                <option value="1">1+</option>
                <option value="3">3+</option>
                <option value="6">6+</option>
            </select>
        </div>

        <div class="station-count" id="stationCount">Yükleniyor...</div>

        <div class="station-list" id="stationList">
            <div class="loading-spinner">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>İstasyonlar yükleniyor...</span>
            </div>
        </div>
    </div>

    <div class="map-main">
        <button class="sidebar-open-btn" id="sidebarOpenBtn" style="display:none;">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        <div class="map-controls">
            <button class="map-ctrl-btn" id="locateBtn" title="Konumumu bul">
                <i class="fa-solid fa-location-crosshairs"></i>
            </button>
            <button class="map-ctrl-btn" id="fitBtn" title="Tüm istasyonları göster">
                <i class="fa-solid fa-expand"></i>
            </button>
        </div>

        <div id="fullMap" class="full-map"></div>
    </div>
</div>

<script>
let allStations = [];
let markers     = [];
let map;
let userMarker  = null;

document.addEventListener('DOMContentLoaded', () => {
    map = L.map('fullMap', { zoomControl: false }).setView([39.0, 35.0], 6);
    L.control.zoom({ position: 'bottomright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
    }).addTo(map);

    fetch(BASE_URL + '/api.php?action=stations')
        .then(r => r.json())
        .then(data => {
            allStations = data;
            populateBrandFilter(data);
            applyFilters();
        });

    ['searchInput','filterGreen','filterBrand','filterAvail','filterSockets'].forEach(id => {
        document.getElementById(id).addEventListener(id === 'searchInput' ? 'input' : 'change', applyFilters);
    });

    document.getElementById('locateBtn').addEventListener('click', locateUser);
    document.getElementById('fitBtn').addEventListener('click', () => {
        if (markers.length) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds(), { padding: [30, 30] });
        }
    });

    document.getElementById('sidebarToggle').addEventListener('click', () => {
        document.getElementById('mapSidebar').classList.add('collapsed');
        document.getElementById('sidebarOpenBtn').style.display = 'flex';
    });
    document.getElementById('sidebarOpenBtn').addEventListener('click', () => {
        document.getElementById('mapSidebar').classList.remove('collapsed');
        document.getElementById('sidebarOpenBtn').style.display = 'none';
    });
});

function populateBrandFilter(stations) {
    const brands = [...new Set(stations.map(s => s.brand).filter(Boolean))].sort();
    const sel    = document.getElementById('filterBrand');
    brands.forEach(b => {
        const o = document.createElement('option');
        o.value = b; o.textContent = b;
        sel.appendChild(o);
    });
}

function applyFilters() {
    const search  = document.getElementById('searchInput').value.toLowerCase();
    const green   = document.getElementById('filterGreen').value;
    const brand   = document.getElementById('filterBrand').value;
    const avail   = document.getElementById('filterAvail').value;
    const minSock = parseInt(document.getElementById('filterSockets').value) || 0;

    const filtered = allStations.filter(s => {
        if (green   && s.green !== green) return false;
        if (brand   && s.brand !== brand) return false;
        if (avail === '1' && !s.available) return false;
        if (avail === '0' && s.available)  return false;
        if (minSock && (s.sockets || []).length < minSock) return false;
        if (search) {
            const h = ((s.title || '') + ' ' + (s.brand || '')).toLowerCase();
            if (!h.includes(search)) return false;
        }
        return true;
    });

    renderList(filtered);
    renderMarkers(filtered);
}

function renderList(stations) {
    document.getElementById('stationCount').textContent = stations.length.toLocaleString('tr') + ' istasyon';
    const list = document.getElementById('stationList');
    if (!stations.length) {
        list.innerHTML = '<div class="no-results"><i class="fa-solid fa-circle-xmark"></i><p>Sonuç bulunamadı</p></div>';
        return;
    }
    list.innerHTML = stations.slice(0, 120).map(s => `
        <div class="station-item" onclick="focusStation(${s.lat},${s.lng},${s.id})">
            <div class="station-item-icon ${s.green === 'EVET' ? 'green' : 'orange'}">
                <i class="fa-solid fa-charging-station"></i>
            </div>
            <div class="station-item-info">
                <div class="station-item-name">${escapeHtml(s.title)}</div>
                <div class="station-item-sub">${escapeHtml(s.brand)} · ${s.sockets ? s.sockets.length : 0} soket</div>
                <div class="station-item-status">
                    <span class="dot ${s.available ? 'dot-green' : 'dot-red'}"></span>
                    ${s.available ? 'Müsait' : 'Dolu'}
                    ${s.green === 'EVET' ? ' · <span class="green-tag"><i class="fa-solid fa-leaf"></i> Yeşil</span>' : ''}
                </div>
            </div>
        </div>
    `).join('');
}

function renderMarkers(stations) {
    markers.forEach(m => map.removeLayer(m));
    markers = [];

    const makeIcon = (color) => L.divIcon({
        className: '',
        html: `<div class="map-pin ${color}-pin"><i class="fa-solid fa-bolt"></i></div>`,
        iconSize: [28, 28], iconAnchor: [14, 14]
    });
    const gIcon = makeIcon('green');
    const oIcon = makeIcon('orange');
    const rIcon = makeIcon('red');

    stations.forEach(s => {
        if (!s.lat || !s.lng) return;
        const icon = s.available ? (s.green === 'EVET' ? gIcon : oIcon) : rIcon;
        const m = L.marker([s.lat, s.lng], { icon }).addTo(map).bindPopup(buildPopup(s));
        markers.push(m);
    });
}

function buildPopup(s) {
    const gmaps  = `https://www.google.com/maps/dir/?api=1&destination=${s.lat},${s.lng}`;
    const yandex = `https://yandex.com.tr/maps/?rtext=~${s.lat},${s.lng}&rtt=auto`;
    const sockCount = s.sockets ? s.sockets.length : 0;
    return `
        <div class="popup-card">
            <div style="display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap;">
                <span class="popup-badge ${s.available ? 'badge-green' : 'badge-red'}">${s.available ? 'Müsait' : 'Dolu'}</span>
                ${s.green === 'EVET' ? '<span class="popup-badge badge-leaf"><i class="fa-solid fa-leaf"></i> Yeşil</span>' : ''}
            </div>
            <h4 class="popup-title">${escapeHtml(s.title)}</h4>
            <p class="popup-brand"><i class="fa-solid fa-tag"></i> ${escapeHtml(s.brand)}</p>
            <p class="popup-no"><i class="fa-solid fa-plug"></i> ${sockCount} soket · ID: ${s.id}</p>
            <div class="popup-actions">
                <a href="${BASE_URL}/?page=station&id=${s.id}" class="popup-btn popup-btn-primary"><i class="fa-solid fa-circle-info"></i> Detay</a>
                <a href="${gmaps}" target="_blank" class="popup-btn popup-btn-nav"><i class="fa-solid fa-route"></i> Git</a>
            </div>
            <div class="popup-nav-row">
                <a href="${yandex}" target="_blank" class="popup-nav-link"><i class="fa-solid fa-map"></i> Yandex</a>
                <a href="https://waze.com/ul?ll=${s.lat},${s.lng}&navigate=yes" target="_blank" class="popup-nav-link"><i class="fa-solid fa-car"></i> Waze</a>
            </div>
        </div>`;
}

function focusStation(lat, lng, id) {
    map.setView([lat, lng], 16);
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
            icon: L.divIcon({ className: '', html: '<div class="map-pin user-pin"><i class="fa-solid fa-person"></i></div>', iconSize: [34,34], iconAnchor: [17,17] })
        }).addTo(map).bindPopup('Konumunuz').openPopup();
        map.setView([lat, lng], 13);
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
