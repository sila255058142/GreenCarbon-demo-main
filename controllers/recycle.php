<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if (!$u) go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
$cats = ($db->query('SELECT * FROM categories ORDER BY id'))->fetch_all(MYSQLI_ASSOC);

if ($_POST) {
    $category = (($statement = $db->prepare('SELECT * FROM categories WHERE id = ?')) && $statement->bind_param(str_repeat('s', count([trim($_POST['category_id'] ?? '')])), ...[trim($_POST['category_id'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_assoc();
    $weight = (float) trim($_POST['weight_kg'] ?? '');
    $delivery = trim($_POST['delivery_type'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if (mb_strlen(trim($_POST['item_name'] ?? '')) < 3 || $weight <= 0 || !$category
        || !in_array($delivery, ['self', 'pickup'], true) || !$address || mb_strlen($address) > 500) {
        go('recycle.php', 'กรุณาตรวจสอบข้อมูลอุปกรณ์ ประเภทการขนส่ง และที่อยู่', 'danger');
    }
    $image = upload('image');
    if ($image === false) go('recycle.php', 'รูปต้องเป็นไฟล์ JPG PNG หรือ GIF ขนาดไม่เกิน 2MB', 'danger');
    $estimatedCo2 = $weight * $category['co2_per_kg'];
    (($statement = $db->prepare('INSERT INTO recycles (user_id, category_id, item_name, weight_kg, image, delivery_type, address) VALUES (?, ?, ?, ?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([$u['id'], trim($_POST['category_id'] ?? ''), trim($_POST['item_name'] ?? ''), $weight, $image, $delivery, $address])), ...[$u['id'], trim($_POST['category_id'] ?? ''), trim($_POST['item_name'] ?? ''), $weight, $image, $delivery, $address]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    (($statement = $db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([null, 'คำขอรีไซเคิลใหม่', mb_substr($u['name'] . ': ' . trim($_POST['item_name'] ?? ''), 0, 500), 'admin/recycles.php?status=pending'])), ...[null, 'คำขอรีไซเคิลใหม่', mb_substr($u['name'] . ': ' . trim($_POST['item_name'] ?? ''), 0, 500), 'admin/recycles.php?status=pending']) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('recycle.php#recycle-form', 'ส่งคำขอแล้ว คาดว่าจะลดคาร์บอนได้ ' . number_format($estimatedCo2, 2) . ' kgCO₂e (ยืนยันผลจริงเมื่อผู้ดูแลตรวจรับ) คุณจะได้ 20 แต้มต่อ 1 กก.');
}

$year = date('Y');
$saved = ((($statement = $db->prepare("SELECT COALESCE(SUM(co2_kg), 0) FROM recycles WHERE user_id = ? AND status = 'done' AND YEAR(created_at) = ?")) && $statement->bind_param(str_repeat('s', count([$u['id'], $year])), ...[$u['id'], $year]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_row() ?? [])[0] ?? null;
