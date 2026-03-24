<?php
$pageTitle = 'ŞarjNet – Türkiye Şarj İstasyonu Platformu';
$activePage = 'home';
require __DIR__ . '/partials/header.php';
?>

<section class="hero">
    <div class="hero-bg">
        <div class="hero-particles"></div>
    </div>
    <div class="hero-content">
        <div class="hero-badge">
            <i class="fa-solid fa-bolt"></i> Türkiye'nin #1 Şarj Platformu
        </div>
        <h1 class="hero-title">Tüm Şarj İstasyonları<br><span class="gradient-text">Tek Platformda</span></h1>
        <p class="hero-subtitle">EPDK lisanslı tüm şarj istasyonlarını haritada görün, navigasyon başlatın ve demo ödeme yapın.</p>
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
                <span class="stat-num" id="heroPublicCount">—</span>
                <span class="stat-label">Halka Açık</span>
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
                <div class="feature-icon green">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <h3>Harita Görünümü</h3>
                <p>Tüm istasyonları interaktif haritada görün, filtreleyin ve yakınınızdakileri bulun.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon blue">
                    <i class="fa-solid fa-route"></i>
                </div>
                <h3>Navigasyon</h3>
                <p>İstasyona tek tıkla Google Maps veya Yandex Maps üzerinden navigasyon başlatın.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon purple">
                    <i class="fa-solid fa-plug-circle-check"></i>
                </div>
                <h3>Soket Bilgileri</h3>
                <p>AC/DC soket tipleri, güç bilgileri ve uyumlu araç listesini görüntüleyin.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon orange">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
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
            <div class="brand-skeleton"></div>
            <div class="brand-skeleton"></div>
            <div class="brand-skeleton"></div>
            <div class="brand-skeleton"></div>
            <div class="brand-skeleton"></div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    fetch('/api.php?action=stations')
        .then(r => r.json())
        .then(stations => {
            document.getElementById('heroTotalCount').textContent = stations.length.toLocaleString('tr');
            const pub = stations.filter(s => s.hizmetSekli === 'HALKA_ACIK').length;
            document.getElementById('heroPublicCount').textContent = pub.toLocaleString('tr');
            const brands = [...new Set(stations.map(s => s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi).filter(Boolean))];
            document.getElementById('heroBrandCount').textContent = brands.length;

            const grid = document.getElementById('brandsGrid');
            grid.innerHTML = '';
            brands.slice(0, 10).forEach(b => {
                const div = document.createElement('div');
                div.className = 'brand-chip';
                div.innerHTML = `<i class="fa-solid fa-bolt-lightning"></i> ${b}`;
                grid.appendChild(div);
            });

            initHomeMap(stations.slice(0, 200));
        })
        .catch(() => {
            document.getElementById('heroTotalCount').textContent = '8.000+';
            document.getElementById('heroPublicCount').textContent = '5.000+';
            document.getElementById('heroBrandCount').textContent = '30+';
        });
});

function initHomeMap(stations) {
    const map = L.map('homeMap', { zoomControl: true, scrollWheelZoom: false })
                 .setView([39.0, 35.0], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18
    }).addTo(map);

    const icon = L.divIcon({
        className: '',
        html: '<div class="map-pin home-pin"><i class="fa-solid fa-bolt"></i></div>',
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });

    stations.forEach(s => {
        if (!s.lat || !s.lng) return;
        L.marker([s.lat, s.lng], { icon })
         .addTo(map)
         .bindPopup(`
            <div class="popup-content">
                <strong>${s.sarjIstasyonuAdi}</strong><br>
                <small>${s.sarjIstasyonuMarkaTescilBelgesiMarkaAdi}</small><br>
                <a href="/?page=station&no=${encodeURIComponent(s.sarjIstasyonuNo)}" class="popup-link">Detay</a>
            </div>
         `);
    });
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
