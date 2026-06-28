<?php
$active_page = 'vouchers';
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Voucher & Ưu đãi - MovieFlex</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --blue:#DF1730;--indigo:#C21025;
  --bg:#F1F5F9;--card:#fff;
  --text:#0F172A;--muted:#64748B;--light:#94A3B8;--border:#E2E8F0;
  --r:14px;--sh:0 2px 16px rgba(15,23,42,.08);--sbw:240px;
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}

.main{margin-left:var(--sbw);flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{background:var(--card);border-bottom:1px solid var(--border);padding:0 28px;height:64px;display:flex;align-items:center;gap:16px;position:sticky;top:0;z-index:50}
.topbar h1{font-size:18px;font-weight:800}
.content{padding:24px 28px;flex:1;display:grid;grid-template-columns:1fr 340px;gap:24px;max-width:1400px;margin:0 auto;width:100%}

.banner-hero{background:#1E1E24;color:#fff;border-radius:var(--r);padding:32px;margin-bottom:24px;box-shadow:var(--sh);grid-column:1 / -1;display:flex;justify-content:space-between;align-items:center;gap:24px;position:relative;overflow:hidden;border:1px solid var(--border)}
.banner-hero::before{content:'';position:absolute;width:150px;height:150px;background:rgba(255,255,255,.05);border-radius:50%;top:-30px;right:-30px}
.banner-content h2{font-size:24px;font-weight:800;margin-bottom:8px;letter-spacing:-.5px}
.banner-content p{font-size:14px;opacity:.9;max-width:500px;line-height:1.5}
.banner-badge{background:rgba(255,255,255,.2);padding:10px 20px;border-radius:24px;font-size:13.5px;font-weight:700;backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,.3)}

.vouchers-grid{display:grid;grid-template-columns:1fr;gap:16px}
.voucher-card{display:flex;background:var(--card);border-radius:12px;border:1.5px solid var(--border);overflow:hidden;position:relative;box-shadow:0 2px 10px rgba(15,23,42,.03)}
.voucher-left{background:var(--blue);color:#fff;width:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:14px;text-align:center;position:relative;flex-shrink:0}
.voucher-left::after{content:'';position:absolute;right:-5px;top:0;bottom:0;width:10px;background-image:radial-gradient(circle at 10px 5px,var(--bg) 4px,transparent 4px);background-size:10px 10px;background-position:right top}
.v-val{font-size:22px;font-weight:800}
.v-type{font-size:10.5px;opacity:.9;margin-top:2px;font-weight:700;text-transform:uppercase}
.voucher-right{flex:1;padding:16px 20px;display:flex;flex-direction:column;justify-content:space-between}
.v-code-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px}
.v-code{font-family:monospace;font-size:15px;font-weight:700;color:var(--blue);background:#FAF0F1;padding:3px 10px;border-radius:6px;border:1.5px dashed #FCA5A5;letter-spacing:.5px}
.v-desc{font-size:14px;font-weight:700;color:var(--text);line-height:1.4;margin-bottom:8px}
.v-meta{font-size:11.5px;color:var(--muted);display:flex;align-items:center;gap:14px}
.v-copy-btn{border:none;background:var(--blue);color:#fff;font-size:11.5px;font-weight:700;padding:6px 14px;border-radius:8px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:6px}
.v-copy-btn:hover{background:var(--indigo)}
.v-copy-btn.copied{background:#10B981}

.privilege-card{background:var(--card);border-radius:var(--r);box-shadow:var(--sh);padding:24px;position:sticky;top:88px}
.privilege-head{font-size:15px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.privilege-head i{color:#F59E0B}
.privilege-list{display:flex;flex-direction:column;gap:14px}
.privilege-item{display:flex;gap:12px;align-items:flex-start}
.privilege-icon{width:28px;height:28px;border-radius:6px;background:#FEF3C7;color:#D97706;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;margin-top:2px}
.privilege-info h4{font-size:13px;font-weight:700;margin-bottom:2px}
.privilege-info p{font-size:11.5px;color:var(--muted);line-height:1.4}

.overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.8);z-index:200;align-items:center;justify-content:center}
.overlay.show{display:flex}
.modal{background:var(--card);border-radius:18px;padding:28px;max-width:400px;width:100%;box-shadow:0 16px 48px rgba(0,0,0,.4);text-align:center}
.modal-btns{display:flex;gap:10px}
.mbtn{flex:1;height:42px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;border:none;font-family:inherit;transition:all .2s}
.mbtn-cancel{background:var(--bg);color:var(--text)}
.mbtn-confirm{background:#10B981;color:#fff}
.mbtn:disabled{opacity:.6;cursor:not-allowed}

.alert{display:flex;align-items:center;gap:10px;padding:14px 18px;border-radius:12px;font-size:14px;font-weight:600;margin-bottom:20px;grid-column:1/-1}
.alert-success{background:#F0FDF4;color:#16A34A;border:1px solid #BBF7D0}
.alert-error{background:#FEF2F2;color:#DC2626;border:1px solid #FECACA}

.skeleton{background:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);background-size:200% 100%;animation:shimmer 1.2s infinite}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

@media(max-width:992px){.content{grid-template-columns:1fr}.privilege-card{position:static}}
@media(max-width:768px){.main{margin-left:0}}
</style>
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <h1><i class="fa-solid fa-tag" style="color:var(--blue);margin-right:8px"></i>Voucher & Khuyến mãi</h1>
  </div>

  <div class="content">
    <div id="alert-box" style="grid-column:1/-1"></div>

    <!-- BANNER -->
    <div class="banner-hero" id="banner-hero">
      <div class="banner-content">
        <h2>Săn Voucher xem phim cực đã!</h2>
        <p id="banner-desc">Đang tải thông tin thành viên...</p>
      </div>
      <div class="banner-badge"><i class="fa-solid fa-gem" style="margin-right:6px"></i>Đặc quyền hội viên</div>
    </div>

    <!-- LEFT -->
    <div>
      <!-- MY VOUCHERS -->
      <div style="background:var(--card);border-radius:var(--r);box-shadow:var(--sh);padding:24px;margin-bottom:24px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:14px">
          <i class="fa-solid fa-tags" style="color:var(--blue);font-size:18px"></i>
          <h2 style="font-size:16px;font-weight:800">Mã giảm giá đang diễn ra</h2>
        </div>
        <div id="voucher-list">
          <div class="skeleton" style="height:80px;border-radius:12px;margin-bottom:12px"></div>
          <div class="skeleton" style="height:80px;border-radius:12px"></div>
        </div>
      </div>

      <!-- REWARD SHOP -->
      <div style="background:var(--card);border-radius:var(--r);box-shadow:var(--sh);padding:24px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:14px">
          <i class="fa-solid fa-gift" style="color:#10B981;font-size:18px"></i>
          <h2 style="font-size:16px;font-weight:800">Cửa hàng đổi quà tích lũy</h2>
        </div>
        <div id="reward-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
          <div class="skeleton" style="height:160px;border-radius:12px"></div>
          <div class="skeleton" style="height:160px;border-radius:12px"></div>
          <div class="skeleton" style="height:160px;border-radius:12px"></div>
          <div class="skeleton" style="height:160px;border-radius:12px"></div>
        </div>
      </div>
    </div>

    <!-- RIGHT -->
    <div>
      <div class="privilege-card">
        <div class="privilege-head"><i class="fa-solid fa-award"></i><span>Lợi ích theo cấp bậc</span></div>
        <div class="privilege-list">
          <div class="privilege-item">
            <div class="privilege-icon">🥈</div>
            <div class="privilege-info"><h4>Silver (1.000 điểm)</h4><p>Tặng 1 voucher giảm 20k vào ngày sinh nhật. Tích lũy điểm thưởng nhanh hơn 1.1x.</p></div>
          </div>
          <div class="privilege-item">
            <div class="privilege-icon">🥇</div>
            <div class="privilege-info"><h4>Gold (5.000 điểm)</h4><p>Tặng 1 bắp + 1 nước miễn phí mỗi tháng. Voucher sinh nhật giảm 50k. Tích lũy 1.2x.</p></div>
          </div>
          <div class="privilege-item">
            <div class="privilege-icon">💎</div>
            <div class="privilege-info"><h4>Platinum (10.000 điểm)</h4><p>2 vé phim miễn phí/tháng. Phòng chờ VIP tại cụm rạp chính. Tích lũy điểm thưởng 1.5x.</p></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Redeem Modal -->
<div class="overlay" id="redeem-overlay">
  <div class="modal">
    <div style="width:56px;height:56px;border-radius:50%;background:#ECFDF5;color:#10B981;display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 16px">
      <i class="fa-solid fa-gift"></i>
    </div>
    <h3 style="font-size:18px;font-weight:800;color:#0F172A;margin-bottom:8px">Xác nhận đổi quà</h3>
    <p style="font-size:13.5px;color:var(--muted);line-height:1.5;margin-bottom:20px">
      Bạn có chắc muốn dùng <strong id="red-points" style="color:#10B981"></strong> điểm để đổi phần quà này?
    </p>
    <div style="background:#F8FAFC;border-radius:12px;padding:14px;text-align:left;font-size:13.5px;margin-bottom:24px;border:1.5px solid #E2E8F0">
      <span style="color:#64748B;display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Quà tặng quy đổi:</span>
      <strong id="red-name" style="color:#0F172A;font-size:14.5px"></strong>
    </div>
    <div class="modal-btns">
      <button class="mbtn mbtn-cancel" onclick="closeRedeem()">Đóng</button>
      <button class="mbtn mbtn-confirm" id="btn-do-redeem" onclick="doRedeem()">Đổi ngay</button>
    </div>
  </div>
</div>

<script>
const API = '/be/api.php';
let pendingRewardId = null;

function escHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showAlert(type, message) {
  const box = document.getElementById('alert-box');
  const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
  box.innerHTML = `<div class="alert alert-${type}"><i class="fa-solid ${icon}" style="font-size:16px;margin-right:4px"></i><span>${escHtml(message)}</span></div>`;
  box.scrollIntoView({behavior:'smooth',block:'start'});
}

// ── LOAD DASHBOARD ────────────────────────────────────────────────────────
async function loadDashboard() {
  try {
    const fd = new FormData();
    fd.append('action', 'voucher_dashboard');
    const res = await fetch(API, {method:'POST', body:fd});
    const data = await res.json();
    if (!data.success) throw new Error(data.message);

    const { user, vouchers, rewards } = data.data;

    // Banner
    document.getElementById('banner-desc').innerHTML =
      `Hạng hội viên hiện tại: <b>${escHtml(user.member_tier)}</b> (${Number(user.loyalty_points).toLocaleString()} điểm). ` +
      `Tích lũy điểm khi mua vé để thăng hạng và nhận thêm nhiều ưu đãi độc quyền.`;

    renderVouchers(vouchers);
    renderRewards(rewards, user.loyalty_points);
  } catch(err) {
    document.getElementById('voucher-list').innerHTML =
      `<div style="text-align:center;padding:20px;color:#EF4444">${escHtml(err.message)}</div>`;
  }
}

function renderVouchers(vouchers) {
  const el = document.getElementById('voucher-list');
  if (!vouchers || vouchers.length === 0) {
    el.innerHTML = `
      <div style="text-align:center;padding:60px 20px;color:var(--muted)">
        <i class="fa-solid fa-ticket-simple" style="font-size:48px;opacity:.2;display:block;margin-bottom:12px"></i>
        <h3 style="font-size:15px;font-weight:700">Chưa có voucher khả dụng</h3>
        <p style="font-size:12.5px;margin-top:4px">Vui lòng quay lại sau để đón nhận các ưu đãi mới từ MovieFlex.</p>
      </div>`;
    return;
  }

  el.innerHTML = `<div class="vouchers-grid">${vouchers.map(v => {
    const valText = parseFloat(v.discount_pct) > 0
      ? parseFloat(v.discount_pct) + '%'
      : Math.round(v.discount_amt / 1000) + 'K';
    const typeText = parseFloat(v.discount_pct) > 0 ? 'GIẢM GIÁ' : 'TIỀN MẶT';
    const expiry = v.expire_date
      ? new Date(v.expire_date + 'T00:00:00').toLocaleDateString('vi-VN')
      : 'Vô thời hạn';
    return `
      <div class="voucher-card">
        <div class="voucher-left">
          <span class="v-val">${escHtml(valText)}</span>
          <span class="v-type">${typeText}</span>
        </div>
        <div class="voucher-right">
          <div class="v-code-row">
            <span class="v-code">${escHtml(v.code)}</span>
            <button class="v-copy-btn" onclick="copyCode(this,'${escHtml(v.code)}')">
              <i class="fa-regular fa-copy"></i> Sao chép mã
            </button>
          </div>
          <div class="v-desc">${escHtml(v.description)}</div>
          <div class="v-meta">
            <span><i class="fa-regular fa-clock" style="margin-right:4px"></i>HSD: ${expiry}</span>
            <span><i class="fa-solid fa-circle-info" style="margin-right:4px"></i>Đơn tối thiểu: ${Number(v.min_order).toLocaleString('vi-VN')}₫</span>
          </div>
        </div>
      </div>`;
  }).join('')}</div>`;
}

function renderRewards(rewards, userPoints) {
  const grid = document.getElementById('reward-grid');
  if (!rewards || rewards.length === 0) {
    grid.innerHTML = `<div style="text-align:center;padding:40px;color:var(--muted);grid-column:1/-1">Không có phần thưởng nào.</div>`;
    return;
  }

  grid.innerHTML = rewards.map(r => {
    const canRedeem = userPoints >= r.points;
    const isActive = !!r.is_active;
    const icon = r.type === 'discount' ? '🎟️' : '🍿';
    const ptsBadge = `⭐ ${r.points} điểm`;

    let btnHtml = '';
    if (!isActive) {
      btnHtml = `<button disabled style="width:100%;border:none;background:#F3F4F6;color:#9CA3AF;font-size:13px;font-weight:700;height:36px;border-radius:8px;cursor:not-allowed">Voucher hết hàng</button>`;
    } else if (canRedeem) {
      btnHtml = `<button onclick="confirmRedeem('${escHtml(r.id)}','${escHtml(r.name)}',${r.points})"
        style="width:100%;border:none;background:#10B981;color:#fff;font-size:13px;font-weight:700;height:36px;border-radius:8px;cursor:pointer;transition:background .2s"
        onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10B981'">Đổi quà ngay</button>`;
    } else {
      btnHtml = `<button disabled style="width:100%;border:none;background:#E2E8F0;color:#94A3B8;font-size:13px;font-weight:700;height:36px;border-radius:8px;cursor:not-allowed">Chưa đủ điểm</button>`;
    }

    return `
      <div style="border:1.5px solid ${isActive?'var(--border)':'#FCA5A5'};border-radius:12px;padding:18px;display:flex;flex-direction:column;justify-content:space-between;background:${isActive?'#FAFBFD':'#FEF2F2'};opacity:${isActive?1:.85}"
        ${isActive?`onmouseover="this.style.borderColor='#10B981';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)';this.style.transform='none'"`:''}>>
        <div>
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
            <span style="font-size:11px;font-weight:800;background:${isActive?'#ECFDF5':'#FEE2E2'};color:${isActive?'#10B981':'#EF4444'};padding:4px 10px;border-radius:20px;border:1px solid ${isActive?'#A7F3D0':'#FCA5A5'}">${ptsBadge}${!isActive?' (Tắt)':''}</span>
            <span style="font-size:20px;opacity:${isActive?1:.5}">${icon}</span>
          </div>
          <h3 style="font-size:14.5px;font-weight:800;color:${isActive?'var(--text)':'#991B1B'};margin-bottom:6px">${escHtml(r.name)}</h3>
          <p style="font-size:12.5px;color:var(--muted);line-height:1.4;margin-bottom:16px">${escHtml(r.desc)}</p>
        </div>
        ${btnHtml}
      </div>`;
  }).join('');
}

function copyCode(btn, code) {
  navigator.clipboard.writeText(code).then(() => {
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Đã sao chép!';
    btn.classList.add('copied');
    setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('copied'); }, 2000);
  });
}

// ── REDEEM ────────────────────────────────────────────────────────────────
function confirmRedeem(id, name, points) {
  pendingRewardId = id;
  document.getElementById('red-name').textContent = name;
  document.getElementById('red-points').textContent = points;
  document.getElementById('redeem-overlay').classList.add('show');
}

function closeRedeem() { document.getElementById('redeem-overlay').classList.remove('show'); }

async function doRedeem() {
  if (!pendingRewardId) return;
  const btn = document.getElementById('btn-do-redeem');
  btn.disabled = true; btn.textContent = 'Đang xử lý...';

  const fd = new FormData();
  fd.append('action', 'redeem_reward');
  fd.append('reward_id', pendingRewardId);

  try {
    const res = await fetch(API, {method:'POST', body:fd});
    const data = await res.json();
    closeRedeem();
    if (data.success) {
      showAlert('success', data.message + (data.voucher_code ? ` Mã: ${data.voucher_code}` : ''));
      loadDashboard();
    } else {
      showAlert('error', data.message);
    }
  } catch { showAlert('error', 'Lỗi kết nối. Vui lòng thử lại.'); }
  finally { btn.disabled = false; btn.textContent = 'Đổi ngay'; }
}

loadDashboard();
</script>
</body>
</html>