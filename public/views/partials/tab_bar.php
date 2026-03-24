<nav class="tab-bar">
    <a href="<?= page_url('home') ?>" class="tab-item <?= ($activePage ?? '') === 'home' ? 'active' : '' ?>">
        <div class="tab-icon"><i class="fa-solid fa-house"></i></div>
        <span class="tab-label">Ana Sayfa</span>
    </a>
    <a href="<?= page_url('map') ?>" class="tab-item <?= ($activePage ?? '') === 'map' ? 'active' : '' ?>">
        <div class="tab-icon"><i class="fa-solid fa-map-location-dot"></i></div>
        <span class="tab-label">Harita</span>
    </a>
    <a href="<?= page_url('stations') ?>" class="tab-item <?= ($activePage ?? '') === 'stations' ? 'active' : '' ?>">
        <div class="tab-icon"><i class="fa-solid fa-charging-station"></i></div>
        <span class="tab-label">İstasyonlar</span>
    </a>
</nav>
