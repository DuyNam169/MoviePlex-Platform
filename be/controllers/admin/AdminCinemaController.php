<?php

require_once BASE_PATH . '/be/core/Response.php';

class AdminCinemaController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. LIST CINEMAS
    public function list(): void
    {
        try {
            $cinemas = $this->pdo->query("
                SELECT c.*, COALESCE(h.halls_count, 0) as halls_count
                FROM cinemas c
                LEFT JOIN (
                    SELECT cinema_id, COUNT(*) as halls_count
                    FROM cinema_halls
                    GROUP BY cinema_id
                ) h ON c.id = h.cinema_id
                ORDER BY c.name ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            Response::success(['data' => $cinemas]);
        } catch (Exception $e) {
            Response::error('Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    // 2. GET CINEMA HALLS
    public function getHalls(): void
    {
        $cinema_id = (int)($_GET['cinema_id'] ?? 0);
        if ($cinema_id > 0) {
            try {
                $hStmt = $this->pdo->prepare("SELECT * FROM cinema_halls WHERE cinema_id = ? ORDER BY name ASC");
                $hStmt->execute([$cinema_id]);
                $halls = $hStmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success(['halls' => $halls]);
            } catch (Exception $e) {
                Response::error('Lỗi hệ thống: ' . $e->getMessage());
            }
        } else {
            Response::error('Cinema ID không hợp lệ');
        }
    }

    // 3. SAVE CINEMA (CREATE / UPDATE)
    public function save(): void
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? 'Hà Nội');
        $phone = trim($_POST['phone'] ?? '');
        $logo_url = trim($_POST['logo_url'] ?? '');

        if ($name && $address) {
            try {
                if ($id > 0) {
                    // Update Cinema
                    $stmt = $this->pdo->prepare("UPDATE cinemas SET name=?, address=?, city=?, phone=?, logo_url=? WHERE id=?");
                    $stmt->execute([$name, $address, $city, $phone ?: null, $logo_url ?: null, $id]);

                    // Log
                    $logDesc = "Đã cập nhật thông tin rạp chiếu: \"$name\"";
                    $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Cập nhật rạp chiếu', ?)")
                        ->execute([$_SESSION['user_name'], $logDesc]);

                    Response::success([], "Đã cập nhật rạp chiếu <b>" . htmlspecialchars($name) . "</b> thành công!");
                } else {
                    $this->pdo->beginTransaction();

                    // Create Cinema
                    $stmt = $this->pdo->prepare("INSERT INTO cinemas (name, address, city, phone, logo_url) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $address, $city, $phone ?: null, $logo_url ?: null]);
                    $newCinemaId = $this->pdo->lastInsertId();

                    // Automatically seed 6 standard rooms (100 seats each) for this new cinema
                    $stmtInsertHall = $this->pdo->prepare("INSERT INTO cinema_halls (cinema_id, name, total_seats) VALUES (?, ?, 100)");
                    for ($i = 1; $i <= 6; $i++) {
                        $hallName = sprintf("Phòng %02d", $i);
                        $stmtInsertHall->execute([$newCinemaId, $hallName]);
                    }

                    // Log
                    $logDesc = "Đã thêm rạp chiếu mới: \"$name\" (Đã khởi tạo 6 phòng chiếu mặc định)";
                    $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Thêm rạp mới', ?)")
                        ->execute([$_SESSION['user_name'], $logDesc]);

                    $this->pdo->commit();

                    Response::success([], "Đã thêm rạp mới <b>" . htmlspecialchars($name) . "</b> thành công (Đã tự động khởi tạo 6 phòng chiếu 100 ghế)!");
                }
            } catch (Exception $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                Response::error("Lỗi hệ thống: " . $e->getMessage());
            }
        } else {
            Response::error("Vui lòng điền đầy đủ các thông tin bắt buộc (Tên rạp và Địa chỉ).");
        }
    }

    // 4. DELETE CINEMA
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                // Safety Check
                $stCount = $this->pdo->prepare("SELECT COUNT(*) FROM showtimes WHERE cinema_id = ?");
                $stCount->execute([$id]);
                if ($stCount->fetchColumn() > 0) {
                    Response::error("Không thể xóa rạp này do đang có các Suất chiếu (Lịch chiếu) được xếp lịch. Hãy hủy hoặc xóa các suất chiếu trước.");
                } else {
                    // Get cinema name for logging
                    $cStmt = $this->pdo->prepare("SELECT name FROM cinemas WHERE id = ?");
                    $cStmt->execute([$id]);
                    $name = $cStmt->fetchColumn();

                    // Delete Cinema
                    $stmt = $this->pdo->prepare("DELETE FROM cinemas WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    // Log
                    $logDesc = "Đã xóa rạp chiếu: \"$name\"";
                    $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Xóa rạp chiếu', ?)")
                        ->execute([$_SESSION['user_name'], $logDesc]);

                    Response::success([], "Đã xóa rạp chiếu <b>" . htmlspecialchars($name) . "</b> thành công!");
                }
            } catch (Exception $e) {
                Response::error("Không thể xóa rạp này: " . $e->getMessage());
            }
        } else {
            Response::error("ID rạp không hợp lệ.");
        }
    }
}
