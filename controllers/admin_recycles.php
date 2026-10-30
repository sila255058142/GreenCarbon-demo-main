<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if ($_POST) {
    $recycleId = (int) ($_POST['id'] ?? 0);
    $stmt = $db->prepare("SELECT r.*, c.co2_per_kg FROM recycles r JOIN categories c ON c.id = r.category_id WHERE r.id = ? AND r.status = 'pending'");
    $stmt->bind_param('i', $recycleId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if (!$r) {
        go('recycles.php', 'ทำรายการไม่ได้', 'danger');
    }
    if (isset($_POST['reject'])) {
        $stmt = $db->prepare("UPDATE recycles SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param('i', $recycleId);
        $stmt->execute();
        $stmt = $db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)');
        $title = 'คำขอรีไซเคิลถูกปฏิเสธ'; $message = mb_substr($r['item_name'], 0, 500); $link = 'recycle.php';
        $stmt->bind_param('isss', $r['user_id'], $title, $message, $link);
        $stmt->execute();
        go('recycles.php', 'ปฏิเสธคำขอแล้ว', 'warning');
    }
    $kg = (float) trim($_POST['weight_kg'] ?? '');
    if ($kg <= 0) {
        go('recycles.php', 'กรุณาตรวจสอบน้ำหนัก', 'danger');
    }
    $points = round($kg * 20);
    $co2 = $kg * $r['co2_per_kg'];
    $stmt = $db->prepare("UPDATE recycles SET status = 'done', weight_kg = ?, points = ?, co2_kg = ? WHERE id = ?");
    $stmt->bind_param('didi', $kg, $points, $co2, $recycleId);
    $stmt->execute();
    $stmt = $db->prepare('UPDATE users SET points = points + ? WHERE id = ?');
    $stmt->bind_param('ii', $points, $r['user_id']);
    $stmt->execute();
    $description = 'แต้มจากรีไซเคิล: ' . $r['item_name'];
    $stmt = $db->prepare("INSERT INTO point_transactions (user_id, amount, description, related_type, related_id) VALUES (?, ?, ?, 'recycle', ?)");
    $stmt->bind_param('iisi', $r['user_id'], $points, $description, $recycleId);
    $stmt->execute();
    $title = "รีไซเคิลสำเร็จ รับ $points แต้ม"; $message = mb_substr($r['item_name'] . ' ลดคาร์บอนได้ ' . $co2 . ' kgCO₂e', 0, 500); $link = 'points.php';
    $stmt = $db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $r['user_id'], $title, $message, $link);
    $stmt->execute();
    go('recycles.php', "ตรวจรับแล้ว สมาชิกได้รับ $points แต้ม");
}

$search = '%' . trim($_GET['q'] ?? '') . '%';
$status = trim($_GET['status'] ?? '');
$stmt = $db->prepare("SELECT r.*, c.name AS category, u.name AS member FROM recycles r JOIN categories c ON c.id = r.category_id JOIN users u ON u.id = r.user_id
    WHERE (r.item_name LIKE ? OR u.name LIKE ?) AND (? = '' OR r.status = ?) ORDER BY r.status = 'pending' DESC, r.id DESC");
$stmt->bind_param('ssss', $search, $search, $status, $status);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
