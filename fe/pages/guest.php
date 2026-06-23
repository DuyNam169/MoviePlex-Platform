<?php
/**
 * guest.php — Trang công khai dành cho khách (không cần đăng nhập)
 * Logic DB đã được tách ra MovieService và CinemaService.
 */
require_once __DIR__ . '/../../be/config/db.php';
require_once __DIR__ . '/../../be/services/MovieService.php';
require_once __DIR__ . '/../../be/services/CinemaService.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Nếu đã đăng nhập → chuyển về home
if (!empty($_SESSION['user_id'])) {
    header('Location: /fe/pages/home.php');
    exit;
}

$movieService  = new MovieService($pdo);
$cinemaService = new CinemaService($pdo);

// Auto-promote coming_soon → now_showing (1 chỗ duy nhất, không rải rác)
$movieService->autoPromoteMovies();

$movies_showing = $movieService->getNowShowingWithFormats(20);
$hero_movies    = $movieService->getHeroMovies($movies_showing, 5);
$movies_coming  = $movieService->getComingSoon(8);
$cinemas        = $cinemaService->getAll(6);

// Vouchers nổi bật (public campaigns)
$promos = $pdo->query("
    SELECT * FROM vouchers WHERE is_active = 1 ORDER BY id DESC LIMIT 3
")->fetchAll();
?>