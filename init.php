<?php
session_start();
// เชื่อมต่อด้วย mysqli และตั้งค่า Charset
$db = new mysqli('localhost', 'root', '', 'green_loop');


define('UP', str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? '../' : '');

const POINT_PER_KG = 20, TREE_KG = 9.5;
const LEVELS = [0 => '🌰 เมล็ดพันธุ์', 100 => '🌱 ต้นกล้า', 300 => '🌳 ต้นไม้', 600 => '🌍 Net Zero Hero'];
const STATUS = ['active' => 'ใช้งานได้', 'suspended' => 'ระงับสิทธิ์', 'pending' => 'รอตรวจรับ', 'done' => 'สำเร็จ', 'rejected' => 'ปฏิเสธ'];
const QUESTIONS = ['ชื่อสัตว์เลี้ยงตัวแรกของคุณ', 'ชื่อโรงเรียนประถมของคุณ', 'อาหารจานโปรดของคุณ', 'จังหวัดที่คุณเกิด'];
const LIVE = "IF(start_at > NOW(), 'รอเผยแพร่', IF(end_at <= NOW(), 'หมดเวลา', 'กำลังแสดง'))";

const MENU_USER = ['recycle.php' => '♻️ รีไซเคิล', 'points.php' => '🏆 แต้มของฉัน', 'coupons.php' => '🎟️ แลกคูปอง', 'profile.php' => '👤 โปรไฟล์'];
const MENU_ADMIN = ['admin/index.php' => 'Dashboard', 'admin/users.php' => 'สมาชิก', 'admin/contents.php' => 'เนื้อหา', 'admin/green.php' => 'ตั้งค่า Green', 'admin/recycles.php' => 'รีไซเคิล', 'admin/coupons.php' => 'คูปอง', 'admin/reports.php' => 'รายงาน'];

// Core Query Helper (ใช้ query($db, $sql, $p) เป็นศูนย์กลาง)
function query($db, $sql, $p = []) {
    if (!$p) return $db->query($sql);
    ($s = $db->prepare($sql))->bind_param(str_repeat('s', count($p)), ...$p);
    $s->execute();
    return $s->get_result() ?: true;
}

function q($sql, $p = []) { global $db; return query($db, $sql, $p); }
function all($sql, $p = []) { return q($sql, $p)->fetch_all(MYSQLI_ASSOC); }
function one($sql, $p = []) { return q($sql, $p)->fetch_assoc(); }
function val($sql, $p = []) { return (q($sql, $p)->fetch_row() ?? [])[0] ?? null; }

// Utility Helpers
function p($k) { return trim($_POST[$k] ?? ''); }
function g($k, $d = '') { return trim($_GET[$k] ?? $d); }
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES); }
function flash($msg, $type = 'danger') { $_SESSION['flash'] = [$msg, $type]; }
function go($url, $msg = '', $type = 'success') { if ($msg) flash($msg, $type); header("Location: $url"); exit; }
function like() { return '%' . g('q') . '%'; }
function dt($s) { return $s ? date('d/m/Y H:i', strtotime($s)) : '-'; }

function me() {
    static $u;
    return $u ??= (!empty($_SESSION['uid']) ? one("SELECT * FROM users WHERE id = ? AND status = 'active'", [$_SESSION['uid']]) : null);
}

function check_user($id = 0) {
    if (!filter_var(p('email'), FILTER_VALIDATE_EMAIL)) return 'รูปแบบอีเมลไม่ถูกต้อง';
    if (val('SELECT 1 FROM users WHERE email = ? AND id <> ?', [p('email'), $id])) return 'อีเมลนี้ถูกใช้สมัครแล้ว';
    return preg_match('/^0\d{9}$/', p('phone')) ? '' : 'เบอร์โทรต้องเป็นตัวเลข 10 หลัก ขึ้นต้นด้วย 0';
}
function check_pass() {
    return strlen(p('password')) < 8 ? 'รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร' : (p('password') !== p('password2') ? 'ยืนยันรหัสผ่านไม่ตรงกัน' : '');
}
function input($type = 'text') { echo "<input class='form-control' type='" . e($type) . "'>"; }
function level($points) {
    $lv = '';
    foreach (LEVELS as $min => $name) if ($points >= $min) $lv = $name;
    return $lv;
}

function question($selected = '') {
    echo "<label class='form-label mt-2'>คำถามกันลืมรหัสผ่าน</label><select class='form-select' name='question' required><option value=''>เลือกคำถาม</option>";
    foreach (QUESTIONS as $t) echo "<option" . ($t == $selected ? ' selected' : '') . ">$t</option>";
    echo '</select>';
}
function need_login() { return me() ?: go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning'); }
function need_admin() { return (need_login()['role'] === 'admin') ? me() : go(UP . 'index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger'); }
function inbox() { return me()['role'] === 'admin' ? null : me()['id']; }

// UI Components
function search($extra = '') { echo "<form class='d-flex flex-wrap gap-2 mb-3 d-print-none'>$extra<input class='form-control w-auto' name='q' value='" . e(g('q')) . "' placeholder='ค้นหา...'><button class='btn btn-success'>ค้นหา</button></form>"; }
function box($label, $value) { echo "<div class='col-6 col-lg-3'><div class='card card-body box text-center mb-3'><small class='text-muted'>$label</small><h4 class='text-success mb-0'>$value</h4></div></div>"; }
function chart($title, $type, $labels, $data) {
    static $n = 0; $id = 'c' . ++$n;
    $cfg = json_encode(['type' => $type, 'data' => ['labels' => $labels, 'datasets' => [['label' => $title, 'data' => $data]]]]);
    echo "<div class='card card-body mb-3'><h6>" . e($title) . "</h6><canvas id='$id'></canvas></div><script>new Chart(document.getElementById('$id'),$cfg);</script>";
}

// Validation & Upload
function upload($field) {
    $file = $_FILES[$field] ?? null;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid() . ($ext ? '.' . strtolower($ext) : '');
    return move_uploaded_file($file['tmp_name'], __DIR__ . "/uploads/$name") ? $name : '';
}
function bad_upload($field) { 
    return !empty($_FILES[$field]['name']) ? 'รูปต้องเป็นไฟล์ JPG PNG หรือ GIF เท่านั้น' : ''; 
}

// Business Logic Functions
function notify($to, $title, $msg, $link = '') {
    q('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)', [$to, $title, mb_substr($msg, 0, 500), $link]);
}
function expire_coupons($userId = null) {
    global $db;
    $db->begin_transaction();
    $sql = "SELECT id, user_id, reward_id, stock_reserved FROM coupon_redemptions WHERE status = 'approved' AND expires_at IS NOT NULL AND expires_at <= NOW()";
    $params = [];
    if ($userId !== null) { $sql .= ' AND user_id = ?'; $params[] = $userId; }

    foreach (all($sql . ' FOR UPDATE', $params) as $c) {
        q("UPDATE coupon_redemptions SET status = 'expired', stock_reserved = 0 WHERE id = ? AND status = 'approved'", [$c['id']]);
        q('UPDATE rewards SET stock = IF(stock IS NULL OR ? = 0, stock, stock + 1) WHERE id = ?', [$c['stock_reserved'], $c['reward_id']]);
        notify($c['user_id'], 'คูปองหมดอายุ', 'คูปองของคุณหมดอายุแล้วและคืนสิทธิ์ของรางวัลเข้าสต็อก', 'coupons.php');
    }
    $db->commit();
}

require_once __DIR__ . '/header.php';