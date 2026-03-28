<?php require __DIR__ . '/partials/header.php'; ?>

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
    </div>

    <div class="filter-pills" id="brandPills"></div>

    <div class="results-info" id="resultsInfo">Yükleniyor...</div>

    <div class="station-cards" id="stationCards">
        <?php for ($i=0;$i<8;$i++): ?><div class="station-card-skeleton"></div><?php endfor; ?>
    </div>

    <div class="pagination" id="pagination"></div>

</div>
</div>

<?php require __DIR__ . '/partials/tab_bar.php'; ?>

<script>
let allStations=[], currentPage=1, filterMode='all', filterBrand='', searchVal='';
const PER_PAGE=20;

document.addEventListener('DOMContentLoaded', () => {
    fetch(PAGES.api + '?action=stations').then(r=>r.json()).then(data => {
        allStations=data; buildBrandPills(data); render();
    });
    document.getElementById('searchInput').addEventListener('input', e => { searchVal=e.target.value.toLowerCase(); currentPage=1; render(); });
    document.querySelectorAll('.filter-pill[data-filter]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-pill[data-filter]').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active'); filterMode=btn.dataset.filter; currentPage=1; render();
        });
    });
});

function buildBrandPills(stations) {
    const brands=[...new Set(stations.map(s=>s.brand).filter(Boolean))].sort().slice(0,12);
    const c=document.getElementById('brandPills');
    c.innerHTML='<button class="filter-pill active" data-brand="">Tüm Markalar</button>'+brands.map(b=>`<button class="filter-pill" data-brand="${esc(b)}">${esc(b)}</button>`).join('');
    c.querySelectorAll('.filter-pill').forEach(btn=>{
        btn.addEventListener('click',()=>{
            c.querySelectorAll('.filter-pill').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active'); filterBrand=btn.dataset.brand; currentPage=1; render();
        });
    });
}

function getFiltered() {
    return allStations.filter(s=>{
        if(filterMode==='avail'&&!s.available) return false;
        if(filterMode==='green'&&s.green!=='EVET') return false;
        if(filterBrand&&s.brand!==filterBrand) return false;
        if(searchVal&&!((s.title||'')+' '+(s.brand||'')).toLowerCase().includes(searchVal)) return false;
        return true;
    });
}

function render() {
    const filtered=getFiltered();
    document.getElementById('resultsInfo').textContent=filtered.length.toLocaleString('tr')+' istasyon bulundu';
    const total=Math.ceil(filtered.length/PER_PAGE);
    const slice=filtered.slice((currentPage-1)*PER_PAGE,currentPage*PER_PAGE);
    document.getElementById('stationCards').innerHTML=slice.map(s=>`
        <a href="${PAGES.station}?id=${s.id}" class="station-card">
            <div class="sc-accent ${s.green==='EVET'?'green':'orange'}"></div>
            <div class="sc-body">
                <div class="sc-top">
                    <span class="sc-name">${esc(s.title)}</span>
                    <span class="sc-status ${s.available?'avail':'busy'}"><span class="dot ${s.available?'dot-green':'dot-red'}"></span>${s.available?'Müsait':'Dolu'}</span>
                </div>
                <div class="sc-meta">
                    <span class="sc-tag"><i class="fa-solid fa-tag"></i> ${esc(s.brand)}</span>
                    ${s.green==='EVET'?'<span class="sc-tag" style="color:var(--green)"><i class="fa-solid fa-leaf"></i> Yeşil</span>':''}
                </div>
            </div>
            <div class="sc-right">
                <span class="sc-sockets">${s.sockets?s.sockets.length:0} soket</span>
                <span class="sc-arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </div>
        </a>`).join('');
    renderPagination(total);
}

function renderPagination(total) {
    const p=document.getElementById('pagination');
    if(total<=1){p.innerHTML='';return;}
    let h='';
    if(currentPage>1) h+=`<button onclick="goPage(${currentPage-1})" class="pag-btn"><i class="fa-solid fa-chevron-left"></i></button>`;
    const s=Math.max(1,currentPage-2),e=Math.min(total,currentPage+2);
    if(s>1) h+=`<button onclick="goPage(1)" class="pag-btn">1</button><span class="pag-ellipsis">…</span>`;
    for(let i=s;i<=e;i++) h+=`<button onclick="goPage(${i})" class="pag-btn ${i===currentPage?'active':''}">${i}</button>`;
    if(e<total) h+=`<span class="pag-ellipsis">…</span><button onclick="goPage(${total})" class="pag-btn">${total}</button>`;
    if(currentPage<total) h+=`<button onclick="goPage(${currentPage+1})" class="pag-btn"><i class="fa-solid fa-chevron-right"></i></button>`;
    p.innerHTML=h;
}

function goPage(n){currentPage=n;render();window.scrollTo({top:0,behavior:'smooth'});}
function esc(s){if(!s)return '';return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
