<?php
$stationId   = (int)($_GET['id'] ?? 0);
$stationName = $_GET['name'] ?? 'Şarj İstasyonu';
$kwh         = $_GET['kwh'] ?? '20';
$price       = $_GET['price'] ?? '88.00';
$txId        = strtoupper(substr(md5(uniqid()), 0, 12));
?>
<?php require __DIR__ . '/partials/header.php'; ?>

<header class="app-bar">
    <a href="<?= page_url('station', ['id'=>$stationId]) ?>" class="app-bar-back"><i class="fa-solid fa-arrow-left"></i></a>
    <span class="app-bar-page-title">Makbuz</span>
</header>

<div class="page-content">
<div class="success-page">
    <div class="success-container">
        <div class="success-anim"><div class="success-circle"><i class="fa-solid fa-check"></i></div></div>
        <h1>Ödeme Başarılı!</h1>
        <p class="success-sub">Şarjınız başlatıldı. İyi yolculuklar 🚗⚡</p>
        <div class="receipt-card">
            <div class="receipt-header"><span><i class="fa-solid fa-receipt"></i> Makbuz</span><span class="receipt-demo">DEMO</span></div>
            <div class="receipt-row"><span>İşlem No</span><span class="mono"><?= htmlspecialchars($txId) ?></span></div>
            <div class="receipt-row"><span>İstasyon</span><span><?= htmlspecialchars($stationName) ?></span></div>
            <div class="receipt-row"><span>Şarj Miktarı</span><span><?= htmlspecialchars($kwh) ?> kWh</span></div>
            <div class="receipt-row"><span>Tarih</span><span><?= date('d.m.Y H:i') ?></span></div>
            <div class="receipt-row receipt-total"><span>Toplam</span><span>₺<?= number_format((float)$price,2,',','.') ?></span></div>
        </div>
        <div class="success-actions">
            <a href="<?= page_url('station', ['id'=>$stationId]) ?>" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> İstasyon</a>
            <a href="<?= page_url('map') ?>" class="btn btn-primary"><i class="fa-solid fa-map-location-dot"></i> Harita</a>
        </div>
    </div>
</div>
</div>

<?php require __DIR__ . '/partials/tab_bar.php'; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
