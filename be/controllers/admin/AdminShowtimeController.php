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

function checkShowtimeConflict($id, $cinema_id, $hall_name, $show_date, $start_time, $end_time, $movie_id) {
    global $pdo;

    // Check operating hours: 08:00 to 23:30
    $start_time_val = substr(trim($start_time), 0, 5); // get HH:MM
    if ($start_time_val < '08:00' || $start_time_val > '23:30') {
        return "Giờ bắt đầu suất chiếu không hợp lệ! Suất chiếu chỉ được phép bắt đầu từ 08:00 đến 23:30.";
    }

    $new_duration = 120;
    $mStmt = $pdo->prepare("SELECT duration_min FROM movies WHERE id = ?");
    $mStmt->execute([$movie_id]);
    $movie_row = $mStmt->fetch();
    if ($movie_row) {
        $new_duration = (int)$movie_row['duration_min'];
    }

    $new_start_ts = strtotime($show_date . ' ' . $start_time);
    if (!empty($end_time)) {
        $new_end_ts = strtotime($show_date . ' ' . $end_time);
        if ($new_end_ts <= $new_start_ts) {
            $new_end_ts += 86400; // ends next day
        }
    } else {
        $new_end_ts = $new_start_ts + ($new_duration * 60);
    }
    $new_block_end_ts = $new_end_ts + (60 * 60); // 60 min buffer

    // Fetch existing showtimes in same cinema/hall around same date
    $sql = "
        SELECT s.id, s.show_date, s.start_time, s.end_time, m.duration_min, m.title as movie_title
        FROM showtimes s
        JOIN movies m ON s.movie_id = m.id
        WHERE s.cinema_id = ? 
          AND s.hall_name = ? 
          AND s.is_cancelled = 0
          AND s.show_date BETWEEN DATE_SUB(?, INTERVAL 1 DAY) AND DATE_ADD(?, INTERVAL 1 DAY)
    ";
    
    $params = [$cinema_id, $hall_name, $show_date, $show_date];
    if ($id > 0) {
        $sql .= " AND s.id != ?";
        $params[] = $id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $existing = $stmt->fetchAll();

    foreach ($existing as $row) {
        $duration = (int)($row['duration_min'] ?: 120);
        $db_start_ts = strtotime($row['show_date'] . ' ' . $row['start_time']);
        
        if (!empty($row['end_time'])) {
            $db_end_ts = strtotime($row['show_date'] . ' ' . $row['end_time']);
            if ($db_end_ts <= $db_start_ts) {
                $db_end_ts += 86400;
            }
        } else {
            $db_end_ts = $db_start_ts + ($duration * 60);
        }
        $db_block_end_ts = $db_end_ts + (60 * 60);

        if ($new_start_ts < $db_block_end_ts && $db_start_ts < $new_block_end_ts) {
            $existing_start = date('H:i', $db_start_ts);
            $existing_end = date('H:i', $db_end_ts);
            $existing_cleanup = date('H:i', $db_block_end_ts);
            return "Trùng lịch chiếu tại {$hall_name}! Suất chiếu của phim \"{$row['movie_title']}\" diễn ra từ {$existing_start} đến {$existing_end} (Cần dọn dẹp đến {$existing_cleanup}).";
        }
    }

    return null; // No conflict
}

// 1. GET INITIAL DATA (Movies & Cinemas)
if ($action === 'get_initial_data') {
    try {
        $movies = $pdo->query("SELECT id, title, duration_min, release_date, status FROM movies WHERE status IN ('now_showing', 'coming_soon') ORDER BY title ASC")->fetchAll();
        $cinemas = $pdo->query("SELECT id, name FROM cinemas ORDER BY name ASC")->fetchAll();
        echo json_encode([
            'success' => true,
            'movies' => $movies,
            'cinemas' => $cinemas
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// 2. GET CINEMA HALLS
if ($action === 'get_cinema_halls') {
    $cinema_id = (int)($_GET['cinema_id'] ?? 0);
    if ($cinema_id > 0) {
        $stmt = $pdo->prepare("SELECT name FROM cinema_halls WHERE cinema_id = ? ORDER BY name ASC");
        $stmt->execute([$cinema_id]);
        $halls = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'halls' => $halls]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cinema ID không hợp lệ']);
    }
    exit;
}

// 3. CHECK CONFLICT
if ($action === 'check_conflict') {
    $id = (int)($_GET['id'] ?? 0);
    $movie_id = (int)($_GET['movie_id'] ?? 0);
    $cinema_id = (int)($_GET['cinema_id'] ?? 0);
    $hall_name = trim($_GET['hall_name'] ?? '');
    $show_date = $_GET['show_date'] ?? '';
    $start_time = $_GET['start_time'] ?? '';
    $end_time = $_GET['end_time'] ?? null;

    if (!$movie_id || !$cinema_id || !$show_date || !$start_time || !$hall_name) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
        exit;
    }

    // Check movie release date vs show date
    $mStmt = $pdo->prepare("SELECT release_date, title FROM movies WHERE id = ?");
    $mStmt->execute([$movie_id]);
    $movie_info = $mStmt->fetch();
    if ($movie_info && $movie_info['release_date'] && $show_date < $movie_info['release_date']) {
        $formatted_release = date('d/m/Y', strtotime($movie_info['release_date']));
        echo json_encode([
            'success' => false,
            'message' => "Không thể tạo suất chiếu cho phim \"" . $movie_info['title'] . "\" trước ngày công chiếu ($formatted_release)."
        ]);
        exit;
    }

    $conflict = checkShowtimeConflict($id, $cinema_id, $hall_name, $show_date, $start_time, $end_time, $movie_id);
    if ($conflict) {
        echo json_encode(['success' => false, 'conflict' => true, 'message' => $conflict]);
    } else {
        echo json_encode(['success' => true]);
    }
    exit;
}

// 4. LIST SHOWTIMES
if ($action === 'list') {
    try {
        $showtimes = $pdo->query("
            SELECT s.*, m.title as movie_title, m.poster_url, m.duration_min, c.name as cinema_name,
                   (SELECT COUNT(*) FROM bookings b WHERE b.showtime_id = s.id AND b.status != 'cancelled') as booked_tickets_count
            FROM showtimes s
            JOIN movies m ON s.movie_id = m.id
            JOIN cinemas c ON s.cinema_id = c.id
            ORDER BY s.show_date DESC, s.start_time DESC
        ")->fetchAll();
        echo json_encode(['success' => true, 'data' => $showtimes]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// 5. SAVE SHOWTIME
if ($action === 'save') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $movie_id = (int)($_POST['movie_id'] ?? 0);
    $cinema_id = (int)($_POST['cinema_id'] ?? 0);
    $hall_name = trim($_POST['hall_name'] ?? 'Phòng chiếu 1');
    $show_date = $_POST['show_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? null;
    $format = $_POST['format'] ?? '2D';
    $subtitle_type = $_POST['subtitle_type'] ?? 'Phụ đề';
    $price = (float)($_POST['price'] ?? 80000);
    $total_seats = (int)($_POST['total_seats'] ?? 100);

    if ($movie_id && $cinema_id && $show_date && $start_time) {
        $today = date('Y-m-d');
        if ($show_date < $today) {
            echo json_encode(['success' => false, 'message' => "Lỗi logic: Không thể thêm hoặc cập nhật suất chiếu vào ngày trong quá khứ ($show_date). Vui lòng chọn từ ngày hôm nay trở đi."]);
            exit;
        } else {
            try {
                // Check release date conflict
                $mStmt = $pdo->prepare("SELECT release_date, title FROM movies WHERE id = ?");
                $mStmt->execute([$movie_id]);
                $movie_info = $mStmt->fetch();
                $release_date = $movie_info ? $movie_info['release_date'] : null;
                $movie_title = $movie_info ? $movie_info['title'] : '';

                if ($release_date && $show_date < $release_date) {
                    $formatted_release = date('d/m/Y', strtotime($release_date));
                    echo json_encode(['success' => false, 'message' => "Lỗi logic: Không thể tạo suất chiếu cho phim \"$movie_title\" trước ngày công chiếu ($formatted_release)."]);
                    exit;
                } else {
                    // Calculate end_time dynamically if empty
                    if (empty($end_time)) {
                        $duration = $movie_info ? (int)$movie_info['duration_min'] : 120;
                        $end_ts = strtotime($show_date . ' ' . $start_time) + ($duration * 60);
                        $end_time = date('H:i:s', $end_ts);
                    }

                    // Check conflict
                    $conflict = checkShowtimeConflict($id, $cinema_id, $hall_name, $show_date, $start_time, $end_time, $movie_id);
                    if ($conflict) {
                        echo json_encode(['success' => false, 'message' => $conflict]);
                        exit;
                    } else {
                        if ($id > 0) {
                            // Check active bookings
                            $bStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE showtime_id = ? AND status != 'cancelled'");
                            $bStmt->execute([$id]);
                            $booked_count = (int)$bStmt->fetchColumn();

                            if ($booked_count > 0) {
                                echo json_encode(['success' => false, 'message' => "Lỗi logic: Suất chiếu này đã có $booked_count vé được đặt. Không được phép chỉnh sửa thông tin."]);
                                exit;
                            } else {
                                // Update
                                $stmt = $pdo->prepare("UPDATE showtimes SET movie_id=?, cinema_id=?, hall_name=?, show_date=?, start_time=?, end_time=?, format=?, subtitle_type=?, price=?, total_seats=? WHERE id=?");
                                $stmt->execute([$movie_id, $cinema_id, $hall_name, $show_date, $start_time, $end_time, $format, $subtitle_type, $price, $total_seats, $id]);
                                
                                // Log
                                $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Cập nhật suất chiếu', ?)")
                                    ->execute([$_SESSION['user_name'], "Đã cập nhật suất chiếu #$id"]);

                                echo json_encode(['success' => true, 'message' => "Đã cập nhật suất chiếu thành công!"]);
                            }
                        } else {
                            // Create
                            $stmt = $pdo->prepare("INSERT INTO showtimes (movie_id, cinema_id, hall_name, show_date, start_time, end_time, format, subtitle_type, price, total_seats, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$movie_id, $cinema_id, $hall_name, $show_date, $start_time, $end_time, $format, $subtitle_type, $price, $total_seats, $total_seats]);
                            $newId = $pdo->lastInsertId();

                            // Log
                            $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Thêm suất chiếu', ?)")
                                ->execute([$_SESSION['user_name'], "Đã tạo suất chiếu mới #$newId"]);

                            echo json_encode(['success' => true, 'message' => "Đã thêm suất chiếu mới thành công!"]);
                        }
                    }
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => "Lỗi hệ thống: " . $e->getMessage()]);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Vui lòng điền đầy đủ các thông tin bắt buộc."]);
    }
    exit;
}

// 6. DELETE SHOWTIME
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $bStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE showtime_id = ? AND status != 'cancelled'");
            $bStmt->execute([$id]);
            $booked_count = (int)$bStmt->fetchColumn();

            if ($booked_count > 0) {
                echo json_encode(['success' => false, 'message' => "Lỗi logic: Suất chiếu này đã có $booked_count vé được đặt/mua. Không được phép xóa suất chiếu."]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM showtimes WHERE id = ?");
                $stmt->execute([$id]);

                // Log
                $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Xóa suất chiếu', ?)")
                    ->execute([$_SESSION['user_name'], "Đã xóa suất chiếu #$id"]);

                echo json_encode(['success' => true, 'message' => "Đã xóa suất chiếu thành công!"]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Không thể xóa suất chiếu này do đã có dữ liệu liên kết."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "ID suất chiếu không hợp lệ."]);
    }
    exit;
}

// 7. CANCEL URGENT SHOWTIME
if ($action === 'cancel_urgent') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $sStmt = $pdo->prepare("
                SELECT s.*, m.title as movie_title, m.duration_min, c.name as cinema_name 
                FROM showtimes s
                JOIN movies m ON s.movie_id = m.id
                JOIN cinemas c ON s.cinema_id = c.id
                WHERE s.id = ? LIMIT 1
            ");
            $sStmt->execute([$id]);
            $showtime = $sStmt->fetch();

            if (!$showtime) {
                throw new Exception("Suất chiếu không tồn tại.");
            }

            $now = time();
            $start_ts = strtotime($showtime['show_date'] . ' ' . $showtime['start_time']);
            if (!empty($showtime['end_time'])) {
                $end_ts = strtotime($showtime['show_date'] . ' ' . $showtime['end_time']);
                if ($end_ts <= $start_ts) {
                    $end_ts += 86400; // ends next day
                }
            } else {
                $duration = (int)($showtime['duration_min'] ?: 120);
                $end_ts = $start_ts + ($duration * 60);
            }

            if ($now > $end_ts) {
                throw new Exception("Suất chiếu này đã chiếu xong. Không được phép hủy khẩn cấp.");
            }
            if ($now >= $start_ts && $now <= $end_ts) {
                throw new Exception("Suất chiếu này đang chiếu. Không được phép hủy khẩn cấp.");
            }

            $pdo->beginTransaction();

            // Mark showtime as cancelled
            $pdo->prepare("UPDATE showtimes SET is_cancelled = 1 WHERE id = ?")->execute([$id]);

            // Find and refund active bookings
            $bStmt = $pdo->prepare("SELECT * FROM bookings WHERE showtime_id = ? AND status != 'cancelled'");
            $bStmt->execute([$id]);
            $bookings = $bStmt->fetchAll();

            $refundCount = 0;
            $totalRefunded = 0;
            foreach ($bookings as $bk) {
                $pdo->prepare("UPDATE bookings SET status = 'cancelled', payment_status = 'refunded', cancel_reason = 'Hệ thống tự động hủy và hoàn tiền do suất chiếu gặp sự cố kỹ thuật' WHERE id = ?")
                    ->execute([$bk['id']]);

                if ($bk['voucher_code']) {
                    $pdo->prepare("UPDATE vouchers SET used_count = GREATEST(0, used_count - 1) WHERE code = ?")
                        ->execute([$bk['voucher_code']]);
                }

                $refundCount++;
                $totalRefunded += $bk['total_amount'];
            }

            // Write system log
            $logDesc = "Đã HỦY KHẨN CẤP suất chiếu #$id (Phim: \"{$showtime['movie_title']}\", Ngày: " . date('d/m/Y', strtotime($showtime['show_date'])) . " lúc " . substr($showtime['start_time'], 0, 5) . ") tại {$showtime['cinema_name']} ({$showtime['hall_name']}). Đã hoàn trả $refundCount vé với tổng số tiền " . number_format($totalRefunded, 0, ',', '.') . "₫.";
            
            $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Hủy suất chiếu khẩn cấp', ?)")
                ->execute([$_SESSION['user_name'], $logDesc]);

            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => "Đã HỦY KHẨN CẤP suất chiếu thành công! Hoàn trả $refundCount hóa đơn đặt vé với tổng trị giá " . number_format($totalRefunded, 0, ',', '.') . "₫."
            ]);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => "Lỗi khi thực hiện hủy khẩn cấp: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "ID suất chiếu không hợp lệ."]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
