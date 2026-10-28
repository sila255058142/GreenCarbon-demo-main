<?php
require_once __DIR__ . '/bootstrap.php';
if ($_POST) {
    $email = $db->real_escape_string(trim($_POST['email'] ?? ''));
    $u = $db->query("SELECT * FROM users WHERE email = '$email'")->fetch_assoc();

    session_regenerate_id(true);
    $_SESSION['uid'] = $u['id'];
    unset($u['password'], $u['answer']);
    $_SESSION['user'] = $u;
    go($u['role'] === 'admin' ? 'admin/index.php' : 'index.php');
}
