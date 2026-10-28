<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if (isset($_POST['save_reward'])) {
    $title = trim($_POST['title'] ?? ''); $description = trim($_POST['description'] ?? ''); $cost = trim($_POST['points_cost'] ?? '');
    $stock = trim($_POST['stock'] ?? ''); $days = trim($_POST['coupon_valid_days'] ?? '');

    if (mb_strlen($title) < 2 || mb_strlen($description) > 500 || !ctype_digit($cost) || (int) $cost < 1
        || ($stock !== '' && (!ctype_digit($stock) || (int) $stock < 0))
        || !ctype_digit($days) || (int) $days < 1 || (int) $days > 365) {
        go('coupons.php', 'กรุณาตรวจสอบชื่อ แต้ม จำนวนสต็อก และวันหมดอายุ (1-365 วัน)', 'danger');
    }
    $image = upload('image');
    if ($image === false) go('coupons.php', 'รูปต้องเป็นไฟล์ JPG PNG หรือ GIF ขนาดไม่เกิน 2MB', 'danger');

    (($statement = $db->prepare('INSERT INTO rewards (title, description, points_cost, stock, coupon_valid_days, image) VALUES (?, ?, ?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([$title, $description, (int) $cost, $stock === '' ? null : (int) $stock, (int) $days, $image])), ...[$title, $description, (int) $cost, $stock === '' ? null : (int) $stock, (int) $days, $image]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('coupons.php', 'บันทึกของรางวัลแล้ว');
}
