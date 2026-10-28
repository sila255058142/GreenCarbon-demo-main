<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if (!$u) go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
$inboxUserId = $u['role'] === 'admin' ? null : $u['id'];

if ($_POST) {
    (($statement = $db->prepare('DELETE FROM notifications WHERE id = ? AND user_id <=> ?')) && $statement->bind_param('ss', trim($_POST['id'] ?? ''), $inboxUserId) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('notifications.php', 'ลบการแจ้งเตือนแล้ว');
}

$search = '%' . trim($_GET['q'] ?? '') . '%';
$rows = (($statement = $db->prepare('SELECT * FROM notifications WHERE user_id <=> ? AND (title LIKE ? OR message LIKE ?) ORDER BY id DESC')) && $statement->bind_param('sss', $inboxUserId, $search, $search) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_all(MYSQLI_ASSOC);
(($statement = $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id <=> ?')) && $statement->bind_param('s', $inboxUserId) && $statement->execute() ? ($statement->get_result() ?: true) : false);
