<?php
$pageTitle = 'ŞarjNet – Türkiye Şarj İstasyonu Platformu';
$activePage = 'home';
require __DIR__ . '/partials/header.php';
?>

<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-badge">
            <i class="fa-solid fa-bolt"></i> Türkiye'nin #1 Şarj Platformu
        </div>
        <h1 class="hero-title">Tüm Şarj İstasyonları<br><span class="gradient-text">Tek Platformda</span></h1>
        <p class="hero-subtitle">EPDK lisanslı 12.000+ şarj istasyonunu haritada görün, navigasyon başlatın ve demo ödeme yapın.</p>
        <div class="hero-actions">
            <a href="/?page=map" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-map-location-dot"></i> Haritayı Aç
            </a>
            <a href="/?page=stations" class="btn btn-outline btn-lg">
                <i class="fa-solid fa-list"></i> İstasyonları Listele
            </a>
        </div>
        <div class="hero-stats">
            <div class="stat">
                <span class="stat-num" id="heroTotalCount">—</span>
                <span class="stat-label">İstasyon</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num" id="heroAvailCount">—</span>
                <span class="stat-label">Müsait</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num" id="heroBrandCount">—</span>
                <span class="stat-label">Marka</span>
            </div>
        </div>
    </div>
</section>

<section class="map-preview">
    <div class="section-container">
        <div class="section-header">
            <h2>Yakınınızdaki İstasyonlar</h2>
            <a href="/?page=map" class="btn btn-sm btn-ghost">Tamamını gör <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div id="homeMap" class="home-map"></div>
    </div>
</section>

<section class="features">
    <div class="section-container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon green"><i class="fa-solid fa-map-location-dot"></i></div>
                <h3>Harita Görünümü</h3>
                <p>12.000+ istasyonu interaktif haritada görün, filtreleyin ve yakınınızdakileri bulun.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon blue"><i class="fa-solid fa-route"></i></div>
                <h3>Navigasyon</h3>
                <p>İstasyona tek tıkla Google Maps, Yandex Maps veya Waze üzerinden yol tarifi alın.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon purple"><i class="fa-solid fa-plug-circle-check"></i></div>
                <h3>Gerçek Zamanlı Durum</h3>
                <p>Soket müsaitliği, fiyat bilgisi ve AC/DC tipi ile güç değerlerini görüntüleyin.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon orange"><i class="fa-solid fa-credit-card"></i></div>
                <h3>Demo Ödeme</h3>
                <p>Şarj ücreti demo ödemesini tamamlayın, ödeme akışını test edin.</p>
            </div>
        </div>
    </div>
</section>

<section class="brands-section">
    <div class="section-container">
        <h2 class="section-title-center">Platformdaki Markalar</h2>
        <div class="brands-grid" id="brandsGrid">
            <?php for ($i = 0; $i < 6; $i++): ?><div class="brand-skeleton"></div><?php endfor; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    fetch('/api.php?action=stations')
        .then(r => r.json())
        .then(stations => {
            document.getElementById('heroTotalCount').textContent = stations.length.toLocaleString('tr');
            const avail = stations.filter(s => s.available).length;
            document.getElementById('heroAvailCount').textContent = avail.toLocaleString('tr');
            const brands = [...new Set(stations.map(s => s.brand).filter(Boolean))];
            document.getElementById('heroBrandCount').textContent = brands.length;

            const grid = document.getElementById('brandsGrid');
            grid.innerHTML = '';
            brands.slice(0, 12).forEach(b => {
                const div = document.createElement('div');
                div.className = 'brand-chip';
                div.innerHTML = `<i class="fa-solid fa-bolt-lightning"></i> ${escapeHtml(b)}`;
                grid.appendChild(div);
            });

            initHomeMap(stations.slice(0, 500));
        });
});

function initHomeMap(stations) {
    const map = L.map('homeMap', { zoomControl: true, scrollWheelZoom: false })
                 .setView([39.0, 35.0], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 18
    }).addTo(map);

    const greenIcon  = L.divIcon({ className: '', html: '<div class="map-pin green-pin"><i class="fa-solid fa-bolt"></i></div>', iconSize: [28,28], iconAnchor: [14,14] });
    const orangeIcon = L.divIcon({ className: '', html: '<div class="map-pin orange-pin"><i class="fa-solid fa-bolt"></i></div>', iconSize: [28,28], iconAnchor: [14,14] });

    stations.forEach(s => {
        if (!s.lat || !s.lng) return;
        const icon = s.green === 'EVET' ? greenIcon : orangeIcon;
        L.marker([s.lat, s.lng], { icon }).addTo(map)
         .bindPopup(`<div class="popup-content"><strong>${escapeHtml(s.title)}</strong><br><small>${escapeHtml(s.brand)}</small><br><a href="/?page=station&id=${s.id}" class="popup-link">Detay →</a></div>`);
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
