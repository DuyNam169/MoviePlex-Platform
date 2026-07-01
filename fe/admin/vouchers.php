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
    <title>MovieFlex Admin - Quản lý Voucher</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .voucher-modal-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .form-group-custom {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group-custom label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }
        .form-input-custom {
            height: 40px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0 12px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-input-custom:focus {
            border-color: var(--primary-blue);
        }
        select.form-input-custom {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 10px center;
            background-repeat: no-repeat;
            background-size: 20px;
            padding-right: 30px;
        }
        .alert-bar {
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-bar.success {
            background-color: var(--success-bg);
            color: var(--success-text);
            border: 1px solid #C9F7D0;
        }
        .alert-bar.error {
            background-color: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid #FFCDD2;
        }
        .text-center {
            text-align: center;
        }
        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
        }
        .badge-status.active {
            background-color: var(--success-bg);
            color: var(--success-text);
        }
        .badge-status.inactive {
            background-color: var(--danger-bg);
            color: var(--danger-text);
        }
        .badge-status.expired {
            background-color: var(--warning-bg);
            color: var(--warning-text);
        }
        .action-btns {
            display: flex;
            gap: 8px;
        }
        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 15px;
            padding: 6px;
            border-radius: 6px;
            transition: background 0.2s, color 0.2s;
        }
        .action-btn.edit {
            color: var(--primary-blue);
        }
        .action-btn.edit:hover {
            background-color: #E8F0FE;
        }
        .action-btn.delete {
            color: var(--danger-text);
        }
        .action-btn.delete:hover {
            background-color: var(--danger-bg);
        }
        .action-btn.toggle {
            color: #4B5563;
        }
        .action-btn.toggle:hover {
            background-color: #F3F4F6;
        }

        /* VOUCHERS ADMIN GRID & CARDS */
        .vouchers-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 24px;
            padding: 20px 0;
        }
        .admin-voucher-card {
            display: flex;
            background: #fff;
            border-radius: 14px;
            border: 1.5px solid var(--border-color);
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(15,23,42,.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .admin-voucher-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(15,23,42,.08);
            border-color: var(--primary-blue);
        }
        .admin-voucher-left {
            background: linear-gradient(135deg, var(--primary-blue), #4F46E5);
            color: #fff;
            width: 105px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 10px;
            text-align: center;
            position: relative;
            flex-shrink: 0;
        }
        .admin-voucher-left.inactive-left {
            background: linear-gradient(135deg, #94A3B8, #64748B) !important;
        }
        .admin-voucher-left::after {
            content: '';
            position: absolute;
            right: -6px;
            top: 0;
            bottom: 0;
            width: 12px;
            background-image: radial-gradient(circle at 12px 6px, #FAFBFD 5px, transparent 5px);
            background-size: 12px 12px;
        }
        .admin-v-val {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .admin-v-type {
            font-size: 9px;
            opacity: .9;
            margin-top: 3px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .admin-voucher-right {
            flex: 1;
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #FAFBFD;
        }
        .admin-v-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            gap: 8px;
        }
        .admin-v-code {
            font-family: monospace;
            font-size: 14px;
            font-weight: 800;
            color: var(--primary-blue);
            background: #EFF6FF;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1.5px dashed #93C5FD;
            letter-spacing: 0.5px;
        }
        .admin-v-code.inactive-code {
            color: #64748B !important;
            background: #F1F5F9 !important;
            border-color: #CBD5E1 !important;
        }
        .admin-v-desc {
            font-size: 13.5px;
            font-weight: 800;
            color: #1F2937;
            line-height: 1.4;
            margin-bottom: 8px;
        }
        .admin-v-meta {
            font-size: 11.5px;
            color: #6B7280;
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }
        .admin-v-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .admin-v-meta-item i {
            color: #9CA3AF;
            width: 12px;
        }
        .admin-v-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
        }

        /* PREMIUM ADMIN STAT CARDS */
        .admin-stat-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            padding: 24px;
            border-radius: var(--radius-md);
            box-shadow: 0 4px 20px rgba(15,23,42,.02);
            border: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .admin-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(15,23,42,.06);
            border-color: var(--primary-blue);
        }
        .admin-stat-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .admin-stat-info .title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .admin-stat-info .value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1.2;
        }
        .admin-stat-info .desc {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .admin-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .admin-stat-icon.blue {
            background-color: #EFF6FF;
            color: var(--primary-blue);
            border: 1px solid #DBEAFE;
        }
        .admin-stat-icon.green {
            background-color: #ECFDF5;
            color: #10B981;
            border: 1px solid #D1FAE5;
        }
        .admin-stat-icon.red {
            background-color: #FEF2F2;
            color: #EF4444;
            border: 1px solid #FEE2E2;
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
                        <h1>Quản lý Mã giảm giá (Voucher)</h1>
                        <p>Tạo và quản lý các chương trình ưu đãi, mã voucher giảm giá cho rạp phim.</p>
                    </div>
                    <div class="dashboard-actions">
                        <button class="btn btn-primary" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Thêm Voucher mới</button>
                    </div>
                </div>

                <!-- Voucher Stats -->
                <div class="stat-cards-bottom" style="margin-bottom: 24px; margin-top: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <span class="title">TỔNG SỐ VOUCHER</span>
                            <span class="value" id="stat-total">0</span>
                            <span class="desc">Chương trình khuyến mãi</span>
                        </div>
                        <div class="admin-stat-icon blue">
                            <i class="fa-solid fa-tags"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <span class="title">ĐANG HOẠT ĐỘNG</span>
                            <span class="value" style="color: #10B981;" id="stat-active">0</span>
                            <span class="desc" style="color: #10B981;"><i class="fa-solid fa-circle" style="font-size: 8px; margin-right: 4px; vertical-align: middle;"></i> Đang có hiệu lực</span>
                        </div>
                        <div class="admin-stat-icon green">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-info">
                            <span class="title">ĐÃ HẾT HẠN / TẮT</span>
                            <span class="value" style="color: #EF4444;" id="stat-expired">0</span>
                            <span class="desc" style="color: #EF4444;">Không còn sử dụng</span>
                        </div>
                        <div class="admin-stat-icon red">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>
                    </div>
                </div>

                <!-- Voucher Table Card -->
                <div class="card">
                    <div class="filter-bar">
                        <div class="search-bar" style="width: 300px;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="search-input" placeholder="Tìm theo mã hoặc mô tả..." oninput="filterTable()">
                        </div>
                        <div class="filter-item">
                            <i class="fa-solid fa-percent"></i>
                            <select id="filter-type" onchange="filterTable()">
                                <option value="">Mọi loại giảm giá</option>
                                <option value="percentage">Giảm theo %</option>
                                <option value="fixed">Giảm tiền mặt</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <i class="fa-solid fa-toggle-on"></i>
                            <select id="filter-status" onchange="filterTable()">
                                <option value="">Mọi trạng thái</option>
                                <option value="active">Đang hoạt động</option>
                                <option value="inactive">Đã tắt</option>
                                <option value="expired">Hết hạn</option>
                            </select>
                        </div>
                    </div>

                    <!-- VOUCHER CARDS GRID -->
                    <div class="vouchers-admin-grid" id="vouchersBody">
                        <div style="text-align:center; padding:60px 20px; color:var(--text-muted); grid-column:1 / -1;">
                            <i class="fa-solid fa-spinner fa-spin" style="font-size:32px; display:block; margin-bottom:12px"></i>
                            <h3 style="font-size:15px; font-weight:700">Đang tải danh sách voucher...</h3>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Voucher Add / Edit Modal -->
    <div id="voucherModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 550px;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 20px;">
                <div class="modal-title" style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-tag" style="color:var(--primary-blue); font-size:18px;"></i>
                    <h3 id="modal-title-text" style="font-size:17px; font-weight:700;">Thêm Voucher mới</h3>
                </div>
                <button class="close-modal" onclick="closeModal()" style="border:none; background:none; font-size:20px; cursor:pointer; color:var(--text-muted);"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form id="voucherForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="form-id" value="0">
                
                <div class="voucher-modal-form">
                    <div class="form-group-custom">
                        <label>Mã Voucher *</label>
                        <input type="text" name="code" id="form-code" class="form-input-custom" placeholder="Ví dụ: SALE30" style="text-transform: uppercase;" required>
                    </div>
                    
                    <div class="form-group-custom">
                        <label>Trạng thái kích hoạt</label>
                        <select name="is_active" id="form-active" class="form-input-custom">
                            <option value="1">Kích hoạt</option>
                            <option value="0">Tạm tắt</option>
                        </select>
                    </div>

                    <div class="form-group-custom full-width">
                        <label>Mô tả ưu đãi *</label>
                        <input type="text" name="description" id="form-desc" class="form-input-custom" placeholder="Ví dụ: Giảm 30k cho hóa đơn từ 150k" required>
                    </div>

                    <div class="form-group-custom">
                        <label>Loại giảm giá</label>
                        <select name="discount_type" id="form-type" class="form-input-custom" onchange="toggleDiscountPlaceholder()">
                            <option value="pct">Giảm theo %</option>
                            <option value="amt">Giảm tiền mặt (₫)</option>
                        </select>
                    </div>

                    <div class="form-group-custom">
                        <label id="discount-label">Mức giảm (%) *</label>
                        <input type="number" name="discount_value" id="form-discount" class="form-input-custom" placeholder="Nhập mức giảm..." required min="1">
                    </div>

                    <div class="form-group-custom">
                        <label>Đơn tối thiểu (₫)</label>
                        <input type="number" name="min_order" id="form-min-order" class="form-input-custom" placeholder="Ví dụ: 100000" min="0" value="0">
                    </div>

                    <div class="form-group-custom">
                        <label>Giới hạn lượt dùng</label>
                        <input type="number" name="max_uses" id="form-max" class="form-input-custom" placeholder="Ví dụ: 100" min="1" value="100">
                    </div>

                    <div class="form-group-custom full-width">
                        <label>Ngày hết hạn</label>
                        <input type="date" name="expire_date" id="form-expire" class="form-input-custom">
                    </div>
                </div>

                <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:12px; border-top: 1px solid var(--border-color); padding-top: 16px; margin-top: 24px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Lưu thông tin</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let allVouchers = [];

        function escapeHtml(text) {
            if (!text) return '';
            return text.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function escapeQuote(text) {
            if (!text) return '';
            return text.toString().replace(/'/g, "\\'");
        }

        async function fetchStats() {
            try {
                const res = await fetch('../../be/api.php?action=admin_voucher_stats');
                const json = await res.json();
                if (json.success) {
                    document.getElementById('stat-total').textContent = json.stats.total;
                    document.getElementById('stat-active').textContent = json.stats.active;
                    document.getElementById('stat-expired').textContent = json.stats.expired;
                }
            } catch (e) {
                console.error('Error fetching stats:', e);
            }
        }

        async function fetchVouchers() {
            try {
                const res = await fetch('../../be/api.php?action=admin_voucher_list');
                const json = await res.json();
                if (json.success) {
                    allVouchers = json.data;
                    renderVouchers(allVouchers);
                } else {
                    document.getElementById('vouchersBody').innerHTML = `
                        <div style="text-align:center; padding:60px 20px; color:var(--text-muted); grid-column:1 / -1;">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:48px; opacity:.4; display:block; margin-bottom:12px"></i>
                            <h3 style="font-size:15px; font-weight:700">Lỗi: ${json.message}</h3>
                        </div>`;
                }
            } catch (e) {
                console.error('Error fetching vouchers:', e);
                document.getElementById('vouchersBody').innerHTML = `
                    <div style="text-align:center; padding:60px 20px; color:var(--text-muted); grid-column:1 / -1;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size:48px; opacity:.4; display:block; margin-bottom:12px"></i>
                        <h3 style="font-size:15px; font-weight:700">Không thể kết nối máy chủ.</h3>
                    </div>`;
            }
        }

        function renderVouchers(vouchersList) {
            const container = document.getElementById('vouchersBody');
            container.innerHTML = '';
            
            if (vouchersList.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:60px 20px; color:var(--text-muted); grid-column:1 / -1;">
                        <i class="fa-solid fa-tag" style="font-size:48px; opacity:.2; display:block; margin-bottom:12px"></i>
                        <h3 style="font-size:15px; font-weight:700">Không tìm thấy voucher nào</h3>
                    </div>`;
                return;
            }
            
            const todayStr = new Date().toISOString().split('T')[0];
            
            vouchersList.forEach(v => {
                const isExpired = v.expire_date !== null && v.expire_date < todayStr;
                let statusClass = 'inactive';
                let statusText = 'Đã tắt';
                if (parseInt(v.is_active) === 1) {
                    if (isExpired) {
                        statusClass = 'expired';
                        statusText = 'Hết hạn';
                    } else {
                        statusClass = 'active';
                        statusText = 'Đang chạy';
                    }
                }
                
                const valText = parseInt(v.discount_pct) > 0 ? v.discount_pct + '%' : (parseInt(v.discount_amt)/1000) + 'K';
                const typeText = parseInt(v.discount_pct) > 0 ? 'GIẢM GIÁ' : 'TIỀN MẶT';
                const typeVal = parseInt(v.discount_pct) > 0 ? 'percentage' : 'fixed';
                const isActive = (parseInt(v.is_active) === 1 && !isExpired);
                
                let typeBadge = '';
                if (v.user_id !== null) {
                    typeBadge = `<span style="font-size: 9px; font-weight: 800; background: #FFF7ED; color: #EA580C; padding: 2px 6px; border-radius: 4px; border: 1px solid #FFEDD5; display: inline-flex; align-items: center; gap: 3px;"><i class="fa-solid fa-user"></i> KH đã đổi</span>`;
                } else if (['REDM30K', 'REDM50K', 'REDM100K', 'GIFTPOP'].includes(v.code)) {
                    typeBadge = `<span style="font-size: 9px; font-weight: 800; background: #EEF2F6; color: #4F46E5; padding: 2px 6px; border-radius: 4px; border: 1px solid #C7D2FE; display: inline-flex; align-items: center; gap: 3px;"><i class="fa-solid fa-gift"></i> Shop đổi quà</span>`;
                } else {
                    typeBadge = `<span style="font-size: 9px; font-weight: 800; background: #ECFDF5; color: #059669; padding: 2px 6px; border-radius: 4px; border: 1px solid #A7F3D0; display: inline-flex; align-items: center; gap: 3px;"><i class="fa-solid fa-globe"></i> Mặc định / Sự kiện</span>`;
                }
                
                const formattedMinOrder = new Intl.NumberFormat('vi-VN').format(v.min_order);
                
                let formattedExpire = 'Vô thời hạn';
                if (v.expire_date) {
                    const parts = v.expire_date.split('-');
                    formattedExpire = `${parts[2]}/${parts[1]}/${parts[0]}`;
                }
                
                const card = document.createElement('div');
                card.className = 'admin-voucher-card';
                card.dataset.type = typeVal;
                card.dataset.status = statusClass;
                card.dataset.code = v.code;
                card.dataset.desc = v.description;
                
                card.innerHTML = `
                    <div class="admin-voucher-left ${isActive ? '' : 'inactive-left'}">
                        <span class="admin-v-val">${valText}</span>
                        <span class="admin-v-type">${typeText}</span>
                    </div>
                    <div class="admin-voucher-right">
                        <div>
                            <div class="admin-v-header">
                                <span class="admin-v-code ${isActive ? '' : 'inactive-code'}">${escapeHtml(v.code)}</span>
                                ${typeBadge}
                            </div>
                            <div class="admin-v-desc">${escapeHtml(v.description)}</div>
                        </div>
                        
                        <div>
                            <div class="admin-v-meta">
                                <div class="admin-v-meta-item">
                                    <i class="fa-regular fa-user"></i>
                                    <span>Lượt dùng: <strong>${v.used_count}</strong> / ${v.max_uses}</span>
                                </div>
                                <div class="admin-v-meta-item">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>Đơn tối thiểu: <strong>${formattedMinOrder}₫</strong></span>
                                </div>
                                <div class="admin-v-meta-item">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>HSD: <strong>${formattedExpire}</strong></span>
                                </div>
                                <div class="admin-v-meta-item">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Trạng thái: <span class="badge-status ${statusClass}" style="padding: 2px 8px; font-size:10px;">${statusText}</span></span>
                                </div>
                            </div>
                            
                            <div class="admin-v-actions">
                                <!-- Toggle Switch -->
                                <button type="button" class="admin-toggle-btn" title="${parseInt(v.is_active) === 1 ? 'Tắt hoạt động' : 'Kích hoạt'}" onclick="toggleVoucherStatus(${v.id}, ${parseInt(v.is_active) === 1 ? 0 : 1})">
                                    <i class="fa-solid ${parseInt(v.is_active) === 1 ? 'fa-toggle-on' : 'fa-toggle-off'}" style="font-size: 24px; color: ${parseInt(v.is_active) === 1 ? 'var(--success-text)' : 'var(--text-muted)'}; transition: color 0.2s;"></i>
                                    <span style="font-size: 11.5px; font-weight: 700; color: #4B5563; margin-left: 6px;">${parseInt(v.is_active) === 1 ? 'Đang bật' : 'Đang tắt'}</span>
                                </button>
                                
                                <!-- Edit / Delete Actions -->
                                <div class="action-btns" style="margin: 0;">
                                    <button class="action-btn edit" title="Chỉnh sửa" onclick="openEditModal(${v.id})" style="padding: 5px 8px; border: 1.5px solid #D1D5DB; border-radius: 8px;"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <button type="button" class="action-btn delete" title="Xóa" style="padding: 5px 8px; border: 1.5px solid rgba(239, 68, 68, 0.2); border-radius: 8px;" onclick="confirmDeleteVoucher(${v.id}, '${escapeQuote(v.code)}')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
            
            // Run table filtering in case user currently has query or filter active
            filterTable();
        }

        async function toggleVoucherStatus(id, newStatus) {
            try {
                const formData = new FormData();
                formData.append('action', 'admin_voucher_toggle');
                formData.append('id', id);
                formData.append('status', newStatus);
                
                const res = await fetch('../../be/api.php', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();
                
                if (json.success) {
                    mfToast('Thành công', json.message || 'Cập nhật trạng thái thành công!', 'success');
                    await fetchVouchers();
                    await fetchStats();
                } else {
                    mfToast('Lỗi', json.message || 'Không thể cập nhật trạng thái.', 'danger');
                }
            } catch (e) {
                console.error('Error toggling voucher status:', e);
                mfToast('Lỗi hệ thống', 'Không thể kết nối máy chủ.', 'warning');
            }
        }

        async function confirmDeleteVoucher(id, code) {
            const ok = await mfConfirm({
                title: 'Xóa mã Voucher',
                desc: `Bạn có chắc chắn muốn xóa vĩnh viễn voucher <strong>${code}</strong>?<br><br>Sau khi xóa, mã này sẽ không thể khôi phục và khách hàng sẽ không dùng được nữa.`,
                type: 'danger',
                confirmText: 'Xóa voucher',
                confirmIcon: 'fa-trash-can',
                cancelText: 'Giữ lại'
            });
            if (ok) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'admin_voucher_delete');
                    formData.append('id', id);
                    
                    const res = await fetch('../../be/api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const json = await res.json();
                    
                    if (json.success) {
                        mfToast('Thành công', json.message || 'Xóa voucher thành công!', 'success');
                        await fetchVouchers();
                        await fetchStats();
                    } else {
                        mfToast('Lỗi khi xóa', json.message || 'Đã xảy ra lỗi khi xóa voucher.', 'danger');
                    }
                } catch (e) {
                    console.error('Error deleting voucher:', e);
                    mfToast('Lỗi hệ thống', 'Không thể kết nối máy chủ.', 'warning');
                }
            }
        }

        document.getElementById('voucherForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';
            
            try {
                const formData = new FormData(this);
                formData.set('action', 'admin_voucher_save');
                
                const res = await fetch('../../be/api.php', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();
                
                if (json.success) {
                    mfToast('Thành công', json.message || 'Lưu voucher thành công!', 'success');
                    closeModal();
                    await fetchVouchers();
                    await fetchStats();
                } else {
                    mfToast('Lỗi', json.message || 'Có lỗi xảy ra khi lưu voucher.', 'danger');
                }
            } catch (err) {
                console.error('Error saving voucher:', err);
                mfToast('Lỗi hệ thống', 'Không thể kết nối máy chủ. Vui lòng thử lại.', 'warning');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHTML;
            }
        });

        function openAddModal() {
            document.getElementById('modal-title-text').textContent = 'Thêm Voucher mới';
            document.getElementById('form-id').value = '0';
            document.getElementById('form-code').value = '';
            document.getElementById('form-code').readOnly = false;
            document.getElementById('form-active').value = '1';
            document.getElementById('form-desc').value = '';
            document.getElementById('form-type').value = 'pct';
            document.getElementById('form-discount').value = '';
            document.getElementById('form-min-order').value = '0';
            document.getElementById('form-max').value = '100';
            document.getElementById('form-expire').value = '';
            
            toggleDiscountPlaceholder();
            document.getElementById('voucherModal').classList.add('active');
        }

        function openEditModal(id) {
            const v = allVouchers.find(item => item.id == id);
            if (!v) return;
            
            document.getElementById('modal-title-text').textContent = 'Chỉnh sửa Voucher #' + v.code;
            document.getElementById('form-id').value = v.id;
            document.getElementById('form-code').value = v.code;
            document.getElementById('form-code').readOnly = true;
            document.getElementById('form-active').value = v.is_active;
            document.getElementById('form-desc').value = v.description;
            
            if (parseInt(v.discount_pct) > 0) {
                document.getElementById('form-type').value = 'pct';
                document.getElementById('form-discount').value = v.discount_pct;
            } else {
                document.getElementById('form-type').value = 'amt';
                document.getElementById('form-discount').value = v.discount_amt;
            }
            
            document.getElementById('form-min-order').value = v.min_order;
            document.getElementById('form-max').value = v.max_uses;
            document.getElementById('form-expire').value = v.expire_date || '';
            
            toggleDiscountPlaceholder();
            document.getElementById('voucherModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('voucherModal').classList.remove('active');
        }

        function toggleDiscountPlaceholder() {
            const type = document.getElementById('form-type').value;
            const label = document.getElementById('discount-label');
            const input = document.getElementById('form-discount');
            
            if (type === 'pct') {
                label.textContent = 'Mức giảm (%) *';
                input.placeholder = 'Ví dụ: 20';
                input.max = '100';
            } else {
                label.textContent = 'Mức giảm (₫) *';
                input.placeholder = 'Ví dụ: 30000';
                input.removeAttribute('max');
            }
        }

        function filterTable() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            const typeFilter = document.getElementById('filter-type').value;
            const statusFilter = document.getElementById('filter-status').value;
            
            const container = document.getElementById('vouchersBody');
            
            // Remove existing empty-state element if any
            const existingEmpty = document.getElementById('filter-empty-row');
            if (existingEmpty) existingEmpty.remove();
            
            const cards = container.querySelectorAll('.admin-voucher-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const code = card.dataset.code.toLowerCase();
                const desc = card.dataset.desc.toLowerCase();
                const type = card.dataset.type;
                const status = card.dataset.status;
                
                const matchesQuery = code.includes(query) || desc.includes(query);
                const matchesType = !typeFilter || type === typeFilter;
                const matchesStatus = !statusFilter || status === statusFilter;
                
                if (matchesQuery && matchesType && matchesStatus) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show empty state if nothing matches
            if (visibleCount === 0 && cards.length > 0) {
                const emptyDiv = document.createElement('div');
                emptyDiv.id = 'filter-empty-row';
                emptyDiv.style.gridColumn = '1 / -1';
                emptyDiv.style.textAlign = 'center';
                emptyDiv.style.padding = '48px 20px';
                emptyDiv.innerHTML = `
                    <div style="display:flex; flex-direction:column; align-items:center; gap:12px; color:var(--text-muted);">
                        <div style="width:56px;height:56px;border-radius:16px;background:#F1F5F9;display:flex;align-items:center;justify-content:center;font-size:22px;">
                            <i class="fa-solid fa-ticket-simple" style="opacity:.4; transform: rotate(-45deg);"></i>
                        </div>
                        <div>
                            <div style="font-size:15px;font-weight:700;color:var(--text-main);margin-bottom:4px;">Không tìm thấy mã giảm giá nào</div>
                            <div style="font-size:13px;">Thử thay đổi từ khoá hoặc bộ lọc để xem kết quả khác.</div>
                        </div>
                    </div>`;
                container.appendChild(emptyDiv);
            }
        }

        // Initialize Page
        fetchStats();
        fetchVouchers();
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
