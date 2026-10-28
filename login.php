<?php
require 'init.php';
require __DIR__ . '/controllers/login.php';
head('เข้าสู่ระบบ');
?>
<form method="post" class="card card-body mx-auto" style="max-width:420px">
  <label class="form-label mt-2">อีเมล</label>
  <input class="form-control" type="email" name="email" value="" required>
  <label class="form-label mt-2">รหัสผ่าน</label>
  <input class="form-control" type="password" name="password" value="" required>
  <button class="btn btn-success mt-3">เข้าสู่ระบบ</button>
  <div class="d-flex justify-content-between mt-3">
    <a href="forgot.php">ลืมรหัสผ่าน?</a>
    <a href="register.php">สมัครสมาชิก</a>
  </div>
</form>
<?php foot() ?>
