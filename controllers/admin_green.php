<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if (isset($_POST['del_category'])) {
    if ((((($statement = $db->prepare('SELECT 1 FROM recycles WHERE category_id = ?')) && $statement->bind_param(str_repeat('s', count([trim($_POST['id'] ?? '')])), ...[trim($_POST['id'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_row() ?? [])[0] ?? null)) go('green.php', 'ลบไม่ได้: มีคำขอรีไซเคิลใช้หมวดนี้อยู่', 'danger');
    (($statement = $db->prepare('DELETE FROM categories WHERE id = ?')) && $statement->bind_param(str_repeat('s', count([trim($_POST['id'] ?? '')])), ...[trim($_POST['id'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('green.php', 'ลบหมวดแล้ว');
}
if (isset($_POST['save_category'])) {
    if (trim($_POST['name'] ?? '') == '' || (float) trim($_POST['co2_per_kg'] ?? '') <= 0) go('green.php', 'กรุณากรอกชื่อหมวด และค่ามากกว่า 0', 'danger');
    if (trim($_POST['id'] ?? '')) (($statement = $db->prepare('UPDATE categories SET name = ?, co2_per_kg = ? WHERE id = ?')) && $statement->bind_param(str_repeat('s', count([trim($_POST['name'] ?? ''), trim($_POST['co2_per_kg'] ?? ''), trim($_POST['id'] ?? '')])), ...[trim($_POST['name'] ?? ''), trim($_POST['co2_per_kg'] ?? ''), trim($_POST['id'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    else (($statement = $db->prepare('INSERT INTO categories (name, co2_per_kg) VALUES (?, ?)')) && $statement->bind_param(str_repeat('s', count([trim($_POST['name'] ?? ''), trim($_POST['co2_per_kg'] ?? '')])), ...[trim($_POST['name'] ?? ''), trim($_POST['co2_per_kg'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('green.php', 'บันทึกหมวดแล้ว');
}

$cats = ($db->query('SELECT * FROM categories ORDER BY id'))->fetch_all(MYSQLI_ASSOC);
$cats[] = ['id' => '', 'name' => '', 'co2_per_kg' => ''];
