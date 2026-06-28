<?php
$active_page = 'cinemas';
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Hệ thống rạp chiếu - MovieFlex</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
html,body{margin:0;padding:0;height:100%;overflow:hidden}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --blue:#DF1730;
  --sb:#0F172A;--sbw:240px;
  --bg:#F1F5F9;--card:#fff;
  --text:#0F172A;--muted:#64748B;--light:#94A3B8;--border:#E2E8F0;
  --radius:14px;--sh:0 2px 16px rgba(15,23,42,.08);
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;height:100vh;width:100vw;overflow:hidden;position:fixed;inset:0}

.main{margin-left:var(--sbw, 240px);flex:1;display:flex;flex-direction:column;height:100vh;overflow:hidden}
.topbar{background:var(--card);border-bottom:1px solid var(--border);padding:0 28px;height:64px;display:flex;align-items:center;gap:16px;position:sticky;top:0;z-index:50}
.topbar h1{font-size:18px;font-weight:800}

.content{flex:1;display:flex;height:calc(100vh - 64px);overflow:hidden}

.cinema-list{width:320px;background:var(--card);border-right:1px solid var(--border);overflow-y:auto;display:flex;flex-direction:column}
.cl-header{padding:16px 20px;font-size:14px;font-weight:700;border-bottom:1px solid var(--border);color:var(--muted);text-transform:uppercase;letter-spacing:1px;position:sticky;top:0;background:var(--card);z-index:10}
.cl-item{padding:16px 20px;border-bottom:1px solid var(--border);cursor:pointer;transition:all .2s;display:block;text-decoration:none}
.cl-item:hover{background:var(--bg)}
.cl-item.active{background:var(--blue);border-color:var(--blue)}
.cl-name{font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px;display:flex;align-items:center;gap:8px}
.cl-item.active .cl-name{color:#fff}
.cl-addr{font-size:12.5px;color:var(--muted);line-height:1.4}
.cl-item.active .cl-addr{color:rgba(255,255,255,.8)}

.st-view{flex:1;overflow-y:auto;padding:24px 32px;background:var(--bg)}
.st-header{margin-bottom:24px}
.st-title{font-size:24px;font-weight:800;margin-bottom:8px;display:flex;align-items:center;gap:10px}
.st-desc{font-size:14px;color:var(--muted);display:flex;align-items:center;gap:16px}
.st-desc span{display:flex;align-items:center;gap:6px}

.date-tabs{display:flex;gap:8px;margin-bottom:24px;overflow-x:auto;padding-bottom:4px}
.date-tab{padding:10px 20px;background:var(--card);border-radius:12px;cursor:pointer;text-align:center;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s;flex-shrink:0}
.date-tab:hover{border-color:var(--blue);color:var(--text)}
.date-tab.active{background:var(--blue);color:#fff;border-color:var(--blue)}
.dt-day{font-size:11px;font-weight:700;text-transform:uppercase}
.dt-date{font-size:16px;font-weight:800;margin-top:2px}

.ms-card{background:var(--card);border-radius:var(--radius);padding:20px;margin-bottom:20px;display:flex;gap:20px;box-shadow:var(--sh)}
.ms-poster{width:110px;height:155px;border-radius:10px;object-fit:cover;background:#e2e8f0;flex-shrink:0}
.ms-poster-ph{width:110px;height:155px;border-radius:10px;flex-shrink:0;background:#1e293b;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:32px}
.ms-info{flex:1}
.ms-title{font-size:18px;font-weight:800;margin-bottom:8px;color:var(--text)}
.ms-meta{font-size:13px;color:var(--muted);margin-bottom:16px;display:flex;align-items:center;gap:12px}
.ms-age{padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;background:#FEF3C7;color:#92400E}
.ms-age.t18{background:#FEE2E2;color:#991B1B}
.ms-age.t13{background:#DCFCE7;color:#166534}

.ts-grid{display:flex;flex-wrap:wrap;gap:10px}
.ts-btn{display:inline-flex;flex-direction:column;align-items:center;padding:8px 16px;background:#fff;border:1.5px solid var(--border);border-radius:8px;text-decoration:none;color:var(--text);transition:all .2s;min-width:80px}
.ts-btn:hover{background:var(--blue);border-color:var(--blue);color:#fff}
.ts-time{font-size:15px;font-weight:800}
.ts-fmt{font-size:11px;color:var(--muted);margin-top:2px}
.ts-btn:hover .ts-fmt{color:rgba(255,255,255,.8)}

.empty-state{text-align:center;padding:60px 20px;color:var(--muted)}
.empty-state i{font-size:48px;opacity:.2;margin-bottom:16px}
.empty-state h3{font-size:18px;font-weight:700}

.skeleton{background:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);background-size:200% 100%;animation:shimmer 1.2s infinite}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

::-webkit-scrollbar{width:6px;height:6px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}
::-webkit-scrollbar-thumb:hover{background:#94a3b8}
</style>
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <h1><i class="fa-solid fa-location-dot" style="color:var(--blue);margin-right:8px"></i>Hệ thống rạp chiếu</h1>
  </div>
  <div class="content">
    <div class="cinema-list">
      <div class="cl-header">Danh sách rạp</div>
      <div id="cinema-list-items">
        <!-- skeleton -->
        <?php for($i=0;$i<4;$i++): ?>
        <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
          <div class="skeleton" style="height:16px;border-radius:4px;margin-bottom:8px"></div>
          <div class="skeleton" style="height:12px;border-radius:4px;width:70%"></div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
    <div class="st-view" id="st-view">
      <div class="empty-state">
        <i class="fa-solid fa-location-dot"></i>
        <h3>Chọn rạp để xem lịch chiếu</h3>
      </div>
    </div>
  </div>
</div>

<script>
const API = '/be/api.php';
let allCinemas = [];
let currentCinemaId = null;
let currentDate = null;
let groupedShowtimes = {};

const DAY_NAMES = ['CN','T2','T3','T4','T5','T6','T7'];

function escHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDateTab(dateStr) {
  const d = new Date(dateStr + 'T00:00:00');
  const today = new Date(); today.setHours(0,0,0,0);
  const tomorrow = new Date(today); tomorrow.setDate(today.getDate()+1);
  const dayName = DAY_NAMES[d.getDay()];
  const dd = String(d.getDate()).padStart(2,'0');
  const mm = String(d.getMonth()+1).padStart(2,'0');
  return { dayName, display: `${dd}/${mm}`, isToday: d.getTime()===today.getTime(), isTomorrow: d.getTime()===tomorrow.getTime() };
}

async function loadCinemas() {
  try {
    const res = await fetch(`${API}?action=cinemas_list`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message);
    allCinemas = data.data;
    renderCinemaList();
    if (allCinemas.length > 0) selectCinema(allCinemas[0].id);
  } catch(err) {
    document.getElementById('cinema-list-items').innerHTML =
      `<div style="padding:20px;color:#ef4444;font-size:13px">${escHtml(err.message)}</div>`;
  }
}

function renderCinemaList() {
  const el = document.getElementById('cinema-list-items');
  el.innerHTML = allCinemas.map(c => `
    <div class="cl-item ${c.id == currentCinemaId ? 'active' : ''}" onclick="selectCinema(${c.id})" id="cl-${c.id}">
      <div class="cl-name"><i class="fa-solid fa-camera-movie"></i> ${escHtml(c.name)}</div>
      <div class="cl-addr">${escHtml(c.address)}</div>
    </div>`).join('');
}

async function selectCinema(id) {
  currentCinemaId = id;
  currentDate = null;
  groupedShowtimes = {};

  document.querySelectorAll('.cl-item').forEach(el => {
    el.classList.toggle('active', el.id === `cl-${id}`);
  });

  const cinema = allCinemas.find(c => c.id == id);
  if (!cinema) return;

  document.getElementById('st-view').innerHTML = `
    <div class="st-header">
      <h2 class="st-title"><i class="fa-solid fa-camera-movie" style="color:var(--blue)"></i> ${escHtml(cinema.name)}</h2>
      <div class="st-desc">
        <span><i class="fa-solid fa-map-location-dot"></i> ${escHtml(cinema.address + (cinema.city ? ', ' + cinema.city : ''))}</span>
        <span><i class="fa-solid fa-phone"></i> ${escHtml(cinema.phone || '—')}</span>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:16px">
      ${[1,2,3].map(() => `
        <div style="background:var(--card);border-radius:14px;padding:20px;box-shadow:var(--sh)">
          <div class="skeleton" style="height:20px;border-radius:4px;margin-bottom:12px;width:40%"></div>
          <div class="skeleton" style="height:40px;border-radius:8px"></div>
        </div>`).join('')}
    </div>`;

  try {
    const res = await fetch(`${API}?action=cinema_showtimes&cinema_id=${id}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message);
    groupedShowtimes = data.data || {};
    const dates = Object.keys(groupedShowtimes);
    currentDate = dates[0] || null;
    renderShowtimePanel(cinema, dates);
  } catch(err) {
    document.getElementById('st-view').innerHTML += `
      <div class="empty-state"><i class="fa-solid fa-circle-exclamation"></i>
        <h3>${escHtml(err.message)}</h3></div>`;
  }
}

function renderShowtimePanel(cinema, dates) {
  const stView = document.getElementById('st-view');

  if (dates.length === 0) {
    stView.innerHTML = `
      <div class="st-header">
        <h2 class="st-title"><i class="fa-solid fa-camera-movie" style="color:var(--blue)"></i> ${escHtml(cinema.name)}</h2>
        <div class="st-desc">
          <span><i class="fa-solid fa-map-location-dot"></i> ${escHtml(cinema.address + (cinema.city ? ', '+cinema.city : ''))}</span>
          <span><i class="fa-solid fa-phone"></i> ${escHtml(cinema.phone || '—')}</span>
        </div>
      </div>
      <div class="empty-state">
        <i class="fa-solid fa-calendar-xmark"></i>
        <h3>Chưa có lịch chiếu</h3>
        <p>Rạp này hiện chưa có suất chiếu nào trong những ngày tới.</p>
      </div>`;
    return;
  }

  const dateTabs = dates.map(d => {
    const f = formatDateTab(d);
    return `<div class="date-tab ${d===currentDate?'active':''}" onclick="selectDate('${d}')"
              id="dtab-${d}">
      <div class="dt-day">${f.dayName}</div>
      <div class="dt-date">${f.display}</div>
    </div>`;
  }).join('');

  stView.innerHTML = `
    <div class="st-header">
      <h2 class="st-title"><i class="fa-solid fa-camera-movie" style="color:var(--blue)"></i> ${escHtml(cinema.name)}</h2>
      <div class="st-desc">
        <span><i class="fa-solid fa-map-location-dot"></i> ${escHtml(cinema.address + (cinema.city ? ', '+cinema.city : ''))}</span>
        <span><i class="fa-solid fa-phone"></i> ${escHtml(cinema.phone || '—')}</span>
      </div>
    </div>
    <div class="date-tabs">${dateTabs}</div>
    <div id="showtime-movies"></div>`;

  renderMoviesForDate(currentDate);
}

function selectDate(date) {
  currentDate = date;
  document.querySelectorAll('.date-tab').forEach(el => {
    el.classList.toggle('active', el.id === `dtab-${date}`);
  });
  renderMoviesForDate(date);
}

function renderMoviesForDate(date) {
  const container = document.getElementById('showtime-movies');
  if (!container) return;

  const moviesOnDate = groupedShowtimes[date] || {};
  const movieIds = Object.keys(moviesOnDate);

  if (movieIds.length === 0) {
    container.innerHTML = `<div class="empty-state"><i class="fa-solid fa-film"></i><h3>Không có phim nào chiếu ngày này</h3></div>`;
    return;
  }

  container.innerHTML = movieIds.map(mid => {
    const { movie, slots } = moviesOnDate[mid];
    const poster = movie.poster_url
      ? `<img src="${escHtml(movie.poster_url)}" class="ms-poster" alt="">`
      : `<div class="ms-poster-ph"><i class="fa-solid fa-film"></i></div>`;
    const ageClass = (movie.age_rating || '').toLowerCase();
    const timeSlots = slots.map(s =>
      `<a href="seat-select.php?id=${s.id}" class="ts-btn">
        <span class="ts-time">${s.start_time.substring(0,5)}</span>
        <span class="ts-fmt">${escHtml(s.format)} ${escHtml(s.subtitle_type)}</span>
      </a>`).join('');

    return `
      <div class="ms-card">
        ${poster}
        <div class="ms-info">
          <h3 class="ms-title">${escHtml(movie.title)}</h3>
          <div class="ms-meta">
            <span class="ms-age ${ageClass}">${escHtml(movie.age_rating || '')}</span>
            <span>${escHtml(movie.genre || '')}</span>
          </div>
          <div class="ts-grid">${timeSlots}</div>
        </div>
      </div>`;
  }).join('');
}

loadCinemas();
</script>
</body>
</html>