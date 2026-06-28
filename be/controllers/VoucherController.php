<?php

require_once BASE_PATH . '/be/services/VoucherService.php';
require_once BASE_PATH . '/be/core/Response.php';
require_once BASE_PATH . '/be/middleware/AuthMiddleware.php';

class VoucherController
{
    private VoucherService $service;

    public function __construct(PDO $pdo)
    {
        $this->service = new VoucherService($pdo);
    }

    public function dashboard(): void
    {
        $userId = AuthMiddleware::userId();
        $data = $this->service->getVoucherDashboard($userId);
        Response::json(['success' => true, 'data' => $data]);
    }

    public function myVouchers(): void
    {
        $userId = AuthMiddleware::userId();
        $vouchers = $this->service->getUserActiveVouchers($userId);
        Response::json(['success' => true, 'data' => $vouchers]);
    }

    public function redeem(): void
    {
        $userId = AuthMiddleware::userId();
        $rewardId = trim($_POST['reward_id'] ?? '');
        $result = $this->service->redeemReward($userId, $rewardId);
        Response::json($result);
    }
}
