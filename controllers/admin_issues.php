<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');
$member = (($statement = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'")) && $statement->bind_param(str_repeat('s', count([trim($_GET['user'] ?? '')])), ...[trim($_GET['user'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_assoc();
if ($member) (($statement = $db->prepare('UPDATE messages SET is_read = 1 WHERE user_id = ?')) && $statement->bind_param(str_repeat('s', count([$member['id']])), ...[$member['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);

if (isset($_POST['clear']) && $member) {
    (($statement = $db->prepare('DELETE FROM messages WHERE user_id = ?')) && $statement->bind_param(str_repeat('s', count([$member['id']])), ...[$member['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('issues.php?user=' . $member['id'], 'ลบแชททั้งหมดของ ' . $member['name'] . ' แล้ว', 'warning');
}
if ($_POST && $member) {
    if (trim($_POST['body'] ?? '') == '') go('issues.php?user=' . $member['id'], 'กรุณาพิมพ์ข้อความ', 'danger');
    (($statement = $db->prepare('INSERT INTO messages (user_id, sender_id, body) VALUES (?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([$member['id'], $admin['id'], mb_substr(trim($_POST['body'] ?? ''), 0, 500)])), ...[$member['id'], $admin['id'], mb_substr(trim($_POST['body'] ?? ''), 0, 500)]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    (($statement = $db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([$member['id'], 'ข้อความจากผู้ดูแลระบบ', mb_substr(trim($_POST['body'] ?? ''), 0, 500), 'issue.php'])), ...[$member['id'], 'ข้อความจากผู้ดูแลระบบ', mb_substr(trim($_POST['body'] ?? ''), 0, 500), 'issue.php']) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('issues.php?user=' . $member['id']);
}

$threads = (($statement = $db->prepare("SELECT u.id, u.name, COALESCE(SUM(m.sender_id = u.id AND m.is_read = 0), 0) AS n, MAX(m.created_at) AS last
    FROM users u LEFT JOIN messages m ON m.user_id = u.id
    WHERE u.role = 'user' AND u.name LIKE ? GROUP BY u.id, u.name ORDER BY n DESC, last DESC, u.name")) && $statement->bind_param(str_repeat('s', count([('%' . trim($_GET['q'] ?? '') . '%')])), ...[('%' . trim($_GET['q'] ?? '') . '%')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_all(MYSQLI_ASSOC);
