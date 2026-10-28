<?php
require 'init.php';
require __DIR__ . '/controllers/register.php';
head('สมัครสมาชิก');
?>
<form method="post" class="card card-body mx-auto" style="max-width:520px">
  <label class="form-label mt-2">ชื่อ-นามสกุล</label>
  <input class="form-control" type="text" name="name" value="<?= htmlspecialchars((trim($_POST['name'] ?? '')) ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
  <label class="form-label mt-2">อีเมล</label>
  <input class="form-control" type="email" name="email" value="<?= htmlspecialchars((trim($_POST['email'] ?? '')) ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
  <label class="form-label mt-2">เบอร์โทรศัพท์ (ตัวเลข 10 หลัก)</label>
  <input class="form-control" type="text" name="phone" value="<?= htmlspecialchars((trim($_POST['phone'] ?? '')) ?? '', ENT_QUOTES, 'UTF-8') ?>" required pattern="0[0-9]{9}" maxlength="10" inputmode="numeric" title="ตัวเลข 10 หลัก ขึ้นต้นด้วย 0">
  <label class="form-label mt-2">รหัสผ่าน (อย่างน้อย 8 ตัวอักษร)</label>
  <input class="form-control" type="password" name="password" value="" required minlength="8">
  <label class="form-label mt-2">ยืนยันรหัสผ่าน</label>
  <input class="form-control" type="password" name="password2" value="" required>
  <label class="form-label mt-2">คำถามกันลืมรหัสผ่าน</label>
  <select class="form-select" name="question" required>
    <option value="">เลือกคำถาม</option>
    <option>ชื่อสัตว์เลี้ยงตัวแรกของคุณ</option>
    <option>ชื่อโรงเรียนประถมของคุณ</option>
  </select>
  <label class="form-label mt-2">คำตอบ (ใช้ตั้งรหัสผ่านใหม่เมื่อลืม)</label>
  <input class="form-control" type="text" name="answer" value="<?= htmlspecialchars((trim($_POST['answer'] ?? '')) ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
  <button type="submit" name="submit" class="btn btn-success mt-3">สมัครสมาชิก</button>
</form>
<?php foot() ?>
