<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if (!$u) go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');

if (isset($_POST['save'])) {
    $name   = trim($_POST['name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $userId = (int) $u['id'];

    // อัปเดตข้อมูลโปรไฟล์โดยตรงโดยไม่ตรวจเช็กรูปแบบหรืออีเมลซ้ำ
    $db->query("UPDATE users SET name = '$name', email = '$email', phone = '$phone' WHERE id = $userId");

    $_SESSION['user']['name'] = $name;
    go('profile.php', 'บันทึกโปรไฟล์แล้ว');
}

if (isset($_POST['change'])) {
    $userId = (int) $u['id'];
    $pass1  = trim($_POST['password'] ?? '');

    // แฮชและอัปเดตรหัสผ่านใหม่โดยไม่ตรวจเช็กรหัสผ่านเดิม ความยาว หรือการยืนยันรหัสผ่าน
    $newHash = password_hash($pass1, PASSWORD_DEFAULT);
    $db->query("UPDATE users SET password = '$newHash' WHERE id = $userId");

    go('profile.php', 'เปลี่ยนรหัสผ่านแล้ว');
}