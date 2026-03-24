<?php
$stationNo = $_GET['no'] ?? '';
$stationName = $_GET['name'] ?? 'Şarj İstasyonu';
$kwh = $_GET['kwh'] ?? '20';
$price = $_GET['price'] ?? '88.00';
$txId = strtoupper(substr(md5(uniqid()), 0, 12));
$pageTitle = 'Ödeme Başarılı – ŞarjNet';
$activePage = '';
require __DIR__ . '/partials/header.php';
?>

<div class="success-page">
    <div class="success-container">
        <div class="success-icon">
            <div class="success-circle">
                <i class="fa-solid fa-check"></i>
            </div>
        </div>
        <h1>Ödeme Başarılı!</h1>
        <p class="success-sub">Şarjınız başlatıldı. İyi yolculuklar!</p>

        <div class="receipt-card">
            <div class="receipt-header">
                <i class="fa-solid fa-receipt"></i> Makbuz
                <span class="receipt-demo">DEMO</span>
            </div>
            <div class="receipt-rows">
                <div class="receipt-row">
                    <span>İşlem No</span>
                    <span class="mono"><?= htmlspecialchars($txId) ?></span>
                </div>
                <div class="receipt-row">
                    <span>İstasyon</span>
                    <span><?= htmlspecialchars($stationName) ?></span>
                </div>
                <div class="receipt-row">
                    <span>İstasyon No</span>
                    <span><?= htmlspecialchars($stationNo) ?></span>
                </div>
                <div class="receipt-row">
                    <span>Şarj Miktarı</span>
                    <span><?= htmlspecialchars($kwh) ?> kWh</span>
                </div>
                <div class="receipt-row">
                    <span>Tarih & Saat</span>
                    <span><?= date('d.m.Y H:i') ?></span>
                </div>
                <div class="receipt-row receipt-total">
                    <span>Toplam</span>
                    <span>₺<?= number_format((float)$price, 2, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <div class="success-actions">
            <a href="/?page=station&no=<?= urlencode($stationNo) ?>" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> İstasyona Dön
            </a>
            <a href="/?page=map" class="btn btn-primary">
                <i class="fa-solid fa-map-location-dot"></i> Haritaya Git
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
