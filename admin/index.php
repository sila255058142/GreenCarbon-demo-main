<?php
require '../init.php';
require __DIR__ . '/../controllers/admin_index.php';
head('Dashboard');
?>
<div class="row">
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">👥 สมาชิกทั้งหมด</small><h4 class="text-success mb-0"><?= $memberCount ?> คน</h4></div></div>
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">🚫 สมาชิกที่ถูกระงับ</small><h4 class="text-success mb-0"><?= $suspendedCount ?> คน</h4></div></div>
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">⏳ คำขอรีไซเคิลรอตรวจรับ</small><h4 class="text-success mb-0"><?= $pendingRecycleCount ?> รายการ</h4></div></div>
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">🎟️ คำขอแลกคูปองรออนุมัติ</small><h4 class="text-success mb-0"><?= $pendingCouponCount ?> รายการ</h4></div></div>
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">🔔 แจ้งเตือนที่ยังไม่อ่าน</small><h4 class="text-success mb-0"><?= $unreadNotificationCount ?> รายการ</h4></div></div>
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">📦 E-Waste ที่รีไซเคิล</small><h4 class="text-success mb-0"><?= $sum['kg'] ?> กก.</h4></div></div>
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">🌍 คาร์บอนที่ลดได้</small><h4 class="text-success mb-0"><?= $sum['co2'] ?> kgCO₂e</h4></div></div>
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">🌳 เทียบเท่าต้นไม้</small><h4 class="text-success mb-0"><?= round($sum['co2'] / 9.5, 1) ?> ต้น</h4></div></div>
</div>
<div class="card card-body mb-3">
  <h5>🎟️ คำขอแลกคูปองที่รออนุมัติ</h5>
  <?php foreach ($couponRequests as $r): ?>
    <div class="border-top py-2 d-flex justify-content-between align-items-center gap-2 flex-wrap">
      <span><?= htmlspecialchars(($r['member']) ?? '', ENT_QUOTES, 'UTF-8') ?> ขอแลก <?= htmlspecialchars(($r['reward_title']) ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= number_format($r['points_spent']) ?> แต้ม)</span>
      <form method="post" action="coupons.php" class="d-flex gap-1">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button class="btn btn-sm btn-success" name="review">อนุมัติ</button>
        <button class="btn btn-sm btn-outline-danger" name="reject" onclick="return confirm('ไม่อนุมัติและคืนแต้มให้สมาชิก?')">ไม่อนุมัติ</button>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (!$couponRequests): ?><p class="text-muted mb-0">ไม่มีคำขอที่รออนุมัติ</p><?php endif; ?>
  <a class="mt-2" href="coupons.php">จัดการของรางวัลและดูคำขอทั้งหมด</a>
</div>
<div class="card card-body mb-3">
  <h5>📦 รายการคูปองที่รอจัดส่ง</h5>
  <?php foreach ($fulfillments as $r): ?>
    <div class="border-top py-2">
      <b><?= htmlspecialchars(($r['recipient_name']) ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars(($r['recipient_phone']) ?? '', ENT_QUOTES, 'UTF-8') ?>)</b>
      <span class="text-muted"> · รหัส <?= htmlspecialchars(($r['coupon_code']) ?? '', ENT_QUOTES, 'UTF-8') ?></span><br>
      <small><?= nl2br(htmlspecialchars(($r['shipping_address']) ?? '', ENT_QUOTES, 'UTF-8')) ?></small>
    </div>
  <?php endforeach; ?>
  <?php if (!$fulfillments): ?><p class="text-muted mb-0">ไม่มีรายการรอจัดส่ง</p><?php endif; ?>
  <a class="mt-2" href="coupons.php?status=used">ดูข้อมูลจัดส่งทั้งหมด</a>
</div>
<div class="row">
  <div class="col-lg-8"><div class="card card-body mb-3"><h6>คาร์บอนที่ลดได้รายเดือน (kgCO₂e)</h6><canvas id="carbon-chart"></canvas></div></div>
  <div class="col-lg-4"><div class="card card-body mb-3"><h6>น้ำหนักที่รีไซเคิลตามหมวด (กก.)</h6><canvas id="category-chart"></canvas></div></div>
  <div class="col-lg-8"><div class="card card-body mb-3"><h6>สมาชิกใหม่รายเดือน (คน)</h6><canvas id="member-chart"></canvas></div></div>
  <div class="col-lg-4">
    <div class="card card-body mb-3">
      <h6>🔔 แจ้งเตือนที่ยังไม่อ่าน</h6>
      <?php foreach ($notis as $n): ?>
        <div class="border-top py-1"><b><?= htmlspecialchars(($n['title']) ?? '', ENT_QUOTES, 'UTF-8') ?></b><br><small><?= htmlspecialchars(($n['message']) ?? '', ENT_QUOTES, 'UTF-8') ?></small></div>
      <?php endforeach; ?>
      <a class="mt-2" href="../notifications.php">ดูทั้งหมด</a>
    </div>
  </div>
</div>
<script>
  new Chart(document.getElementById('carbon-chart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_column($byMonth, 'm'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
      datasets: [{ label: 'คาร์บอนที่ลดได้รายเดือน (kgCO₂e)', data: <?= json_encode(array_column($byMonth, 'v')) ?> }]
    }
  });
  new Chart(document.getElementById('category-chart'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_column($byCat, 'name'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
      datasets: [{ label: 'น้ำหนักที่รีไซเคิลตามหมวด (กก.)', data: <?= json_encode(array_column($byCat, 'v')) ?> }]
    }
  });
  new Chart(document.getElementById('member-chart'), {
    type: 'line',
    data: {
      labels: <?= json_encode(array_column($members, 'm'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
      datasets: [{ label: 'สมาชิกใหม่รายเดือน (คน)', data: <?= json_encode(array_column($members, 'v')) ?> }]
    }
  });
</script>
<?php foot() ?>
