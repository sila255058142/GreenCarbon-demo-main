<?php
session_start();
date_default_timezone_set('Asia/Bangkok');
$db = new PDO('mysql:host=localhost;dbname=greenloop;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
define('UP', strpos($_SERVER['SCRIPT_NAME'], '/admin/') === false ? '' : '../');
const POINT_PER_KG = 20;
const TREE_KG = 9.5;
const LEVELS = [0 => '🌰 เมล็ดพันธุ์',
100 => '🌱 ต้นกล้า',
300 => '🌳 ต้นไม้',
600 => '🌍 Net Zero Hero'];
const STATUS = ['active' => 'ใช้งานได้',
'suspended' => 'ระงับสิทธิ์',
'pending' => 'รอตรวจรับ',
'done' => 'สำเร็จ',
'rejected' => 'ปฏิเสธ'];
const QUESTIONS = ['ชื่อสัตว์เลี้ยงตัวแรกของคุณ',
'ชื่อโรงเรียนประถมของคุณ',
'อาหารจานโปรดของคุณ',
'จังหวัดที่คุณเกิด'];
const LIVE = "IF(start_at > NOW(), 'รอเผยแพร่', IF(end_at <= NOW(), 'หมดเวลา', 'กำลังแสดง'))";
const MENU_USER = ['recycle.php' => '♻️ รีไซเคิล',
'points.php' => '🏆 แต้มของฉัน',
'coupons.php' => '🎟️ แลกคูปอง',
'profile.php' => '👤 โปรไฟล์'];

const MENU_ADMIN = ['admin/index.php' => 'Dashboard',
'admin/users.php' => 'สมาชิก',
'admin/contents.php' => 'เนื้อหา',
'admin/green.php' => 'ตั้งค่า Green',
'admin/recycles.php' => 'รีไซเคิล',
'admin/coupons.php' => 'คูปอง',
'admin/reports.php' => 'รายงาน'];

function q($sql, $p = []) { global $db; $s = $db->prepare($sql); $s->execute($p); return $s; }
function all($sql, $p = []) { return q($sql, $p)->fetchAll(); }
function one($sql, $p = []) { return q($sql, $p)->fetch(); }
function val($sql, $p = []) { return q($sql, $p)->fetchColumn(); }
function p($k) { return trim($_POST[$k] ?? ''); }
function g($k, $d = '') { return trim($_GET[$k] ?? $d); }
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES); }
function flash($msg, $type = 'danger') { $_SESSION['flash'] = [$msg, $type]; }
function go($url, $msg = '', $type = 'success') { if ($msg) flash($msg, $type); header("Location: $url"); exit; }
function me()
{
    static $u;
    if (!$u && isset($_SESSION['uid'])) $u = one("SELECT * FROM users WHERE id = ? AND status = 'active'", [$_SESSION['uid']]);
    return $u;
}
function like() { return '%' . g('q') . '%'; }
function level($points) { foreach (LEVELS as $min => $name) if ($points >= $min) $lv = $name; return $lv; }
function dt($s) { return $s ? date('d/m/Y H:i', strtotime($s)) : '-'; }
function search($extra = '') { echo "<form class='d-flex flex-wrap gap-2 mb-3 d-print-none'>$extra<input class='form-control w-auto' name='q' value='" . e(g('q')) . "' placeholder='ค้นหา...'><button class='btn btn-success'>ค้นหา</button></form>"; }

function need_login() { if (!me()) go(UP . 'login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning'); return me(); }
function input($label, $name, $type = 'text', $value = '', $attr = 'required') {
    echo "<label class='form-label mt-2'>$label</label><input class='form-control' type='$type' name='$name' value='" . e($value) . "' $attr>"; }

function question($selected = '') {
    echo "<label class='form-label mt-2'>คำถามกันลืมรหัสผ่าน</label><select class='form-select' name='question' required><option value=''>เลือกคำถาม</option>"; foreach (QUESTIONS as $t) echo '<option' . ($t == $selected ? ' selected' : '') . ">$t</option>"; echo '</select>'; }
function check_user($id = 0)
{
    if (mb_strlen(p('name')) < 2) return 'กรุณากรอกชื่อ-นามสกุล';
    if (!filter_var(p('email'), FILTER_VALIDATE_EMAIL)) return 'รูปแบบอีเมลไม่ถูกต้อง';
    if (val('SELECT 1 FROM users WHERE email = ? AND id <> ?', [p('email'), $id])) return 'อีเมลนี้ถูกใช้สมัครแล้ว';
    if (!preg_match('/^0\d{9}$/', p('phone'))) return 'เบอร์โทรต้องเป็นตัวเลข 10 หลัก ขึ้นต้นด้วย 0';
    return '';
}
function check_pass() { if (strlen(p('password')) < 8) return 'รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร'; if (p('password') != p('password2')) return 'ยืนยันรหัสผ่านไม่ตรงกัน'; return ''; }
function notify($to, $title, $msg, $link = '') { q('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)', [$to, $title, mb_substr($msg, 0, 500), $link]); }
function expire_coupons($userId = null)
{
    global $db;
    $db->beginTransaction();
    $sql = "SELECT id, user_id, reward_id, stock_reserved FROM coupon_redemptions WHERE status = 'approved' AND expires_at IS NOT NULL AND expires_at <= NOW()";
    $expired = all($sql . ($userId === null ? '' : ' AND user_id = ?') . ' FOR UPDATE', $userId === null ? [] : [$userId]);
    foreach ($expired as $c) {
        q("UPDATE coupon_redemptions SET status = 'expired' WHERE id = ? AND status = 'approved'", [$c['id']]);
        q('UPDATE rewards SET stock = IF(stock IS NULL OR ? = 0, stock, stock + 1) WHERE id = ?', [$c['stock_reserved'], $c['reward_id']]);
        q('UPDATE coupon_redemptions SET stock_reserved = 0 WHERE id = ?', [$c['id']]);
        notify($c['user_id'], 'คูปองหมดอายุ', 'คูปองของคุณหมดอายุแล้วและคืนสิทธิ์ของรางวัลเข้าสต็อก', 'coupons.php');
    }
    $db->commit();
}
function inbox() { return me()['role'] == 'admin' ? null : me()['id']; }
function need_admin() { if (need_login()['role'] != 'admin') go(UP . 'index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger'); return me(); }
function box($label, $value) { echo "<div class='col-6 col-lg-3'><div class='card card-body box text-center mb-3'><small class='text-muted'>$label</small><h4 class='text-success mb-0'>$value</h4></div></div>"; }
function chart($title, $type, $labels, $data)
{
    static $n = 0; $id = 'c' . ++$n;
    echo "<div class='card card-body mb-3'><h6>$title</h6><canvas id='$id'></canvas></div><script>new Chart(document.getElementById('$id'), {type: '$type', data: {labels: " . json_encode($labels) . ", datasets: [{label: '$title', data: " . json_encode($data, JSON_NUMERIC_CHECK) . "}]}});</script>";
}

require_once __DIR__ . '/header.php';
