<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if (!$u) go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');

if ($_POST) {
    if (trim($_POST['body'] ?? '') == '') go('issue.php', 'กรุณาพิมพ์ข้อความ', 'danger');
    (($statement = $db->prepare('INSERT INTO messages (user_id, sender_id, body) VALUES (?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([$u['id'], $u['id'], mb_substr(trim($_POST['body'] ?? ''), 0, 500)])), ...[$u['id'], $u['id'], mb_substr(trim($_POST['body'] ?? ''), 0, 500)]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    (($statement = $db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([null, 'ข้อความจาก ' . $u['name'], mb_substr(trim($_POST['body'] ?? ''), 0, 500), 'admin/issues.php?user=' . $u['id']])), ...[null, 'ข้อความจาก ' . $u['name'], mb_substr(trim($_POST['body'] ?? ''), 0, 500), 'admin/issues.php?user=' . $u['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('issue.php');
}
