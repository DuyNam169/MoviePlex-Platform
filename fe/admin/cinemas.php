<?php
session_start();
// Check if user is logged in and is admin
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieFlex Admin - Quản lý Rạp chiếu</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cinemas-layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
            align-items: start;
        }
        
        .cinema-sidebar-card {
            background: white;
            border-radius: var(--border-radius, 14px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #E2E8F0;
            overflow: hidden;
        }

        .cinema-sidebar-card h3 {
            padding: 16px 20px;
            font-size: 15px;
            font-weight: 800;
            border-bottom: 1px solid #E2E8F0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cinema-list-wrapper {
            max-height: 70vh;
            overflow-y: auto;
        }

        .cinema-list-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            border-bottom: 1px solid #F1F5F9;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .cinema-list-item:hover {
            background-color: #F8FAFC;
        }

        .cinema-list-item.active {
            background-color: #EEF2FF;
            border-left: 4px solid var(--primary-blue, #4F46E5);
        }

        .cinema-list-logo {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            background: #EEF2FF;
            color: var(--primary-blue, #4F46E5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            object-fit: cover;
            border: 1px solid #E2E8F0;
        }

        .cinema-list-info {
            flex: 1;
            min-width: 0;
        }

        .cinema-list-name {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cinema-list-addr {
            font-size: 12px;
            color: #64748B;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cinema-list-badge {
            font-size: 10.5px;
            font-weight: 700;
            background: #E0F2F1;
            color: #00897B;
            padding: 2px 8px;
            border-radius: 12px;
            margin-top: 4px;
            display: inline-block;
        }

        .cinema-details-card {
            background: white;
            border-radius: var(--border-radius, 14px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #E2E8F0;
            padding: 24px;
        }

        .details-header {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            border-bottom: 1px dashed #E2E8F0;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .details-logo {
            width: 72px;
            height: 72px;
            border-radius: 14px;
            background: #EEF2FF;
            color: var(--primary-blue, #4F46E5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            border: 1.5px solid #C7D2FE;
            object-fit: cover;
        }

        .details-title-info {
            flex: 1;
        }

        .details-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 6px;
            color: #0F172A;
        }

        .details-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 13.5px;
            color: #64748B;
        }

        .details-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .details-meta-item i {
            color: var(--primary-blue, #4F46E5);
        }

        .halls-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .halls-table th {
            text-align: left;
            padding: 12px 16px;
            background: #F8FAFC;
            border-bottom: 1px solid #E2E8F0;
            font-size: 11px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .halls-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #F1F5F9;
            font-size: 13.5px;
        }

        .halls-badge {
            background: #EEF2FF;
            color: var(--primary-blue, #4F46E5);
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            border: 1px solid #C7D2FE;
        }

        .btn-cinema-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }

        .btn-cinema-edit {
            background-color: #EFF6FF;
            color: var(--primary-blue, #4F46E5);
            border: 1px solid #BFDBFE;
        }

        .btn-cinema-edit:hover {
            background-color: #DBEAFE;
        }

        .btn-cinema-delete {
            background-color: #FEF2F2;
            color: #EF4444;
            border: 1px solid #FEE2E2;
        }

        .btn-cinema-delete:hover {
            background-color: #FEE2E2;
        }

        .modal-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .full-row {
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
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 0 12px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            font-family: inherit;
        }

        .form-input-custom:focus {
            border-color: var(--primary-blue, #4F46E5);
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
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .alert-bar.error {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FEE2E2;
        }
        .action-btns {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <div class="cinemas-layout" style="margin-top: 24px;">
                <!-- Left Cinema List -->
                <div class="cinema-sidebar-card">
                    <h3>
                        <span><i class="fa-solid fa-shop" style="color:var(--primary-blue);margin-right:6px"></i>Danh sách Rạp</span>
                        <button class="btn btn-primary" onclick="openAddCinemaModal()" style="font-size:11.5px; padding:6px 12px; height:auto; border-radius:6px;">
                            <i class="fa-solid fa-plus"></i> Thêm rạp
                        </button>
                    </h3>
                    
                    <div class="cinema-list-wrapper" id="cinemaList">
                        <div style="padding:40px 20px; text-align:center; color:#64748B;">
                            <i class="fa-solid fa-spinner fa-spin" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                            Đang tải danh sách rạp...
                        </div>
                    </div>
                </div>

                <!-- Right Selected Cinema Details & Halls -->
                <div class="cinema-details-card" id="cinemaDetailsContainer">
                    <div style="padding:80px 20px; text-align:center; color:#64748B;">
                        <i class="fa-solid fa-shop" style="font-size:48px; opacity:0.2; margin-bottom:16px; display:block;"></i>
                        <h3>Chọn một rạp từ danh sách để xem chi tiết phòng chiếu</h3>
                        <p style="margin-top:6px; font-size:13px;">Hoặc nhấn nút "Thêm rạp" để đăng ký chi nhánh rạp mới.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Cinema Modal -->
    <div id="cinemaModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 540px;">
            <div class="modal-header" style="border-bottom: 1px solid #E2E8F0; padding-bottom: 16px; margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
                <div class="modal-title" style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-shop" style="color:var(--primary-blue); font-size:18px;"></i>
                    <h3 id="modal-title-text" style="font-size:16px; font-weight:800;">Thêm Rạp chiếu mới</h3>
                </div>
                <button class="close-modal" onclick="closeCinemaModal()" style="border:none; background:none; font-size:20px; cursor:pointer; color:#64748B;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form id="cinemaForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="form-id" value="0">
                
                <div class="modal-form-grid">
                    <div class="form-group-custom full-row">
                        <label>Tên rạp chiếu *</label>
                        <input type="text" name="name" id="form-name" class="form-input-custom" placeholder="Ví dụ: CGV Vincom Center" required>
                    </div>

                    <div class="form-group-custom full-row">
                        <label>Địa chỉ rạp *</label>
                        <input type="text" name="address" id="form-address" class="form-input-custom" placeholder="Số, tên đường, quận/huyện..." required>
                    </div>

                    <div class="form-group-custom">
                        <label>Thành phố *</label>
                        <select name="city" id="form-city" class="form-input-custom" required>
                            <option value="Hà Nội">Hà Nội</option>
                            <option value="Hồ Chí Minh">TP. Hồ Chí Minh</option>
                            <option value="Đà Nẵng">Đà Nẵng</option>
                            <option value="Cần Thơ">Cần Thơ</option>
                            <option value="Hải Phòng">Hải Phòng</option>
                        </select>
                    </div>

                    <div class="form-group-custom">
                        <label>Số điện thoại liên hệ</label>
                        <input type="text" name="phone" id="form-phone" class="form-input-custom" placeholder="Ví dụ: 024.3974.8888">
                    </div>

                    <div class="form-group-custom full-row">
                        <label>URL Ảnh đại diện / Logo Rạp</label>
                        <input type="url" name="logo_url" id="form-logo" class="form-input-custom" placeholder="Nhập liên kết hình ảnh rạp...">
                    </div>
                </div>

                <div id="add-cinema-hint" style="margin-top:16px; background:#ECFDF5; border:1px solid #A7F3D0; border-radius:8px; padding:10px; color:#065F46; font-size:12px; line-height:1.4;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <strong>Tự động hóa thông minh:</strong> Khi thêm rạp thành công, hệ thống sẽ tự động khởi tạo và gán 6 phòng chiếu tiêu chuẩn (mỗi phòng 100 ghế) cho rạp này ngay lập tức.
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; border-top: 1px solid #E2E8F0; padding-top: 16px; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeCinemaModal()">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Lưu rạp chiếu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let allCinemas = [];
        let selectedCinemaId = null;
        let selectedHalls = [];

        function escapeHtml(text) {
            if (!text) return '';
            return text.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function escapeJs(text) {
            if (!text) return '';
            return text.toString().replace(/'/g, "\\'").replace(/"/g, '\\"');
        }

        async function fetchCinemas() {
            try {
                const res = await fetch('../../be/api.php?action=admin_cinema_list');
                const json = await res.json();
                
                if (json.success) {
                    allCinemas = json.data || [];
                    renderCinemaList();
                    
                    if (allCinemas.length > 0) {
                        if (selectedCinemaId === null) {
                            selectCinema(allCinemas[0].id);
                        } else {
                            const stillExists = allCinemas.some(item => item.id == selectedCinemaId);
                            if (stillExists) {
                                selectCinema(selectedCinemaId);
                            } else {
                                selectCinema(allCinemas[0].id);
                            }
                        }
                    } else {
                        selectedCinemaId = null;
                        selectedHalls = [];
                        renderCinemaDetails();
                    }
                } else {
                    mfToast('Lỗi tải danh sách', json.message || 'Không thể tải danh sách rạp.', 'danger');
                }
            } catch (e) {
                console.error('Error fetching cinemas:', e);
                mfToast('Lỗi hệ thống', 'Không thể kết nối máy chủ để tải thông tin rạp.', 'warning');
            }
        }

        async function selectCinema(cinemaId) {
            selectedCinemaId = cinemaId;
            
            // Highlight list item
            const items = document.querySelectorAll('.cinema-list-item');
            items.forEach(item => item.classList.remove('active'));
            
            // Find current item in DOM to highlight
            const currentItem = Array.from(items).find(item => item.getAttribute('data-id') == cinemaId);
            if (currentItem) currentItem.classList.add('active');

            // Render details (loading state for halls)
            const detailsContainer = document.getElementById('cinemaDetailsContainer');
            const c = allCinemas.find(item => item.id == cinemaId);
            if (!c) return;

            // Fetch halls
            try {
                const res = await fetch(`../../be/api.php?action=admin_cinema_halls&cinema_id=${cinemaId}`);
                const json = await res.json();
                
                if (json.success) {
                    selectedHalls = json.halls || [];
                } else {
                    selectedHalls = [];
                    console.error('Failed to load halls:', json.message);
                }
            } catch (e) {
                selectedHalls = [];
                console.error('Error loading halls:', e);
            }
            
            renderCinemaDetails();
        }

        function renderCinemaList() {
            const listWrapper = document.getElementById('cinemaList');
            if (allCinemas.length === 0) {
                listWrapper.innerHTML = `
                    <div style="padding:40px 20px; text-align:center; color:#64748B;">
                        <i class="fa-solid fa-shop-slash" style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i>
                        Chưa có rạp chiếu nào.
                    </div>`;
                return;
            }

            let html = '';
            allCinemas.forEach(c => {
                const isActive = (c.id == selectedCinemaId) ? 'active' : '';
                const logoHtml = c.logo_url 
                    ? `<img src="${escapeHtml(c.logo_url)}" class="cinema-list-logo" alt="">`
                    : `<div class="cinema-list-logo"><i class="fa-solid fa-location-dot"></i></div>`;
                    
                html += `
                    <div class="cinema-list-item ${isActive}" data-id="${c.id}" onclick="selectCinema(${c.id})">
                        ${logoHtml}
                        <div class="cinema-list-info">
                            <div class="cinema-list-name" title="${escapeHtml(c.name)}">${escapeHtml(c.name)}</div>
                            <div class="cinema-list-addr" title="${escapeHtml(c.address)}">${escapeHtml(c.address)}</div>
                            <span class="cinema-list-badge"><i class="fa-solid fa-door-open"></i> ${c.halls_count} Phòng chiếu</span>
                        </div>
                    </div>`;
            });
            listWrapper.innerHTML = html;
        }

        function renderCinemaDetails() {
            const container = document.getElementById('cinemaDetailsContainer');
            if (!selectedCinemaId) {
                container.innerHTML = `
                    <div style="padding:80px 20px; text-align:center; color:#64748B;">
                        <i class="fa-solid fa-shop" style="font-size:48px; opacity:0.2; margin-bottom:16px; display:block;"></i>
                        <h3>Chọn một rạp từ danh sách để xem chi tiết phòng chiếu</h3>
                        <p style="margin-top:6px; font-size:13px;">Hoặc nhấn nút "Thêm rạp" để đăng ký chi nhánh rạp mới.</p>
                    </div>`;
                return;
            }

            const c = allCinemas.find(item => item.id == selectedCinemaId);
            if (!c) return;

            const logoHtml = c.logo_url 
                ? `<img src="${escapeHtml(c.logo_url)}" class="details-logo" alt="">`
                : `<div class="details-logo"><i class="fa-solid fa-location-dot"></i></div>`;

            const phoneHtml = c.phone 
                ? `<div class="details-meta-item"><i class="fa-solid fa-phone"></i> <span>${escapeHtml(c.phone)}</span></div>`
                : '';

            let hallsRows = '';
            if (selectedHalls.length === 0) {
                hallsRows = `<tr><td colspan="5" class="text-center" style="color:var(--text-muted);">Không tìm thấy phòng chiếu nào.</td></tr>`;
            } else {
                selectedHalls.forEach(hall => {
                    hallsRows += `
                        <tr>
                            <td><strong>${escapeHtml(hall.name)}</strong></td>
                            <td><span class="halls-badge">Tiêu chuẩn (Standard)</span></td>
                            <td><strong>${hall.total_seats} ghế</strong></td>
                            <td>Hàng A đến J (10 ghế/hàng)</td>
                            <td><span style="color:#10B981; font-weight:700; font-size:12.5px;"><i class="fa-solid fa-circle-check"></i> Đang hoạt động</span></td>
                        </tr>`;
                });
            }

            container.innerHTML = `
                <!-- Details Header -->
                <div class="details-header">
                    ${logoHtml}
                    
                    <div class="details-title-info">
                        <h2 class="details-title">${escapeHtml(c.name)}</h2>
                        <div class="details-meta-row">
                            <div class="details-meta-item"><i class="fa-solid fa-map-pin"></i> <span>${escapeHtml(c.address)} (${escapeHtml(c.city || 'Hà Nội')})</span></div>
                            ${phoneHtml}
                        </div>
                    </div>
                    
                    <div class="action-btns" style="align-self: flex-start;">
                        <button class="btn-cinema-action btn-cinema-edit" onclick="openEditCinemaModal(${c.id})">
                            <i class="fa-solid fa-pen-to-square"></i> Sửa rạp
                        </button>
                        
                        <button type="button" class="btn-cinema-action btn-cinema-delete" onclick="confirmDeleteCinema(${c.id}, '${escapeJs(c.name)}')">
                            <i class="fa-solid fa-trash-can"></i> Xóa rạp
                        </button>
                    </div>
                </div>

                <!-- Halls Section -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="font-size:15px; font-weight:800; color:#334155;"><i class="fa-solid fa-door-open" style="color:var(--primary-blue);margin-right:6px"></i>Sơ đồ Phòng chiếu (Halls)</h3>
                    <span style="font-size:12.5px; color:#64748B; font-weight:500;">Quy chuẩn rạp: <strong>${selectedHalls.length} Phòng tiêu chuẩn</strong></span>
                </div>

                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 13px; color: #475569; line-height: 1.5;">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary-blue); margin-right: 6px;"></i>
                    Mỗi rạp chiếu phim trong hệ thống MovieFlex được chuẩn hóa đồng bộ **6 phòng chiếu tiêu chuẩn** (Phòng 01 đến Phòng 06, với sức chứa **100 ghế ngồi** tương đương sơ đồ 10 hàng ghế A-J). Tính năng thêm/xóa phòng chiếu thủ công được bỏ qua để đảm bảo tính nhất quán của sơ đồ phòng chiếu toàn hệ thống.
                </div>

                <table class="halls-table">
                    <thead>
                        <tr>
                            <th>Tên phòng chiếu</th>
                            <th>Loại phòng</th>
                            <th>Sức chứa</th>
                            <th>Cơ cấu hàng ghế</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${hallsRows}
                    </tbody>
                </table>`;
        }

        async function confirmDeleteCinema(cinemaId, cinemaName) {
            const ok = await mfConfirm({
                title: 'Xóa rạp chiếu',
                desc: `Bạn có chắc chắn muốn XÓA rạp <strong>${cinemaName}</strong>?<br><br>⚠️ Tất cả <strong>phòng chiếu</strong> liên kết sẽ bị xóa theo. Hành động này <strong>không thể hoàn tác</strong>.`,
                type: 'danger',
                confirmText: 'Xóa vĩnh viễn',
                confirmIcon: 'fa-trash-can',
                cancelText: 'Giữ lại'
            });
            if (ok) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'admin_cinema_delete');
                    formData.append('id', cinemaId);

                    const res = await fetch('../../be/api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const json = await res.json();
                    
                    if (json.success) {
                        mfToast('Thành công', json.message || 'Đã xóa rạp chiếu thành công!', 'success');
                        selectedCinemaId = null;
                        await fetchCinemas();
                    } else {
                        mfToast('Lỗi khi xóa', json.message || 'Đã xảy ra lỗi khi xóa rạp chiếu.', 'danger', 7000);
                    }
                } catch (e) {
                    console.error('Error deleting cinema:', e);
                    mfToast('Lỗi hệ thống', 'Không thể kết nối máy chủ để xóa rạp. Vui lòng thử lại.', 'warning');
                }
            }
        }

        document.getElementById('cinemaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu rạp...';

            try {
                const formData = new FormData(this);
                formData.set('action', 'admin_cinema_save');

                const res = await fetch('../../be/api.php', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();
                
                if (json.success) {
                    mfToast('Thành công', json.message || 'Đã lưu thông tin rạp thành công!', 'success');
                    closeCinemaModal();
                    
                    // Set active selection if updating or set to null to load the newly added one
                    const formIdVal = document.getElementById('form-id').value;
                    if (formIdVal != '0') {
                        selectedCinemaId = formIdVal;
                    } else {
                        selectedCinemaId = null; // Auto pick first
                    }
                    
                    await fetchCinemas();
                } else {
                    mfToast('Lỗi lưu rạp', json.message || 'Đã xảy ra lỗi khi lưu rạp.', 'danger', 7000);
                }
            } catch (err) {
                console.error('Lỗi khi lưu rạp:', err);
                mfToast('Lỗi hệ thống', 'Không thể kết nối máy chủ để lưu rạp. Vui lòng thử lại.', 'warning');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHTML;
            }
        });

        function openAddCinemaModal() {
            document.getElementById('modal-title-text').textContent = 'Thêm Rạp chiếu mới';
            document.getElementById('form-id').value = '0';
            document.getElementById('form-name').value = '';
            document.getElementById('form-address').value = '';
            document.getElementById('form-city').value = 'Hà Nội';
            document.getElementById('form-phone').value = '';
            document.getElementById('form-logo').value = '';
            document.getElementById('add-cinema-hint').style.display = 'block';

            document.getElementById('cinemaModal').classList.add('active');
        }

        function openEditCinemaModal(id) {
            const c = allCinemas.find(item => item.id == id);
            if (!c) return;

            document.getElementById('modal-title-text').textContent = 'Sửa thông tin Rạp #' + c.id;
            document.getElementById('form-id').value = c.id;
            document.getElementById('form-name').value = c.name;
            document.getElementById('form-address').value = c.address;
            document.getElementById('form-city').value = c.city || 'Hà Nội';
            document.getElementById('form-phone').value = c.phone || '';
            document.getElementById('form-logo').value = c.logo_url || '';
            document.getElementById('add-cinema-hint').style.display = 'none';

            document.getElementById('cinemaModal').classList.add('active');
        }

        function closeCinemaModal() {
            document.getElementById('cinemaModal').classList.remove('active');
        }

        // Initialize page
        fetchCinemas();
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
