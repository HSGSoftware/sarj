<?php
$pageTitle  = 'İstasyonlar – ŞarjNet';
$activePage = 'stations';
require __DIR__ . '/partials/header.php';
?>

<header class="app-bar">
    <div class="app-bar-brand">
        <div class="app-bar-icon"><i class="fa-solid fa-bolt"></i></div>
        <span class="app-bar-title">İstasyonlar</span>
    </div>
</header>

<div class="page-content">
    <div class="station-list-wrap">

        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass" style="color:var(--text-dim)"></i>
            <input type="text" id="searchInput" placeholder="İstasyon adı veya marka ara...">
        </div>

        <div class="filter-pills">
            <button class="filter-pill active" data-filter="all">Tümü</button>
            <button class="filter-pill" data-filter="avail">Müsait</button>
            <button class="filter-pill" data-filter="green">Yeşil Enerji</button>
            <button class="filter-pill" data-brand="">Marka</button>
        </div>

        <!-- Dinamik marka filtreleri -->
        <div class="filter-pills" id="brandPills" style="display:none"></div>

        <div class="results-info" id="resultsInfo">Yükleniyor...</div>

        <div class="station-cards" id="stationCards">
            <?php for ($i=0; $i<8; $i++): ?>
            <div class="station-card-skeleton"></div>
            <?php endfor; ?>
        </div>

        <div class="pagination" id="pagination"></div>

    </div>
</div>

<?php require __DIR__ . '/partials/tab_bar.php'; ?>

<script>
let allStations = [], currentPage = 1;
const PER_PAGE = 20;
let filterMode = 'all';
let filterBrand = '';
let searchVal = '';

document.addEventListener('DOMContentLoaded', () => {
    fetch(BASE_URL + '/api.php?action=stations')
        .then(r => r.json())
        .then(data => {
            allStations = data;
            buildBrandPills(data);
            render();
        });

    document.getElementById('searchInput').addEventListener('input', e => {
        searchVal = e.target.value.toLowerCase();
        currentPage = 1; render();
    });

    document.querySelectorAll('.filter-pill').forEach(btn => {
        if (btn.dataset.filter !== undefined) {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-pill[data-filter]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterMode = btn.dataset.filter;
                currentPage = 1; render();
            });
        }
    });
});

function buildBrandPills(stations) {
    const brands = [...new Set(stations.map(s=>s.brand).filter(Boolean))].sort().slice(0,12);
    const container = document.getElementById('brandPills');
    container.style.display = '';
    container.innerHTML = `<button class="filter-pill active" data-brand="">Tüm Markalar</button>` +
        brands.map(b => `<button class="filter-pill" data-brand="${escapeHtml(b)}">${escapeHtml(b)}</button>`).join('');
    container.querySelectorAll('.filter-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            container.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterBrand = btn.dataset.brand;
            currentPage = 1; render();
        });
    });
}

function getFiltered() {
    return allStations.filter(s => {
        if (filterMode === 'avail' && !s.available) return false;
        if (filterMode === 'green' && s.green !== 'EVET') return false;
        if (filterBrand && s.brand !== filterBrand) return false;
        if (searchVal) {
            if (!((s.title||'') + ' ' + (s.brand||'')).toLowerCase().includes(searchVal)) return false;
        }
        return true;
    });
}

function render() {
    const filtered = getFiltered();
    document.getElementById('resultsInfo').textContent = filtered.length.toLocaleString('tr') + ' istasyon bulundu';
    const total = Math.ceil(filtered.length / PER_PAGE);
    const slice = filtered.slice((currentPage-1)*PER_PAGE, currentPage*PER_PAGE);

    document.getElementById('stationCards').innerHTML = slice.map(s => {
        const green = s.green === 'EVET';
        const avail = s.available;
        return `
        <a href="${BASE_URL}/?page=station&id=${s.id}" class="station-card">
            <div class="sc-accent ${green ? 'green' : 'orange'}"></div>
            <div class="sc-body">
                <div class="sc-top">
                    <span class="sc-name">${escapeHtml(s.title)}</span>
                    <span class="sc-status ${avail ? 'avail' : 'busy'}">
                        <span class="dot ${avail ? 'dot-green' : 'dot-red'}"></span>
                        ${avail ? 'Müsait' : 'Dolu'}
                    </span>
                </div>
                <div class="sc-meta">
                    <span class="sc-tag"><i class="fa-solid fa-tag"></i> ${escapeHtml(s.brand)}</span>
                    ${green ? '<span class="sc-tag" style="color:var(--green)"><i class="fa-solid fa-leaf"></i> Yeşil</span>' : ''}
                </div>
            </div>
            <div class="sc-right">
                <span class="sc-sockets">${s.sockets ? s.sockets.length : 0} soket</span>
                <span class="sc-arrow"><i class="fa-solid fa-chevron-right"></i></span>
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
    const s = Math.max(1, currentPage-2), e = Math.min(total, currentPage+2);
    if (s > 1) html += `<button onclick="goPage(1)" class="pag-btn">1</button><span class="pag-ellipsis">…</span>`;
    for (let i=s; i<=e; i++) html += `<button onclick="goPage(${i})" class="pag-btn ${i===currentPage?'active':''}">${i}</button>`;
    if (e < total) html += `<span class="pag-ellipsis">…</span><button onclick="goPage(${total})" class="pag-btn">${total}</button>`;
    if (currentPage < total) html += `<button onclick="goPage(${currentPage+1})" class="pag-btn"><i class="fa-solid fa-chevron-right"></i></button>`;
    pag.innerHTML = html;
}

function goPage(n) { currentPage = n; render(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
function escapeHtml(s) { if(!s) return ''; return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
