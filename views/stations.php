<?php
$pageTitle = 'İstasyonlar – ŞarjNet';
$activePage = 'stations';
require __DIR__ . '/partials/header.php';
?>

<div class="page-hero mini">
    <div class="page-hero-content">
        <h1><i class="fa-solid fa-charging-station"></i> Şarj İstasyonları</h1>
        <p>EPDK lisanslı tüm şarj istasyonları</p>
    </div>
</div>

<div class="stations-page">
    <div class="filters-bar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="İstasyon adı, adres veya marka ara...">
        </div>
        <div class="filter-group">
            <select id="filterType" class="filter-select">
                <option value="">Tüm Tipler</option>
                <option value="HALKA_ACIK">Halka Açık</option>
                <option value="OZEL">Özel</option>
            </select>
            <select id="filterBrand" class="filter-select">
                <option value="">Tüm Markalar</option>
            </select>
            <select id="filterCity" class="filter-select">
                <option value="">Tüm Şehirler</option>
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
const PER_PAGE = 24;

document.addEventListener('DOMContentLoaded', () => {
    fetch('/api.php?action=stations')
        .then(r => r.json())
        .then(data => {
            allStations = data;
            populateFilters(data);
            renderPage();
        });

    document.getElementById('searchInput').addEventListener('input', () => { currentPage = 1; renderPage(); });
    document.getElementById('filterType').addEventListener('change', () => { currentPage = 1; renderPage(); });
    document.getElementById('filterBrand').addEventListener('change', () => { currentPage = 1; renderPage(); });
    document.getElementById('filterCity').addEventListener('change', () => { currentPage = 1; renderPage(); });
});

function populateFilters(stations) {
    const brands = [...new Set(stations.map(s => s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi).filter(Boolean))].sort();
    const cities = [...new Set(stations.map(s => extractCity(s.adresMahalleCaddeSokak)).filter(Boolean))].sort();

    const bSel = document.getElementById('filterBrand');
    brands.forEach(b => { const o = document.createElement('option'); o.value = b; o.textContent = b; bSel.appendChild(o); });

    const cSel = document.getElementById('filterCity');
    cities.forEach(c => { const o = document.createElement('option'); o.value = c; o.textContent = c; cSel.appendChild(o); });
}

function extractCity(addr) {
    if (!addr) return '';
    const m = addr.match(/\/\s*([A-ZÇŞĞÜÖİA-Za-zçşğüöı]+)$/);
    return m ? m[1].toUpperCase() : '';
}

function getFiltered() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const type = document.getElementById('filterType').value;
    const brand = document.getElementById('filterBrand').value;
    const city = document.getElementById('filterCity').value;

    return allStations.filter(s => {
        if (type && s.hizmetSekli !== type) return false;
        if (brand && s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi !== brand) return false;
        if (city && extractCity(s.adresMahalleCaddeSokak) !== city) return false;
        if (search) {
            const h = (s.sarjIstasyonuAdi + ' ' + s.adresMahalleCaddeSokak + ' ' + s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi).toLowerCase();
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

    const grid = document.getElementById('stationsGrid');
    grid.innerHTML = slice.map(s => `
        <a href="/?page=station&no=${encodeURIComponent(s.sarjIstasyonuNo)}" class="station-card">
            <div class="card-header ${s.hizmetSekli === 'HALKA_ACIK' ? 'card-green' : 'card-orange'}">
                <div class="card-icon"><i class="fa-solid fa-charging-station"></i></div>
                <span class="card-badge">${s.hizmetSekli === 'HALKA_ACIK' ? 'Halka Açık' : 'Özel'}</span>
            </div>
            <div class="card-body">
                <h3 class="card-title">${escapeHtml(s.sarjIstasyonuAdi)}</h3>
                <p class="card-brand"><i class="fa-solid fa-tag"></i> ${escapeHtml(s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi)}</p>
                <p class="card-addr"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(s.adresMahalleCaddeSokak)}</p>
                <p class="card-no"><i class="fa-solid fa-hashtag"></i> ${escapeHtml(s.sarjIstasyonuNo)}</p>
            </div>
            <div class="card-footer">
                <span class="card-action">Detayı Gör <i class="fa-solid fa-arrow-right"></i></span>
            </div>
        </a>
    `).join('');

    renderPagination(total);
}

function renderPagination(total) {
    const pag = document.getElementById('pagination');
    if (total <= 1) { pag.innerHTML = ''; return; }
    let html = '';
    if (currentPage > 1) html += `<button onclick="goPage(${currentPage-1})" class="pag-btn"><i class="fa-solid fa-chevron-left"></i></button>`;
    const start = Math.max(1, currentPage - 2);
    const end = Math.min(total, currentPage + 2);
    if (start > 1) html += `<button onclick="goPage(1)" class="pag-btn">1</button><span class="pag-ellipsis">…</span>`;
    for (let i = start; i <= end; i++) {
        html += `<button onclick="goPage(${i})" class="pag-btn ${i === currentPage ? 'active' : ''}">${i}</button>`;
    }
    if (end < total) html += `<span class="pag-ellipsis">…</span><button onclick="goPage(${total})" class="pag-btn">${total}</button>`;
    if (currentPage < total) html += `<button onclick="goPage(${currentPage+1})" class="pag-btn"><i class="fa-solid fa-chevron-right"></i></button>`;
    pag.innerHTML = html;
}

function goPage(n) {
    currentPage = n;
    renderPage();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
