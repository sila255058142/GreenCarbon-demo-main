<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if (isset($_POST['delete'])) {
    $id = trim($_POST['id'] ?? '');
    $stmt = $db->prepare('DELETE FROM contents WHERE id = ?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    go('contents.php', 'ลบเนื้อหาแล้ว');
}
if ($_POST) {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $startAt = trim($_POST['start_at'] ?? '');
    $endAt = trim($_POST['end_at'] ?? '');
    $id = trim($_POST['id'] ?? '');

    // if (mb_strlen($title) < 3 || $body === '' || $startAt === '') go('contents.php', 'กรุณากรอกหัวข้อ เนื้อหา และวันเวลาเริ่มแสดง', 'danger');
    // if ($endAt !== '' && $endAt <= $startAt) go('contents.php', 'วันเวลาสิ้นสุดต้องอยู่หลังวันเวลาเริ่มแสดง', 'danger');

    $endAt = $endAt ?: null;
    $sql = $id !== ''
        ? 'UPDATE contents SET title = ?, body = ?, start_at = ?, end_at = ? WHERE id = ?'
        : 'INSERT INTO contents (title, body, start_at, end_at, user_id) VALUES (?, ?, ?, ?, ?)';
    $stmt = $db->prepare($sql);
    $stmt->bind_param('sssss', $title, $body, $startAt, $endAt, $recordId);
    $stmt->execute();
    go('contents.php', 'บันทึกเนื้อหาแล้ว');
}

$editId = trim($_GET['edit'] ?? '');
$stmt = $db->prepare('SELECT * FROM contents WHERE id = ?');
$stmt->bind_param('s', $editId);
$stmt->execute();
$edit = $stmt->get_result()->fetch_assoc() ?: [
    'id' => '',
    'title' => '',
    'body' => '',
    'start_at' => date('Y-m-d H:i'),
    'end_at' => ''
];

$search = '%' . trim($_GET['q'] ?? '') . '%';
$stmt = $db->prepare("SELECT *, IF(start_at > NOW(), 'รอเผยแพร่', IF(end_at <= NOW(), 'หมดเวลา', 'กำลังแสดง')) AS live FROM contents WHERE title LIKE ? OR body LIKE ? ORDER BY start_at DESC");
$stmt->bind_param('ss', $search, $search);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$local = function ($s) { return $s ? date('Y-m-d\TH:i', strtotime($s)) : ''; };
