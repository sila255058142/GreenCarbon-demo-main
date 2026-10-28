<?php
if (!defined('APP_BOOTSTRAPPED')) define('APP_BOOTSTRAPPED', true);
session_start();
require_once __DIR__ . '/conn/db.php';

// Utility Helpers
function go($url, $msg = '', $type = 'success') {
    if ($msg) $_SESSION['flash'] = [$msg, $type];
    header("Location: $url");
    exit;
}

function me() {
    return !empty($_SESSION['uid']) ? ($_SESSION['user'] ?? null) : null;
}

function upload($field) {
    $file = $_FILES[$field] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return '';
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024 || !in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) return false;
    $name = uniqid() . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], __DIR__ . "/uploads/$name") ? $name : false;
}

require_once __DIR__ . '/controllers/layout.php';
require_once __DIR__ . '/header.php';