<?php

require_once BASE_PATH . '/be/services/BookingService.php';
require_once BASE_PATH . '/be/core/Response.php';
require_once BASE_PATH . '/be/middleware/AuthMiddleware.php';

class BookingController
{
    private BookingService $service;

    public function __construct(PDO $pdo)
    {
        $this->service = new BookingService($pdo);
    }

    public function checkoutData(): void
    {
        $userId = AuthMiddleware::userId();
        $showtimeId = (int) ($_GET['showtime_id'] ?? 0);
        $seatsParam = trim($_GET['seats'] ?? '');

        try {
            $data = $this->service->getCheckoutContext($userId, $showtimeId, $seatsParam);
            Response::json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function createBooking(): void
    {
        $userId = AuthMiddleware::userId();
        $showtimeId = (int) ($_POST['showtime_id'] ?? 0);
        $seatsParam = trim($_POST['seats'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? 'momo');
        $voucherCode = trim($_POST['voucher_code'] ?? '');
        $snacks = json_decode($_POST['snacks_json'] ?? '[]', true);

        $result = $this->service->createBooking($userId, $showtimeId, $seatsParam, $paymentMethod, $voucherCode, is_array($snacks) ? $snacks : []);
        Response::json($result);
    }

    public function validateVoucher(): void
    {
        $userId = AuthMiddleware::userId();
        $voucherCode = trim($_POST['voucher_code'] ?? '');
        $showtimeId = (int) ($_POST['showtime_id'] ?? 0);
        $seatParam = trim($_POST['seats'] ?? '');
        $snacks = json_decode($_POST['snacks_json'] ?? '[]', true);

        $seatGroups = $this->service->parseSeatGroups($seatParam);
        $show = $this->service->getShowtime($showtimeId);
        $snacksTotal = $this->service->calculateSnackTotal(is_array($snacks) ? $snacks : []);

        if (!$show) {
            Response::error('Suất chiếu không tồn tại.');
            return;
        }

        $pricing = $this->service->calculateSeatPricing($seatGroups, (int) $show['price']);
        $voucher = $this->service->getVoucherService()->validateCheckoutVoucher($voucherCode, $userId, $pricing['serverTotal'], $snacksTotal);

        if (!$voucher['valid']) {
            Response::error($voucher['message']);
            return;
        }

        Response::json(['success' => true, 'message' => $voucher['message'], 'discount' => $voucher['discount'], 'voucher' => $voucher['voucher'] ?? null]);
    }

    public function bookingConfirm(): void
    {
        $userId = AuthMiddleware::userId();
        $userRole = $_SESSION['user_role'] ?? 'user';
        $code = trim($_GET['code'] ?? '');

        if ($code === '') {
            Response::error('Thiếu mã đặt vé.');
            return;
        }

        try {
            $data = $this->service->getBookingConfirmation($code, $userId, $userRole);
            Response::json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function seatSelectionData(): void
    {
        $showtimeId = (int) ($_GET['showtime_id'] ?? 0);

        try {
            $data = $this->service->getSeatSelectionContext($showtimeId);
            Response::json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}
