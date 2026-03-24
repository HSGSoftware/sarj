<?php
$stationId   = (int)($_GET['id'] ?? 0);
$stationName = $_GET['name'] ?? 'Şarj İstasyonu';
$brand       = $_GET['brand'] ?? '';
$pageTitle   = 'Demo Ödeme – ŞarjNet';
$activePage  = '';
require __DIR__ . '/partials/header.php';
?>

<div class="page-hero mini">
    <div class="page-hero-content">
        <a href="<?= base_url('?page=station&id=' . $stationId) ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> İstasyona Dön</a>
        <h1><i class="fa-solid fa-bolt"></i> Şarj & Ödeme</h1>
        <p><?= htmlspecialchars($stationName) ?><?= $brand ? ' · ' . htmlspecialchars($brand) : '' ?></p>
    </div>
</div>

<div class="payment-page">
    <div class="payment-container">

        <div class="payment-summary">
            <div class="summary-header">
                <div class="summary-icon"><i class="fa-solid fa-charging-station"></i></div>
                <div>
                    <h3><?= htmlspecialchars($stationName) ?></h3>
                    <span class="summary-no">ID: <?= $stationId ?><?= $brand ? ' · ' . htmlspecialchars($brand) : '' ?></span>
                </div>
            </div>

            <div class="charge-options">
                <h4>Şarj Miktarı Seç</h4>
                <div class="charge-grid">
                    <button class="charge-opt" data-kwh="10" data-price="45.00">
                        <span class="opt-kwh">10 kWh</span>
                        <span class="opt-price">₺45,00</span>
                    </button>
                    <button class="charge-opt active" data-kwh="20" data-price="88.00">
                        <span class="opt-kwh">20 kWh</span>
                        <span class="opt-price">₺88,00</span>
                        <span class="opt-badge">Popüler</span>
                    </button>
                    <button class="charge-opt" data-kwh="30" data-price="129.00">
                        <span class="opt-kwh">30 kWh</span>
                        <span class="opt-price">₺129,00</span>
                    </button>
                    <button class="charge-opt" data-kwh="50" data-price="210.00">
                        <span class="opt-kwh">50 kWh</span>
                        <span class="opt-price">₺210,00</span>
                    </button>
                </div>
            </div>

            <div class="order-summary">
                <div class="order-row"><span>Şarj miktarı</span><span id="summaryKwh">20 kWh</span></div>
                <div class="order-row"><span>Birim fiyat</span><span>₺4,40/kWh</span></div>
                <div class="order-row order-total"><span>Toplam</span><span id="summaryTotal">₺88,00</span></div>
            </div>
        </div>

        <div class="payment-form-card">
            <h3><i class="fa-solid fa-credit-card"></i> Ödeme Bilgileri</h3>
            <p class="demo-notice"><i class="fa-solid fa-triangle-exclamation"></i> Bu bir demo ödemedir. Gerçek ücret tahsil edilmez.</p>

            <form id="paymentForm" onsubmit="processPayment(event)">
                <div class="form-group">
                    <label>Kart Sahibi</label>
                    <input type="text" id="cardName" placeholder="Ad Soyad" required>
                </div>
                <div class="form-group">
                    <label>Kart Numarası</label>
                    <input type="text" id="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Son Kullanma</label>
                        <input type="text" id="cardExpiry" placeholder="AA/YY" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" id="cardCvv" placeholder="123" maxlength="4" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>E-posta (makbuz için)</label>
                    <input type="email" id="cardEmail" placeholder="ornek@email.com">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-pay" id="payBtn">
                    <i class="fa-solid fa-lock"></i>
                    <span>Güvenli Ödeme Yap – <span id="payBtnAmount">₺88,00</span></span>
                </button>
            </form>

            <div class="security-badges">
                <span><i class="fa-solid fa-shield-halved"></i> SSL Güvenli</span>
                <span><i class="fa-solid fa-lock"></i> 256-bit Şifreleme</span>
                <span><i class="fa-solid fa-check-circle"></i> PCI DSS</span>
            </div>
        </div>
    </div>
</div>

<script>
const stationId   = <?= (int)$stationId ?>;
const stationName = <?= json_encode($stationName) ?>;
let selectedKwh   = 20;
let selectedPrice = '88.00';

document.querySelectorAll('.charge-opt').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.charge-opt').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedKwh   = btn.dataset.kwh;
        selectedPrice = btn.dataset.price;
        document.getElementById('summaryKwh').textContent   = selectedKwh + ' kWh';
        document.getElementById('summaryTotal').textContent = '₺' + parseFloat(selectedPrice).toLocaleString('tr', {minimumFractionDigits:2});
        document.getElementById('payBtnAmount').textContent = '₺' + parseFloat(selectedPrice).toLocaleString('tr', {minimumFractionDigits:2});
    });
});

document.getElementById('cardNumber').addEventListener('input', function() {
    let v = this.value.replace(/\D/g,'').substring(0,16);
    this.value = v.replace(/(.{4})/g,'$1 ').trim();
});
document.getElementById('cardExpiry').addEventListener('input', function() {
    let v = this.value.replace(/\D/g,'');
    if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
    this.value = v;
});

function processPayment(e) {
    e.preventDefault();
    const btn = document.getElementById('payBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> İşleniyor...';
    setTimeout(() => {
        const p = new URLSearchParams({ id: stationId, name: stationName, kwh: selectedKwh, price: selectedPrice });
        window.location.href = BASE_URL + '/?page=payment_success&' + p.toString();
    }, 2000);
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
