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

// เชื่อมต่อและเรียกใช้ฐานข้อมูล: q(), all(), one(), val()
// ใช้ใน: coupons.php, forgot.php, issue.php, notifications.php, profile.php, recycle.php, register.php และ admin/contents.php, admin/coupons.php, admin/green.php, admin/issues.php, admin/recycles.php, admin/users.php
function q($sql, $p = []) { global $db; $s = $db->prepare($sql); $s->execute($p); return $s; }
// ใช้ใน: coupons.php, index.php, notifications.php, points.php, recycle.php และ admin/contents.php, admin/coupons.php, admin/green.php, admin/index.php, admin/issues.php, admin/recycles.php, admin/reports.php, admin/users.php
function all($sql, $p = []) { return q($sql, $p)->fetchAll(); }
// ใช้ใน: coupons.php, forgot.php, index.php, login.php, points.php, recycle.php และ admin/contents.php, admin/coupons.php, admin/index.php, admin/issues.php, admin/recycles.php
function one($sql, $p = []) { return q($sql, $p)->fetch(); }
// ใช้ใน: coupons.php, index.php, recycle.php และ admin/coupons.php, admin/green.php, admin/index.php
function val($sql, $p = []) { return q($sql, $p)->fetchColumn(); }
function head($title, $show = true)
{
    $u = me();
    $menu = !$u ? [] : ($u['role'] == 'admin' ? MENU_ADMIN : MENU_USER);
?>
<!doctype html><html lang="th">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $title ?> | GreenLoop</title><link rel="stylesheet" href="<?= UP ?>assets/bootstrap.min.css"><link rel="stylesheet" href="<?= UP ?>assets/style.css">
<script src="<?= UP ?>assets/chart.umd.js"></script>
    </head>
    <body>
<nav class="navbar navbar-expand-lg navbar-dark d-print-none"><div class="container"><a class="navbar-brand" href="<?= UP ?>index.php">♻ GreenLoop</a>
<button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="menu"><div class="navbar-nav me-auto">
<?php foreach ($menu as $file => $name): ?><a class="nav-link" href="<?= UP . $file ?>"><?= $name ?></a><?php endforeach; ?>
</div><div class="navbar-nav align-items-lg-center">
<?php if (!$u): ?><a class="nav-link" href="<?= UP ?>login.php">เข้าสู่ระบบ</a><a class="nav-link" href="<?= UP ?>register.php">สมัครสมาชิก</a><?php else: ?>
<a class="nav-link" href="<?= UP ?>notifications.php">🔔 <span class="badge bg-danger"><?= val('SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND user_id <=> ?', [inbox()]) ?></span></a><span class="navbar-text text-white mx-2"><?= pic($u['avatar'], 28) ?> <?= e($u['name']) ?></span><a class="nav-link" href="<?= UP ?>logout.php">ออกจากระบบ</a>
<?php endif; ?></div></div></div></nav>
<main class="container py-4">
<?php if (isset($_SESSION['flash'])): ?><div class="alert alert-<?= $_SESSION['flash'][1] ?>"><?= e($_SESSION['flash'][0]) ?></div><?php unset($_SESSION['flash']); endif; if ($show): ?><h3 class="mb-3"><?= $title ?></h3><?php endif; ?>
<?php
}
// ใช้ใน: coupons.php, forgot.php, index.php, issue.php, login.php, notifications.php, points.php, profile.php, recycle.php, register.php, admin/contents.php, admin/coupons.php, admin/green.php, admin/index.php, admin/issues.php, admin/recycles.php, admin/reports.php, admin/users.php
function foot() { echo '</main><footer class="text-center text-muted small py-4 d-print-none">♻ GreenLoop · วัดคาร์บอน ลดด้วยการรีไซเคิล เพื่อโลกที่ยั่งยืน</footer><script src="' . UP . 'assets/bootstrap.bundle.min.js"></script></body></html>'; }

// ตัวช่วยใช้ร่วมกัน
// ใช้ใน: coupons.php, index.php, notifications.php, points.php, recycle.php, admin/contents.php, admin/coupons.php, admin/green.php, admin/index.php, admin/issues.php, admin/recycles.php, admin/reports.php, admin/users.php
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES); }
// ใช้ใน: index.php, recycle.php, admin/contents.php, admin/coupons.php, admin/issues.php, admin/recycles.php, admin/reports.php, admin/users.php
function g($k, $d = '') { return trim($_GET[$k] ?? $d); }
// ใช้ใน: coupons.php, forgot.php, issue.php, login.php, notifications.php, profile.php, recycle.php, register.php, admin/contents.php, admin/coupons.php, admin/green.php, admin/issues.php, admin/recycles.php, admin/users.php
function p($k) { return trim($_POST[$k] ?? ''); }
// ใช้ใน: register.php
function flash($msg, $type = 'danger') { $_SESSION['flash'] = [$msg, $type]; }
// ใช้ใน: coupons.php, footprint.php, forgot.php, index.php, issue.php, login.php, notifications.php, profile.php, recycle.php, register.php, admin/contents.php, admin/coupons.php, admin/green.php, admin/issues.php, admin/recycles.php, admin/users.php
function go($url, $msg = '', $type = 'success') { if ($msg) flash($msg, $type); header("Location: $url"); exit; }

// 1) index.php: ตรวจผู้ใช้ ค้นหา และแสดงข้อมูล
// ใช้ใน: index.php, admin/coupons.php
function me()
{
    static $u;
    if (!$u && isset($_SESSION['uid'])) $u = one("SELECT * FROM users WHERE id = ? AND status = 'active'", [$_SESSION['uid']]);
    return $u;
}
// ใช้ใน: index.php, notifications.php, recycle.php, admin/contents.php, admin/coupons.php, admin/issues.php, admin/recycles.php, admin/reports.php, admin/users.php
function like() { return '%' . g('q') . '%'; }
// ใช้ใน: index.php, points.php, profile.php, admin/users.php
function level($points) { foreach (LEVELS as $min => $name) if ($points >= $min) $lv = $name; return $lv; }
// ใช้ใน: coupons.php, index.php, notifications.php, recycle.php, admin/contents.php, admin/coupons.php, admin/issues.php, admin/recycles.php, admin/users.php
function dt($s) { return $s ? date('d/m/Y H:i', strtotime($s)) : '-'; }
// ใช้ใน: index.php, notifications.php, recycle.php, admin/contents.php, admin/coupons.php, admin/issues.php, admin/recycles.php, admin/reports.php, admin/users.php
function search($extra = '') { echo "<form class='d-flex flex-wrap gap-2 mb-3 d-print-none'>$extra<input class='form-control w-auto' name='q' value='" . e(g('q')) . "' placeholder='ค้นหา...'><button class='btn btn-success'>ค้นหา</button></form>"; }

// 2) login.php: สร้างช่องกรอกข้อมูล
// ใช้ใน: coupons.php, forgot.php, login.php, profile.php, recycle.php, register.php และ admin/contents.php
function input($label, $name, $type = 'text', $value = '', $attr = 'required') {
    echo "<label class='form-label mt-2'>$label</label><input class='form-control' type='$type' name='$name' value='" . e($value) . "' $attr>"; }

// 3) register.php: ตรวจข้อมูลและจัดการรูป/คำถาม
// ใช้ใน: profile.php, register.php
function check_user($id = 0)
{
    if (mb_strlen(p('name')) < 2) return 'กรุณากรอกชื่อ-นามสกุล';
    if (!filter_var(p('email'), FILTER_VALIDATE_EMAIL)) return 'รูปแบบอีเมลไม่ถูกต้อง';
    if (val('SELECT 1 FROM users WHERE email = ? AND id <> ?', [p('email'), $id])) return 'อีเมลนี้ถูกใช้สมัครแล้ว';
    if (!preg_match('/^0\d{9}$/', p('phone'))) return 'เบอร์โทรต้องเป็นตัวเลข 10 หลัก ขึ้นต้นด้วย 0';
    return '';
}


// ใช้ใน: forgot.php, profile.php, register.php
function check_pass() { if (strlen(p('password')) < 8) return 'รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร'; if (p('password') != p('password2')) return 'ยืนยันรหัสผ่านไม่ตรงกัน'; return ''; }
// ใช้ใน: profile.php, recycle.php, register.php
function upload($field)
{
    $f = $_FILES[$field] ?? null;
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'];
    if (!$f || $f['error'] || $f['size'] > 2 * 1024 * 1024) return '';
    $info = getimagesize($f['tmp_name']);
    if (!$info || !isset($types[$info[2]])) return '';
    $name = uniqid() . '.' . $types[$info[2]];
    move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $name);
    return $name;
}
// ใช้ใน: profile.php, recycle.php, register.php
function bad_upload($field) { return !empty($_FILES[$field]['name']) ? 'รูปต้องเป็นไฟล์ JPG PNG หรือ GIF ขนาดไม่เกิน 2MB' : ''; }
// ใช้ใน: forgot.php, register.php
function question($selected = '') {
    echo "<label class='form-label mt-2'>คำถามกันลืมรหัสผ่าน</label><select class='form-select' name='question' required><option value=''>เลือกคำถาม</option>"; foreach (QUESTIONS as $t) echo '<option' . ($t == $selected ? ' selected' : '') . ">$t</option>"; echo '</select>'; }
// ใช้ใน: coupons.php, issue.php, recycle.php, register.php และ admin/coupons.php, admin/issues.php, admin/recycles.php
function notify($to, $title, $msg, $link = '') { q('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)', [$to, $title, mb_substr($msg, 0, 500), $link]); }

// ฟังก์ชันที่ใช้ในหน้าอื่นของระบบ
// ใช้ใน: coupons.php, admin/coupons.php, admin/index.php
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
// ใช้ใน: notifications.php (และใน head() เพื่อแสดงจำนวนแจ้งเตือน)
function inbox() { return me()['role'] == 'admin' ? null : me()['id']; }
// ใช้ใน: points.php, profile.php, recycle.php, admin/issues.php, admin/recycles.php, admin/users.php
function pic($file, $size = 40, $icon = '👤', $link = false)
{
    if (!$file) return "<span class='pic' style='width:{$size}px;height:{$size}px;font-size:" . $size * .6 . "px'>$icon</span>";
    $img = "<img class='pic' src='" . UP . "uploads/$file' width='$size' height='$size' alt=''>";
    return $link ? "<a href='" . UP . "uploads/$file' target='_blank'>$img</a>" : $img;
}
// ใช้ใน: coupons.php, issue.php, notifications.php, points.php, profile.php, recycle.php
function need_login() { if (!me()) go(UP . 'login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning'); return me(); }
// ใช้ใน: admin/contents.php, admin/coupons.php, admin/green.php, admin/index.php, admin/issues.php, admin/recycles.php, admin/reports.php, admin/users.php
function need_admin() { if (need_login()['role'] != 'admin') go(UP . 'index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger'); return me(); }
// ใช้ใน: points.php, admin/index.php, admin/reports.php
function box($label, $value) { echo "<div class='col-6 col-lg-3'><div class='card card-body box text-center mb-3'><small class='text-muted'>$label</small><h4 class='text-success mb-0'>$value</h4></div></div>"; }
// ใช้ใน: admin/index.php, admin/reports.php
function chart($title, $type, $labels, $data)
{
    static $n = 0; $id = 'c' . ++$n;
    echo "<div class='card card-body mb-3'><h6>$title</h6><canvas id='$id'></canvas></div><script>new Chart(document.getElementById('$id'), {type: '$type', data: {labels: " . json_encode($labels) . ", datasets: [{label: '$title', data: " . json_encode($data, JSON_NUMERIC_CHECK) . "}]}});</script>";
}

// ใช้ใน: coupons.php, forgot.php, index.php, issue.php, login.php, notifications.php, points.php, profile.php, recycle.php, register.php, admin/contents.php, admin/coupons.php, admin/green.php, admin/index.php, admin/issues.php, admin/recycles.php, admin/reports.php, admin/users.php
