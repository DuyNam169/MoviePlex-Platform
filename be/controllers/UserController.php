<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/UserService.php';

header('Content-Type: application/json; charset=utf-8');

// Mọi action trong controller này đều yêu cầu đăng nhập
AuthMiddleware::api();

$request = new Request();
$service = new UserService($pdo);
$userId  = AuthMiddleware::userId();
$action  = $request->post('action') ?? $request->get('action') ?? '';

switch ($action) {

    // ── PROFILE ────────────────────────────────────────────────────────────
    case 'profile':
        $result = $service->getProfile($userId);
        Response::json($result);
        break;

    case 'update_profile':
        $result = $service->updateProfile($userId, [
            'full_name' => $_POST['full_name'] ?? '',
            'phone'     => $_POST['phone'] ?? '',
        ]);

        if ($result['success']) {
            $_SESSION['user_name'] = $result['full_name'];
        }

        Response::json($result);
        break;

    // ── CHANGE PASSWORD ───────────────────────────────────────────────────
    case 'change_password':
        $oldPwd  = $_POST['old_password'] ?? '';
        $newPwd  = $_POST['new_password'] ?? '';
        $confPwd = $_POST['confirm_password'] ?? '';

        $result = $service->changePassword($userId, $oldPwd, $newPwd, $confPwd);
        Response::json($result);
        break;

    // ── MY TICKETS ─────────────────────────────────────────────────────────
    case 'my_tickets':
        $result = $service->getMyTickets($userId);
        Response::json($result);
        break;

    case 'cancel_booking':
        $bookingCode = trim($_POST['booking_code'] ?? '');
        $result      = $service->cancelBooking($userId, $bookingCode);
        Response::json($result);
        break;

    // ── MOVIE REVIEW ───────────────────────────────────────────────────────
    case 'submit_review':
        $bookingCode = trim($_POST['booking_code'] ?? '');
        $rating      = (int) ($_POST['rating'] ?? 10);
        $comment     = trim($_POST['comment'] ?? '');

        $result = $service->submitReview($userId, $bookingCode, $rating, $comment);
        Response::json($result);
        break;

    // ── MY VOUCHERS ────────────────────────────────────────────────────────
    case 'my_vouchers':
        $result = $service->getMyVouchers($userId);
        Response::json($result);
        break;

    // ── SUBMIT SUPPORT TICKET ──────────────────────────────────────────────
    case 'submit_support_ticket':
        $fullName = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $phone    = trim($_POST['phone']    ?? '');
        $subject  = trim($_POST['subject']  ?? '');
        $content  = trim($_POST['content']  ?? '');

        if (!$fullName || !$email || !$subject || !$content) {
            Response::error('Vui lòng điền đầy đủ các thông tin bắt buộc.');
            break;
        }

        try {
            $pdo->prepare("
                INSERT INTO support_tickets (fullname, email, phone, subject, content)
                VALUES (?, ?, ?, ?, ?)
            ")->execute([$fullName, $email, $phone ?: null, $subject, $content]);

            Response::json([
                'success' => true,
                'message' => 'Yêu cầu hỗ trợ của bạn đã được gửi đi! Chúng tôi sẽ phản hồi qua email trong vòng 24h.',
            ]);
        } catch (Exception $e) {
            Response::error('Đã có lỗi xảy ra. Vui lòng thử lại sau.');
        }
        break;

    // ── INVALID ACTION ─────────────────────────────────────────────────────
    default:
        Response::error('Hành động không hợp lệ.', 400);
        break;
}