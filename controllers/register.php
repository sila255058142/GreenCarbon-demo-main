<?php
require_once __DIR__ . '/bootstrap.php';
if ($_POST) {
    $err = '';
    if (!filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
        $err = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif ((((($statement = $db->prepare('SELECT 1 FROM users WHERE email = ?')) && $statement->bind_param(str_repeat('s', count([trim($_POST['email'] ?? '')])), ...[trim($_POST['email'] ?? '')]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_row() ?? [])[0] ?? null)) {
        $err = 'อีเมลนี้ถูกใช้สมัครแล้ว';
    } elseif (!preg_match('/^0\d{9}$/', trim($_POST['phone'] ?? ''))) {
        $err = 'เบอร์โทรต้องเป็นตัวเลข 10 หลัก ขึ้นต้นด้วย 0';
    } elseif (strlen(trim($_POST['password'] ?? '')) < 8) {
        $err = 'รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร';
    } elseif (trim($_POST['password'] ?? '') !== trim($_POST['password2'] ?? '')) {
        $err = 'ยืนยันรหัสผ่านไม่ตรงกัน';
    }

    if (!$err) {
        (($statement = $db->prepare('INSERT INTO users (name, email, phone, password, question, answer) VALUES (?, ?, ?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([trim($_POST['name'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''), password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT), trim($_POST['question'] ?? ''), password_hash(trim($_POST['answer'] ?? ''), PASSWORD_DEFAULT)])), ...[trim($_POST['name'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''), password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT), trim($_POST['question'] ?? ''), password_hash(trim($_POST['answer'] ?? ''), PASSWORD_DEFAULT)]) && $statement->execute() ? ($statement->get_result() ?: true) : false);
        session_regenerate_id(true);
        $_SESSION['uid'] = $db->insert_id;
        $_SESSION['user'] = [
            'id' => $_SESSION['uid'],
            'name' => trim($_POST['name'] ?? ''),
            'role' => 'user',
            'points' => 0,
        ];
        (($statement = $db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([null, 'สมาชิกใหม่', mb_substr(trim($_POST['name'] ?? '') . ' (' . trim($_POST['email'] ?? '') . ')', 0, 500), 'admin/users.php'])), ...[null, 'สมาชิกใหม่', mb_substr(trim($_POST['name'] ?? '') . ' (' . trim($_POST['email'] ?? '') . ')', 0, 500), 'admin/users.php']) && $statement->execute() ? ($statement->get_result() ?: true) : false);
        (($statement = $db->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)')) && $statement->bind_param(str_repeat('s', count([$_SESSION['uid'], 'ยินดีต้อนรับสู่ GreenLoop', mb_substr('เริ่มส่งขยะอิเล็กทรอนิกส์ไปรีไซเคิลและสะสมแต้มได้เลย', 0, 500), 'recycle.php'])), ...[$_SESSION['uid'], 'ยินดีต้อนรับสู่ GreenLoop', mb_substr('เริ่มส่งขยะอิเล็กทรอนิกส์ไปรีไซเคิลและสะสมแต้มได้เลย', 0, 500), 'recycle.php']) && $statement->execute() ? ($statement->get_result() ?: true) : false);
        go('recycle.php', 'สมัครสมาชิกสำเร็จ เริ่มส่งขยะอิเล็กทรอนิกส์ไปรีไซเคิลได้เลย');
    }
    $_SESSION['flash'] = [$err, 'danger'];
}
