<?php
$active_page = 'movies';
session_start();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: movies.php'); exit; }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Chi tiết phim - MovieFlex</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* mfConfirm dialog */
#mf-dialog-overlay{position:fixed;inset:0;background:rgba(15,23,42,.65);backdrop-filter:blur(4px);z-index:999999;display:none;align-items:center;justify-content:center;padding:16px}
#mf-dialog-overlay.active{display:flex}
.mf-dialog{background:#fff;border-radius:20px;box-shadow:0 24px 48px -12px rgba(0,0,0,.25);width:100%;max-width:400px;overflow:hidden;animation:mfSlide .25s cubic-bezier(.34,1.56,.64,1)}
@keyframes mfSlide{from{transform:scale(.9) translateY(20px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
.mf-dialog-icon-wrap{display:flex;justify-content:center;padding:28px 24px 0}
.mf-dialog-icon{width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px}
.mf-dialog-icon.info{background:#FAF0F1;color:#DF1730}
.mf-dialog-body{padding:20px 28px 24px;text-align:center}
.mf-dialog-title{font-size:17px;font-weight:800;color:#0F172A;margin-bottom:8px}
.mf-dialog-desc{font-size:13.5px;color:#64748B;line-height:1.6}
.mf-dialog-footer{padding:0 24px 24px;display:flex;gap:10px}
.mf-btn-cancel{flex:1;height:42px;background:#F1F5F9;color:#475569;border:1px solid #E2E8F0;border-radius:10px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit}
.mf-btn-confirm{flex:1;height:42px;border:none;border-radius:10px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;color:#fff;display:flex;align-items:center;justify-content:center;gap:6px}
.mf-btn-confirm.info{background:#DF1730}

*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--blue:#DF1730;--blue-h:#C21025;--sb:#0F172A;--sbw:240px;--bg:#F1F5F9;--card:#fff;--text:#0F172A;--muted:#64748B;--light:#94A3B8;--border:#E2E8F0;--r:14px;--sh:0 2px 16px rgba(15,23,42,.08)}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}

.main{margin-left:var(--sbw);flex:1}
.topbar{background:var(--card);border-bottom:1px solid var(--border);padding:0 26px;height:60px;display:flex;align-items:center;gap:14px;position:sticky;top:0;z-index:50}
.back-btn{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:14px;font-weight:600;text-decoration:none;transition:color .2s}
.back-btn:hover{color:var(--text)}
.tb-title{font-size:15px;font-weight:700;margin-left:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tb-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.tb-av{width:34px;height:34px;border-radius:50%;background:var(--blue);display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;cursor:pointer}

.hero{position:relative;height:380px;background:#0f172a;overflow:hidden}
.hero-bg{width:100%;height:100%;object-fit:cover;opacity:.45;position:absolute;inset:0}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(to right,rgba(15,23,42,.95) 0%,rgba(15,23,42,.5) 55%,transparent 100%)}
.hero-content{position:absolute;inset:0;display:flex;align-items:flex-end;padding:36px 40px;gap:28px}
.hero-poster{width:140px;height:200px;border-radius:12px;object-fit:cover;box-shadow:0 8px 32px rgba(0,0,0,.6);flex-shrink:0}
.hero-poster-ph{width:140px;height:200px;border-radius:12px;background:#1e293b;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:40px}
.hero-info{color:#fff;flex:1}
.hero-badges{display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap}
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.3px}
.badge-red{background:rgba(239,68,68,.85);color:#fff}
.badge-blue{background:rgba(37,99,235,.8);color:#fff}
.badge-gray{background:rgba(255,255,255,.15);color:rgba(255,255,255,.85);backdrop-filter:blur(4px)}
.hero-title{font-size:28px;font-weight:800;line-height:1.2;margin-bottom:10px}
.hero-meta{display:flex;align-items:center;gap:18px;font-size:13px;color:rgba(255,255,255,.7);margin-bottom:12px;flex-wrap:wrap}
.hero-meta span{display:flex;align-items:center;gap:5px}
.hero-rating{color:#F59E0B;font-weight:700}
.hero-btns{display:flex;gap:10px}
.btn{display:inline-flex;align-items:center;gap:7px;padding:0 20px;height:40px;border-radius:10px;font-size:13.5px;font-weight:700;cursor:pointer;border:none;font-family:inherit;text-decoration:none;transition:all .2s}
.btn-blue{background:var(--blue);color:#fff}
.btn-blue:hover{background:var(--blue-h)}
.btn-outline{background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.25);backdrop-filter:blur(4px)}
.btn-outline:hover{background:rgba(255,255,255,.22)}

.content{padding:28px 36px;display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start}
.card{background:var(--card);border-radius:var(--r);box-shadow:var(--sh);padding:24px;margin-bottom:20px}
.card-title{font-size:15px;font-weight:700;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.info-item label{display:block;font-size:11.5px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.info-item span{font-size:14px;font-weight:600;color:var(--text)}
.desc-text{font-size:14px;line-height:1.7;color:var(--muted)}

.panel{background:var(--card);border-radius:var(--r);box-shadow:var(--sh);overflow:hidden;position:sticky;top:70px}
.panel-head{padding:18px 20px;border-bottom:1px solid var(--border)}
.panel-head h3{font-size:15px;font-weight:700}

.date-tabs{display:flex;gap:0;overflow-x:auto;padding:12px 16px;border-bottom:1px solid var(--border)}
.date-tab{flex-shrink:0;padding:8px 16px;border-radius:10px;cursor:pointer;text-align:center;transition:all .2s;border:1.5px solid transparent}
.date-tab:hover{background:var(--bg)}
.date-tab.active{background:var(--blue);color:#fff}
.date-tab .dt-day{font-size:11px;font-weight:600;opacity:.8}
.date-tab .dt-date{font-size:15px;font-weight:800;margin:2px 0}
.date-tab .dt-label{font-size:10px;opacity:.7}

.st-body{padding:16px;max-height:480px;overflow-y:auto}
.cinema-group{margin-bottom:18px}
.cinema-name-label{font-size:12.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:10px;display:flex;align-items:center;gap:6px}
.cinema-name-label i{color:var(--blue)}
.time-slots{display:flex;flex-wrap:wrap;gap:8px}
.time-slot{padding:7px 12px;border-radius:8px;border:1.5px solid var(--border);cursor:pointer;transition:all .2s;text-align:center}
.time-slot:hover{border-color:var(--blue);color:var(--blue)}
.time-slot.selected{background:var(--blue);color:#fff;border-color:var(--blue)}
.time-slot.full{opacity:.4;cursor:not-allowed}
.ts-time{font-size:14px;font-weight:700;display:block}
.ts-fmt{font-size:10.5px;color:inherit;opacity:.75}
.ts-price{font-size:10.5px;font-weight:600;color:var(--blue);margin-top:2px;display:block}
.time-slot.selected .ts-price{color:rgba(255,255,255,.85)}

.st-footer{padding:14px 16px;border-top:1px solid var(--border);background:var(--card)}
.st-footer-info{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:13px}
.st-footer-price{font-size:18px;font-weight:800;color:var(--blue)}
.btn-book{width:100%;height:44px;background:var(--blue);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:background .2s;display:flex;align-items:center;justify-content:center;gap:7px}
.btn-book:hover{background:#1D4ED8}
.btn-book:disabled{opacity:.5;cursor:not-allowed}
.no-st{text-align:center;padding:32px 16px;color:var(--muted);font-size:14px}

.skeleton{background:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);background-size:200% 100%;animation:shimmer 1.2s infinite}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
</head>
<body>

<div id="mf-dialog-overlay">
  <div class="mf-dialog">
    <div class="mf-dialog-icon-wrap"><div class="mf-dialog-icon info" id="mf-icon"><i class="fa-solid fa-circle-info"></i></div></div>
    <div class="mf-dialog-body">
      <div class="mf-dialog-title" id="mf-title"></div>
      <div class="mf-dialog-desc" id="mf-desc"></div>
    </div>
    <div class="mf-dialog-footer">
      <button class="mf-btn-cancel" id="mf-cancel">Ở lại</button>
      <button class="mf-btn-confirm info" id="mf-confirm"><i class="fa-solid fa-arrow-right-to-bracket"></i> Đến trang đăng nhập</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <a href="movies.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
    <span class="tb-title" id="tb-title">Đang tải...</span>
    <div class="tb-right">
      <?php if(!empty($_SESSION['user_id'])): ?>
      <div class="tb-av"><?= mb_strtoupper(mb_substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
      <?php else: ?>
      <a href="login.php" class="btn btn-blue" style="height:34px;font-size:13px">Đăng nhập</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- HERO SKELETON -->
  <div id="hero-container">
    <div class="hero"><div class="skeleton" style="width:100%;height:100%;position:absolute;inset:0"></div></div>
  </div>

  <div class="content" id="main-content">
    <!-- Skeleton left -->
    <div>
      <div class="card">
        <div class="skeleton" style="height:16px;border-radius:4px;margin-bottom:12px;width:30%"></div>
        <div class="skeleton" style="height:14px;border-radius:4px;margin-bottom:8px"></div>
        <div class="skeleton" style="height:14px;border-radius:4px;margin-bottom:8px;width:80%"></div>
        <div class="skeleton" style="height:14px;border-radius:4px;width:60%"></div>
      </div>
    </div>
    <!-- Skeleton right -->
    <div>
      <div class="panel">
        <div class="panel-head"><div class="skeleton" style="height:16px;border-radius:4px;width:50%"></div></div>
        <div style="padding:24px"><div class="skeleton" style="height:80px;border-radius:8px"></div></div>
      </div>
    </div>
  </div>
</div>

<script>
const MOVIE_ID = <?= $id ?>;
const IS_LOGGED_IN = <?= !empty($_SESSION['user_id']) ? 'true' : 'false' ?>;
const API = '/be/api.php';
const DAY_NAMES = ['CN','T2','T3','T4','T5','T6','T7'];

let selShowtime = null;
let groupedShowtimes = {};
let currentDate = null;

function escHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function nl2br(str) {
  return escHtml(str).replace(/\n/g,'<br>');
}

function fmt(n, decimals=1) {
  return parseFloat(n || 0).toFixed(decimals);
}

// ── DIALOG ────────────────────────────────────────────────────────────────
function mfConfirm(opts) {
  return new Promise(resolve => {
    document.getElementById('mf-title').textContent = opts.title || '';
    document.getElementById('mf-desc').textContent = opts.desc || '';
    document.getElementById('mf-dialog-overlay').classList.add('active');
    const confirmBtn = document.getElementById('mf-confirm');
    const cancelBtn = document.getElementById('mf-cancel');
    const close = (val) => {
      document.getElementById('mf-dialog-overlay').classList.remove('active');
      resolve(val);
    };
    confirmBtn.onclick = () => close(true);
    cancelBtn.onclick = () => close(false);
  });
}

// ── RENDER HERO ───────────────────────────────────────────────────────────
function renderHero(movie) {
  document.getElementById('tb-title').textContent = movie.title;
  const bgImg = movie.backdrop_url
    ? `<img class="hero-bg" src="${escHtml(movie.backdrop_url)}" alt="">`
    : '';
  const poster = movie.poster_url
    ? `<img class="hero-poster" src="${escHtml(movie.poster_url)}" alt="">`
    : `<div class="hero-poster-ph"><i class="fa-solid fa-film"></i></div>`;
  const statusBadge = movie.status === 'now_showing'
    ? `<span class="badge badge-red"><i class="fa-solid fa-circle-play"></i> Đang chiếu</span>`
    : `<span class="badge badge-blue"><i class="fa-regular fa-clock"></i> Sắp chiếu</span>`;
  const trailer = movie.trailer_url
    ? `<a href="${escHtml(movie.trailer_url)}" target="_blank" class="btn btn-outline"><i class="fa-solid fa-play"></i> Xem trailer</a>`
    : '';

  document.getElementById('hero-container').innerHTML = `
    <div class="hero">
      ${bgImg}
      <div class="hero-overlay"></div>
      <div class="hero-content">
        ${poster}
        <div class="hero-info">
          <div class="hero-badges">
            ${statusBadge}
            <span class="badge badge-gray">${escHtml(movie.age_rating || '')}</span>
          </div>
          <h1 class="hero-title">${escHtml(movie.title)}</h1>
          <div class="hero-meta">
            <span><i class="fa-regular fa-clock"></i> ${escHtml(movie.duration_min)} phút</span>
            <span><i class="fa-solid fa-star hero-rating"></i> <b class="hero-rating">${fmt(movie.rating)}</b>/10</span>
            ${movie.genre ? `<span><i class="fa-solid fa-masks-theater"></i> ${escHtml(movie.genre)}</span>` : ''}
            ${movie.director ? `<span><i class="fa-solid fa-video"></i> ${escHtml(movie.director)}</span>` : ''}
          </div>
          <div class="hero-btns">
            <a href="#showtimes" class="btn btn-blue"><i class="fa-solid fa-ticket"></i> Đặt vé ngay</a>
            ${trailer}
          </div>
        </div>
      </div>
    </div>`;
}

// ── RENDER INFO CARDS ─────────────────────────────────────────────────────
function renderInfo(movie, reviews) {
  const releaseDate = movie.release_date
    ? new Date(movie.release_date).toLocaleDateString('vi-VN')
    : '—';
  const statusText = movie.status === 'now_showing' ? '🟢 Đang chiếu' : '🔵 Sắp chiếu';

  const reviewsHtml = reviews.length === 0
    ? `<div style="text-align:center;padding:32px 16px;color:var(--muted);font-size:13.5px">
        <i class="fa-regular fa-star" style="font-size:32px;margin-bottom:10px;display:block;opacity:.3"></i>
        Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá sau khi xem phim!
       </div>`
    : reviews.map(r => {
        const init = (r.full_name || 'K')[0].toUpperCase();
        return `
          <div style="border-bottom:1px solid #E2E8F0;padding-bottom:14px;display:flex;gap:12px;align-items:flex-start">
            <div style="width:36px;height:36px;border-radius:50%;background:#EFF6FF;color:var(--blue);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0">${escHtml(init)}</div>
            <div style="flex:1">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                <strong style="font-size:14px;color:#0F172A">${escHtml(r.full_name)}</strong>
                <span style="font-size:12.5px;color:#F59E0B;font-weight:800">⭐ ${escHtml(String(r.rating))}/10</span>
              </div>
              <div style="font-size:11px;color:var(--muted);margin-bottom:6px">📅 ${new Date(r.created_at).toLocaleString('vi-VN')}</div>
              <p style="font-size:13.5px;color:#334155;line-height:1.5">${nl2br(r.comment || '')}</p>
            </div>
          </div>`;
      }).join('');

  return `
    <div>
      <div class="card">
        <div class="card-title">Nội dung phim</div>
        <p class="desc-text">${nl2br(movie.description || '')}</p>
      </div>
      <div class="card">
        <div class="card-title">Thông tin chi tiết</div>
        <div class="info-grid">
          <div class="info-item"><label>Đạo diễn</label><span>${escHtml(movie.director || '—')}</span></div>
          <div class="info-item"><label>Diễn viên</label><span>${escHtml(movie.cast_list || '—')}</span></div>
          <div class="info-item"><label>Thể loại</label><span>${escHtml(movie.genre || '—')}</span></div>
          <div class="info-item"><label>Thời lượng</label><span>${escHtml(String(movie.duration_min))} phút</span></div>
          <div class="info-item"><label>Khởi chiếu</label><span>${releaseDate}</span></div>
          <div class="info-item"><label>Giới hạn tuổi</label><span>${escHtml(movie.age_rating || '—')}</span></div>
          <div class="info-item"><label>Đánh giá</label><span style="color:#F59E0B">⭐ ${fmt(movie.rating)}/10</span></div>
          <div class="info-item"><label>Trạng thái</label><span>${statusText}</span></div>
        </div>
      </div>
      <div class="card">
        <div class="card-title" style="display:flex;justify-content:space-between;align-items:center">
          <span>Đánh giá từ khán giả (${reviews.length})</span>
          <span style="font-size:13.5px;color:#F59E0B;font-weight:800">⭐ ${fmt(movie.rating)}/10</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:16px">${reviewsHtml}</div>
      </div>
    </div>`;
}

// ── RENDER SHOWTIME PANEL ─────────────────────────────────────────────────
function renderShowtimePanel(movie, showtimes) {
  groupedShowtimes = showtimes;
  const dates = Object.keys(showtimes);

  if (dates.length === 0) {
    const comingSoon = movie.status === 'coming_soon'
      ? `<div class="no-st">
          <i class="fa-regular fa-clock" style="font-size:36px;margin-bottom:12px;display:block;color:#3B82F6;opacity:.7"></i>
          <div style="font-size:14px;font-weight:700;color:#0F172A;margin-bottom:6px">Phim sắp ra mắt</div>
          ${movie.release_date ? `<div style="font-size:13px;color:#64748B">Dự kiến khởi chiếu: <strong style="color:#2563EB">${new Date(movie.release_date).toLocaleDateString('vi-VN')}</strong></div>` : ''}
          <div style="margin-top:14px;font-size:12px;color:#94A3B8">⏰ Chưa có suất chiếu khả dụng</div>
         </div>`
      : `<div class="no-st"><i class="fa-solid fa-calendar-xmark" style="font-size:32px;margin-bottom:10px;display:block;opacity:.3"></i>Chưa có suất chiếu</div>`;
    return `<div class="panel" id="showtimes">
      <div class="panel-head"><h3><i class="fa-regular fa-calendar" style="color:var(--blue);margin-right:6px"></i>Chọn suất chiếu</h3></div>
      ${comingSoon}
    </div>`;
  }

  currentDate = dates[0];
  const today = new Date(); today.setHours(0,0,0,0);
  const tomorrow = new Date(today); tomorrow.setDate(today.getDate()+1);

  const tabsHtml = dates.map((d, i) => {
    const dt = new Date(d + 'T00:00:00');
    const isToday = dt.getTime() === today.getTime();
    const isTom = dt.getTime() === tomorrow.getTime();
    const dayStr = DAY_NAMES[dt.getDay()];
    const dateStr = `${String(dt.getDate()).padStart(2,'0')}/${String(dt.getMonth()+1).padStart(2,'0')}`;
    return `<div class="date-tab ${i===0?'active':''}" id="dtab-${d}" onclick="selectDate('${d}')">
      <div class="dt-day">${dayStr}</div>
      <div class="dt-date">${dateStr}</div>
      <div class="dt-label">${isToday?'Hôm nay':isTom?'Ngày mai':''}</div>
    </div>`;
  }).join('');

  return `<div class="panel" id="showtimes">
    <div class="panel-head"><h3><i class="fa-regular fa-calendar" style="color:var(--blue);margin-right:6px"></i>Chọn suất chiếu</h3></div>
    <div class="date-tabs">${tabsHtml}</div>
    <div class="st-body" id="st-body">${renderSlotsForDate(currentDate)}</div>
    <div class="st-footer">
      <div class="st-footer-info">
        <span id="sel-info" style="color:var(--muted)">Chưa chọn suất chiếu</span>
        <span class="st-footer-price" id="sel-price"></span>
      </div>
      <button class="btn-book" id="btn-book" disabled onclick="goBook()">
        <i class="fa-solid fa-ticket"></i> Tiếp tục chọn ghế
      </button>
    </div>
  </div>`;
}

function renderSlotsForDate(date) {
  const cinemas = groupedShowtimes[date] || {};
  return Object.entries(cinemas).map(([cinemaName, slots]) => {
    const fmtCls = (fmt) => 'fmt-' + fmt.toLowerCase();
    const slotsHtml = slots.map(s => {
      const full = parseInt(s.available_seats) === 0;
      return `<div class="time-slot ${full?'full':''}"
          data-id="${s.id}" data-time="${escHtml(s.start_time)}"
          data-cinema="${escHtml(cinemaName)}" data-hall="${escHtml(s.hall_name||'Phòng chiếu 1')}"
          data-format="${escHtml(s.format)}" data-price="${escHtml(String(s.price))}"
          onclick="${full?'':'selectSlot(this)'}">
        <span class="ts-time">${s.start_time.substring(0,5)}</span>
        <span class="ts-fmt ${fmtCls(s.format)}">${escHtml(s.format)} · ${escHtml(s.subtitle_type)} · ${escHtml(s.hall_name||'Phòng chiếu 1')}</span>
        <span class="ts-price">${Number(s.price).toLocaleString('vi-VN')}₫</span>
        ${full?`<span style="font-size:10px;color:#ef4444">Hết ghế</span>`:''}
      </div>`;
    }).join('');
    return `<div class="cinema-group">
      <div class="cinema-name-label"><i class="fa-solid fa-location-dot"></i>${escHtml(cinemaName)}</div>
      <div class="time-slots">${slotsHtml}</div>
    </div>`;
  }).join('') || `<div class="no-st">Không có phim chiếu ngày này</div>`;
}

function selectDate(d) {
  currentDate = d;
  document.querySelectorAll('.date-tab').forEach(el => el.classList.toggle('active', el.id === `dtab-${d}`));
  selShowtime = null;
  const stBody = document.getElementById('st-body');
  if (stBody) stBody.innerHTML = renderSlotsForDate(d);
  const selInfo = document.getElementById('sel-info');
  const selPrice = document.getElementById('sel-price');
  const btnBook = document.getElementById('btn-book');
  if (selInfo) selInfo.textContent = 'Chưa chọn suất chiếu';
  if (selPrice) selPrice.textContent = '';
  if (btnBook) btnBook.disabled = true;
}

function selectSlot(el) {
  document.querySelectorAll('.time-slot:not(.full)').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  selShowtime = {
    id: el.dataset.id,
    time: el.dataset.time,
    cinema: el.dataset.cinema,
    format: el.dataset.format,
    price: el.dataset.price,
    hall: el.dataset.hall
  };
  document.getElementById('sel-info').textContent =
    `${el.dataset.cinema} · ${el.dataset.hall} · ${el.dataset.time.substring(0,5)} · ${el.dataset.format}`;
  document.getElementById('sel-price').textContent =
    `${Number(el.dataset.price).toLocaleString('vi-VN')}₫/vé`;
  document.getElementById('btn-book').disabled = false;
}

async function goBook() {
  if (!selShowtime) return;
  if (!IS_LOGGED_IN) {
    const ok = await mfConfirm({
      title: 'Đăng nhập để đặt vé',
      desc: 'Bạn cần đăng nhập để tiến hành đặt vé. Đến trang đăng nhập ngay?'
    });
    if (ok) window.location.href = 'login.php';
    return;
  }
  window.location.href = `seat-select.php?showtime_id=${selShowtime.id}`;
}

// ── LOAD DATA ─────────────────────────────────────────────────────────────
async function loadMovieDetail() {
  try {
    const res = await fetch(`${API}?action=movie_detail&id=${MOVIE_ID}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message);

    const { movie, showtimes, reviews } = data.data;

    renderHero(movie);

    const leftHtml = renderInfo(movie, reviews);
    const rightHtml = renderShowtimePanel(movie, showtimes);

    document.getElementById('main-content').innerHTML = leftHtml + rightHtml;

  } catch(err) {
    document.getElementById('hero-container').innerHTML =
      `<div style="background:#FEF2F2;padding:20px;color:#B91C1C;font-weight:600">${escHtml(err.message)}</div>`;
    document.getElementById('main-content').innerHTML = '';
  }
}

loadMovieDetail();
</script>
<script src="../assets/js/script.js"></script>
</body>
</html>