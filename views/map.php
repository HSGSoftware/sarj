<?php require __DIR__ . '/partials/header.php'; ?>

<header class="app-bar">
    <div class="app-bar-brand">
        <div class="app-bar-icon"><i class="fa-solid fa-bolt"></i></div>
        <span class="app-bar-title">Harita</span>
    </div>
    <button class="app-bar-action" id="locateBtn"><i class="fa-solid fa-location-crosshairs"></i></button>
</header>

<!-- Harita tam ekran, tab-bar üstünde duruyor -->
<div class="map-wrapper">
    <div id="fullMap"></div>

    <div class="map-fabs">
        <button class="map-fab" id="fitBtn" title="Türkiye'ye sığdır">
            <i class="fa-solid fa-expand"></i>
        </button>
    </div>

    <!-- Bottom sheet -->
    <div class="map-sheet" id="mapSheet">
        <div class="sheet-handle-wrap" id="sheetHandle">
            <div class="sheet-handle"></div>
        </div>
        <div class="sheet-search">
            <div class="sheet-search-input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="İstasyon veya marka ara...">
                <i class="fa-solid fa-xmark" id="clearSearch" style="cursor:pointer;display:none"></i>
            </div>
        </div>
        <div class="sheet-filters">
            <button class="filter-pill active" data-filter="all">Tümü</button>
            <button class="filter-pill" data-filter="avail">Müsait</button>
            <button class="filter-pill" data-filter="green">Yeşil Enerji</button>
            <button class="filter-pill" data-filter="AC">AC</button>
            <button class="filter-pill" data-filter="DC">DC</button>
        </div>
        <div class="sheet-count" id="sheetCount"><i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...</div>
        <div class="sheet-list" id="sheetList"></div>
    </div>
</div>

<?php require __DIR__ . '/partials/tab_bar.php'; ?>

<script>
let allStations = [], clusterGroup = null, map, userMarker = null;
let sheetExpanded = false, activeFilter = 'all', searchVal = '';

document.addEventListener('DOMContentLoaded', () => {
    // Harita init
    map = L.map('fullMap', {
        zoomControl: false,
        preferCanvas: true,
        tap: false          // iOS dokunma çakışmasını önle
    }).setView([39.0, 35.0], 6);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OSM',
        maxZoom: 19
    }).addTo(map);

    // Veri yükle
    fetch(PAGES.api + '?action=stations')
        .then(r => r.json())
        .then(data => {
            allStations = data;
            applyFilters();
            document.getElementById('sheetCount').textContent = data.length.toLocaleString('tr') + ' istasyon';
        })
        .catch(() => {
            document.getElementById('sheetCount').textContent = 'Veri yüklenemedi';
        });

    // Sheet toggle
    document.getElementById('sheetHandle').addEventListener('click', toggleSheet);

    // Arama
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

    // Filtreler
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
        map.setView([39.0, 35.0], 6);
    });
});

function toggleSheet() {
    sheetExpanded = !sheetExpanded;
    document.getElementById('mapSheet').classList.toggle('expanded', sheetExpanded);
}

function applyFilters() {
    const filtered = allStations.filter(s => {
        if (activeFilter === 'avail' && !s.available) return false;
        if (activeFilter === 'green' && s.green !== 'EVET') return false;
        if (activeFilter === 'AC' && !(s.sockets||[]).some(sk => sk.type === 'AC')) return false;
        if (activeFilter === 'DC' && !(s.sockets||[]).some(sk => sk.type === 'DC')) return false;
        if (searchVal) {
            const h = ((s.title||'') + ' ' + (s.brand||'')).toLowerCase();
            if (!h.includes(searchVal)) return false;
        }
        return true;
    });

    document.getElementById('sheetCount').textContent = filtered.length.toLocaleString('tr') + ' istasyon';
    renderMarkers(filtered);
    renderList(filtered);
}

function renderMarkers(stations) {
    if (clusterGroup) {
        map.removeLayer(clusterGroup);
        clusterGroup = null;
    }

    clusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        chunkInterval: 200,
        chunkDelay: 50,
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        iconCreateFunction(cluster) {
            const n = cluster.getChildCount();
            const sz = n < 20 ? 36 : n < 100 ? 42 : 48;
            const col = n < 20 ? '#22c55e' : n < 100 ? '#f97316' : '#ef4444';
            return L.divIcon({
                html: `<div style="width:${sz}px;height:${sz}px;border-radius:50%;background:${col};display:flex;align-items:center;justify-content:center;color:#000;font-weight:800;font-size:${sz<40?'11':'13'}px;box-shadow:0 2px 8px rgba(0,0,0,.4);border:2px solid rgba(255,255,255,.6)">${n}</div>`,
                className: '',
                iconSize: [sz, sz],
                iconAnchor: [sz/2, sz/2]
            });
        }
    });

    const gIcon = L.divIcon({ className:'', html:'<div style="width:20px;height:20px;border-radius:50%;background:#22c55e;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.5)"></div>', iconSize:[20,20], iconAnchor:[10,10] });
    const oIcon = L.divIcon({ className:'', html:'<div style="width:20px;height:20px;border-radius:50%;background:#f97316;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.5)"></div>', iconSize:[20,20], iconAnchor:[10,10] });
    const rIcon = L.divIcon({ className:'', html:'<div style="width:20px;height:20px;border-radius:50%;background:#ef4444;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.5)"></div>', iconSize:[20,20], iconAnchor:[10,10] });

    const markers = stations
        .filter(s => s.lat && s.lng)
        .map(s => {
            const icon = s.available ? (s.green === 'EVET' ? gIcon : oIcon) : rIcon;
            const m = L.marker([s.lat, s.lng], { icon });
            m.bindPopup(buildPopup(s), { maxWidth: 240, closeButton: false });
            return m;
        });

    clusterGroup.addLayers(markers);
    map.addLayer(clusterGroup);
}

function buildPopup(s) {
    return `<div style="font-family:-apple-system,sans-serif;min-width:180px">
        <div style="font-weight:700;font-size:.9rem;margin-bottom:4px;line-height:1.3">${esc(s.title)}</div>
        <div style="font-size:.78rem;color:#64748b;margin-bottom:8px">${esc(s.brand)} · ${s.sockets?s.sockets.length:0} soket</div>
        <div style="display:flex;gap:4px;margin-bottom:8px">
            <span style="padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;background:${s.available?'#dcfce7':'#fee2e2'};color:${s.available?'#16a34a':'#dc2626'}">${s.available?'● Müsait':'● Dolu'}</span>
            ${s.green==='EVET'?'<span style="padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;background:#d1fae5;color:#059669">🌿 Yeşil</span>':''}
        </div>
        <div style="display:flex;gap:6px">
            <a href="${PAGES.station}?id=${s.id}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:4px;padding:7px;border-radius:8px;background:#22c55e;color:#000;font-size:.78rem;font-weight:700;text-decoration:none">Detay</a>
            <a href="https://www.google.com/maps/dir/?api=1&destination=${s.lat},${s.lng}" target="_blank" style="flex:1;display:flex;align-items:center;justify-content:center;gap:4px;padding:7px;border-radius:8px;background:#3b82f6;color:#fff;font-size:.78rem;font-weight:700;text-decoration:none">Git</a>
        </div>
    </div>`;
}

function renderList(stations) {
    const list = document.getElementById('sheetList');
    if (!stations.length) {
        list.innerHTML = '<div style="text-align:center;padding:24px;color:var(--text-muted)"><i class="fa-solid fa-circle-xmark" style="font-size:1.5rem;margin-bottom:8px;display:block"></i>Sonuç bulunamadı</div>';
        return;
    }
    list.innerHTML = stations.slice(0, 80).map(s => {
        const color = !s.available ? '#ef4444' : (s.green === 'EVET' ? '#22c55e' : '#f97316');
        return `<div class="sheet-station-item" onclick="goToStation(${s.lat},${s.lng},${s.id})">
            <div class="ssi-icon" style="background:${color}20;color:${color}">
                <i class="fa-solid fa-charging-station"></i>
            </div>
            <div class="ssi-info">
                <div class="ssi-name">${esc(s.title)}</div>
                <div class="ssi-brand">${esc(s.brand)}</div>
                <div class="ssi-status">
                    <span class="dot" style="background:${color}"></span>
                    ${s.available?'Müsait':'Dolu'}
                    · ${s.sockets?s.sockets.length:0} soket
                    ${s.green==='EVET'?' · 🌿':''}
                </div>
            </div>
            <div class="ssi-right">
                <i class="fa-solid fa-chevron-right" style="color:var(--text-dim)"></i>
            </div>
        </div>`;
    }).join('');
}

function goToStation(lat, lng, id) {
    map.setView([lat, lng], 16);
    document.getElementById('mapSheet').classList.remove('expanded');
    sheetExpanded = false;
}

function locateUser() {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(pos => {
        const { latitude: lat, longitude: lng } = pos.coords;
        if (userMarker) map.removeLayer(userMarker);
        userMarker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: '',
                html: '<div style="width:24px;height:24px;border-radius:50%;background:#3b82f6;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>',
                iconSize: [24,24], iconAnchor: [12,12]
            })
        }).addTo(map).bindPopup('📍 Konumunuz').openPopup();
        map.setView([lat, lng], 13);
    });
}

function esc(s) {
    if (!s) return '';
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
