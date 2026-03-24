<nav class="tab-bar">
    <a href="<?= base_url() ?>" class="tab-item <?= ($activePage ?? '') === 'home' ? 'active' : '' ?>">
        <div class="tab-icon"><i class="fa-solid fa-house"></i></div>
        <span class="tab-label">Ana Sayfa</span>
    </a>
    <a href="<?= base_url('?page=map') ?>" class="tab-item <?= ($activePage ?? '') === 'map' ? 'active' : '' ?>">
        <div class="tab-icon"><i class="fa-solid fa-map-location-dot"></i></div>
        <span class="tab-label">Harita</span>
    </a>
    <a href="<?= base_url('?page=stations') ?>" class="tab-item <?= ($activePage ?? '') === 'stations' ? 'active' : '' ?>">
        <div class="tab-icon"><i class="fa-solid fa-charging-station"></i></div>
        <span class="tab-label">İstasyonlar</span>
    </a>
</nav>
