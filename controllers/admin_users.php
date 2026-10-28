<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if ($_POST) {
    (($statement = $db->prepare("UPDATE users SET status = IF(status = 'active', 'suspended', 'active') WHERE id = ? AND role = 'user'")) && $statement->bind_param(str_repeat('s', count([trim($_POST['id'] ?? '')])), ...[trim($_POST['id'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('users.php', 'เปลี่ยนสถานะสมาชิกแล้ว');
}

$rows = (($statement = $db->prepare("SELECT * FROM users WHERE role = 'user' AND (name LIKE ? OR email LIKE ? OR phone LIKE ?) ORDER BY id DESC")) && $statement->bind_param(str_repeat('s', count([('%' . trim($_GET['q'] ?? '') . '%'), ('%' . trim($_GET['q'] ?? '') . '%'), ('%' . trim($_GET['q'] ?? '') . '%')])), ...[('%' . trim($_GET['q'] ?? '') . '%'), ('%' . trim($_GET['q'] ?? '') . '%'), ('%' . trim($_GET['q'] ?? '') . '%')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_all(MYSQLI_ASSOC);
