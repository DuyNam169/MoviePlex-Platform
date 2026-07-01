<?php
session_start();
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'admin_monitor'])) {
    header('Location: ../pages/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieFlex Admin - Báo cáo Doanh thu</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .revenue-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }
        .progress-bar-wrap {
            width: 100%;
            height: 8px;
            background-color: #E2E8F0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 6px;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-blue), #7C3AED);
            border-radius: 4px;
        }
        .stat-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--border-color);
        }
        .stat-detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <div class="dashboard">
                <div class="dashboard-header">
                    <div>
                        <h1>Báo cáo & Thống kê Doanh thu</h1>
                        <p>Theo dõi các chỉ số tài chính, doanh số bán vé, và hiệu suất doanh thu phòng vé thời gian thực.</p>
                    </div>
                    <div class="dashboard-actions" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <!-- Bộ lọc thời gian -->
                        <div class="filter-item" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 6px 12px; background: white; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <i class="fa-solid fa-clock-rotate-left" style="color: var(--text-muted); font-size: 14px;"></i>
                            <select id="time-filter" onchange="applyTimeFilter(this.value)" style="border: none; outline: none; font-size: 13.5px; font-weight: 600; cursor: pointer; font-family: inherit; color: var(--text-main); background: transparent; padding-right: 4px;">
                                <option value="today">Hôm nay</option>
                                <option value="week">Tuần này</option>
                                <option value="month">Tháng này</option>
                                <option value="year">Năm nay</option>
                                <option value="custom" id="time-filter-custom" disabled>Khoảng ngày</option>
                            </select>
                        </div>

                        <!-- Lọc theo khoảng ngày -->
                        <div class="filter-item" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 4px 8px; background: white; display: flex; align-items: center; gap: 6px; font-size: 13.5px; font-weight: 600; color: var(--text-muted); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <span>Từ</span>
                            <input type="date" id="filter-start-date" style="border: none; outline: none; font-size: 13.5px; font-weight: 600; cursor: pointer; font-family: inherit; color: var(--text-main);">
                        </div>
                        <div class="filter-item" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 4px 8px; background: white; display: flex; align-items: center; gap: 6px; font-size: 13.5px; font-weight: 600; color: var(--text-muted); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <span>Đến</span>
                            <input type="date" id="filter-end-date" style="border: none; outline: none; font-size: 13.5px; font-weight: 600; cursor: pointer; font-family: inherit; color: var(--text-main);">
                        </div>
                        <button class="btn btn-primary" onclick="applyCustomDateFilter()" style="padding: 8px 16px; font-size: 13.5px; font-weight: 600; border-radius: 8px;"><i class="fa-solid fa-filter"></i> Lọc</button>

                        <button class="btn btn-outline" onclick="window.print()"><i class="fa-solid fa-print"></i> In báo cáo</button>
                    </div>
                </div>

                <!-- KPI Overview Cards -->
                <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
                    <div class="kpi-card" style="display:flex; flex-direction:column; justify-content:space-between; padding: 20px; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                        <div>
                            <div class="kpi-icon blue" style="width:40px; height:40px; border-radius:10px;"><i class="fa-solid fa-coins"></i></div>
                            <p class="kpi-label" style="font-size:11px; margin-top:12px;">TỔNG DOANH THU</p>
                            <h3 class="kpi-value" id="kpi-total-revenue" style="font-size:20px; font-weight:700; color:var(--text-main); margin:4px 0 8px;">0₫</h3>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px dashed var(--border-color); font-size: 11px; color: var(--text-muted);">
                            <span id="kpi-online-revenue"><i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>0₫</strong></span>
                            <span id="kpi-direct-revenue"><i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>0₫</strong></span>
                        </div>
                    </div>
                    <div class="kpi-card" style="display:flex; flex-direction:column; justify-content:space-between; padding: 20px; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                        <div>
                            <div class="kpi-icon green" style="width:40px; height:40px; border-radius:10px;"><i class="fa-solid fa-ticket"></i></div>
                            <p class="kpi-label" style="font-size:11px; margin-top:12px;">VÉ PHIM ĐÃ BÁN</p>
                            <h3 class="kpi-value" id="kpi-total-tickets" style="font-size:20px; font-weight:700; color:var(--text-main); margin:4px 0 8px;">0 vé</h3>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px dashed var(--border-color); font-size: 11px; color: var(--text-muted);">
                            <span id="kpi-online-tickets"><i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>0</strong></span>
                            <span id="kpi-direct-tickets"><i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>0</strong></span>
                        </div>
                    </div>
                    <div class="kpi-card" style="display:flex; flex-direction:column; justify-content:space-between; padding: 20px; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                        <div>
                            <div class="kpi-icon teal" style="width:40px; height:40px; border-radius:10px;"><i class="fa-solid fa-cart-shopping"></i></div>
                            <p class="kpi-label" style="font-size:11px; margin-top:12px;">DOANH THU BẮP NƯỚC</p>
                            <h3 class="kpi-value" id="kpi-snack-revenue" style="font-size:20px; font-weight:700; color:var(--text-main); margin:4px 0 8px;">0₫</h3>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px dashed var(--border-color); font-size: 11px; color: var(--text-muted);">
                            <span id="kpi-online-snack-revenue"><i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>0₫</strong></span>
                            <span id="kpi-direct-snack-revenue"><i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>0₫</strong></span>
                        </div>
                    </div>
                    <div class="kpi-card" style="display:flex; flex-direction:column; justify-content:space-between; padding: 20px; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                        <div>
                            <div class="kpi-icon red" style="width:40px; height:40px; border-radius:10px;"><i class="fa-solid fa-receipt"></i></div>
                            <p class="kpi-label" style="font-size:11px; margin-top:12px;">TỔNG GIAO DỊCH</p>
                            <h3 class="kpi-value" id="kpi-total-bookings" style="font-size:20px; font-weight:700; color:var(--text-main); margin:4px 0 8px;">0 đơn</h3>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px dashed var(--border-color); font-size: 11px; color: var(--text-muted);">
                            <span id="kpi-online-bookings"><i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>0</strong></span>
                            <span id="kpi-direct-bookings"><i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>0</strong></span>
                        </div>
                    </div>
                    <div class="kpi-card" style="display:flex; flex-direction:column; justify-content:space-between; padding: 20px; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                        <div>
                            <div class="kpi-icon gray" style="width:40px; height:40px; border-radius:10px;"><i class="fa-solid fa-calculator"></i></div>
                            <p class="kpi-label" style="font-size:11px; margin-top:12px;">GIÁ VÉ TRUNG BÌNH</p>
                            <h3 class="kpi-value" id="kpi-avg-ticket" style="font-size:20px; font-weight:700; color:var(--text-main); margin:4px 0 8px;">0₫</h3>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px dashed var(--border-color); font-size: 11px; color: var(--text-muted);">
                            <span id="kpi-online-avg-ticket"><i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>0₫</strong></span>
                            <span id="kpi-direct-avg-ticket"><i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>0₫</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Charts & Stats Details Grid -->
                <div class="revenue-grid">
                    <!-- Line Chart Card -->
                    <div class="card" style="margin-bottom: 0;">
                        <div class="card-header">
                            <h3 id="chart-title-text">Xu hướng doanh số bán hàng</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="salesTrendChart"></canvas>
                        </div>
                    </div>

                    <!-- Revenue Details Breakdowns -->
                    <div class="card" style="margin-bottom: 0; display:flex; flex-direction:column; justify-content:space-between; padding: 24px; box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                        <div>
                            <div class="card-header" style="margin-bottom: 16px;">
                                <h3 style="font-size:15px; font-weight:700; color:var(--text-main);"><i class="fa-solid fa-chart-pie" style="color:var(--primary-blue); margin-right:8px;"></i>Cơ cấu Doanh thu chi tiết</h3>
                            </div>
                            <div class="stat-detail-item" style="padding-bottom: 16px; margin-bottom: 16px;">
                                <div>
                                    <span style="font-weight:600; color:var(--text-main); font-size:13.5px;">Doanh thu vé xem phim</span>
                                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                                        <span style="margin-right:12px;"><i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:4px;"></i>Online: <strong id="breakdown-ticket-online">0₫</strong></span>
                                        <span><i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:4px;"></i>Tại quầy: <strong id="breakdown-ticket-direct">0₫</strong></span>
                                    </div>
                                </div>
                                <strong id="breakdown-ticket-revenue" style="color:var(--primary-blue); font-size:15px;">0₫</strong>
                            </div>
                            <div class="stat-detail-item" style="padding-bottom: 16px; margin-bottom: 16px;">
                                <div>
                                    <span style="font-weight:600; color:var(--text-main); font-size:13.5px;">Doanh thu bắp nước & Dịch vụ</span>
                                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                                        <span style="margin-right:12px;"><i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:4px;"></i>Online: <strong id="breakdown-snack-online">0₫</strong></span>
                                        <span><i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:4px;"></i>Tại quầy: <strong id="breakdown-snack-direct">0₫</strong></span>
                                    </div>
                                </div>
                                <strong id="breakdown-snack-revenue" style="color:var(--primary-blue); font-size:15px;">0₫</strong>
                            </div>
                        </div>

                        <div style="margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                            <span style="font-weight:700; font-size:13px; color:var(--text-main);">Tỉ lệ cơ cấu Doanh thu (Vé vs Bắp nước)</span>
                            <div style="display:flex; justify-content:space-between; font-size:12px; margin-top:10px; font-weight:600; color:var(--text-muted);">
                                <span id="pct-ticket-text">Vé: 80%</span>
                                <span id="pct-snack-text">Bắp nước: 20%</span>
                            </div>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" id="pct-progress-fill" style="width: 80%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tables breakdowns: Movie vs Cinema Performance -->
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-bottom: 24px;">
                    <!-- Movie Performance Table -->
                    <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                        <div class="card-header">
                            <h3 style="font-size:15px; font-weight:700;"><i class="fa-solid fa-film" style="color:var(--primary-blue); margin-right:8px;"></i>Hiệu suất Doanh thu theo Phim</h3>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tên Phim</th>
                                    <th class="text-center">Số đơn</th>
                                    <th class="text-center">Vé bán</th>
                                    <th style="text-align: right;">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody id="movie-revenue-body">
                                <tr><td colspan="4" class="text-center" style="padding: 20px; color: var(--text-muted);">Đang tải dữ liệu phim...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cinema Performance Table -->
                    <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-md);">
                        <div class="card-header">
                            <h3 style="font-size:15px; font-weight:700;"><i class="fa-solid fa-location-dot" style="color:var(--primary-blue); margin-right:8px;"></i>Hiệu suất Doanh thu theo Chi nhánh rạp</h3>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tên Rạp chiếu</th>
                                    <th class="text-center">Số đơn</th>
                                    <th class="text-center">Vé bán</th>
                                    <th style="text-align: right;">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody id="cinema-revenue-body">
                                <tr><td colspan="4" class="text-center" style="padding: 20px; color: var(--text-muted);">Đang tải dữ liệu rạp...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detailed Transactions Table -->
                <div class="card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-md); margin-bottom: 24px;">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                        <h3 style="font-size:15px; font-weight:700;"><i class="fa-solid fa-list-check" style="color:var(--primary-blue); margin-right:8px;"></i>Danh sách giao dịch chi tiết (Tối đa 50 giao dịch gần nhất)</h3>
                        <span style="font-size:11.5px; color:var(--text-muted); font-weight:600;"><i class="fa-solid fa-circle-info"></i> Hiển thị đầy đủ vé & bắp nước trong ngày đã lọc</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="data-table" style="min-width:1050px;">
                            <thead>
                                <tr>
                                    <th>MÃ HÓA ĐƠN</th>
                                    <th>THỜI GIAN</th>
                                    <th>KHÁCH HÀNG</th>
                                    <th class="text-center">HÌNH THỨC</th>
                                    <th>PHIM & RẠP CHIẾU</th>
                                    <th class="text-center">SỐ VÉ</th>
                                    <th>THANH TOÁN</th>
                                    <th style="text-align: right;">TỔNG TIỀN</th>
                                </tr>
                            </thead>
                            <tbody id="detailed-bookings-body">
                                <tr><td colspan="8" class="text-center" style="padding: 40px; color: var(--text-muted); font-weight:600;">Đang tải danh sách giao dịch...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Include global script for dynamic utility methods and toasts -->
    <script src="../assets/js/script.js"></script>

    <script>
        let salesChart = null;

        function escapeHtml(text) {
            if (!text) return '';
            return text.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        async function loadRevenueReport(params = {}) {
            // Show loading indicators
            const kpiElements = [
                'kpi-total-revenue', 'kpi-total-tickets', 'kpi-snack-revenue', 'kpi-total-bookings', 'kpi-avg-ticket',
                'breakdown-ticket-revenue', 'breakdown-ticket-online', 'breakdown-ticket-direct',
                'breakdown-snack-revenue', 'breakdown-snack-online', 'breakdown-snack-direct'
            ];
            
            kpiElements.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = '...';
            });

            const kpiSubElements = [
                'kpi-online-revenue', 'kpi-direct-revenue', 'kpi-online-tickets', 'kpi-direct-tickets',
                'kpi-online-snack-revenue', 'kpi-direct-snack-revenue', 'kpi-online-bookings', 'kpi-direct-bookings',
                'kpi-online-avg-ticket', 'kpi-direct-avg-ticket'
            ];
            kpiSubElements.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.innerHTML = el.innerHTML.replace(/<strong>.*?<\/strong>/g, '<strong>...</strong>');
                }
            });

            document.getElementById('movie-revenue-body').innerHTML = `
                <tr><td colspan="4" class="text-center" style="padding: 20px; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</td></tr>
            `;
            document.getElementById('cinema-revenue-body').innerHTML = `
                <tr><td colspan="4" class="text-center" style="padding: 20px; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</td></tr>
            `;
            document.getElementById('detailed-bookings-body').innerHTML = `
                <tr><td colspan="8" class="text-center" style="padding: 40px; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách giao dịch...</td></tr>
            `;

            try {
                const queryParams = new URLSearchParams();
                queryParams.append('action', 'admin_revenue_report');
                for (const [key, val] of Object.entries(params)) {
                    if (val) queryParams.append(key, val);
                }

                const res = await fetch('../../be/api.php?' + queryParams.toString());
                const json = await res.json();
                
                if (!json.success) {
                    mfToast('Lỗi', json.message || 'Lỗi tải báo cáo doanh thu', 'danger');
                    return;
                }

                // Update filters state
                document.getElementById('filter-start-date').value = json.startDate;
                document.getElementById('filter-end-date').value = json.endDate;
                
                const timeFilterSelect = document.getElementById('time-filter');
                const customOpt = document.getElementById('time-filter-custom');
                
                if (json.filter === 'custom') {
                    customOpt.disabled = false;
                    timeFilterSelect.value = 'custom';
                } else {
                    customOpt.disabled = true;
                    timeFilterSelect.value = json.filter;
                }

                // Format dates for title
                const formatTitleDate = (dStr) => {
                    if (!dStr) return '';
                    const p = dStr.split('-');
                    return `${p[2]}/${p[1]}/${p[0]}`;
                };
                document.getElementById('chart-title-text').textContent = `Xu hướng doanh số bán hàng (Từ ${formatTitleDate(json.startDate)} đến ${formatTitleDate(json.endDate)})`;

                // Format currencies and numbers helper
                const fmtCurr = (val) => new Intl.NumberFormat('vi-VN').format(val) + '₫';
                const fmtNum = (val) => new Intl.NumberFormat('vi-VN').format(val);

                // Update KPIs
                const kpis = json.kpis;
                document.getElementById('kpi-total-revenue').textContent = fmtCurr(kpis.total_revenue);
                document.getElementById('kpi-online-revenue').innerHTML = `<i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>${fmtCurr(kpis.online.revenue)}</strong>`;
                document.getElementById('kpi-direct-revenue').innerHTML = `<i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>${fmtCurr(kpis.direct.revenue)}</strong>`;

                document.getElementById('kpi-total-tickets').textContent = fmtNum(kpis.total_tickets) + ' vé';
                document.getElementById('kpi-online-tickets').innerHTML = `<i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>${fmtNum(kpis.online.tickets)}</strong>`;
                document.getElementById('kpi-direct-tickets').innerHTML = `<i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>${fmtNum(kpis.direct.tickets)}</strong>`;

                document.getElementById('kpi-snack-revenue').textContent = fmtCurr(kpis.snack_revenue);
                document.getElementById('kpi-online-snack-revenue').innerHTML = `<i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>${fmtCurr(kpis.online.snack_revenue)}</strong>`;
                document.getElementById('kpi-direct-snack-revenue').innerHTML = `<i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>${fmtCurr(kpis.direct.snack_revenue)}</strong>`;

                document.getElementById('kpi-total-bookings').textContent = fmtNum(kpis.total_bookings) + ' đơn';
                document.getElementById('kpi-online-bookings').innerHTML = `<i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>${fmtNum(kpis.online.bookings)}</strong>`;
                document.getElementById('kpi-direct-bookings').innerHTML = `<i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>${fmtNum(kpis.direct.bookings)}</strong>`;

                const avgTicket = kpis.total_tickets > 0 ? (kpis.ticket_revenue / kpis.total_tickets) : 0;
                const onlineAvg = kpis.online.tickets > 0 ? (kpis.online.ticket_revenue / kpis.online.tickets) : 0;
                const directAvg = kpis.direct.tickets > 0 ? (kpis.direct.ticket_revenue / kpis.direct.tickets) : 0;
                document.getElementById('kpi-avg-ticket').textContent = fmtCurr(avgTicket);
                document.getElementById('kpi-online-avg-ticket').innerHTML = `<i class="fa-solid fa-globe" style="color:var(--primary-blue); margin-right:3px;"></i> Online: <strong>${fmtCurr(onlineAvg)}</strong>`;
                document.getElementById('kpi-direct-avg-ticket').innerHTML = `<i class="fa-solid fa-shop" style="color:#F59E0B; margin-right:3px;"></i> Quầy: <strong>${fmtCurr(directAvg)}</strong>`;

                // Update breakdowns
                document.getElementById('breakdown-ticket-revenue').textContent = fmtCurr(kpis.ticket_revenue);
                document.getElementById('breakdown-ticket-online').textContent = fmtCurr(kpis.online.ticket_revenue);
                document.getElementById('breakdown-ticket-direct').textContent = fmtCurr(kpis.direct.ticket_revenue);

                document.getElementById('breakdown-snack-revenue').textContent = fmtCurr(kpis.snack_revenue);
                document.getElementById('breakdown-snack-online').textContent = fmtCurr(kpis.online.snack_revenue);
                document.getElementById('breakdown-snack-direct').textContent = fmtCurr(kpis.direct.snack_revenue);

                // Update progress bar
                const ticketPct = kpis.total_revenue > 0 ? (kpis.ticket_revenue / kpis.total_revenue) * 100 : 80;
                const snackPct = 100 - ticketPct;
                document.getElementById('pct-ticket-text').textContent = `Vé: ${Math.round(ticketPct)}%`;
                document.getElementById('pct-snack-text').textContent = `Bắp nước: ${Math.round(snackPct)}%`;
                document.getElementById('pct-progress-fill').style.width = `${ticketPct}%`;

                // Render line chart
                const salesData = json.daily_sales;
                const labels = salesData.map(item => item.day_label);
                const onlineRevenues = salesData.map(item => parseFloat(item.online_revenue) || 0);
                const directRevenues = salesData.map(item => parseFloat(item.direct_revenue) || 0);

                if (salesChart) {
                    salesChart.destroy();
                }

                const ctx = document.getElementById('salesTrendChart').getContext('2d');
                salesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Doanh thu Online (₫)',
                                data: onlineRevenues,
                                borderColor: '#4F46E5',
                                backgroundColor: 'rgba(79, 70, 229, 0.04)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#4F46E5',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Doanh thu tại Quầy (₫)',
                                data: directRevenues,
                                borderColor: '#F59E0B',
                                backgroundColor: 'rgba(245, 158, 11, 0.04)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#F59E0B',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font: {
                                        family: 'Inter',
                                        weight: '600',
                                        size: 12
                                    },
                                    color: '#0F172A',
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#F1F3F5'
                                },
                                ticks: {
                                    callback: function(value) {
                                        if (value >= 1000000) {
                                            return (value / 1000000).toFixed(1) + 'M';
                                        }
                                        if (value >= 1000) {
                                            return (value / 1000).toFixed(0) + 'K';
                                        }
                                        return value;
                                    }
                                },
                                border: {
                                    display: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                border: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                // Render Movie Performance Table
                const movieBody = document.getElementById('movie-revenue-body');
                movieBody.innerHTML = '';
                if (json.movie_revenue.length === 0) {
                    movieBody.innerHTML = `<tr><td colspan="4" class="text-center" style="padding: 20px; color: var(--text-muted);">Chưa có giao dịch phim nào.</td></tr>`;
                } else {
                    json.movie_revenue.slice(0, 5).forEach(mr => {
                        const posterHTML = mr.poster_url ? `<img src="${escapeHtml(mr.poster_url)}" alt="" style="width:25px; height:35px; object-fit:cover; border-radius:4px;">` : '';
                        movieBody.innerHTML += `
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        ${posterHTML}
                                        <span style="font-weight:700; color:#111;">${escapeHtml(mr.title)}</span>
                                    </div>
                                </td>
                                <td class="text-center"><strong>${mr.bookings_count}</strong> đơn</td>
                                <td class="text-center"><strong>${mr.tickets_count}</strong> vé</td>
                                <td style="text-align: right; font-weight:700; color:var(--primary-blue);">${fmtCurr(mr.revenue)}</td>
                            </tr>
                        `;
                    });
                }

                // Render Cinema Performance Table
                const cinemaBody = document.getElementById('cinema-revenue-body');
                cinemaBody.innerHTML = '';
                if (json.cinema_revenue.length === 0) {
                    cinemaBody.innerHTML = `<tr><td colspan="4" class="text-center" style="padding: 20px; color: var(--text-muted);">Chưa có giao dịch rạp nào.</td></tr>`;
                } else {
                    json.cinema_revenue.forEach(cr => {
                        cinemaBody.innerHTML += `
                            <tr>
                                <td><strong style="color:#111;"><i class="fa-solid fa-location-dot" style="color:var(--primary-blue); margin-right:6px;"></i>${escapeHtml(cr.cinema_name)}</strong></td>
                                <td class="text-center"><strong>${cr.bookings_count}</strong> đơn</td>
                                <td class="text-center"><strong>${cr.tickets_count}</strong> vé</td>
                                <td style="text-align: right; font-weight:700; color:var(--primary-blue);">${fmtCurr(cr.revenue)}</td>
                            </tr>
                        `;
                    });
                }

                // Render Detailed Transactions Table
                const bookingsBody = document.getElementById('detailed-bookings-body');
                bookingsBody.innerHTML = '';
                if (json.detailed_bookings.length === 0) {
                    bookingsBody.innerHTML = `<tr><td colspan="8" class="text-center" style="padding: 40px; color: var(--text-muted); font-weight:600;">Không tìm thấy giao dịch nào phù hợp trong khoảng thời gian này.</td></tr>`;
                } else {
                    json.detailed_bookings.forEach(b => {
                        const methodBadge = b.payment_method !== 'cash' 
                            ? `<span class="badge" style="background-color: var(--info-bg); color: var(--info-text); padding: 4px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-globe"></i> Online</span>`
                            : `<span class="badge" style="background-color: var(--warning-bg); color: var(--warning-text); padding: 4px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-shop"></i> Tại quầy</span>`;
                        
                        let payMethodHTML = '';
                        if (b.payment_method === 'cash') {
                            payMethodHTML = `<i class="fa-solid fa-money-bill-1" style="color:#15803D; margin-right:4px;"></i> Tiền mặt`;
                        } else if (b.payment_method === 'card') {
                            payMethodHTML = `<i class="fa-solid fa-credit-card" style="color:#0369A1; margin-right:4px;"></i> Thẻ (POS)`;
                        } else {
                            payMethodHTML = `<i class="fa-solid fa-wallet" style="color:#4F46E5; margin-right:4px;"></i> ${escapeHtml(b.payment_method)}`;
                        }

                        // Format time
                        const dt = new Date(b.created_at);
                        const pad = (n) => n.toString().padStart(2, '0');
                        const timeStr = `${pad(dt.getDate())}/${pad(dt.getMonth()+1)}/${dt.getFullYear()} ${pad(dt.getHours())}:${pad(dt.getMinutes())}`;

                        let seatsHTML = '';
                        try {
                            const seats = JSON.parse(b.seats_json) || [];
                            if (seats.length > 0) {
                                seatsHTML = `| Ghế: <strong>${escapeHtml(seats.join(', '))}</strong>`;
                            }
                        } catch(e) {}

                        bookingsBody.innerHTML += `
                            <tr>
                                <td><strong style="color:var(--text-main); font-family:monospace; font-size:13px;">${escapeHtml(b.booking_code)}</strong></td>
                                <td style="font-size:12px; color:var(--text-muted);">${timeStr}</td>
                                <td>
                                    <div style="font-weight:600; color:#111;">${escapeHtml(b.customer_name || 'Khách vãng lai')}</div>
                                </td>
                                <td class="text-center">${methodBadge}</td>
                                <td>
                                    <div style="font-weight:600; color:var(--text-main); font-size:13px;">${escapeHtml(b.movie_title)}</div>
                                    <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                        Rạp: <strong>${escapeHtml(b.cinema_name)}</strong> 
                                        ${seatsHTML}
                                    </div>
                                </td>
                                <td class="text-center"><strong>${b.num_tickets}</strong></td>
                                <td>
                                    <span style="font-size:12px; font-weight:600; color:var(--text-main); text-transform:uppercase;">
                                        ${payMethodHTML}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight:700; color:var(--primary-blue); font-size:14px;">${fmtCurr(b.total_amount)}</td>
                            </tr>
                        `;
                    });
                }
            } catch (e) {
                console.error('Error fetching revenue report:', e);
                mfToast('Lỗi hệ thống', 'Không thể kết nối với máy chủ.', 'warning');
            }
        }

        function applyTimeFilter(value) {
            if (value === 'custom') return;
            loadRevenueReport({ filter: value });
        }

        function applyCustomDateFilter() {
            const start = document.getElementById('filter-start-date').value;
            const end = document.getElementById('filter-end-date').value;
            
            if (!start || !end) {
                if (window.mfToast) {
                    window.mfToast('Chọn thiếu ngày', 'Vui lòng chọn đầy đủ cả Ngày bắt đầu và Ngày kết thúc.', 'warning', 5000);
                } else {
                    alert('Vui lòng chọn đầy đủ cả Ngày bắt đầu và Ngày kết thúc.');
                }
                return;
            }
            if (start > end) {
                if (window.mfToast) {
                    window.mfToast('Ngày không hợp lệ', 'Ngày bắt đầu không thể lớn hơn Ngày kết thúc.', 'danger', 5000);
                } else {
                    alert('Ngày bắt đầu không được lớn hơn Ngày kết thúc.');
                }
                return;
            }
            loadRevenueReport({ startDate: start, endDate: end });
        }

        // Initialize Page
        document.addEventListener('DOMContentLoaded', () => {
            // Get URL params for initial load if specified (e.g. from redirect or print page)
            const urlParams = new URLSearchParams(window.location.search);
            const initialParams = {};
            if (urlParams.has('filter')) initialParams.filter = urlParams.get('filter');
            if (urlParams.has('startDate')) initialParams.startDate = urlParams.get('startDate');
            if (urlParams.has('endDate')) initialParams.endDate = urlParams.get('endDate');

            loadRevenueReport(initialParams);
        });
    </script>
</body>
</html>
