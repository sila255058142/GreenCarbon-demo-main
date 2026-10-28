<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if (!$u) go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
$currentLevel = $u['points'] >= 600 ? '🌍 Net Zero Hero' : ($u['points'] >= 300 ? '🌳 ต้นไม้' : ($u['points'] >= 100 ? '🌱 ต้นกล้า' : '🌰 เมล็ดพันธุ์'));

if (isset($_POST['save'])) {
    if (!filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL)) go('profile.php', 'รูปแบบอีเมลไม่ถูกต้อง', 'danger');
    if ((((($statement = $db->prepare('SELECT 1 FROM users WHERE email = ? AND id <> ?')) && $statement->bind_param(str_repeat('s', count([trim($_POST['email'] ?? ''), $u['id']])), ...[trim($_POST['email'] ?? ''), $u['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_row() ?? [])[0] ?? null)) go('profile.php', 'อีเมลนี้ถูกใช้สมัครแล้ว', 'danger');
    if (!preg_match('/^0\d{9}$/', trim($_POST['phone'] ?? ''))) go('profile.php', 'เบอร์โทรต้องเป็นตัวเลข 10 หลัก ขึ้นต้นด้วย 0', 'danger');
    (($statement = $db->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?')) && $statement->bind_param(str_repeat('s', count([trim($_POST['name'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''), $u['id']])), ...[trim($_POST['name'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''), $u['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    $_SESSION['user']['name'] = trim($_POST['name'] ?? '');
    go('profile.php', 'บันทึกโปรไฟล์แล้ว');
}
if (isset($_POST['change'])) {
    $password = $db->query('SELECT password FROM users WHERE id = ' . (int) $u['id'])->fetch_assoc();
    if (!$password || !password_verify(trim($_POST['current'] ?? ''), $password['password'])) go('profile.php', 'รหัสผ่านปัจจุบันไม่ถูกต้อง', 'danger');
    if (strlen(trim($_POST['password'] ?? '')) < 8) go('profile.php', 'รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร', 'danger');
    if (trim($_POST['password'] ?? '') !== trim($_POST['password2'] ?? '')) go('profile.php', 'ยืนยันรหัสผ่านไม่ตรงกัน', 'danger');
    (($statement = $db->prepare('UPDATE users SET password = ? WHERE id = ?')) && $statement->bind_param(str_repeat('s', count([password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT), $u['id']])), ...[password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT), $u['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('profile.php', 'เปลี่ยนรหัสผ่านแล้ว');
}
