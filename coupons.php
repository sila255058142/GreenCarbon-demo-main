<?php
// แลกแต้มเป็นคูปองของรางวัล
require 'init.php';
$u = need_login();
expire_coupons($u['id']);

if (isset($_POST['redeem'])) {
    $rewardId = (int) p('reward_id');
    $db->beginTransaction();
    $lockedUser = one('SELECT id, points FROM users WHERE id = ? FOR UPDATE', [$u['id']]);
    if (!$lockedUser) {
        $db->rollBack();
        go('login.php', 'ไม่พบบัญชีผู้ใช้ กรุณาเข้าสู่ระบบอีกครั้ง', 'warning');
    }
    $reward = one('SELECT id, title, points_cost, stock FROM rewards WHERE id = ? AND is_active = 1 FOR UPDATE', [$rewardId]);
    if (!$reward || ($reward['stock'] !== null && (int) $reward['stock'] < 1)) {
        $db->rollBack();
        go('coupons.php', 'ไม่พบของรางวัลนี้ ปิดรับแลกแล้ว หรือของรางวัลหมด', 'danger');
    }
    if ((int) $lockedUser['points'] < (int) $reward['points_cost']) {
        $db->rollBack();
        go('coupons.php', 'แต้มของคุณไม่เพียงพอสำหรับแลกรางวัลนี้', 'danger');
    }
    q('UPDATE users SET points = points - ? WHERE id = ?', [$reward['points_cost'], $u['id']]);
    if ($reward['stock'] !== null) q('UPDATE rewards SET stock = stock - 1 WHERE id = ? AND stock > 0', [$reward['id']]);
    q("INSERT INTO coupon_redemptions (user_id, reward_id, points_spent, stock_reserved) VALUES (?, ?, ?, ?)",
        [$u['id'], $reward['id'], $reward['points_cost'], $reward['stock'] === null ? 0 : 1]);
    $redemptionId = $db->lastInsertId();
    q("INSERT INTO point_transactions (user_id, amount, description, related_type, related_id) VALUES (?, ?, ?, 'coupon', ?)",
        [$u['id'], -$reward['points_cost'], 'แลกของรางวัล: ' . $reward['title'], $redemptionId]);
    $db->commit();
    notify(null, 'คำขอแลกคูปองใหม่', $u['name'] . ' ขอแลก ' . $reward['title'], 'admin/coupons.php');
    go('coupons.php', 'ส่งคำขอแลกแล้ว แต้มถูกหักไว้ระหว่างรออนุมัติ');
}
if (isset($_POST['claim'])) {
    $code = strtoupper(p('coupon_code'));
    $recipient = p('recipient_name');
    $phone = p('recipient_phone');
    $address = p('shipping_address');
    $digits = preg_replace('/\D/', '', $phone);
    if (mb_strlen($recipient) < 2 || strlen($digits) < 8 || strlen($digits) > 15 || mb_strlen($address) < 10) {
        go('coupons.php', 'กรุณากรอกชื่อผู้รับ เบอร์โทร และที่อยู่จัดส่งให้ครบถ้วน', 'danger');
    }

    $db->beginTransaction();
    $coupon = one("SELECT id, reward_id, status, expires_at, stock_reserved FROM coupon_redemptions
        WHERE coupon_code = ? AND user_id = ? FOR UPDATE", [$code, $u['id']]);
    if (!$coupon || $coupon['status'] != 'approved') {
        $db->rollBack();
        go('coupons.php', 'ไม่พบคูปองที่ยังใช้ได้ในบัญชีของคุณ', 'danger');
    }
    if ($coupon['expires_at'] && strtotime($coupon['expires_at']) <= time()) {
        q("UPDATE coupon_redemptions SET status = 'expired' WHERE id = ? AND status = 'approved'", [$coupon['id']]);
        q('UPDATE rewards SET stock = IF(stock IS NULL OR ? = 0, stock, stock + 1) WHERE id = ?', [$coupon['stock_reserved'], $coupon['reward_id']]);
        q('UPDATE coupon_redemptions SET stock_reserved = 0 WHERE id = ?', [$coupon['id']]);
        $db->commit();
        notify($u['id'], 'คูปองหมดอายุ', 'คูปอง ' . $code . ' หมดอายุแล้ว', 'coupons.php');
        go('coupons.php', 'คูปองนี้หมดอายุแล้ว', 'danger');
    }
    q("UPDATE coupon_redemptions SET status = 'used', used_at = NOW(), stock_reserved = 0, recipient_name = ?, recipient_phone = ?, shipping_address = ?
        WHERE id = ? AND status = 'approved'", [$recipient, $phone, $address, $coupon['id']]);
    $db->commit();
    notify(null, 'ใช้คูปองรับของ', $u['name'] . ' ใช้คูปอง ' . $code . ' กรุณาจัดส่งของ', 'admin/coupons.php');
    go('coupons.php', 'ยืนยันใช้คูปองแล้ว แอดมินได้รับข้อมูลสำหรับจัดส่งเรียบร้อย');
}

$rewards = all('SELECT * FROM rewards WHERE is_active = 1 AND (stock IS NULL OR stock > 0) ORDER BY points_cost, id');
$balance = (int) val('SELECT points FROM users WHERE id = ?', [$u['id']]);

head('แลกคูปอง');
?>
<div class="bg-success text-white rounded p-4 mb-4">
  <p class="lead mb-1">แต้มของคุณพร้อมแลกรางวัล</p>
  <div class="fs-1 lh-sm"><?= number_format($balance) ?> แต้ม</div>
  <p class="mb-0">เลือกของรางวัลที่ชอบ แล้วส่งคำขอให้ทีมงานตรวจสอบ</p>
</div>

<h5 class="mb-4">🎁 ของรางวัล</h5>
<div class="row g-3">
  <?php foreach ($rewards as $r): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card card-body h-100">
        <img src="<?=  UP ?>uploads/<?= e($r['image']) ?>" class="card-img-top w-40 h-30" alt="<?= e($r['title']) ?>">
        <h5><?= e($r['title']) ?></h5>
        <p class="mb-2"><?= nl2br(e($r['description'])) ?></p>
        <p class="text-muted">ราคา <?= number_format($r['points_cost']) ?> แต้ม · คงเหลือ <?= $r['stock'] === null ? 'ไม่จำกัด' : number_format($r['stock']) ?> ชิ้น</p>
        <form method="post" class="mt-auto" onsubmit="return confirm('ยืนยันขอแลกรางวัลนี้หรือไม่?')">
          <input type="hidden" name="reward_id" value="<?= $r['id'] ?>">
          <button class="btn btn-success w-100" name="redeem" <?= $balance < $r['points_cost'] ? 'disabled' : '' ?>>แลกรางวัล</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php if (!$rewards): ?><div class="alert alert-secondary mt-2">ยังไม่มีของรางวัลที่เปิดให้แลก</div><?php endif; ?>

<h5 class="mt-4">📦 ใช้คูปองรับของ</h5>
<p class="text-muted">กรอกรหัสคูปองที่อนุมัติแล้ว พร้อมข้อมูลจัดส่ง รหัสใช้ได้ครั้งเดียวก่อนวันหมดอายุ</p>
<form method="post" class="card card-body mb-4">
  <label class="form-label" for="claim-code">รหัสคูปอง</label>
  <input class="form-control" id="claim-code" name="coupon_code" required maxlength="32" autocomplete="off">
  <?php input('ชื่อผู้รับ', 'recipient_name', 'text', '', 'required maxlength="100"') ?>
  <?php input('เบอร์โทรศัพท์ผู้รับ', 'recipient_phone', 'tel', '', 'required maxlength="20" pattern="[0-9+() -]{8,20}"') ?>
  <label class="form-label mt-2" for="shipping-address">ที่อยู่จัดส่ง</label>
  <textarea class="form-control" id="shipping-address" name="shipping_address" rows="3" required maxlength="500"></textarea>
  <button class="btn btn-success mt-3 align-self-start" name="claim">ยืนยันใช้คูปอง</button>
</form>
<?php foot() ?>
