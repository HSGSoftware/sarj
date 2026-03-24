<?php
$stationId   = (int)($_GET['id'] ?? 0);
$stationName = $_GET['name'] ?? 'Şarj İstasyonu';
$brand       = $_GET['brand'] ?? '';
$pageTitle   = 'Ödeme – ŞarjNet';
$activePage  = '';
require __DIR__ . '/partials/header.php';
?>

<header class="app-bar">
    <a href="<?= base_url('?page=station&id=' . $stationId) ?>" class="app-bar-back">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <span class="app-bar-page-title">Şarj & Ödeme</span>
</header>

<div class="page-content">
<div class="payment-wrap">

    <div class="station-summary-card">
        <div class="ssc-icon"><i class="fa-solid fa-charging-station"></i></div>
        <div>
            <div class="ssc-name"><?= htmlspecialchars($stationName) ?></div>
            <div class="ssc-sub">ID: <?= $stationId ?><?= $brand ? ' · ' . htmlspecialchars($brand) : '' ?></div>
        </div>
    </div>

    <div class="kwh-label">Şarj Miktarı Seç</div>
    <div class="kwh-grid">
        <button class="kwh-opt" data-kwh="10" data-price="45.00">
            <span class="kwh-opt-amount">10 kWh</span>
            <span class="kwh-opt-price">₺45,00</span>
        </button>
        <button class="kwh-opt active" data-kwh="20" data-price="88.00">
            <span class="kwh-opt-amount">20 kWh</span>
            <span class="kwh-opt-price">₺88,00</span>
            <span class="kwh-opt-badge">Popüler</span>
        </button>
        <button class="kwh-opt" data-kwh="30" data-price="129.00">
            <span class="kwh-opt-amount">30 kWh</span>
            <span class="kwh-opt-price">₺129,00</span>
        </button>
        <button class="kwh-opt" data-kwh="50" data-price="210.00">
            <span class="kwh-opt-amount">50 kWh</span>
            <span class="kwh-opt-price">₺210,00</span>
        </button>
    </div>

    <div class="order-card">
        <div class="order-row"><span>Şarj miktarı</span><span id="summaryKwh">20 kWh</span></div>
        <div class="order-row"><span>Birim fiyat</span><span>₺4,40/kWh</span></div>
        <div class="order-row total"><span>Toplam</span><span id="summaryTotal">₺88,00</span></div>
    </div>

    <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-credit-card"></i> Ödeme Bilgileri</div>
        <div class="demo-notice"><i class="fa-solid fa-triangle-exclamation"></i> Demo ödeme – gerçek ücret tahsil edilmez.</div>

        <form id="paymentForm" onsubmit="processPayment(event)">
            <div class="form-group">
                <label>Kart Sahibi</label>
                <input type="text" id="cardName" placeholder="Ad Soyad" required autocomplete="cc-name">
            </div>
            <div class="form-group">
                <label>Kart Numarası</label>
                <input type="text" id="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19" required inputmode="numeric" autocomplete="cc-number">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Son Kullanma</label>
                    <input type="text" id="cardExpiry" placeholder="AA/YY" maxlength="5" required inputmode="numeric" autocomplete="cc-exp">
                </div>
                <div class="form-group">
                    <label>CVV</label>
                    <input type="text" id="cardCvv" placeholder="123" maxlength="4" required inputmode="numeric" autocomplete="cc-csc">
                </div>
            </div>
            <div class="form-group">
                <label>E-posta</label>
                <input type="email" id="cardEmail" placeholder="ornek@email.com" inputmode="email" autocomplete="email">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-pay" id="payBtn">
                <i class="fa-solid fa-lock"></i>
                Güvenli Öde – <span id="payAmount">₺88,00</span>
            </button>
        </form>
    </div>

    <div class="security-badges">
        <span><i class="fa-solid fa-shield-halved"></i> SSL</span>
        <span><i class="fa-solid fa-lock"></i> 256-bit</span>
        <span><i class="fa-solid fa-check-circle"></i> PCI DSS</span>
    </div>

</div>
</div>

<?php require __DIR__ . '/partials/tab_bar.php'; ?>

<script>
const stationId   = <?= (int)$stationId ?>;
const stationName = <?= json_encode($stationName) ?>;
let selKwh = 20, selPrice = '88.00';

document.querySelectorAll('.kwh-opt').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.kwh-opt').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selKwh = btn.dataset.kwh; selPrice = btn.dataset.price;
        document.getElementById('summaryKwh').textContent  = selKwh + ' kWh';
        const fmt = parseFloat(selPrice).toLocaleString('tr', {minimumFractionDigits:2});
        document.getElementById('summaryTotal').textContent = '₺' + fmt;
        document.getElementById('payAmount').textContent    = '₺' + fmt;
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
        const p = new URLSearchParams({ id: stationId, name: stationName, kwh: selKwh, price: selPrice });
        window.location.href = BASE_URL + '/?page=payment_success&' + p.toString();
    }, 2000);
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
