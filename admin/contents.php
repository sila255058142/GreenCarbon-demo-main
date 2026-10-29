<?php
// จัดการเนื้อหา: เพิ่ม ลบ แก้ไข ค้นหา และตั้งวันเวลาเปิด-ปิดการแสดงผล
require '../init.php';
require __DIR__ . '/../controllers/admin_contents.php';
head('จัดการเนื้อหา');
?>
<form method="post" class="card card-body mb-4">
  <h5><?= $edit['id'] ? 'แก้ไขเนื้อหา' : 'เพิ่มเนื้อหา' ?></h5>
  <input type="hidden" name="id" value="<?= $edit['id'] ?>">
  <label class="form-label mt-2">หัวข้อ</label>
  <input class="form-control" type="text" name="title" value="<?= htmlspecialchars(($edit['title']) ?? '', ENT_QUOTES, 'UTF-8') ?>" required minlength="3">
  <label class="form-label mt-2">เนื้อหา</label>
  <textarea class="form-control" name="body" rows="3" required><?= htmlspecialchars(($edit['body']) ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  <div class="row">
    <div class="col-md-6">
      <label class="form-label mt-2">เริ่มแสดงผล</label>
      <input class="form-control" type="datetime-local" name="start_at" value="<?= htmlspecialchars(($local($edit['start_at'])) ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label mt-2">สิ้นสุดการแสดงผล (เว้นว่าง = ไม่มีกำหนด)</label>
      <input class="form-control" type="datetime-local" name="end_at" value="<?= htmlspecialchars(($local($edit['end_at'])) ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </div>
  </div>
  <div class="mt-3"><button class="btn btn-success">บันทึก</button> <a class="btn btn-outline-secondary" href="contents.php">ล้างฟอร์ม</a></div>
</form>

<form method="get" class="d-flex flex-wrap gap-2 mb-3 d-print-none">
  <input class="form-control w-auto" name="q" value="<?= htmlspecialchars((trim($_GET['q'] ?? '')) ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="ค้นหา...">
  <button class="btn btn-success">ค้นหา</button>
</form>
<div class="table-responsive">
<table class="table table-striped bg-white">
  <tr><th>หัวข้อ</th><th>เริ่มแสดง</th><th>สิ้นสุด</th><th>สถานะ</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars(($r['title']) ?? '', ENT_QUOTES, 'UTF-8') ?></td><td><?= ($r['start_at'] ? date('d/m/Y H:i', strtotime($r['start_at'])) : '-') ?></td><td><?= ($r['end_at'] ? date('d/m/Y H:i', strtotime($r['end_at'])) : '-') ?></td><td><?= $r['live'] ?></td>
      <td><form method="post"><input type="hidden" name="id" value="<?= $r['id'] ?>">
        <a class="btn btn-sm btn-outline-success" href="admin/contents.php?edit=<?= $r['id'] ?>">แก้ไข</a>
        <button class="btn btn-sm btn-outline-danger" name="delete" onclick="return confirm('ลบเนื้อหานี้?')">ลบ</button></form></td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
<?php foot() ?>
