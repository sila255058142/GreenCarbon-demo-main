<?php
// แลกแต้มเป็นคูปองของรางวัล
require 'init.php';
require __DIR__ . '/controllers/coupons.php';
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
        <img src="uploads/<?= htmlspecialchars(($r['image']) ?? '', ENT_QUOTES, 'UTF-8') ?>" class="card-img-top w-40 h-30" alt="<?= htmlspecialchars(($r['title']) ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <h5><?= htmlspecialchars(($r['title']) ?? '', ENT_QUOTES, 'UTF-8') ?></h5>
        <p class="mb-2"><?= nl2br(htmlspecialchars(($r['description']) ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
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
  <label class="form-label mt-2">ชื่อผู้รับ</label>
  <input class="form-control" type="text" name="recipient_name" value="" required maxlength="100">
  <label class="form-label mt-2">เบอร์โทรศัพท์ผู้รับ</label>
  <input class="form-control" type="tel" name="recipient_phone" value="" required maxlength="20" pattern="[0-9+() -]{8,20}">
  <label class="form-label mt-2" for="shipping-address">ที่อยู่จัดส่ง</label>
  <textarea class="form-control" id="shipping-address" name="shipping_address" rows="3" required maxlength="500"></textarea>
  <button class="btn btn-success mt-3 align-self-start" name="claim">ยืนยันใช้คูปอง</button>
</form>
<?php foot() ?>
