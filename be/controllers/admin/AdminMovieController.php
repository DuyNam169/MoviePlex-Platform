<?php

require_once BASE_PATH . '/be/core/Response.php';

class AdminMovieController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. LIST MOVIES
    public function list(): void
    {
        try {
            $movies = $this->pdo->query("SELECT * FROM movies ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            Response::success(['data' => $movies]);
        } catch (Exception $e) {
            Response::error('Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    // 2. MOVIE STATS
    public function stats(): void
    {
        try {
            $total_movies = $this->pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
            $now_showing = $this->pdo->query("SELECT COUNT(*) FROM movies WHERE status = 'now_showing'")->fetchColumn();
            $coming_soon = $this->pdo->query("SELECT COUNT(*) FROM movies WHERE status = 'coming_soon'")->fetchColumn();
            Response::success([
                'stats' => [
                    'total_movies' => (int)$total_movies,
                    'now_showing' => (int)$now_showing,
                    'coming_soon' => (int)$coming_soon
                ]
            ]);
        } catch (Exception $e) {
            Response::error('Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    // 3. SAVE MOVIE (CREATE / UPDATE)
    public function save(): void
    {
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
                Response::error("Lỗi logic: Phim có ngày khởi chiếu trong tương lai (" . date('d/m/Y', strtotime($release_date)) . ") không thể đặt trạng thái là <b>Đang chiếu</b>. Vui lòng chỉnh sửa ngày khởi chiếu hoặc chọn trạng thái 'Sắp chiếu'.");
            }

            try {
                if ($id > 0) {
                    // Update
                    $stmt = $this->pdo->prepare("UPDATE movies SET title=?, description=?, genre=?, duration_min=?, release_date=?, rating=?, poster_url=?, backdrop_url=?, trailer_url=?, director=?, cast_list=?, age_rating=?, status=? WHERE id=?");
                    $stmt->execute([$title, $description ?: null, $genre ?: null, $duration_min, $release_date ?: null, $rating, $poster_url ?: null, $backdrop_url ?: null, $trailer_url ?: null, $director ?: null, $cast_list ?: null, $age_rating, $status, $id]);
                    
                    // Log
                    $logDesc = "Đã cập nhật thông tin phim: \"$title\"";
                    $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Cập nhật phim', ?)")
                        ->execute([$_SESSION['user_name'], $logDesc]);

                    Response::success([], "Đã cập nhật phim <b>" . htmlspecialchars($title) . "</b> thành công!");
                } else {
                    // Create
                    $stmt = $this->pdo->prepare("INSERT INTO movies (title, description, genre, duration_min, release_date, rating, poster_url, backdrop_url, trailer_url, director, cast_list, age_rating, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description ?: null, $genre ?: null, $duration_min, $release_date ?: null, $rating, $poster_url ?: null, $backdrop_url ?: null, $trailer_url ?: null, $director ?: null, $cast_list ?: null, $age_rating, $status]);
                    
                    // Log
                    $logDesc = "Đã thêm phim mới: \"$title\"";
                    $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Thêm phim mới', ?)")
                        ->execute([$_SESSION['user_name'], $logDesc]);

                    Response::success([], "Đã thêm phim mới <b>" . htmlspecialchars($title) . "</b> thành công!");
                }
            } catch (Exception $e) {
                Response::error("Lỗi hệ thống: " . $e->getMessage());
            }
        } else {
            Response::error("Vui lòng điền tiêu đề phim.");
        }
    }

    // 4. DELETE MOVIE
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                // Get movie title first for logging
                $mStmt = $this->pdo->prepare("SELECT title FROM movies WHERE id = ?");
                $mStmt->execute([$id]);
                $title = $mStmt->fetchColumn();

                $stmt = $this->pdo->prepare("DELETE FROM movies WHERE id = ?");
                $stmt->execute([$id]);
                
                // Log
                $logDesc = "Đã xóa phim: \"$title\"";
                $this->pdo->prepare("INSERT INTO system_logs (log_time, user_name, role, action_type, action_desc) VALUES (NOW(), ?, 'admin', 'Xóa phim', ?)")
                    ->execute([$_SESSION['user_name'], $logDesc]);

                Response::success([], "Đã xóa phim thành công!");
            } catch (Exception $e) {
                Response::error("Không thể xóa phim này do đã có Suất chiếu được xếp lịch.");
            }
        } else {
            Response::error("ID phim không hợp lệ.");
        }
    }
}
