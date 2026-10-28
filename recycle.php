<?php
// แจ้งรีไซเคิลขยะอิเล็กทรอนิกส์และติดตามสถานะคำขอ
require 'init.php';
require __DIR__ . '/controllers/recycle.php';
head('รีไซเคิล');
?>
<div class="hero mb-4 d-flex justify-content-between align-items-center">
  <div>
    <span class="small fw-bold text-uppercase opacity-75">Recycle &amp; Make a Difference</span>
    <p class="lead mb-1">🌱 คาร์บอนที่ลดได้ในปี <?= $year + 543 ?></p>
    <div class="fs-1 lh-sm"><?= number_format($saved, 2) ?> <span>kgCO₂e</span></div>
    <p class="mb-0">ส่งต่อขยะอิเล็กทรอนิกส์ให้กลับมาใช้ประโยชน์ และช่วยลดผลกระทบต่อสิ่งแวดล้อม</p>
  </div>
  <div class="d-none d-md-block fs-1 text-nowrap" aria-hidden="true">📦 ✦ 🌿</div>
</div>

<form method="post" enctype="multipart/form-data" class="card border-0 shadow-sm overflow-hidden mb-4">
  <div class="row g-0">
        <aside class="col-md-4 p-4 d-flex flex-column justify-content-center" style="background:#e8f5ee">
      <div class="fs-1 mb-2" aria-hidden="true">♻️</div>
      <span class="small fw-bold text-uppercase mb-1" style="color:#198754">Give it a new life</span>
      <h2 class="h4 fw-bold" style="color:#14532d">เริ่มต้นจากของชิ้นเล็ก ๆ</h2>
      <p style="color:#3f6b52">แจ้งข้อมูลอุปกรณ์และน้ำหนักโดยประมาณ ทีมงานจะตรวจสอบก่อนยืนยันผลการรีไซเคิล</p>
      <div class="rounded-3 px-3 py-2 bg-white fw-semibold" style="color:#14532d; border:1px solid #b7dfc7">✨ รับ 20 แต้มต่อขยะที่รีไซเคิลสำเร็จ 1 กก.</div>
    </aside>
    <div class="col-md-8 p-4">
      <h5 class="fw-bold mb-1" id="recycle-form">แจ้งรายการรีไซเคิล</h5>
      <p class="text-muted mb-3">กรอกข้อมูลอุปกรณ์ที่ต้องการส่งรีไซเคิล</p>
      <label class="form-label">หมวดอุปกรณ์</label>
      <select class="form-select mb-3" id="recycle-category" name="category_id" required>
        <option value="">เลือกหมวด</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= $c['id'] ?>" data-factor="<?= htmlspecialchars(($c['co2_per_kg']) ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(($c['name']) ?? '', ENT_QUOTES, 'UTF-8') ?> (ลด <?= $c['co2_per_kg'] ?> kgCO₂e ต่อ กก.)</option>
        <?php endforeach; ?>
      </select>
      <label class="form-label mt-2">รายการ</label>
      <input class="form-control" type="text" name="item_name" value="" required minlength="3">
      <label class="form-label mt-2">น้ำหนักโดยประมาณ (กก.)</label>
      <input class="form-control" type="number" name="weight_kg" value="" required min="0.01" step="0.01" id="recycle-weight">
      <label class="form-label mt-2">ประเภทการขนส่ง</label>
      <select class="form-select" name="delivery_type" required>
        <option value="">เลือกประเภทการขนส่ง</option>
        <option value="self">นำส่งเอง</option>
        <option value="pickup">นัดรับที่บ้าน</option>
      </select>
      <label class="form-label mt-2">ที่อยู่สำหรับรับ/ส่ง</label>
      <textarea class="form-control" name="address" rows="3" maxlength="500" required></textarea>
      <div id="recycle-estimate" class="form-text text-success mb-3" aria-live="polite">เลือกหมวดและกรอกน้ำหนักเพื่อดูปริมาณคาร์บอนที่คาดว่าจะลดได้</div>
      <label class="form-label mt-2">รูปสินค้า (ไม่บังคับ JPG PNG GIF ไม่เกิน 2MB)</label>
      <input class="form-control" type="file" name="image" value="" accept="image/*">
      <button class="btn btn-success mt-3 px-4">ส่งคำขอ</button>
    </div>
  </div>
</form>
<script>
  const recycleCategory = document.getElementById('recycle-category');
  const recycleWeight = document.getElementById('recycle-weight');
  const recycleEstimate = document.getElementById('recycle-estimate');
  const updateRecycleEstimate = () => {
    const factor = Number(recycleCategory.selectedOptions[0].dataset.factor);
    const weight = Number(recycleWeight.value);
    if (!factor || !weight || weight <= 0) {
      recycleEstimate.textContent = 'เลือกหมวดและกรอกน้ำหนักเพื่อดูปริมาณคาร์บอนที่คาดว่าจะลดได้';
      return;
    }
    const estimate = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(factor * weight);
    recycleEstimate.textContent = `คาดว่าจะลดได้ ${estimate} kgCO₂e (ผลจริงยืนยันหลังผู้ดูแลตรวจรับ)`;
  };
  recycleCategory.addEventListener('change', updateRecycleEstimate);
  recycleWeight.addEventListener('input', updateRecycleEstimate);
</script>

<?php foot() ?>
