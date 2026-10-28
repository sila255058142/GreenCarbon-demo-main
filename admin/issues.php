<?php
require '../init.php';
require __DIR__ . '/../controllers/admin_issues.php';
head('แชทกับสมาชิก');
?>
<div class="row">
  <div class="col-lg-4">
    <form method="get" class="d-flex flex-wrap gap-2 mb-3 d-print-none">
      <input class="form-control w-auto" name="q" value="<?= htmlspecialchars((trim($_GET['q'] ?? '')) ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="ค้นหา...">
      <button class="btn btn-success">ค้นหา</button>
    </form>
    <div class="list-group mb-3">
      <?php foreach ($threads as $t): ?>
        <a class="list-group-item list-group-item-action <?= $member && $member['id'] == $t['id'] ? 'active' : '' ?>" href="?user=<?= $t['id'] ?>">
          <?= htmlspecialchars(($t['name']) ?? '', ENT_QUOTES, 'UTF-8') ?> <?php if ($t['n']): ?><span class="badge bg-danger"><?= $t['n'] ?></span><?php endif; ?><br><small><?= $t['last'] ? ($t['last'] ? date('d/m/Y H:i', strtotime($t['last'])) : '-') : 'ยังไม่มีข้อความ' ?></small>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (!$threads): ?><div class="alert alert-secondary">ไม่พบสมาชิก</div><?php endif; ?>
  </div>
  <div class="col-lg-8">
    <?php if ($member): ?>
      <form method="post" class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0"><?= htmlspecialchars(($member['name']) ?? '', ENT_QUOTES, 'UTF-8') ?> <small class="text-muted"><?= htmlspecialchars(($member['email']) ?? '', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars(($member['phone']) ?? '', ENT_QUOTES, 'UTF-8') ?></small></h5>
        <button class="btn btn-sm btn-outline-danger" name="clear" onclick="return confirm('ลบแชททั้งหมดของสมาชิกคนนี้? กู้คืนไม่ได้')">🗑 ลบแชททั้งหมด</button>
      </form>
      <?php chat($member['id']) ?>
    <?php else: ?>
      <div class="alert alert-success">เลือกสมาชิกจากรายการเพื่ออ่าน ตอบกลับ หรือเริ่มแชท</div>
    <?php endif; ?>
  </div>
</div>
<?php foot() ?>
