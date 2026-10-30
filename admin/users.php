<?php
// จัดการสมาชิก: เพิ่ม ลบ แก้ไข ค้นหา และระงับ / ยกเลิกระงับสิทธิ์
require '../init.php';
require __DIR__ . '/../controllers/admin_users.php';
head('จัดการสมาชิก');
?>
<form method="get" class="d-flex flex-wrap gap-2 mb-3 d-print-none">
  <input class="form-control w-auto" name="q" value="<?= htmlspecialchars((trim($_GET['q'] ?? '')) ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="ค้นหา...">
  <button class="btn btn-success">ค้นหา</button>
</form>
<div class="card card-body">
  <h5>สมาชิก</h5>
  <?php foreach ($rows as $r): ?>
    <?php $memberLevel = $r['points'] >= 600 ? '🌍 Net Zero Hero' : ($r['points'] >= 300 ? '🌳 ต้นไม้' : ($r['points'] >= 100 ? '🌱 ต้นกล้า' : '🌰 เมล็ดพันธุ์')); ?>
    <form method="post" class="row g-2 mb-2 align-items-center">
      <input type="hidden" name="id" value="<?= $r['id'] ?>">
      <input type="hidden" name="save_user" value="1">
      <div class="col-md-3"><input class="form-control" name="name" value="<?= htmlspecialchars(($r['name']) ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
      <div class="col-md-2"><input class="form-control" type="email" name="email" value="<?= htmlspecialchars(($r['email']) ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
      <div class="col-md-2"><input class="form-control" name="phone" value="<?= htmlspecialchars(($r['phone']) ?? '', ENT_QUOTES, 'UTF-8') ?>" required pattern="[0-9]{9,10}"></div>
      <div class="col-md-1"><input class="form-control" type="number" min="0" name="points" value="<?= $r['points'] ?>"></div>
      <div class="col-md-2">
        <select class="form-select" name="status">
          <option value="active" <?= $r['status'] === 'active' ? 'selected' : '' ?>>ใช้งานได้</option>
          <option value="suspended" <?= $r['status'] === 'suspended' ? 'selected' : '' ?>>ระงับสิทธิ์</option>
        </select>
      </div>
      <div class="col-md-2 text-end">
        <button class="btn btn-success btn-sm" type="submit">บันทึก</button>
        <button class="btn btn-outline-danger btn-sm" type="submit" name="delete" onclick="return confirm('ลบสมาชิกนี้?')">ลบ</button>
      </div>
      <div class="col-12 text-muted small">
        <?= $memberLevel ?> · สมัครเมื่อ <?= ($r['created_at'] ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-') ?>
      </div>
    </form>
  <?php endforeach; ?>

  <form method="post" class="row g-2 mt-3 border-top pt-3">
    <input type="hidden" name="save_user" value="1">
    <div class="col-md-3"><input class="form-control" name="name" placeholder="ชื่อ" required></div>
    <div class="col-md-2"><input class="form-control" type="email" name="email" placeholder="อีเมล" required></div>
    <div class="col-md-2"><input class="form-control" name="phone" placeholder="เบอร์โทร" required pattern="[0-9]{9,10}"></div>
    <div class="col-md-1"><input class="form-control" type="number" min="0" name="points" value="0"></div>
    <div class="col-md-2">
      <select class="form-select" name="status">
        <option value="active">ใช้งานได้</option>
        <option value="suspended">ระงับสิทธิ์</option>
      </select>
    </div>
    <div class="col-md-2">
      <input class="form-control" type="password" name="password" placeholder="รหัสผ่าน" required minlength="6">
    </div>
    <div class="col-12">
      <button class="btn btn-success">เพิ่มสมาชิก</button>
    </div>
  </form>
</div>
<?php foot() ?>
