<?php
$pageTitle = 'İstasyonlar – ŞarjNet';
$activePage = 'stations';
require __DIR__ . '/partials/header.php';
?>

<div class="page-hero mini">
    <div class="page-hero-content">
        <h1><i class="fa-solid fa-charging-station"></i> Şarj İstasyonları</h1>
        <p>EPDK lisanslı tüm şarj istasyonları – sarjtr.epdk.gov.tr</p>
    </div>
</div>

<div class="stations-page">
    <div class="filters-bar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="İstasyon adı veya marka ara...">
        </div>
        <div class="filter-group">
            <select id="filterGreen" class="filter-select">
                <option value="">Tüm Tipler</option>
                <option value="EVET">Yeşil Enerji</option>
                <option value="HAYIR">Standart</option>
            </select>
            <select id="filterBrand" class="filter-select">
                <option value="">Tüm Markalar</option>
            </select>
            <select id="filterAvail" class="filter-select">
                <option value="">Tüm Durumlar</option>
                <option value="1">Müsait</option>
                <option value="0">Dolu</option>
            </select>
        </div>
        <div class="results-count" id="resultsCount">Yükleniyor...</div>
    </div>

    <div class="stations-grid" id="stationsGrid">
        <?php for ($i = 0; $i < 12; $i++): ?>
        <div class="station-card-skeleton"></div>
        <?php endfor; ?>
    </div>

    <div class="pagination" id="pagination"></div>
</div>

<script>
let allStations = [];
let currentPage = 1;
const PER_PAGE  = 24;

document.addEventListener('DOMContentLoaded', () => {
    fetch(BASE_URL + '/api.php?action=stations')
        .then(r => r.json())
        .then(data => {
            allStations = data;
            populateFilters(data);
            renderPage();
        });

    ['searchInput','filterGreen','filterBrand','filterAvail'].forEach(id => {
        const el = document.getElementById(id);
        el.addEventListener(id === 'searchInput' ? 'input' : 'change', () => { currentPage = 1; renderPage(); });
    });
});

function populateFilters(stations) {
    const brands = [...new Set(stations.map(s => s.brand).filter(Boolean))].sort();
    const bSel   = document.getElementById('filterBrand');
    brands.forEach(b => { const o = document.createElement('option'); o.value = b; o.textContent = b; bSel.appendChild(o); });
}

function getFiltered() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const green  = document.getElementById('filterGreen').value;
    const brand  = document.getElementById('filterBrand').value;
    const avail  = document.getElementById('filterAvail').value;

    return allStations.filter(s => {
        if (green && s.green !== green) return false;
        if (brand && s.brand !== brand) return false;
        if (avail === '1' && !s.available) return false;
        if (avail === '0' && s.available)  return false;
        if (search) {
            const h = ((s.title || '') + ' ' + (s.brand || '')).toLowerCase();
            if (!h.includes(search)) return false;
        }
        return true;
    });
}

function renderPage() {
    const filtered = getFiltered();
    document.getElementById('resultsCount').textContent = filtered.length.toLocaleString('tr') + ' istasyon bulundu';
    const total = Math.ceil(filtered.length / PER_PAGE);
    const slice = filtered.slice((currentPage - 1) * PER_PAGE, currentPage * PER_PAGE);

    document.getElementById('stationsGrid').innerHTML = slice.map(s => {
        const sockCount = s.sockets ? s.sockets.length : 0;
        const isGreen   = s.green === 'EVET';
        return `
        <a href="${BASE_URL}/?page=station&id=${s.id}" class="station-card">
            <div class="card-header ${isGreen ? 'card-green' : 'card-orange'}">
                <div class="card-icon"><i class="fa-solid fa-charging-station"></i></div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                    ${isGreen ? '<span class="card-badge-leaf"><i class="fa-solid fa-leaf"></i> Yeşil</span>' : ''}
                    <span class="card-avail ${s.available ? 'avail-yes' : 'avail-no'}">
                        <span class="dot ${s.available ? 'dot-green' : 'dot-red'}"></span>
                        ${s.available ? 'Müsait' : 'Dolu'}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <h3 class="card-title">${escapeHtml(s.title)}</h3>
                <p class="card-brand"><i class="fa-solid fa-tag"></i> ${escapeHtml(s.brand)}</p>
                <p class="card-brand"><i class="fa-solid fa-plug"></i> ${sockCount} soket</p>
                <p class="card-no"><i class="fa-solid fa-hashtag"></i> ID: ${s.id}</p>
            </div>
            <div class="card-footer">
                <span class="card-action">Detayı Gör <i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </a>`;
    }).join('');

    renderPagination(total);
}

function renderPagination(total) {
    const pag = document.getElementById('pagination');
    if (total <= 1) { pag.innerHTML = ''; return; }
    let html = '';
    if (currentPage > 1) html += `<button onclick="goPage(${currentPage-1})" class="pag-btn"><i class="fa-solid fa-chevron-left"></i></button>`;
    const start = Math.max(1, currentPage - 2);
    const end   = Math.min(total, currentPage + 2);
    if (start > 1)   html += `<button onclick="goPage(1)" class="pag-btn">1</button><span class="pag-ellipsis">…</span>`;
    for (let i = start; i <= end; i++) html += `<button onclick="goPage(${i})" class="pag-btn ${i === currentPage ? 'active' : ''}">${i}</button>`;
    if (end < total) html += `<span class="pag-ellipsis">…</span><button onclick="goPage(${total})" class="pag-btn">${total}</button>`;
    if (currentPage < total) html += `<button onclick="goPage(${currentPage+1})" class="pag-btn"><i class="fa-solid fa-chevron-right"></i></button>`;
    pag.innerHTML = html;
}

function goPage(n) { currentPage = n; renderPage(); window.scrollTo({ top: 0, behavior: 'smooth' }); }

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
