<?php
require 'init.php';
$u = need_login();

if (isset($_POST['save'])) {
    if ($err = check_user($u['id'])) go('profile.php', $err, 'danger');
    q('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?', [p('name'), p('email'), p('phone'), $u['id']]);
    go('profile.php', 'บันทึกโปรไฟล์แล้ว');
}
if (isset($_POST['change'])) {
    if (!password_verify(p('current'), $u['password'])) go('profile.php', 'รหัสผ่านปัจจุบันไม่ถูกต้อง', 'danger');
    if ($err = check_pass()) go('profile.php', $err, 'danger');
    q('UPDATE users SET password = ? WHERE id = ?', [password_hash(p('password'), PASSWORD_DEFAULT), $u['id']]);
    go('profile.php', 'เปลี่ยนรหัสผ่านแล้ว');
}

head('โปรไฟล์ของฉัน');
?>
<div class="row">
  <div class="col-lg-6">
    <form method="post" class="card card-body mb-3">
      <div class="text-center"><div class="fs-1">👤</div><div class="mt-2"><?= level($u['points']) ?> · <?= $u['points'] ?> แต้ม</div></div>
      <label class="form-label mt-2">ชื่อ-นามสกุล</label>
      <input class="form-control" type="text" name="name" value="<?= e($u['name']) ?>" required>
      <label class="form-label mt-2">อีเมล</label>
      <input class="form-control" type="email" name="email" value="<?= e($u['email']) ?>" required>
      <label class="form-label mt-2">เบอร์โทรศัพท์ (ตัวเลข 10 หลัก)</label>
      <input class="form-control" type="text" name="phone" value="<?= e($u['phone']) ?>" required pattern="0[0-9]{9}" maxlength="10" inputmode="numeric" title="ตัวเลข 10 หลัก ขึ้นต้นด้วย 0">
      <button class="btn btn-success mt-3" name="save">บันทึก</button>
    </form>
  </div>
  <div class="col-lg-6">
    <form method="post" class="card card-body mb-3">
      <h5>เปลี่ยนรหัสผ่าน</h5>
      <label class="form-label mt-2">รหัสผ่านปัจจุบัน</label>
      <input class="form-control" type="password" name="current" value="" required>
      <label class="form-label mt-2">รหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร)</label>
      <input class="form-control" type="password" name="password" value="" required minlength="8">
      <label class="form-label mt-2">ยืนยันรหัสผ่านใหม่</label>
      <input class="form-control" type="password" name="password2" value="" required>
      <button class="btn btn-outline-success mt-3" name="change">เปลี่ยนรหัสผ่าน</button>
    </form>
  </div>
</div>
<?php foot() ?>
