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
            <button class="sidebar-toggle-btn" id="sidebarToggle" title="Sidebar'ı kapat">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </div>

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="İstasyon veya adres ara...">
        </div>

        <div class="filter-row">
            <select id="filterType" class="filter-select">
                <option value="">Tüm Tipler</option>
                <option value="HALKA_ACIK">Halka Açık</option>
                <option value="OZEL">Özel</option>
            </select>
            <select id="filterBrand" class="filter-select">
                <option value="">Tüm Markalar</option>
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
            <button class="map-ctrl-btn" id="clusterToggle" title="Kümelemeyi aç/kapat">
                <i class="fa-solid fa-layer-group"></i>
            </button>
        </div>

        <div id="fullMap" class="full-map"></div>
    </div>
</div>

<script>
let allStations = [];
let markers = [];
let map;
let userMarker = null;
let activeFilter = { type: '', brand: '', search: '' };

document.addEventListener('DOMContentLoaded', () => {
    map = L.map('fullMap', { zoomControl: false }).setView([39.0, 35.0], 6);
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(map);

    fetch('/api.php?action=stations')
        .then(r => r.json())
        .then(data => {
            allStations = data;
            populateBrandFilter(data);
            renderList(data);
            renderMarkers(data);
        });

    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('filterType').addEventListener('change', applyFilters);
    document.getElementById('filterBrand').addEventListener('change', applyFilters);

    document.getElementById('locateBtn').addEventListener('click', locateUser);

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
    const brands = [...new Set(stations.map(s => s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi).filter(Boolean))].sort();
    const sel = document.getElementById('filterBrand');
    brands.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b;
        opt.textContent = b;
        sel.appendChild(opt);
    });
}

function applyFilters() {
    activeFilter.type = document.getElementById('filterType').value;
    activeFilter.brand = document.getElementById('filterBrand').value;
    activeFilter.search = document.getElementById('searchInput').value.toLowerCase();

    const filtered = allStations.filter(s => {
        if (activeFilter.type && s.hizmetSekli !== activeFilter.type) return false;
        if (activeFilter.brand && s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi !== activeFilter.brand) return false;
        if (activeFilter.search) {
            const haystack = (s.sarjIstasyonuAdi + ' ' + s.adresMahalleCaddeSokak + ' ' + s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi).toLowerCase();
            if (!haystack.includes(activeFilter.search)) return false;
        }
        return true;
    });

    renderList(filtered);
    renderMarkers(filtered);
}

function renderList(stations) {
    document.getElementById('stationCount').textContent = stations.length.toLocaleString('tr') + ' istasyon';
    const list = document.getElementById('stationList');
    if (stations.length === 0) {
        list.innerHTML = '<div class="no-results"><i class="fa-solid fa-circle-xmark"></i><p>Sonuç bulunamadı</p></div>';
        return;
    }
    list.innerHTML = stations.slice(0, 100).map(s => `
        <div class="station-item" onclick="focusStation(${s.lat}, ${s.lng}, '${escapeHtml(s.sarjIstasyonuNo)}')">
            <div class="station-item-icon ${s.hizmetSekli === 'HALKA_ACIK' ? 'green' : 'orange'}">
                <i class="fa-solid fa-charging-station"></i>
            </div>
            <div class="station-item-info">
                <div class="station-item-name">${escapeHtml(s.sarjIstasyonuAdi)}</div>
                <div class="station-item-sub">${escapeHtml(s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi)} · ${s.hizmetSekli === 'HALKA_ACIK' ? 'Halka Açık' : 'Özel'}</div>
                <div class="station-item-addr">${escapeHtml(s.adresMahalleCaddeSokak)}</div>
            </div>
        </div>
    `).join('');
}

function renderMarkers(stations) {
    markers.forEach(m => map.removeLayer(m));
    markers = [];

    const greenIcon = L.divIcon({ className: '', html: '<div class="map-pin green-pin"><i class="fa-solid fa-bolt"></i></div>', iconSize: [30, 30], iconAnchor: [15, 15] });
    const orangeIcon = L.divIcon({ className: '', html: '<div class="map-pin orange-pin"><i class="fa-solid fa-bolt"></i></div>', iconSize: [30, 30], iconAnchor: [15, 15] });

    stations.forEach(s => {
        if (!s.lat || !s.lng) return;
        const icon = s.hizmetSekli === 'HALKA_ACIK' ? greenIcon : orangeIcon;
        const m = L.marker([s.lat, s.lng], { icon })
            .addTo(map)
            .bindPopup(buildPopup(s));
        markers.push(m);
    });
}

function buildPopup(s) {
    const lat = s.lat, lng = s.lng;
    const gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    const yandexUrl = `https://yandex.com.tr/maps/?rtext=~${lat},${lng}&rtt=auto`;
    return `
        <div class="popup-card">
            <div class="popup-badge ${s.hizmetSekli === 'HALKA_ACIK' ? 'badge-green' : 'badge-orange'}">
                ${s.hizmetSekli === 'HALKA_ACIK' ? 'Halka Açık' : 'Özel'}
            </div>
            <h4 class="popup-title">${escapeHtml(s.sarjIstasyonuAdi)}</h4>
            <p class="popup-brand"><i class="fa-solid fa-tag"></i> ${escapeHtml(s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi)}</p>
            <p class="popup-addr"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(s.adresMahalleCaddeSokak)}</p>
            <p class="popup-no"><i class="fa-solid fa-hashtag"></i> ${escapeHtml(s.sarjIstasyonuNo)}</p>
            <div class="popup-actions">
                <a href="/?page=station&no=${encodeURIComponent(s.sarjIstasyonuNo)}" class="popup-btn popup-btn-primary">
                    <i class="fa-solid fa-circle-info"></i> Detay
                </a>
                <a href="${gmapsUrl}" target="_blank" class="popup-btn popup-btn-nav">
                    <i class="fa-solid fa-route"></i> Git
                </a>
            </div>
            <div class="popup-nav-row">
                <a href="${yandexUrl}" target="_blank" class="popup-nav-link">
                    <i class="fa-solid fa-map"></i> Yandex Maps
                </a>
                <a href="https://waze.com/ul?ll=${lat},${lng}&navigate=yes" target="_blank" class="popup-nav-link">
                    <i class="fa-solid fa-car"></i> Waze
                </a>
            </div>
        </div>
    `;
}

function focusStation(lat, lng, no) {
    if (!lat || !lng) return;
    map.setView([lat, lng], 16);
    markers.forEach(m => {
        if (Math.abs(m.getLatLng().lat - lat) < 0.001 && Math.abs(m.getLatLng().lng - lng) < 0.001) {
            m.openPopup();
        }
    });
}

function locateUser() {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(pos => {
        const { latitude: lat, longitude: lng } = pos.coords;
        if (userMarker) map.removeLayer(userMarker);
        userMarker = L.marker([lat, lng], {
            icon: L.divIcon({ className: '', html: '<div class="map-pin user-pin"><i class="fa-solid fa-person"></i></div>', iconSize: [34, 34], iconAnchor: [17, 17] })
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
