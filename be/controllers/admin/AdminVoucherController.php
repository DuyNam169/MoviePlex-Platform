<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

// Check if user is logged in and is admin or admin_monitor
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'admin_monitor'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// 1. LIST VOUCHERS
if ($action === 'list') {
    try {
        $vouchers = $pdo->query("SELECT * FROM vouchers WHERE user_id IS NULL ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $vouchers]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// 2. VOUCHER STATS
if ($action === 'stats') {
    try {
        $total_vouchers = $pdo->query("SELECT COUNT(*) FROM vouchers WHERE user_id IS NULL")->fetchColumn();
        $active_vouchers = $pdo->query("SELECT COUNT(*) FROM vouchers WHERE user_id IS NULL AND is_active = 1 AND (expire_date IS NULL OR expire_date >= CURDATE())")->fetchColumn();
        $expired_vouchers = $pdo->query("SELECT COUNT(*) FROM vouchers WHERE user_id IS NULL AND (is_active = 0 OR (expire_date IS NOT NULL AND expire_date < CURDATE()))")->fetchColumn();
        echo json_encode([
            'success' => true,
            'stats' => [
                'total' => (int)$total_vouchers,
                'active' => (int)$active_vouchers,
                'expired' => (int)$expired_vouchers
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// 3. SAVE VOUCHER (CREATE / UPDATE)
if ($action === 'save') {
    // Only admin role can save/delete vouchers (admin_monitor is read-only)
    if ($_SESSION['user_role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Quyền giám sát không được phép chỉnh sửa dữ liệu']);
        exit;
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $description = trim($_POST['description'] ?? '');
    $discount_type = $_POST['discount_type'] ?? 'pct';
    $discount_val = (int)($_POST['discount_value'] ?? 0);
    $min_order = (int)($_POST['min_order'] ?? 0);
    $max_uses = (int)($_POST['max_uses'] ?? 100);
    $expire_date = $_POST['expire_date'] ?? null;
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    $discount_pct = 0;
    $discount_amt = 0;
    if ($discount_type === 'pct') {
        $discount_pct = min(100, max(0, $discount_val));
    } else {
        $discount_amt = max(0, $discount_val);
    }

    if ($code && $description) {
        try {
            if ($id > 0) {
                // Update
                $stmt = $pdo->prepare("UPDATE vouchers SET code=?, description=?, discount_pct=?, discount_amt=?, min_order=?, max_uses=?, expire_date=?, is_active=? WHERE id=?");
                $stmt->execute([$code, $description, $discount_pct, $discount_amt, $min_order, $max_uses, $expire_date ?: null, $is_active, $id]);
                
                // Log
                $logDesc = "Đã cập nhật voucher: \"$code\"";
                $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Cập nhật voucher', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                echo json_encode(['success' => true, 'message' => "Đã cập nhật voucher <b>$code</b> thành công!"]);
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO vouchers (code, description, discount_pct, discount_amt, min_order, max_uses, expire_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $description, $discount_pct, $discount_amt, $min_order, $max_uses, $expire_date ?: null, $is_active]);
                
                // Log
                $logDesc = "Đã tạo voucher mới: \"$code\"";
                $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Tạo voucher', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                echo json_encode(['success' => true, 'message' => "Đã thêm voucher mới <b>$code</b> thành công!"]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Lỗi: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Vui lòng nhập đầy đủ Mã và Mô tả voucher."]);
    }
    exit;
}

// 4. TOGGLE ACTIVE STATUS
if ($action === 'toggle_active') {
    if ($_SESSION['user_role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Quyền giám sát không được phép chỉnh sửa dữ liệu']);
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    if ($id > 0) {
        try {
            // Get code first
            $cStmt = $pdo->prepare("SELECT code FROM vouchers WHERE id = ?");
            $cStmt->execute([$id]);
            $code = $cStmt->fetchColumn();

            $stmt = $pdo->prepare("UPDATE vouchers SET is_active = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            // Log
            $statusText = $status == 1 ? "Bật" : "Tắt";
            $logDesc = "Đã $statusText hoạt động của voucher: \"$code\"";
            $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Cập nhật trạng thái voucher', ?)")
                ->execute([$_SESSION['user_name'], $logDesc]);

            echo json_encode(['success' => true, 'message' => "Đã thay đổi trạng thái hoạt động của voucher!"]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Lỗi: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "ID không hợp lệ."]);
    }
    exit;
}

// 5. DELETE VOUCHER
if ($action === 'delete') {
    if ($_SESSION['user_role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Quyền giám sát không được phép chỉnh sửa dữ liệu']);
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            // Get code first
            $cStmt = $pdo->prepare("SELECT code FROM vouchers WHERE id = ?");
            $cStmt->execute([$id]);
            $code = $cStmt->fetchColumn();

            $stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = ?");
            $stmt->execute([$id]);
            
            // Log
            $logDesc = "Đã xóa voucher: \"$code\"";
            $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Xóa voucher', ?)")
                ->execute([$_SESSION['user_name'], $logDesc]);

            echo json_encode(['success' => true, 'message' => "Đã xóa voucher thành công!"]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Không thể xóa voucher này do đang có đơn hàng liên kết."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "ID không hợp lệ."]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
