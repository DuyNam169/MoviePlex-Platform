<?php
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$showtimeId = (int)($_GET['showtime_id'] ?? 0);
$seatsParam = trim($_GET['seats'] ?? '');
if ($showtimeId <= 0 || $seatsParam === '') { header('Location: home.php'); exit; }
$seatsParamEscaped = htmlspecialchars($seatsParam, ENT_QUOTES, 'UTF-8');
$showtimeIdEscaped = $showtimeId;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Thanh toán - MovieFlex</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--blue:#DF1730;--bg:#F1F5F9;--card:#fff;--text:#0F172A;--muted:#64748B;--border:#E2E8F0;--r:14px;--green:#22C55E}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
.topbar{background:var(--card);border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;padding:0 24px;gap:16px;position:sticky;top:0;z-index:100;box-shadow:0 1px 8px rgba(15,23,42,.06)}
.logo{display:flex;align-items:center;gap:9px;text-decoration:none}
.logo-icon{width:30px;height:30px;background:var(--blue);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px}
.logo-name{font-size:16px;font-weight:800;color:var(--blue)}
.step-bar{display:flex;align-items:center;gap:0;flex:1;justify-content:center}
.step{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:var(--muted)}
.step.active{color:var(--blue)}
.step.done{color:var(--green)}
.step-num{width:24px;height:24px;border-radius:50%;border:2px solid currentColor;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700}
.step.done .step-num{background:var(--green);border-color:var(--green);color:#fff}
.step.active .step-num{background:var(--blue);border-color:var(--blue);color:#fff}
.step-line{width:40px;height:2px;background:var(--border);margin:0 8px}
.step-line.done{background:var(--green)}
.layout{display:grid;grid-template-columns:1fr 360px;gap:20px;padding:24px;max-width:1100px;margin:0 auto}
.card{background:var(--card);border-radius:var(--r);box-shadow:0 2px 16px rgba(15,23,42,.07);margin-bottom:16px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:9px}
.card-head h3{font-size:15px;font-weight:700}
.card-head .icon{width:32px;height:32px;border-radius:8px;background:#FAF0F1;color:var(--blue);display:flex;align-items:center;justify-content:center;font-size:14px}
.card-body{padding:18px 20px}
.order-movie{display:flex;gap:14px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border)}
.op{width:60px;height:85px;border-radius:9px;object-fit:cover;flex-shrink:0;background:#e2e8f0}
.op-ph{width:60px;height:85px;border-radius:9px;flex-shrink:0;background:#1e293b;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:20px}
.om-info h4{font-size:15px;font-weight:700;margin-bottom:6px}
.om-meta{font-size:13px;color:var(--muted);line-height:1.7}
.seats-row{display:flex;flex-wrap:wrap;gap:6px;margin:10px 0}
.seat-tag{background:#FAF0F1;color:var(--blue);font-size:12px;font-weight:700;padding:3px 9px;border-radius:6px;border:1px solid #FCA5A5}
.snack-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.snack-card{border:1.5px solid var(--border);border-radius:10px;padding:12px;cursor:pointer;transition:all .2s;position:relative}
.snack-card:hover{border-color:var(--blue)}
.snack-card.selected{border-color:var(--blue);background:#FAF0F1}
.snack-card.selected::after{content:'✓';position:absolute;top:8px;right:10px;color:var(--blue);font-weight:800;font-size:13px}
.snack-name{font-size:13px;font-weight:600;margin-bottom:3px}
.snack-price{font-size:13px;font-weight:700;color:var(--blue)}
.voucher-row{display:flex;gap:8px}
.voucher-input{flex:1;height:42px;border:1.5px solid var(--border);border-radius:10px;padding:0 14px;font-size:14px;font-family:inherit;outline:none;transition:border-color .2s}
.voucher-input:focus{border-color:var(--blue)}
.btn-apply{height:42px;padding:0 18px;background:var(--blue);color:#fff;border:none;border-radius:10px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:background .2s;white-space:nowrap}
.btn-apply:hover{background:#1D4ED8}
.voucher-tags{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
.vtag{padding:4px 10px;border-radius:6px;border:1.5px dashed var(--border);font-size:12px;font-weight:600;cursor:pointer;color:var(--muted);transition:all .2s}
.vtag:hover{border-color:var(--blue);color:var(--blue)}
.vtag.applied{border-color:var(--green);color:var(--green);background:#F0FDF4}
.pay-methods{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.pay-opt{display:flex;align-items:center;gap:10px;padding:12px 14px;border:1.5px solid var(--border);border-radius:10px;cursor:pointer;transition:all .2s}
.pay-opt.selected,.pay-opt:hover{border-color:var(--blue)}
.pay-opt input[type=radio]{accent-color:var(--blue)}
.pay-icon{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:16px}
.pay-name{font-size:13.5px;font-weight:600}
.sum-section{position:sticky;top:76px}
.sum-rows{padding:16px 20px}
.sum-row{display:flex;justify-content:space-between;font-size:13.5px;margin-bottom:10px}
.sum-label{color:var(--muted)}
.sum-val{font-weight:600}
.sum-discount{color:var(--green)}
.sum-divider{height:1px;background:var(--border);margin:12px 0}
.sum-total{display:flex;justify-content:space-between;font-size:17px;font-weight:800;padding:0 20px 16px}
.sum-total-price{color:var(--blue)}
.btn-pay{width:calc(100% - 40px);margin:0 20px 20px;height:48px;background:var(--blue);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s}
.btn-pay:hover{background:#1D4ED8}
.alert-err{background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;border-radius:10px;padding:12px 16px;font-size:13.5px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.info-note{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px;margin-top:10px}
.hidden{display:none}
</style>
</head>
<body>
<div class="topbar">
  <a href="home.php" class="logo"><div class="logo-icon"><i class="fa-solid fa-clapperboard"></i></div><span class="logo-name">MovieFlex</span></a>
  <div class="step-bar">
    <div class="step done"><div class="step-num"><i class="fa-solid fa-check" style="font-size:9px"></i></div> Chọn phim</div>
    <div class="step-line done"></div>
    <div class="step done"><div class="step-num"><i class="fa-solid fa-check" style="font-size:9px"></i></div> Chọn ghế</div>
    <div class="step-line done"></div>
    <div class="step active"><div class="step-num">3</div> Thanh toán</div>
    <div class="step-line"></div>
    <div class="step"><div class="step-num">4</div> Xác nhận</div>
  </div>
</div>

<div class="layout">
  <div>
    <div id="error-panel" class="alert-err hidden"><i class="fa-solid fa-circle-exclamation"></i><span id="error-text"></span></div>

    <div class="card">
      <div class="card-head"><div class="icon"><i class="fa-solid fa-film"></i></div><h3>Tóm tắt đặt vé</h3></div>
      <div class="card-body" id="order-summary">
        <div class="order-movie">
          <div id="poster-container"></div>
          <div class="om-info">
            <h4 id="movie-title">Đang tải...</h4>
            <div class="om-meta" id="movie-meta"></div>
          </div>
        </div>
        <div style="font-size:13px;font-weight:600;color:var(--muted);margin-bottom:6px">Ghế đã chọn:</div>
        <div class="seats-row" id="selected-seats"></div>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><div class="icon"><i class="fa-solid fa-popcorn"></i></div><h3>Thêm bắp nước (tuỳ chọn)</h3></div>
      <div class="card-body">
        <div class="snack-grid" id="snack-grid"></div>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><div class="icon"><i class="fa-solid fa-tag"></i></div><h3>Mã giảm giá</h3></div>
      <div class="card-body">
        <div class="voucher-row">
          <input class="voucher-input" type="text" id="voucher-input" placeholder="Nhập mã voucher..." maxlength="30">
          <button class="btn-apply" type="button" id="apply-voucher-btn">Áp dụng</button>
        </div>
        <div class="voucher-tags" id="voucher-tags"></div>
        <div id="voucher-msg" style="font-size:13px;margin-top:8px"></div>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><div class="icon"><i class="fa-solid fa-credit-card"></i></div><h3>Phương thức thanh toán</h3></div>
      <div class="card-body">
        <div class="pay-methods" id="pay-methods">
          <label class="pay-opt selected"><input type="radio" name="pay" value="momo" checked> <div class="pay-icon" style="background:#FCE4EC">💳</div><span class="pay-name">MoMo</span></label>
          <label class="pay-opt"><input type="radio" name="pay" value="vnpay"> <div class="pay-icon" style="background:#E3F2FD">🏦</div><span class="pay-name">VNPay</span></label>
          <label class="pay-opt"><input type="radio" name="pay" value="zalopay"> <div class="pay-icon" style="background:#E8F5E9">📱</div><span class="pay-name">ZaloPay</span></label>
          <label class="pay-opt"><input type="radio" name="pay" value="cash"> <div class="pay-icon" style="background:#FFF9C4">💵</div><span class="pay-name">Tiền mặt</span></label>
        </div>
        <div class="info-note"><i class="fa-solid fa-circle-info"></i>Thanh toán an toàn và được mã hóa</div>
      </div>
    </div>
  </div>

  <div class="sum-section">
    <div class="card">
      <div class="card-head"><div class="icon"><i class="fa-solid fa-receipt"></i></div><h3>Chi tiết thanh toán</h3></div>
      <div class="sum-rows">
        <div class="sum-row"><span class="sum-label">Vé phim (<span id="ticket-count">0</span> ghế)</span><span class="sum-val" id="s-tickets">0₫</span></div>
        <div class="sum-row"><span class="sum-label">Bắp nước</span><span class="sum-val" id="s-snacks">0₫</span></div>
        <div class="sum-row"><span class="sum-label">Mã giảm giá</span><span class="sum-val sum-discount" id="s-discount">−0₫</span></div>
        <div class="sum-divider"></div>
      </div>
      <div class="sum-total"><span>Tổng cộng</span><span class="sum-total-price" id="s-total">0₫</span></div>
      <form id="pay-form">
        <input type="hidden" name="voucher_code" id="f-voucher">
        <input type="hidden" name="payment_method" id="f-method" value="momo">
        <input type="hidden" name="snacks_json" id="f-snacks" value="[]">
        <button type="button" class="btn-pay" id="submit-pay">Xác nhận thanh toán</button>
      </form>
    </div>
  </div>
</div>

<script>
const showtimeId = <?= $showtimeIdEscaped ?>;
const seatsParam = '<?= $seatsParamEscaped ?>';
const apiBase = '/be/api.php';
let checkoutData = null;
let selectedSnacks = [];
let currentVoucher = null;
let currentDiscount = 0;
let currentVoucherCode = '';
let loading = false;

function showError(message) {
  const panel = document.getElementById('error-panel');
  document.getElementById('error-text').textContent = message;
  panel.classList.remove('hidden');
}

function hideError() {
  document.getElementById('error-panel').classList.add('hidden');
}

function formatCurrency(value) {
  return Number(value).toLocaleString('vi-VN') + '₫';
}

function setLoading(state) {
  loading = state;
  document.getElementById('submit-pay').disabled = state;
  document.getElementById('apply-voucher-btn').disabled = state;
}

async function fetchJson(url, options = {}) {
  const response = await fetch(url, options);
  if (!response.ok) {
    throw new Error('Lỗi kết nối máy chủ.');
  }
  const data = await response.json();
  if (!data.success) {
    throw new Error(data.message || 'Lỗi API.');
  }
  return data;
}

function renderCheckout() {
  if (!checkoutData) return;
  const show = checkoutData.show;
  const allSeats = checkoutData.allSeats;
  const snacks = checkoutData.snacks;
  const vouchers = checkoutData.vouchers;
  const pricing = checkoutData.pricing;

  document.getElementById('ticket-count').textContent = pricing.count;
  document.getElementById('s-tickets').textContent = formatCurrency(pricing.serverTotal);
  document.getElementById('s-snacks').textContent = formatCurrency(0);
  document.getElementById('s-discount').textContent = '−0₫';
  document.getElementById('s-total').textContent = formatCurrency(pricing.serverTotal);

  const selectedSeats = document.getElementById('selected-seats');
  selectedSeats.innerHTML = allSeats.map(seat => `<span class="seat-tag">${seat}</span>`).join('');

  const posterContainer = document.getElementById('poster-container');
  if (show.poster_url) {
    posterContainer.innerHTML = `<img class="op" src="${show.poster_url}" alt="Poster">`;
  } else {
    posterContainer.innerHTML = '<div class="op-ph"><i class="fa-solid fa-film"></i></div>';
  }

  document.getElementById('movie-title').textContent = show.title;
  document.getElementById('movie-meta').innerHTML = `📅 ${new Date(show.show_date).toLocaleDateString('vi-VN')}<br>⏰ ${show.start_time.slice(0,5)} · ${show.format} · ${show.subtitle_type} · ${show.hall_name || 'Phòng chiếu 1'}<br>📍 ${show.cinema_name}`;

  const snackGrid = document.getElementById('snack-grid');
  snackGrid.innerHTML = snacks.map(sn => `
    <div class="snack-card" data-id="${sn.id}" data-price="${sn.price}" data-name="${sn.name}" onclick="toggleSnack(this)">
      <div class="snack-name">${sn.name}</div>
      <div class="snack-price">${formatCurrency(sn.price)}</div>
    </div>
  `).join('');

  const voucherTags = document.getElementById('voucher-tags');
  voucherTags.innerHTML = vouchers.map(v => `
    <span class="vtag" data-code="${v.code}" onclick="chooseVoucher(this)">${v.code} - ${v.description}</span>
  `).join('');
}

function updateTotal() {
  if (!checkoutData) return;
  const pricing = checkoutData.pricing;
  const snacksTotal = selectedSnacks.reduce((sum, item) => sum + item.price, 0);
  const baseTotal = pricing.serverTotal;
  const total = Math.max(0, baseTotal + snacksTotal - currentDiscount);

  document.getElementById('s-snacks').textContent = formatCurrency(snacksTotal);
  document.getElementById('s-discount').textContent = currentDiscount > 0 ? `−${formatCurrency(currentDiscount)}` : '−0₫';
  document.getElementById('s-total').textContent = formatCurrency(total);
}

function toggleSnack(el) {
  el.classList.toggle('selected');
  const id = parseInt(el.dataset.id, 10);
  const price = parseInt(el.dataset.price, 10);
  const name = el.dataset.name;
  if (el.classList.contains('selected')) {
    selectedSnacks.push({id, name, price});
  } else {
    selectedSnacks = selectedSnacks.filter(s => s.id !== id);
  }
  if (currentVoucherCode && currentVoucherCode.startsWith('GIFTPOP')) {
    applyVoucher(true);
  } else {
    updateTotal();
  }
}

function chooseVoucher(el) {
  document.getElementById('voucher-input').value = el.dataset.code;
  applyVoucher();
}

async function applyVoucher(recalc = false) {
  hideError();
  const code = document.getElementById('voucher-input').value.trim().toUpperCase();
  const message = document.getElementById('voucher-msg');
  message.textContent = '';
  currentDiscount = 0;
  currentVoucher = null;
  currentVoucherCode = '';

  if (!code) {
    updateTotal();
    return;
  }

  const snacksTotal = selectedSnacks.reduce((sum, item) => sum + item.price, 0);
  const body = new URLSearchParams();
  body.append('action', 'validate_voucher');
  body.append('voucher_code', code);
  body.append('showtime_id', showtimeId);
  body.append('seats', seatsParam);
  body.append('snacks_json', JSON.stringify(selectedSnacks));

  try {
    setLoading(true);
    const data = await fetchJson(apiBase, {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: body.toString(),
    });
    currentDiscount = parseInt(data.discount, 10) || 0;
    currentVoucher = data.voucher || null;
    currentVoucherCode = code;
    message.innerHTML = `<span style="color:#22C55E">✅ ${data.message}</span>`;
    document.querySelectorAll('.vtag').forEach(tag => {
      tag.classList.toggle('applied', tag.dataset.code.toUpperCase() === code);
    });
  } catch (error) {
    message.innerHTML = `<span style="color:#ef4444">❌ ${error.message}</span>`;
    currentDiscount = 0;
    currentVoucherCode = '';
    document.querySelectorAll('.vtag').forEach(tag => tag.classList.remove('applied'));
  } finally {
    setLoading(false);
    updateTotal();
  }
}

async function submitPayment() {
  hideError();
  if (!checkoutData) return;
  const method = document.querySelector('input[name="pay"]:checked')?.value || 'momo';
  const body = new URLSearchParams();
  body.append('action', 'create_booking');
  body.append('showtime_id', showtimeId);
  body.append('seats', seatsParam);
  body.append('payment_method', method);
  body.append('voucher_code', currentVoucherCode);
  body.append('snacks_json', JSON.stringify(selectedSnacks));

  try {
    setLoading(true);
    const data = await fetchJson(apiBase, {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: body.toString(),
    });
    window.location.href = `booking-confirm.php?code=${encodeURIComponent(data.booking_code)}`;
  } catch (error) {
    showError(error.message);
  } finally {
    setLoading(false);
  }
}

async function loadCheckoutData() {
  try {
    setLoading(true);
    const data = await fetchJson(`${apiBase}?action=checkout_data&showtime_id=${showtimeId}&seats=${encodeURIComponent(seatsParam)}`);
    checkoutData = data.data;
    renderCheckout();
  } catch (error) {
    showError(error.message);
  } finally {
    setLoading(false);
  }
}

document.getElementById('apply-voucher-btn').addEventListener('click', () => applyVoucher());
document.getElementById('submit-pay').addEventListener('click', submitPayment);

document.getElementById('pay-methods').addEventListener('click', (event) => {
  const opt = event.target.closest('.pay-opt');
  if (!opt) return;
  document.querySelectorAll('.pay-opt').forEach(o => o.classList.remove('selected'));
  opt.classList.add('selected');
  const radio = opt.querySelector('input[name="pay"]');
  if (radio) radio.checked = true;
});

loadCheckoutData();
</script>
</body>
</html>
