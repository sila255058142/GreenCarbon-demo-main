<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

$sum = ($db->query("SELECT COALESCE(SUM(weight_kg), 0) AS kg, COALESCE(SUM(co2_kg), 0) AS co2 FROM recycles WHERE status = 'done'"))->fetch_assoc();
$byMonth = ($db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, SUM(co2_kg) AS v FROM recycles WHERE status = 'done' GROUP BY m ORDER BY m"))->fetch_all(MYSQLI_ASSOC);
$byCat = ($db->query("SELECT c.name, SUM(r.weight_kg) AS v FROM recycles r JOIN categories c ON c.id = r.category_id WHERE r.status = 'done' GROUP BY c.name"))->fetch_all(MYSQLI_ASSOC);
$members = ($db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, COUNT(*) AS v FROM users WHERE role = 'user' GROUP BY m ORDER BY m"))->fetch_all(MYSQLI_ASSOC);


$notis = ($db->query('SELECT * FROM notifications WHERE user_id IS NULL AND is_read = 0 ORDER BY id DESC LIMIT 5'))->fetch_all(MYSQLI_ASSOC);
$couponRequests = ($db->query("SELECT cr.id, cr.points_spent, u.name AS member, r.title AS reward_title FROM coupon_redemptions cr
    JOIN users u ON u.id = cr.user_id JOIN rewards r ON r.id = cr.reward_id
    WHERE cr.status = 'pending' ORDER BY cr.id LIMIT 5"))->fetch_all(MYSQLI_ASSOC);
$fulfillments = ($db->query("SELECT coupon_code, recipient_name, recipient_phone, shipping_address, created_at FROM coupon_redemptions
    WHERE status = 'used' ORDER BY used_at DESC LIMIT 5"))->fetch_all(MYSQLI_ASSOC);
$memberCount = (($db->query("SELECT COUNT(*) FROM users WHERE role = 'user'"))->fetch_row() ?? [])[0] ?? null;
$suspendedCount = (($db->query("SELECT COUNT(*) FROM users WHERE status = 'suspended'"))->fetch_row() ?? [])[0] ?? null;
$pendingRecycleCount = (($db->query("SELECT COUNT(*) FROM recycles WHERE status = 'pending'"))->fetch_row() ?? [])[0] ?? null;
$pendingCouponCount = (($db->query("SELECT COUNT(*) FROM coupon_redemptions WHERE status = 'pending'"))->fetch_row() ?? [])[0] ?? null;
$unreadNotificationCount = (($db->query('SELECT COUNT(*) FROM notifications WHERE user_id IS NULL AND is_read = 0'))->fetch_row() ?? [])[0] ?? null;
