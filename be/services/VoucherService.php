<?php

require_once __DIR__ . '/../models/Voucher.php';
require_once __DIR__ . '/../models/Booking.php';

class VoucherService
{
    private PDO $pdo;
    private Voucher $voucherModel;
    private Booking $bookingModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->voucherModel = new Voucher($pdo);
        $this->bookingModel = new Booking($pdo);
    }

    public function getUserActiveVouchers(int $userId): array
    {
        return $this->voucherModel->getByUser($userId);
    }

    public function validateCheckoutVoucher(string $code, int $userId, int $ticketTotal, int $snacksTotal): array
    {
        $code = trim(strtoupper($code));
        if ($code === '') {
            return ['valid' => false, 'message' => 'Vui lòng nhập mã voucher.'];
        }

        $voucher = $this->voucherModel->findByCode($code);
        if (!$voucher) {
            return ['valid' => false, 'message' => 'Mã voucher không hợp lệ hoặc không tồn tại.'];
        }

        if (!(int) $voucher['is_active']) {
            return ['valid' => false, 'message' => 'Mã voucher này đã bị vô hiệu hóa.'];
        }

        if ($voucher['expire_date'] && $voucher['expire_date'] < date('Y-m-d')) {
            return ['valid' => false, 'message' => 'Mã voucher đã hết hạn.'];
        }

        if ($voucher['max_uses'] !== null && (int) $voucher['used_count'] >= (int) $voucher['max_uses']) {
            return ['valid' => false, 'message' => 'Mã voucher đã hết lượt sử dụng.'];
        }

        if ($voucher['user_id'] !== null && (int) $voucher['user_id'] !== $userId) {
            return ['valid' => false, 'message' => 'Bạn không có quyền sử dụng mã voucher này.'];
        }

        $parts = explode('-', $code);
        if (count($parts) > 1) {
            $prefix = $parts[0];
            $parent = $this->voucherModel->findByCode($prefix);
            if ($parent && !(int) $parent['is_active']) {
                return ['valid' => false, 'message' => 'Mã voucher này đã hết hạn hoặc tạm dừng áp dụng.'];
            }
        }

        $orderAmount = $ticketTotal + $snacksTotal;
        if ($orderAmount < (int) $voucher['min_order']) {
            return ['valid' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã này.'];
        }

        if (str_starts_with($code, 'GIFTPOP')) {
            $discount = max(0, $snacksTotal);
            return ['valid' => true, 'discount' => $discount, 'message' => 'Áp dụng voucher thành công.', 'voucher' => $voucher];
        }

        if ((float) $voucher['discount_pct'] > 0) {
            $discount = (int) round($orderAmount * (float) $voucher['discount_pct'] / 100);
        } elseif ((float) $voucher['discount_amt'] > 0) {
            $discount = min((int) $voucher['discount_amt'], $orderAmount);
        } else {
            $discount = 0;
        }

        if ($discount <= 0) {
            return ['valid' => false, 'message' => 'Mã voucher không áp dụng được cho đơn hàng này.'];
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM bookings WHERE user_id = ? AND voucher_code = ? AND status != "cancelled"');
        $stmt->execute([$userId, $code]);
        if ((int) $stmt->fetchColumn() > 0) {
            return ['valid' => false, 'message' => 'Bạn đã sử dụng mã voucher này cho một giao dịch trước đó.'];
        }

        return ['valid' => true, 'discount' => $discount, 'message' => 'Áp dụng voucher thành công.', 'voucher' => $voucher];
    }

    private function getRewardDefinitions(): array
    {
        return [
            [
                'id' => 'r1',
                'name' => 'Voucher giảm giá 30.000₫',
                'desc' => 'Áp dụng cho mọi hóa đơn đặt vé hoặc combo bắp nước.',
                'points' => 15,
                'type' => 'discount',
                'value' => 30000,
                'code_prefix' => 'REDM30K',
                'is_active' => 1,
            ],
            [
                'id' => 'r2',
                'name' => 'Voucher giảm giá 50.000₫',
                'desc' => 'Áp dụng cho mọi hóa đơn đặt vé hoặc combo bắp nước.',
                'points' => 25,
                'type' => 'discount',
                'value' => 50000,
                'code_prefix' => 'REDM50K',
                'is_active' => 1,
            ],
            [
                'id' => 'r3',
                'name' => 'Voucher giảm giá 100.000₫',
                'desc' => 'Món quà đặc biệt dành cho thành viên tích cực.',
                'points' => 50,
                'type' => 'discount',
                'value' => 100000,
                'code_prefix' => 'REDM100K',
                'is_active' => 1,
            ],
            [
                'id' => 'r4',
                'name' => 'Combo Bắp + Nước miễn phí',
                'desc' => 'Quy đổi lấy 1 bắp cỡ L và 1 nước cỡ L tại quầy rạp.',
                'points' => 35,
                'type' => 'gift',
                'value' => 'Bắp L + Pepsi L',
                'code_prefix' => 'GIFTPOP',
                'is_active' => 1,
            ],
        ];
    }

    private function ensureGlobalVoucherTemplates(): void
    {
        $templates = $this->getRewardDefinitions();
        $existing = [];
        $codes = array_map(fn($item) => $item['code_prefix'], $templates);

        if (empty($codes)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $stmt = $this->pdo->prepare("SELECT code FROM vouchers WHERE code IN ($placeholders) AND user_id IS NULL");
        $stmt->execute($codes);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $code) {
            $existing[$code] = true;
        }

        foreach ($templates as $template) {
            if (!isset($existing[$template['code_prefix']])) {
                $this->pdo->prepare(
                    'INSERT INTO vouchers (code, description, discount_pct, discount_amt, min_order, max_uses, used_count, expire_date, is_active, user_id)
                     VALUES (?, ?, ?, ?, ?, 9999, 0, NULL, 1, NULL)'
                )->execute([
                    $template['code_prefix'],
                    $template['desc'],
                    $template['type'] === 'discount' ? 0 : 100,
                    $template['type'] === 'discount' ? $template['value'] : 0,
                    0,
                ]);
            }
        }
    }

    private function seedWelcomeVouchersForUser(int $userId): void
    {
        $welcomeCodes = ['SUMMER30', 'NEWUSER50', 'MOVIE20'];
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM vouchers WHERE user_id = ? AND (code LIKE ? OR code LIKE ? OR code LIKE ? )'
        );
        $stmt->execute([$userId, 'SUMMER30%', 'NEWUSER50%', 'MOVIE20%']);
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($welcomeCodes), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM vouchers WHERE code IN ($placeholders) AND user_id IS NULL");
        $stmt->execute($welcomeCodes);
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($templates as $template) {
            $uniq = strtoupper(substr(uniqid(), -6));
            $code = $template['code'] . '-' . $uniq;
            $this->pdo->prepare(
                'INSERT INTO vouchers (code, description, discount_pct, discount_amt, min_order, max_uses, used_count, expire_date, is_active, user_id)
                 VALUES (?, ?, ?, ?, ?, 1, 0, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1, ?)'
            )->execute([
                $code,
                $template['description'],
                $template['discount_pct'],
                $template['discount_amt'],
                $template['min_order'],
                $userId,
            ]);
        }
    }

    public function getVoucherDashboard(int $userId): array
    {
        $this->ensureGlobalVoucherTemplates();
        $this->seedWelcomeVouchersForUser($userId);

        $userStmt = $this->pdo->prepare('SELECT member_tier, loyalty_points FROM users WHERE id = ? LIMIT 1');
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'user' => [
                'member_tier' => $user['member_tier'] ?? 'STANDARD',
                'loyalty_points' => (int) ($user['loyalty_points'] ?? 0),
            ],
            'vouchers' => $this->getUserActiveVouchers($userId),
            'rewards' => $this->getRewardDefinitions(),
        ];
    }

    public function redeemReward(int $userId, string $rewardId): array
    {
        $dashboard = $this->getVoucherDashboard($userId);
        $userPoints = $dashboard['user']['loyalty_points'];
        $reward = null;

        foreach ($this->getRewardDefinitions() as $item) {
            if ($item['id'] === $rewardId) {
                $reward = $item;
                break;
            }
        }

        if ($reward === null) {
            return ['success' => false, 'message' => 'Phần thưởng không tồn tại.'];
        }

        if (!$reward['is_active']) {
            return ['success' => false, 'message' => 'Phần thưởng hiện đang tạm dừng.'];
        }

        if ($userPoints < $reward['points']) {
            return ['success' => false, 'message' => 'Bạn không đủ điểm để đổi phần thưởng này.'];
        }

        $codePrefix = $reward['code_prefix'];
        $uniq = strtoupper(substr(uniqid('', true), -6));
        $code = $codePrefix . '-' . $uniq;
        $description = 'Đổi thưởng (' . $reward['points'] . ' điểm): ' . $reward['name'];
        $discountPct = $reward['type'] === 'gift' ? 0 : 0;
        $discountAmt = $reward['type'] === 'discount' ? (int) $reward['value'] : 0;
        if ($reward['type'] === 'gift') {
            $discountAmt = 999999;
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                'UPDATE users SET loyalty_points = loyalty_points - ? WHERE id = ?'
            );
            $stmt->execute([$reward['points'], $userId]);

            $ins = $this->pdo->prepare(
                'INSERT INTO vouchers (code, description, discount_pct, discount_amt, min_order, max_uses, used_count, expire_date, is_active, user_id)
                 VALUES (?, ?, ?, ?, ?, 1, 0, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1, ?)'
            );
            $ins->execute([
                $code,
                $description,
                $discountPct,
                $discountAmt,
                0,
                $userId,
            ]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Đổi thưởng thành công. Mã voucher đã được thêm vào ví.', 'voucher_code' => $code, 'points_used' => $reward['points']];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Đổi thưởng không thành công. Vui lòng thử lại.'];
        }
    }

    public function markVoucherUsed(string $code): bool
    {
        return $this->voucherModel->markUsed($code);
    }
}
