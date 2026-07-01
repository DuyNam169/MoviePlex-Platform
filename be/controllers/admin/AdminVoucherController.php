<?php

require_once BASE_PATH . '/be/core/Response.php';

class AdminVoucherController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. LIST VOUCHERS
    public function list(): void
    {
        try {
            $vouchers = $this->pdo->query("SELECT * FROM vouchers WHERE user_id IS NULL ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            Response::success(['data' => $vouchers]);
        } catch (Exception $e) {
            Response::error('Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    // 2. VOUCHER STATS
    public function stats(): void
    {
        try {
            $total_vouchers = $this->pdo->query("SELECT COUNT(*) FROM vouchers WHERE user_id IS NULL")->fetchColumn();
            $active_vouchers = $this->pdo->query("SELECT COUNT(*) FROM vouchers WHERE user_id IS NULL AND is_active = 1 AND (expire_date IS NULL OR expire_date >= CURDATE())")->fetchColumn();
            $expired_vouchers = $this->pdo->query("SELECT COUNT(*) FROM vouchers WHERE user_id IS NULL AND (is_active = 0 OR (expire_date IS NOT NULL AND expire_date < CURDATE()))")->fetchColumn();
            Response::success([
                'stats' => [
                    'total' => (int)$total_vouchers,
                    'active' => (int)$active_vouchers,
                    'expired' => (int)$expired_vouchers
                ]
            ]);
        } catch (Exception $e) {
            Response::error('Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    // 3. SAVE VOUCHER (CREATE / UPDATE)
    public function save(): void
    {
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
                    $stmt = $this->pdo->prepare("UPDATE vouchers SET code=?, description=?, discount_pct=?, discount_amt=?, min_order=?, max_uses=?, expire_date=?, is_active=? WHERE id=?");
                    $stmt->execute([$code, $description, $discount_pct, $discount_amt, $min_order, $max_uses, $expire_date ?: null, $is_active, $id]);
                    
                    // Log
                    $logDesc = "Đã cập nhật voucher: \"$code\"";
                    $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Cập nhật voucher', ?)")
                        ->execute([$_SESSION['user_name'], $logDesc]);

                    Response::success([], "Đã cập nhật voucher <b>$code</b> thành công!");
                } else {
                    // Create
                    $stmt = $this->pdo->prepare("INSERT INTO vouchers (code, description, discount_pct, discount_amt, min_order, max_uses, expire_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$code, $description, $discount_pct, $discount_amt, $min_order, $max_uses, $expire_date ?: null, $is_active]);
                    
                    // Log
                    $logDesc = "Đã tạo voucher mới: \"$code\"";
                    $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Tạo voucher', ?)")
                        ->execute([$_SESSION['user_name'], $logDesc]);

                    Response::success([], "Đã thêm voucher mới <b>$code</b> thành công!");
                }
            } catch (Exception $e) {
                Response::error("Lỗi: " . $e->getMessage());
            }
        } else {
            Response::error("Vui lòng nhập đầy đủ Mã và Mô tả voucher.");
        }
    }

    // 4. TOGGLE ACTIVE STATUS
    public function toggleActive(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['status'] ?? 1);
        if ($id > 0) {
            try {
                // Get code first
                $cStmt = $this->pdo->prepare("SELECT code FROM vouchers WHERE id = ?");
                $cStmt->execute([$id]);
                $code = $cStmt->fetchColumn();

                $stmt = $this->pdo->prepare("UPDATE vouchers SET is_active = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                
                // Log
                $statusText = $status == 1 ? "Bật" : "Tắt";
                $logDesc = "Đã $statusText hoạt động của voucher: \"$code\"";
                $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Cập nhật trạng thái voucher', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                Response::success([], "Đã thay đổi trạng thái hoạt động của voucher!");
            } catch (Exception $e) {
                Response::error("Lỗi: " . $e->getMessage());
            }
        } else {
            Response::error("ID không hợp lệ.");
        }
    }

    // 5. DELETE VOUCHER
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                // Get code first
                $cStmt = $this->pdo->prepare("SELECT code FROM vouchers WHERE id = ?");
                $cStmt->execute([$id]);
                $code = $cStmt->fetchColumn();

                $stmt = $this->pdo->prepare("DELETE FROM vouchers WHERE id = ?");
                $stmt->execute([$id]);
                
                // Log
                $logDesc = "Đã xóa voucher: \"$code\"";
                $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Xóa voucher', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                Response::success([], "Đã xóa voucher thành công!");
            } catch (Exception $e) {
                Response::error("Không thể xóa voucher này do đang có đơn hàng liên kết.");
            }
        } else {
            Response::error("ID không hợp lệ.");
        }
    }
}
