<?php
require '../init.php';
require __DIR__ . '/../controllers/admin_coupons.php';
head('จัดการคูปอง');
?>
<h5>🎁 จัดการของรางวัล</h5>
<form method="post" class="card card-body mb-4" enctype="multipart/form-data">
  <h6>➕ เพิ่มของรางวัล</h6>
  <div class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label">ชื่อของรางวัล</label><input class="form-control" name="title" required minlength="2"></div>
    <div class="col-md-3"><label class="form-label">รายละเอียด/เงื่อนไข</label><input class="form-control" name="description"></div>
    <div class="col-md-2"><label class="form-label">แต้มที่ใช้</label><input class="form-control" name="points_cost" type="number" min="1" required></div>
    <div class="col-md-2"><label class="form-label">สต็อก (ว่าง=ไม่จำกัด)</label><input class="form-control" name="stock" type="number" min="0"></div>
    <div class="col-md-2"><label class="form-label">อายุคูปอง (วัน)</label><input class="form-control" name="coupon_valid_days" type="number" min="1" max="365" value="30" required></div>
    <div class="col-12"><label class="form-label">รูปสินค้า (ไม่บังคับ JPG PNG GIF ไม่เกิน 2MB)</label><input class="form-control" type="file" name="image" accept="image/*"></div>
    <div class="col-12"><button class="btn btn-success" name="save_reward">เพิ่มรางวัล</button></div>
  </div>
</form>
<?php foot() ?>
