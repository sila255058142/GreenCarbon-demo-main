<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if (isset($_POST['delete'])) {
    (($statement = $db->prepare('DELETE FROM contents WHERE id = ?')) && $statement->bind_param(str_repeat('s', count([trim($_POST['id'] ?? '')])), ...[trim($_POST['id'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('contents.php', 'ลบเนื้อหาแล้ว');
}
if ($_POST) {
    if (mb_strlen(trim($_POST['title'] ?? '')) < 3 || trim($_POST['body'] ?? '') == '' || trim($_POST['start_at'] ?? '') == '') go('contents.php', 'กรุณากรอกหัวข้อ เนื้อหา และวันเวลาเริ่มแสดง', 'danger');
    if (trim($_POST['end_at'] ?? '') != '' && trim($_POST['end_at'] ?? '') <= trim($_POST['start_at'] ?? '')) go('contents.php', 'วันเวลาสิ้นสุดต้องอยู่หลังวันเวลาเริ่มแสดง', 'danger');
    $data = [trim($_POST['title'] ?? ''), trim($_POST['body'] ?? ''), trim($_POST['start_at'] ?? ''), trim($_POST['end_at'] ?? '') ?: null];
    if (trim($_POST['id'] ?? '')) {
        (($statement = $db->prepare('UPDATE contents SET title = ?, body = ?, start_at = ?, end_at = ? WHERE id = ?')) && $statement->bind_param(str_repeat('s', count(array_merge($data, [trim($_POST['id'] ?? '')]))), ...array_merge($data, [trim($_POST['id'] ?? '')])) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    } else {
        (($statement = $db->prepare('INSERT INTO contents (title, body, start_at, end_at, user_id) VALUES (?, ?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count(array_merge($data, [$admin['id']]))), ...array_merge($data, [$admin['id']])) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    }
    go('contents.php', 'บันทึกเนื้อหาแล้ว');
}

$edit = (($statement = $db->prepare('SELECT * FROM contents WHERE id = ?')) && $statement->bind_param(str_repeat('s', count([trim($_GET['edit'] ?? '')])), ...[trim($_GET['edit'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_assoc() ?: ['id' => '', 'title' => '', 'body' => '', 'start_at' => date('Y-m-d H:i'), 'end_at' => ''];
$rows = (($statement = $db->prepare("SELECT *, IF(start_at > NOW(), 'รอเผยแพร่', IF(end_at <= NOW(), 'หมดเวลา', 'กำลังแสดง')) AS live FROM contents WHERE title LIKE ? OR body LIKE ? ORDER BY start_at DESC")) && $statement->bind_param(str_repeat('s', count([('%' . trim($_GET['q'] ?? '') . '%'), ('%' . trim($_GET['q'] ?? '') . '%')])), ...[('%' . trim($_GET['q'] ?? '') . '%'), ('%' . trim($_GET['q'] ?? '') . '%')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_all(MYSQLI_ASSOC);
$local = function ($s) { return $s ? date('Y-m-d\TH:i', strtotime($s)) : ''; };
