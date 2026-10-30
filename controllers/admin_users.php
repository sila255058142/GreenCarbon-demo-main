<?php
require_once __DIR__ . '/bootstrap.php';

function bind_values($stmt, $types, array $values) {
    $refs = [];
    foreach ($values as $i => $value) {
        $refs[$i] = &$values[$i];
    }
    $stmt->bind_param($types, ...$refs);
}

$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

if (isset($_POST['delete'])) {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && $id !== (int) $admin['id']) {
        $stmt = $db->prepare('DELETE FROM users WHERE id = ? AND role = "user"');
        bind_values($stmt, 'i', [$id]);
        $stmt->execute();
    }
    go('users.php', 'ลบสมาชิกแล้ว');
}

if (isset($_POST['toggle_status'])) {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && $id !== (int) $admin['id']) {
        $stmt = $db->prepare("UPDATE users SET status = IF(status = 'active', 'suspended', 'active') WHERE id = ? AND role = 'user'");
        bind_values($stmt, 'i', [$id]);
        $stmt->execute();
    }
    go('users.php', 'เปลี่ยนสถานะสมาชิกแล้ว');
}

if (isset($_POST['save_user'])) {
    $id = trim($_POST['id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $status = in_array($_POST['status'] ?? 'active', ['active', 'suspended'], true) ? $_POST['status'] : 'active';
    $points = max(0, (int) ($_POST['points'] ?? 0));

    if ($name === '' || $email === '' || $phone === '') go('users.php', 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน', 'danger');
    if (!preg_match('/^[0-9]{9,10}$/', $phone)) go('users.php', 'เบอร์โทรต้องเป็นตัวเลข 9-10 หลักเท่านั้น', 'danger');
    if ($id === '' && $password === '') go('users.php', 'กรุณาระบุรหัสผ่านสำหรับสมาชิกใหม่', 'danger');
    if (($id === '' || $password !== '') && strlen($password) < 6) go('users.php', 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร', 'danger');

    $duplicate = $db->prepare('SELECT 1 FROM users WHERE email = ? AND id != ? LIMIT 1');
    bind_values($duplicate, 'si', [$email, $id === '' ? 0 : (int) $id]);
    $duplicate->execute();
    if ($duplicate->get_result()->fetch_row()) go('users.php', 'อีเมลนี้มีผู้ใช้งานแล้ว', 'danger');

    $question = 'ชื่อสัตว์เลี้ยงตัวแรกของคุณ';
    $answer = password_hash('greenloop', PASSWORD_DEFAULT);

    if ($id === '') {
        $stmt = $db->prepare('INSERT INTO users (name, email, phone, password, question, answer, role, status, points) VALUES (?, ?, ?, ?, ?, ?, "user", ?, ?)');
        bind_values($stmt, 'sssssssi', [$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $question, $answer, $status, $points]);
        $stmt->execute();
        go('users.php', 'เพิ่มสมาชิกแล้ว');
    }

    $set = ['name = ?', 'email = ?', 'phone = ?', 'points = ?', 'status = ?'];
    $vals = [$name, $email, $phone, $points, $status];
    $types = 'sssis';
    if ($password !== '') {
        $set[] = 'password = ?';
        $vals[] = password_hash($password, PASSWORD_DEFAULT);
        $types .= 's';
    }
    $vals[] = (int) $id;
    $types .= 'i';

    $stmt = $db->prepare('UPDATE users SET ' . implode(', ', $set) . ' WHERE id = ? AND role = "user"');
    bind_values($stmt, $types, $vals);
    $stmt->execute();
    go('users.php', 'บันทึกข้อมูลสมาชิกแล้ว');
}

$search = '%' . trim($_GET['q'] ?? '') . '%';
$stmt = $db->prepare("SELECT * FROM users WHERE role = 'user' AND (name LIKE ? OR email LIKE ? OR phone LIKE ?) ORDER BY id DESC");
bind_values($stmt, 'sss', [$search, $search, $search]);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
