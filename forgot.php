<?php
// ลืมรหัสผ่าน: ยืนยันตัวตนด้วยคำถามและคำตอบกันลืมที่เลือกไว้ตอนสมัคร
require 'init.php';
require __DIR__ . '/controllers/forgot.php';
head('ลืมรหัสผ่าน');
?>
<form method="post" class="card card-body mx-auto" style="max-width:420px">
  <label class="form-label mt-2">อีเมลที่ใช้สมัคร</label>
  <input class="form-control" type="email" name="email" value="" required>
  <label class="form-label mt-2">คำถามกันลืมรหัสผ่าน</label>
  <select class="form-select" name="question" required>
    <option value="">เลือกคำถาม</option>
    <option value="ชื่อสัตว์เลี้ยงตัวแรกของคุณ">ชื่อสัตว์เลี้ยงตัวแรกของคุณ</option>
  </select>
  <label class="form-label mt-2">คำตอบ</label>
  <input class="form-control" type="text" name="answer" value="" required>
  <label class="form-label mt-2">รหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร)</label>
  <input class="form-control" type="password" name="password" value="" required minlength="8">
  <label class="form-label mt-2">ยืนยันรหัสผ่านใหม่</label>
  <input class="form-control" type="password" name="password2" value="" required>
  <button class="btn btn-success mt-3">ตั้งรหัสผ่านใหม่</button>
</form>
<?php foot() ?>
