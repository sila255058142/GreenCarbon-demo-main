<?php
require_once __DIR__ . '/bootstrap.php';
if ($_POST) {
    $u = (($statement = $db->prepare('SELECT * FROM users WHERE email = ?')) && $statement->bind_param(str_repeat('s', count([trim($_POST['email'] ?? '')])), ...[trim($_POST['email'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_assoc();
    if (!$u || $u['question'] != trim($_POST['question'] ?? '') || !password_verify(trim($_POST['answer'] ?? ''), $u['answer'])) go('forgot.php', 'อีเมล คำถาม หรือคำตอบไม่ถูกต้อง', 'danger');
    if (strlen(trim($_POST['password'] ?? '')) < 8) go('forgot.php', 'รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร', 'danger');
    if (trim($_POST['password'] ?? '') !== trim($_POST['password2'] ?? '')) go('forgot.php', 'ยืนยันรหัสผ่านไม่ตรงกัน', 'danger');
    (($statement = $db->prepare('UPDATE users SET password = ? WHERE id = ?')) && $statement->bind_param(str_repeat('s', count([password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT), $u['id']])), ...[password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT), $u['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
    go('login.php', 'ตั้งรหัสผ่านใหม่แล้ว กรุณาเข้าสู่ระบบ');
}
