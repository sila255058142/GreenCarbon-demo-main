<?php
// ตั้งค่าหมวดขยะอิเล็กทรอนิกส์ที่รับรีไซเคิล
require '../init.php';
require __DIR__ . '/../controllers/admin_green.php';
head('ตั้งค่า Green');
?>
<div class="card card-body">
  <h5>หมวดขยะอิเล็กทรอนิกส์ (kgCO₂e ที่ลดได้เมื่อรีไซเคิล 1 กก.)</h5>
  <?php foreach ($cats as $c): ?>
    <form method="post" class="row g-2 mb-2">
      <input type="hidden" name="id" value="<?= $c['id'] ?>">
      <div class="col-md-7"><input class="form-control" name="name" value="<?= htmlspecialchars(($c['name']) ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="ชื่อหมวด" required></div>
      <div class="col-md-2"><input class="form-control" name="co2_per_kg" type="number" step="0.01" min="0.01" value="<?= $c['co2_per_kg'] ?>" placeholder="kgCO₂e ต่อ กก." required></div>
      <div class="col-md-3">
        <button class="btn btn-success" name="save_category"><?= $c['id'] ? 'บันทึก' : 'เพิ่ม' ?></button>
        <?php if ($c['id']): ?><button class="btn btn-outline-danger" name="del_category" onclick="return confirm('ลบหมวดนี้?')">ลบ</button><?php endif; ?>
      </div>
    </form>
  <?php endforeach; ?>
</div>
<?php foot() ?>
