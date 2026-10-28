<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if ($_POST) {
    $db->begin_transaction();
    $r = (($statement = $db->prepare("SELECT r.*, c.co2_per_kg FROM recycles r JOIN categories c ON c.id = r.category_id WHERE r.id = ? AND r.status = 'pending' FOR UPDATE")) && $statement->bind_param(str_repeat('s', count([trim($_POST['id'] ?? '')])), ...[trim($_POST['id'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_assoc();
    if (!$r) {
        $db->rollback();
        go('recycles.php', 'ทำรายการไม่ได้', 'danger');
    }
    if (isset($_POST['reject'])) {
        (($statement = $db->prepare("UPDATE recycles SET status = 'rejected' WHERE id = ?")) && $statement->bind_param(str_repeat('s', count([$r['id']])), ...[$r['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
        $db->commit();
        (($statement = $db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([$r['user_id'], 'คำขอรีไซเคิลถูกปฏิเสธ', mb_substr($r['item_name'], 0, 500), 'recycle.php'])), ...[$r['user_id'], 'คำขอรีไซเคิลถูกปฏิเสธ', mb_substr($r['item_name'], 0, 500), 'recycle.php']) && $statement->execute() ? ($statement->get_result() ?: true) : false);
        go('recycles.php', 'ปฏิเสธคำขอแล้ว', 'warning');
    }
    $kg = (float) trim($_POST['weight_kg'] ?? '');
    if ($kg <= 0) {
        $db->rollback();
        go('recycles.php', 'กรุณาตรวจสอบน้ำหนัก', 'danger');
    }
    $points = round($kg * 20);
    (($statement = $db->prepare("UPDATE recycles SET status = 'done', weight_kg = ?, points = ?, co2_kg = ? WHERE id = ?")) && $statement->bind_param(str_repeat('s', count([$kg, $points, $kg * $r['co2_per_kg'], $r['id']])), ...[$kg, $points, $kg * $r['co2_per_kg'], $r['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    (($statement = $db->prepare('UPDATE users SET points = points + ? WHERE id = ?')) && $statement->bind_param(str_repeat('s', count([$points, $r['user_id']])), ...[$points, $r['user_id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    (($statement = $db->prepare("INSERT INTO point_transactions (user_id, amount, description, related_type, related_id) VALUES (?, ?, ?, 'recycle', ?)")) && $statement->bind_param(str_repeat('s', count([$r['user_id'], $points, 'แต้มจากรีไซเคิล: ' . $r['item_name'], $r['id']])), ...[$r['user_id'], $points, 'แต้มจากรีไซเคิล: ' . $r['item_name'], $r['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    $db->commit();
    (($statement = $db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([$r['user_id'], 'รีไซเคิลสำเร็จ รับ ' . $points . ' แต้ม', mb_substr($r['item_name'] . ' ลดคาร์บอนได้ ' . $kg * $r['co2_per_kg'] . ' kgCO₂e', 0, 500), 'points.php'])), ...[$r['user_id'], 'รีไซเคิลสำเร็จ รับ ' . $points . ' แต้ม', mb_substr($r['item_name'] . ' ลดคาร์บอนได้ ' . $kg * $r['co2_per_kg'] . ' kgCO₂e', 0, 500), 'points.php']) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('recycles.php', "ตรวจรับแล้ว สมาชิกได้รับ $points แต้ม");
}

$rows = (($statement = $db->prepare("SELECT r.*, c.name AS category, u.name AS member FROM recycles r JOIN categories c ON c.id = r.category_id JOIN users u ON u.id = r.user_id
    WHERE (r.item_name LIKE ? OR u.name LIKE ?) AND (? = '' OR r.status = ?) ORDER BY r.status = 'pending' DESC, r.id DESC")) && $statement->bind_param(str_repeat('s', count([('%' . trim($_GET['q'] ?? '') . '%'), ('%' . trim($_GET['q'] ?? '') . '%'), trim($_GET['status'] ?? ''), trim($_GET['status'] ?? '')])), ...[('%' . trim($_GET['q'] ?? '') . '%'), ('%' . trim($_GET['q'] ?? '') . '%'), trim($_GET['status'] ?? ''), trim($_GET['status'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_all(MYSQLI_ASSOC);
