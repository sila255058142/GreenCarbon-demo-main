<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if (!$u) go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
$cats = ($db->query('SELECT * FROM categories ORDER BY id'))->fetch_all(MYSQLI_ASSOC);
$userId = (int) $u['id'];

if ($_POST) {
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();

    $itemName = trim($_POST['item_name'] ?? '');
    $weight = (float) ($_POST['weight_kg'] ?? 0);
    $delivery = trim($_POST['delivery_type'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if (mb_strlen($itemName) < 3 || $weight <= 0 || !$category) {
        go('recycle.php', 'กรุณาตรวจสอบข้อมูลอุปกรณ์ ประเภทการขนส่ง และที่อยู่', 'danger');
    }

    $image = upload('image');
    $estimatedCo2 = $weight * $category['co2_per_kg'];
    $stmt = $db->prepare('INSERT INTO recycles (user_id, category_id, item_name, weight_kg, image, delivery_type, address) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iisdsss', $userId, $categoryId, $itemName, $weight, $image, $delivery, $address);
    $stmt->execute();

    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (NULL, ?, ?, 'admin/recycles.php?status=pending')");
    $title = 'คำขอรีไซเคิลใหม่';
    $stmt->bind_param('ss', $title, $message);
    $stmt->execute();

    go('recycle.php#recycle-form', 'ส่งคำขอแล้ว คาดว่าจะลดคาร์บอนได้ ' . number_format($estimatedCo2, 2) . ' kgCO₂e (ยืนยันผลจริงเมื่อผู้ดูแลตรวจรับ) คุณจะได้ 20 แต้มต่อ 1 กก.');
}

$year = (int) date('Y');
$stmt = $db->prepare("SELECT COALESCE(SUM(co2_kg), 0) AS total FROM recycles WHERE user_id = ? AND status = 'done' AND YEAR(created_at) = ?");
$stmt->bind_param('ii', $userId, $year);
$stmt->execute();
$saved = $stmt->get_result()->fetch_assoc()['total'] ?? null;
