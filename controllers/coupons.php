<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if (!$u) go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
$userId = $u['id'];

// 1. แลกคูปอง
if (isset($_POST['redeem'])) {
    $rewardId = $_POST['reward_id'] ?? 0;
    $reward = $db->query("SELECT * FROM rewards WHERE id = $rewardId AND is_active = 1")->fetch_assoc();
    
    // เช็กว่ามีของรางวัลจริงไหม
    if (!$reward) go('coupons.php', 'ไม่พบของรางวัลนี้', 'danger');

    $cost = $reward['points_cost'];
    $db->query("UPDATE users SET points = points - $cost WHERE id = $userId");
    $db->query("UPDATE rewards SET stock = stock - 1 WHERE id = $rewardId AND stock IS NOT NULL");
    $db->query("INSERT INTO coupon_redemptions (user_id, reward_id, points_spent) VALUES ($userId, $rewardId, $cost)");
    
    go('coupons.php', 'ส่งคำขอแลกแล้ว');
}

// 2. ใช้คูปองรับของ
if (isset($_POST['claim'])) {
    $code      = $_POST['coupon_code'] ?? '';
    $recipient = $_POST['recipient_name'] ?? '';
    $phone     = $_POST['recipient_phone'] ?? '';
    $address   = $_POST['shipping_address'] ?? '';

    // เช็กว่ามีคูปองนี้ในระบบของสมาชิกคนนี้ไหม
    $coupon = $db->query("SELECT id FROM coupon_redemptions WHERE coupon_code = '$code' AND user_id = $userId")->fetch_assoc();
    if (!$coupon) go('coupons.php', 'ไม่พบคูปอง หรือคูปองไม่ถูกต้อง', 'danger');

    $db->query("UPDATE coupon_redemptions SET status = 'used', used_at = NOW(), recipient_name = '$recipient', recipient_phone = '$phone', shipping_address = '$address' WHERE coupon_code = '$code' AND user_id = $userId");
    
    go('coupons.php', 'ยืนยันใช้คูปองแล้ว');
}

// 3. ดึงข้อมูล
$rewards = $db->query('SELECT * FROM rewards WHERE is_active = 1')->fetch_all(MYSQLI_ASSOC);
$balance = $db->query("SELECT points FROM users WHERE id = $userId")->fetch_row()[0] ?? 0;