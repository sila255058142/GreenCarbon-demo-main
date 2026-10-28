<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if (!$u) go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');

$userId = (int) $u['id'];
if (isset($_POST['redeem'])) {
    $rewardId = (int) ($_POST['reward_id'] ?? 0);
    $db->begin_transaction();
    $reward = $db->query("SELECT id, title, points_cost, stock FROM rewards WHERE id = $rewardId AND is_active = 1 FOR UPDATE")->fetch_assoc();

    if (!$reward || ($reward['stock'] !== null && $reward['stock'] < 1)) {
        $db->rollback();
        go('coupons.php', 'ไม่พบของรางวัลนี้ ปิดรับแลกแล้ว หรือของรางวัลหมด', 'danger');
    }

    $cost = (int) $reward['points_cost'];
    if (!$db->query("UPDATE users SET points = points - $cost WHERE id = $userId AND points >= $cost") || !$db->affected_rows) {
        $db->rollback();
        go('coupons.php', 'แต้มของคุณไม่เพียงพอสำหรับแลกรางวัลนี้', 'danger');
    }

    $rewardId = (int) $reward['id'];
    $reserved = $reward['stock'] === null ? 0 : 1;
    if ($reserved && !$db->query("UPDATE rewards SET stock = stock - 1 WHERE id = $rewardId AND stock > 0")) {
        $db->rollback();
        go('coupons.php', 'ของรางวัลหมดแล้ว', 'danger');
    }

    $db->query("INSERT INTO coupon_redemptions (user_id, reward_id, points_spent, stock_reserved) VALUES ($userId, $rewardId, $cost, $reserved)");
    $redemptionId = $db->insert_id;
    $description = $db->real_escape_string('แลกของรางวัล: ' . $reward['title']);
    $db->query("INSERT INTO point_transactions (user_id, amount, description, related_type, related_id) VALUES ($userId, -$cost, '$description', 'coupon', $redemptionId)");
    $db->commit();

    $_SESSION['user']['points'] -= $cost;
    $message = $db->real_escape_string(mb_substr($u['name'] . ' ขอแลก ' . $reward['title'], 0, 500));
    $db->query("INSERT INTO notifications (user_id, title, message, link) VALUES (NULL, 'คำขอแลกคูปองใหม่', '$message', 'admin/coupons.php')");
    go('coupons.php', 'ส่งคำขอแลกแล้ว แต้มถูกหักไว้ระหว่างรออนุมัติ');
}

if (isset($_POST['claim'])) {
    $code = $db->real_escape_string(strtoupper(trim($_POST['coupon_code'] ?? '')));
    $recipient = trim($_POST['recipient_name'] ?? '');
    $phone = trim($_POST['recipient_phone'] ?? '');
    $address = trim($_POST['shipping_address'] ?? '');
    $digits = preg_replace('/\D/', '', $phone);

    if (mb_strlen($recipient) < 2 || strlen($digits) < 8 || strlen($digits) > 15 || mb_strlen($address) < 10) {
        go('coupons.php', 'กรุณากรอกชื่อผู้รับ เบอร์โทร และที่อยู่จัดส่งให้ครบถ้วน', 'danger');
    }

    $db->begin_transaction();
    $coupon = $db->query("SELECT id, reward_id, status, expires_at, stock_reserved FROM coupon_redemptions WHERE coupon_code = '$code' AND user_id = $userId FOR UPDATE")->fetch_assoc();
    if (!$coupon || $coupon['status'] !== 'approved') {
        $db->rollback();
        go('coupons.php', 'ไม่พบคูปองที่ยังใช้ได้ในบัญชีของคุณ', 'danger');
    }

    $couponId = (int) $coupon['id'];
    if ($coupon['expires_at'] && strtotime($coupon['expires_at']) <= time()) {
        $rewardId = (int) $coupon['reward_id'];
        $reserved = (int) $coupon['stock_reserved'];
        $db->query("UPDATE coupon_redemptions SET status = 'expired', stock_reserved = 0 WHERE id = $couponId");
        $db->query("UPDATE rewards SET stock = IF(stock IS NULL OR $reserved = 0, stock, stock + 1) WHERE id = $rewardId");
        $db->commit();

        $message = $db->real_escape_string(mb_substr("คูปอง $code หมดอายุแล้ว", 0, 500));
        $db->query("INSERT INTO notifications (user_id, title, message, link) VALUES ($userId, 'คูปองหมดอายุ', '$message', 'coupons.php')");
        go('coupons.php', 'คูปองนี้หมดอายุแล้ว', 'danger');
    }

    $recipient = $db->real_escape_string($recipient);
    $phone = $db->real_escape_string($phone);
    $address = $db->real_escape_string($address);
    $db->query("UPDATE coupon_redemptions SET status = 'used', used_at = NOW(), stock_reserved = 0, recipient_name = '$recipient', recipient_phone = '$phone', shipping_address = '$address' WHERE id = $couponId AND status = 'approved'");
    $db->commit();

    $message = $db->real_escape_string(mb_substr($u['name'] . " ใช้คูปอง $code กรุณาจัดส่งของ", 0, 500));
    $db->query("INSERT INTO notifications (user_id, title, message, link) VALUES (NULL, 'ใช้คูปองรับของ', '$message', 'admin/coupons.php')");
    go('coupons.php', 'ยืนยันใช้คูปองแล้ว แอดมินได้รับข้อมูลสำหรับจัดส่งเรียบร้อย');
}

$rewards = $db->query('SELECT * FROM rewards WHERE is_active = 1 AND (stock IS NULL OR stock > 0) ORDER BY points_cost, id')->fetch_all(MYSQLI_ASSOC);
$balance = (int) $db->query("SELECT points FROM users WHERE id = $userId")->fetch_row()[0];
