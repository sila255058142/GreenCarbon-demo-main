<?php
// จัดการสมาชิก: ค้นหา และระงับ / ยกเลิกระงับสิทธิ์
require '../init.php';
require __DIR__ . '/../controllers/admin_users.php';
head('จัดการสมาชิก');
?>
<form method="get" class="d-flex flex-wrap gap-2 mb-3 d-print-none">
  <input class="form-control w-auto" name="q" value="<?= htmlspecialchars((trim($_GET['q'] ?? '')) ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="ค้นหา...">
  <button class="btn btn-success">ค้นหา</button>
</form>
<div class="table-responsive">
<table class="table table-striped bg-white align-middle">
  <tr><th>ชื่อ</th><th>อีเมล</th><th>เบอร์โทร</th><th>แต้ม</th><th>ระดับ</th><th>วันที่สมัคร</th><th>สถานะ</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
    <?php $memberLevel = $r['points'] >= 600 ? '🌍 Net Zero Hero' : ($r['points'] >= 300 ? '🌳 ต้นไม้' : ($r['points'] >= 100 ? '🌱 ต้นกล้า' : '🌰 เมล็ดพันธุ์')); ?>
    <tr>
      <td><?= htmlspecialchars(($r['name']) ?? '', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(($r['email']) ?? '', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(($r['phone']) ?? '', ENT_QUOTES, 'UTF-8') ?></td><td><?= $r['points'] ?></td>
      <td><?= $memberLevel ?></td><td><?= ($r['created_at'] ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-') ?></td><td><?= $r['status'] === 'active' ? 'ใช้งานได้' : 'ระงับสิทธิ์' ?></td>
      <td><form method="post"><input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button class="btn btn-sm btn-outline-<?= $r['status'] == 'active' ? 'danger' : 'success' ?>" onclick="return confirm('ยืนยันเปลี่ยนสถานะ?')"><?= $r['status'] == 'active' ? 'ระงับสิทธิ์' : 'ยกเลิกระงับ' ?></button></form></td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
<?php foot() ?>
