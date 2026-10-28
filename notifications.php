<?php
// การแจ้งเตือนของผู้ใช้ปัจจุบัน (ใช้ร่วมกันทั้งสมาชิกและผู้ดูแลระบบ) เปิดหน้านี้แล้วถือว่าอ่านทั้งหมด
require 'init.php';
require __DIR__ . '/controllers/notifications.php';
head('การแจ้งเตือน');
?>
<form method="get" class="d-flex flex-wrap gap-2 mb-3 d-print-none">
  <input class="form-control w-auto" name="q" value="<?= htmlspecialchars((trim($_GET['q'] ?? '')) ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="ค้นหา...">
  <button class="btn btn-success">ค้นหา</button>
</form>
<?php foreach ($rows as $r): ?>
  <div class="card card-body mb-2 <?= $r['is_read'] ? '' : 'border border-success' ?>">
    <form method="post" class="d-flex justify-content-between gap-2">
      <div><b><?= htmlspecialchars(($r['title']) ?? '', ENT_QUOTES, 'UTF-8') ?></b> <?= $r['is_read'] ? '' : '<span class="badge bg-danger">ใหม่</span>' ?><br><?= htmlspecialchars(($r['message']) ?? '', ENT_QUOTES, 'UTF-8') ?><br><small class="text-muted"><?= ($r['created_at'] ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-') ?></small></div>
      <div class="text-nowrap">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <?php if ($r['link']): ?><a class="btn btn-sm btn-success" href="<?= htmlspecialchars(($r['link']) ?? '', ENT_QUOTES, 'UTF-8') ?>">เปิด</a><?php endif; ?>
        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('ลบการแจ้งเตือนนี้?')">ลบ</button>
      </div>
    </form>
  </div>
<?php endforeach; ?>
<?php if (!$rows): ?><div class="alert alert-secondary">ไม่มีการแจ้งเตือน</div><?php endif; ?>
<?php foot() ?>
