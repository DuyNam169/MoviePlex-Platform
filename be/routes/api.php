<?php

/**
 * API Routes
 *
 * All routes defined here are dispatched via /be/api.php.
 * $router and $pdo are available from the entry point.
 *
 * Middleware conventions:
 *   AuthMiddleware::api()   — requires login, returns 401 JSON if not
 *   AdminMiddleware::api()  — requires admin role, returns 403 JSON if not
 */

require_once BASE_PATH . '/be/models/User.php';
require_once BASE_PATH . '/be/services/AuthService.php';
require_once BASE_PATH . '/be/services/UserService.php';
require_once BASE_PATH . '/be/services/MovieService.php';
require_once BASE_PATH . '/be/services/CinemaService.php';
require_once BASE_PATH . '/be/services/BookingService.php';
require_once BASE_PATH . '/be/services/VoucherService.php';
require_once BASE_PATH . '/be/controllers/AuthController.php';
require_once BASE_PATH . '/be/controllers/UserController.php';
require_once BASE_PATH . '/be/controllers/MovieController.php';
require_once BASE_PATH . '/be/controllers/CinemaController.php';
require_once BASE_PATH . '/be/controllers/BookingController.php';
require_once BASE_PATH . '/be/controllers/VoucherController.php';

$authController    = new AuthController($pdo);
$userController    = new UserController($pdo);
$movieController   = new MovieController($pdo);
$cinemaController  = new CinemaController($pdo);
$bookingController = new BookingController($pdo);
$voucherController = new VoucherController($pdo);

// ── AUTH (public) ──────────────────────────────────────────────────────────

$router->post('login',    [$authController, 'login']);
$router->post('register', [$authController, 'register']);
$router->post('logout',   [$authController, 'logout']);

// ── FORGOT PASSWORD (public, session-tracked) ──────────────────────────────

$router->post('send_otp',   [$authController, 'sendOtp']);
$router->post('verify_otp', [$authController, 'verifyOtp']);
$router->post('reset_pwd',  [$authController, 'resetPwd']);

// ── USER (requires login) ──────────────────────────────────────────────────

$auth = [AuthMiddleware::class, 'api'];

$router->post('profile',              [$userController, 'profile'],            [$auth]);
$router->post('update_profile',       [$userController, 'updateProfile'],      [$auth]);
$router->post('change_password',      [$userController, 'changePassword'],     [$auth]);
$router->post('my_tickets',           [$userController, 'myTickets'],          [$auth]);
$router->post('cancel_booking',       [$userController, 'cancelBooking'],      [$auth]);
$router->post('submit_review',        [$userController, 'submitReview'],       [$auth]);
$router->post('my_vouchers',          [$userController, 'myVouchers'],         [$auth]);
$router->post('submit_support_ticket',[$userController, 'submitSupportTicket'],[$auth]);

// ── MOVIES ────────────────────────────────────────────────────────────────

$router->any('movies_by_status',     [$movieController, 'listByStatus']);
$router->get('movie_detail',          [$movieController, 'detail']);

// ── CINEMAS ─────────────────────────────────────────────────────────────

$router->any('cinemas_list',         [$cinemaController, 'list']);
$router->any('cinema_showtimes',     [$cinemaController, 'showtimes']);

// ── BOOKING ───────────────────────────────────────────────────────────────

$router->get('checkout_data',         [$bookingController, 'checkoutData'],    [$auth]);
$router->post('create_booking',       [$bookingController, 'createBooking'],   [$auth]);
$router->post('validate_voucher',     [$bookingController, 'validateVoucher'], [$auth]);
$router->get('booking_confirm',       [$bookingController, 'bookingConfirm'],  [$auth]);
$router->get('seat_selection',        [$bookingController, 'seatSelectionData']);

// ── VOUCHER ────────────────────────────────────────────────────────────────

$router->post('voucher_dashboard',    [$voucherController, 'dashboard'],       [$auth]);
$router->post('redeem_reward',        [$voucherController, 'redeem'],          [$auth]);
$router->post('my_vouchers',          [$voucherController, 'myVouchers'],      [$auth]);

// ── ADMIN (requires admin_monitor or admin role) ──────────────────────────

require_once BASE_PATH . '/be/controllers/admin/AdminCinemaController.php';
require_once BASE_PATH . '/be/controllers/admin/AdminMovieController.php';
require_once BASE_PATH . '/be/controllers/admin/AdminVoucherController.php';
require_once BASE_PATH . '/be/controllers/admin/AdminRevenueController.php';
require_once BASE_PATH . '/be/controllers/admin/AdminShowtimeController.php';

$adminCinema = new AdminCinemaController($pdo);
$adminMovie = new AdminMovieController($pdo);
$adminVoucher = new AdminVoucherController($pdo);
$adminRevenue = new AdminRevenueController($pdo);
$adminShowtime = new AdminShowtimeController($pdo);

$admin = [AdminMiddleware::class, 'api'];

// Admin Cinema Routes
$router->get('admin_cinema_list',      [$adminCinema, 'list'],      [$admin]);
$router->get('admin_cinema_halls',     [$adminCinema, 'getHalls'],  [$admin]);
$router->post('admin_cinema_save',     [$adminCinema, 'save'],      [$admin]);
$router->post('admin_cinema_delete',   [$adminCinema, 'delete'],    [$admin]);

// Admin Movie Routes
$router->get('admin_movie_list',       [$adminMovie, 'list'],       [$admin]);
$router->get('admin_movie_stats',      [$adminMovie, 'stats'],      [$admin]);
$router->post('admin_movie_save',      [$adminMovie, 'save'],       [$admin]);
$router->post('admin_movie_delete',    [$adminMovie, 'delete'],     [$admin]);

// Admin Voucher Routes
$router->get('admin_voucher_list',     [$adminVoucher, 'list'],     [$admin]);
$router->get('admin_voucher_stats',    [$adminVoucher, 'stats'],    [$admin]);
$router->post('admin_voucher_save',    [$adminVoucher, 'save'],     [$admin]);
$router->post('admin_voucher_toggle',  [$adminVoucher, 'toggleActive'], [$admin]);
$router->post('admin_voucher_delete',  [$adminVoucher, 'delete'],   [$admin]);

// Admin Revenue & Dashboard & Logs & Employees Routes
$router->get('admin_dashboard_data',   [$adminRevenue, 'dashboardData'], [$admin]);
$router->get('admin_logs',             [$adminRevenue, 'getLogs'],       [$admin]);
$router->get('admin_employees',        [$adminRevenue, 'getEmployees'],  [$admin]);
$router->get('admin_revenue_report',   [$adminRevenue, 'getRevenueReport'], [$admin]);

// Admin Showtime Routes
$router->get('admin_showtime_initial', [$adminShowtime, 'getInitialData'], [$admin]);
$router->get('admin_showtime_list',    [$adminShowtime, 'list'],            [$admin]);
$router->get('admin_showtime_halls',   [$adminShowtime, 'getCinemaHalls'],  [$admin]);
$router->get('admin_showtime_conflict',[$adminShowtime, 'checkConflict'],   [$admin]);
$router->post('admin_showtime_save',   [$adminShowtime, 'save'],            [$admin]);
$router->post('admin_showtime_delete', [$adminShowtime, 'delete'],          [$admin]);
$router->post('admin_showtime_cancel', [$adminShowtime, 'cancelUrgent'],    [$admin]);
