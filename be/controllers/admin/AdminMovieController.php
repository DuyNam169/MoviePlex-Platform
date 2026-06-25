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

// 1. LIST MOVIES
if ($action === 'list') {
    try {
        $movies = $pdo->query("SELECT * FROM movies ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $movies]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// 2. MOVIE STATS
if ($action === 'stats') {
    try {
        $total_movies = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
        $now_showing = $pdo->query("SELECT COUNT(*) FROM movies WHERE status = 'now_showing'")->fetchColumn();
        $coming_soon = $pdo->query("SELECT COUNT(*) FROM movies WHERE status = 'coming_soon'")->fetchColumn();
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_movies' => (int)$total_movies,
                'now_showing' => (int)$now_showing,
                'coming_soon' => (int)$coming_soon
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// 3. SAVE MOVIE (CREATE / UPDATE)
if ($action === 'save') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $duration_min = (int)($_POST['duration_min'] ?? 120);
    $release_date = $_POST['release_date'] ?? null;
    $rating = (float)($_POST['rating'] ?? 8.0);
    $poster_url = trim($_POST['poster_url'] ?? '');
    $backdrop_url = trim($_POST['backdrop_url'] ?? '');
    $trailer_url = trim($_POST['trailer_url'] ?? '');
    $director = trim($_POST['director'] ?? '');
    $cast_list = trim($_POST['cast_list'] ?? '');
    $age_rating = trim($_POST['age_rating'] ?? 'P');
    $status = $_POST['status'] ?? 'now_showing';

    if ($title) {
        $today = date('Y-m-d');
        if ($status === 'now_showing' && $release_date && $release_date > $today) {
            echo json_encode([
                'success' => false, 
                'message' => "Lỗi logic: Phim có ngày khởi chiếu trong tương lai (" . date('d/m/Y', strtotime($release_date)) . ") không thể đặt trạng thái là <b>Đang chiếu</b>. Vui lòng chỉnh sửa ngày khởi chiếu hoặc chọn trạng thái 'Sắp chiếu'."
            ]);
            exit;
        }

        try {
            if ($id > 0) {
                // Update
                $stmt = $pdo->prepare("UPDATE movies SET title=?, description=?, genre=?, duration_min=?, release_date=?, rating=?, poster_url=?, backdrop_url=?, trailer_url=?, director=?, cast_list=?, age_rating=?, status=? WHERE id=?");
                $stmt->execute([$title, $description ?: null, $genre ?: null, $duration_min, $release_date ?: null, $rating, $poster_url ?: null, $backdrop_url ?: null, $trailer_url ?: null, $director ?: null, $cast_list ?: null, $age_rating, $status, $id]);
                
                // Log
                $logDesc = "Đã cập nhật thông tin phim: \"$title\"";
                $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Cập nhật phim', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                echo json_encode(['success' => true, 'message' => "Đã cập nhật phim <b>" . htmlspecialchars($title) . "</b> thành công!"]);
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO movies (title, description, genre, duration_min, release_date, rating, poster_url, backdrop_url, trailer_url, director, cast_list, age_rating, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description ?: null, $genre ?: null, $duration_min, $release_date ?: null, $rating, $poster_url ?: null, $backdrop_url ?: null, $trailer_url ?: null, $director ?: null, $cast_list ?: null, $age_rating, $status]);
                
                // Log
                $logDesc = "Đã thêm phim mới: \"$title\"";
                $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Thêm phim mới', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                echo json_encode(['success' => true, 'message' => "Đã thêm phim mới <b>" . htmlspecialchars($title) . "</b> thành công!"]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Lỗi hệ thống: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Vui lòng điền tiêu đề phim."]);
    }
    exit;
}

// 4. DELETE MOVIE
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            // Get movie title first for logging
            $mStmt = $pdo->prepare("SELECT title FROM movies WHERE id = ?");
            $mStmt->execute([$id]);
            $title = $mStmt->fetchColumn();

            $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
            $stmt->execute([$id]);
            
            // Log
            $logDesc = "Đã xóa phim: \"$title\"";
            $pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Xóa phim', ?)")
                ->execute([$_SESSION['user_name'], $logDesc]);

            echo json_encode(['success' => true, 'message' => "Đã xóa phim thành công!"]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Không thể xóa phim này do đã có Suất chiếu được xếp lịch."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "ID phim không hợp lệ."]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
