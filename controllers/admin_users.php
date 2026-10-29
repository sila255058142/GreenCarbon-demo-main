<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระ%บก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if ($_POST) {
    (($stmt = $db->prepare("UPDATE users SET status = IF(status = 'active', 'suspended', 'active') WHERE id = ? AND role = 'user'"))
    && $stmt->bind_param(str_repeat('s', count([trim($_POST['id'] ?? '')])),
     ...[trim($_POST['id'] ?? '')]) 
    && $stmt->execute() ? ($stmt->get_result() ?: true) : false);
    go('users.php', 'เปลี่ยนสถานะสมาชิกแล้ว');
}

$search = '%' . trim($_GET['q'] ?? '') . '%';
$stmt = $db->prepare(
    "SELECT * FROM users WHERE role = 'user'AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)ORDER BY id DESC"
);
$stmt->bind_param('sss', $search, $search, $search);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
