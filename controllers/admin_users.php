<?php
require_once __DIR__ . '/bootstrap.php';

$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if (isset($_POST['delete'])) {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && $id !== (int) $admin['id']) {
        $db->query("DELETE FROM users WHERE id = $id AND role = 'user'");
    }
    go('users.php', 'ลบสมาชิกแล้ว');
}

if (isset($_POST['toggle_status'])) {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && $id !== (int) $admin['id']) {
        $db->query("UPDATE users SET status = IF(status = 'active', 'suspended', 'active') WHERE id = $id AND role = 'user'");
    }
    go('users.php', 'เปลี่ยนสถานะสมาชิกแล้ว');
}

if (isset($_POST['save_user'])) {
    $id       = trim($_POST['id'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $phone    = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $status   = in_array($_POST['status'] ?? 'active', ['active', 'suspended'], true) ? $_POST['status'] : 'active';
    $points   = max(0, (int) ($_POST['points'] ?? 0));

    if ($name === '' || $email === '' || $phone === '') go('users.php', 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน', 'danger');
    if (!preg_match('/^[0-9]{9,10}$/', $phone)) go('users.php', 'เบอร์โทรต้องเป็นตัวเลข 9-10 หลักเท่านั้น', 'danger');
    if ($id === '' && $password === '') go('users.php', 'กรุณาระบุรหัสผ่านสำหรับสมาชิกใหม่', 'danger');
    if (($id === '' || $password !== '') && strlen($password) < 6) go('users.php', 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร', 'danger');

    $checkId   = $id === '' ? 0 : (int) $id;
    $duplicate = $db->query("SELECT 1 FROM users WHERE email = '$email' AND id != $checkId LIMIT 1")->fetch_row();
    if ($duplicate) go('users.php', 'อีเมลนี้มีผู้ใช้งานแล้ว', 'danger');

    $question = 'ชื่อสัตว์เลี้ยงตัวแรกของคุณ';
    $answer   = password_hash('greenloop', PASSWORD_DEFAULT);

    if ($id === '') {
        $passHash = password_hash($password, PASSWORD_DEFAULT);
        $db->query("INSERT INTO users (name, email, phone, password, question, answer, role, status, points) VALUES ('$name', '$email', '$phone', '$passHash', '$question', '$answer', 'user', '$status', $points)");
        go('users.php', 'เพิ่มสมาชิกแล้ว');
    }

    $passSql = $password !== '' ? ", password = '" . password_hash($password, PASSWORD_DEFAULT) . "'" : '';
    $db->query("UPDATE users SET name = '$name', email = '$email', phone = '$phone', points = $points, status = '$status' $passSql WHERE id = $id AND role = 'user'");
    go('users.php', 'บันทึกข้อมูลสมาชิกแล้ว');
}

$search = trim($_GET['q'] ?? '');
$rows   = $db->query("SELECT * FROM users WHERE role = 'user' AND (name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%') ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);