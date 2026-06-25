<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';

// Check if user is logged in and is admin
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// 1. LIST CINEMAS
if ($action === 'list') {
    try {
        $cinemas = $pdo->query("
            SELECT c.*, COALESCE(h.halls_count, 0) as halls_count
            FROM cinemas c
            LEFT JOIN (
                SELECT cinema_id, COUNT(*) as halls_count
                FROM cinema_halls
                GROUP BY cinema_id
            ) h ON c.id = h.cinema_id
            ORDER BY c.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $cinemas]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// 2. GET CINEMA HALLS
if ($action === 'get_halls') {
    $cinema_id = (int)($_GET['cinema_id'] ?? 0);
    if ($cinema_id > 0) {
        try {
            $hStmt = $pdo->prepare("SELECT * FROM cinema_halls WHERE cinema_id = ? ORDER BY name ASC");
            $hStmt->execute([$cinema_id]);
            $halls = $hStmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'halls' => $halls]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Cinema ID không hợp lệ']);
    }
    exit;
}

// 3. SAVE CINEMA (CREATE / UPDATE)
if ($action === 'save') {
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
                $stmt = $pdo->prepare("UPDATE cinemas SET name=?, address=?, city=?, phone=?, logo_url=? WHERE id=?");
                $stmt->execute([$name, $address, $city, $phone ?: null, $logo_url ?: null, $id]);

                // Log
                $logDesc = "Đã cập nhật thông tin rạp chiếu: \"$name\"";
                $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Cập nhật rạp chiếu', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                echo json_encode(['success' => true, 'message' => "Đã cập nhật rạp chiếu <b>" . htmlspecialchars($name) . "</b> thành công!"]);
            } else {
                $pdo->beginTransaction();

                // Create Cinema
                $stmt = $pdo->prepare("INSERT INTO cinemas (name, address, city, phone, logo_url) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $address, $city, $phone ?: null, $logo_url ?: null]);
                $newCinemaId = $pdo->lastInsertId();

                // Automatically seed 6 standard rooms (100 seats each) for this new cinema
                $stmtInsertHall = $pdo->prepare("INSERT INTO cinema_halls (cinema_id, name, total_seats) VALUES (?, ?, 100)");
                for ($i = 1; $i <= 6; $i++) {
                    $hallName = sprintf("Phòng %02d", $i);
                    $stmtInsertHall->execute([$newCinemaId, $hallName]);
                }

                // Log
                $logDesc = "Đã thêm rạp chiếu mới: \"$name\" (Đã khởi tạo 6 phòng chiếu mặc định)";
                $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Thêm rạp mới', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                $pdo->commit();

                echo json_encode(['success' => true, 'message' => "Đã thêm rạp mới <b>" . htmlspecialchars($name) . "</b> thành công (Đã tự động khởi tạo 6 phòng chiếu 100 ghế)!"]);
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => "Lỗi hệ thống: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Vui lòng điền đầy đủ các thông tin bắt buộc (Tên rạp và Địa chỉ)."]);
    }
    exit;
}

// 4. DELETE CINEMA
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            // Safety Check
            $stCount = $pdo->prepare("SELECT COUNT(*) FROM showtimes WHERE cinema_id = ?");
            $stCount->execute([$id]);
            if ($stCount->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => "Không thể xóa rạp này do đang có các Suất chiếu (Lịch chiếu) được xếp lịch. Hãy hủy hoặc xóa các suất chiếu trước."]);
            } else {
                // Get cinema name for logging
                $cStmt = $pdo->prepare("SELECT name FROM cinemas WHERE id = ?");
                $cStmt->execute([$id]);
                $name = $cStmt->fetchColumn();

                // Delete Cinema
                $stmt = $pdo->prepare("DELETE FROM cinemas WHERE id = ?");
                $stmt->execute([$id]);
                
                // Log
                $logDesc = "Đã xóa rạp chiếu: \"$name\"";
                $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Xóa rạp chiếu', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                echo json_encode(['success' => true, 'message' => "Đã xóa rạp chiếu <b>" . htmlspecialchars($name) . "</b> thành công!"]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Không thể xóa rạp này: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "ID rạp không hợp lệ."]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
