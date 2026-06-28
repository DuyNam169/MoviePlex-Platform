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
    <title>MovieFlex Admin - Nhật ký hệ thống</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <!-- Dashboard Content -->
            <div class="dashboard">
                <div class="dashboard-header">
                    <div>
                        <h1>Nhật ký hệ thống</h1>
                        <p>Xem danh sách các hành động lịch sử và các thao tác bảo mật.</p>
                    </div>
                    <div class="dashboard-actions">
                        <button class="btn btn-outline" onclick="fetchLogsData()"><i class="fa-solid fa-rotate"></i> Làm mới</button>
                    </div>
                </div>

                <!-- Main Content Card -->
                <div class="card" style="background: var(--card-bg); border-radius: var(--radius-md); box-shadow: var(--shadow-md); padding: 24px; border: 1px solid var(--border-color);">
                    <!-- Filter Bar -->
                    <div class="filter-bar" style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; align-items: center;">
                        <!-- Search Box -->
                        <div class="search-bar" style="width: 300px; position: relative;">
                            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
                            <input type="text" id="search-logs" placeholder="Tìm theo tên, mô tả..." oninput="filterLogs(true)" style="padding-left: 40px; width: 100%; height: 42px; border: 1.5px solid var(--border-color); border-radius: var(--radius-sm); outline: none; font-family: inherit; font-size: 13.5px; transition: border-color 0.2s;">
                        </div>

                        <!-- Start Date -->
                        <div class="filter-item" style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Từ ngày:</span>
                            <input type="date" id="filter-start-date" onchange="filterLogs(true)" style="height: 42px; border: 1.5px solid var(--border-color); border-radius: var(--radius-sm); padding: 0 12px; outline: none; font-family: inherit; font-size: 13.5px; background: #fff;">
                        </div>

                        <!-- End Date -->
                        <div class="filter-item" style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Đến ngày:</span>
                            <input type="date" id="filter-end-date" onchange="filterLogs(true)" style="height: 42px; border: 1.5px solid var(--border-color); border-radius: var(--radius-sm); padding: 0 12px; outline: none; font-family: inherit; font-size: 13.5px; background: #fff;">
                        </div>

                        <!-- Action Filter -->
                        <div class="filter-item" style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Hành động:</span>
                            <select id="filter-action" onchange="filterLogs(true)" style="height: 42px; border: 1.5px solid var(--border-color); border-radius: var(--radius-sm); padding: 0 12px; outline: none; font-family: inherit; font-size: 13.5px; background: #fff; min-width: 160px; cursor: pointer;">
                                <option value="">Tất cả hành động</option>
                                <option value="Đăng nhập">Đăng nhập</option>
                                <option value="Bán vé">Bán vé</option>
                                <option value="Bán bắp nước">Bán bắp nước</option>
                                <option value="Checkin">Check-in</option>
                                <option value="Thêm">Thêm mới</option>
                                <option value="Cập nhật">Cập nhật</option>
                                <option value="Xóa">Xóa</option>
                            </select>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color);">
                                <th style="padding: 12px 16px; font-weight: 700; color: var(--text-muted); font-size: 12px; text-transform: uppercase;">THỜI GIAN</th>
                                <th style="padding: 12px 16px; font-weight: 700; color: var(--text-muted); font-size: 12px; text-transform: uppercase;">NGƯỜI DÙNG</th>
                                <th style="padding: 12px 16px; font-weight: 700; color: var(--text-muted); font-size: 12px; text-transform: uppercase;">HÀNH ĐỘNG</th>
                                <th style="padding: 12px 16px; font-weight: 700; color: var(--text-muted); font-size: 12px; text-transform: uppercase;">MÔ TẢ KHÓA</th>
                                <th style="padding: 12px 16px; font-weight: 700; color: var(--text-muted); font-size: 12px; text-transform: uppercase; width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody id="fullLogsBody">
                            <tr><td colspan="5" style="text-align: center; padding: 24px;">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="pagination-area" style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                        <div class="page-info" id="logs-page-info" style="font-size: 12.5px; font-weight: 700; color: var(--text-muted);">HIỂN THỊ 0 CỦA 0</div>
                        <div class="pagination" id="logs-pagination" style="display: flex; gap: 6px; align-items: center;">
                            <!-- pagination controls loaded dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Script imports -->
    <script src="../assets/js/script.js"></script>
</body>
</html>
