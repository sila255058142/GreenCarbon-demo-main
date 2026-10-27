<?php
require 'init.php';

if ($_POST) {
    $err = check_user() ?: check_pass();
    if (!$err) {
        q('INSERT INTO users (name, email, phone, password, question, answer) VALUES (?, ?, ?, ?, ?, ?)',
            [p('name'), p('email'), p('phone'), password_hash(p('password'), PASSWORD_DEFAULT), p('question'), password_hash(p('answer'), PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['uid'] = $db->insert_id;
        notify(null, 'สมาชิกใหม่', p('name') . ' (' . p('email') . ')', 'admin/users.php');
        notify($_SESSION['uid'], 'ยินดีต้อนรับสู่ GreenLoop', 'เริ่มส่งขยะอิเล็กทรอนิกส์ไปรีไซเคิลและสะสมแต้มได้เลย', 'recycle.php');
        go('recycle.php', 'สมัครสมาชิกสำเร็จ เริ่มส่งขยะอิเล็กทรอนิกส์ไปรีไซเคิลได้เลย');
    }
    flash($err);
}

head('สมัครสมาชิก');
?>
<form method="post" class="card card-body mx-auto" style="max-width:520px">
  <label class="form-label mt-2">ชื่อ-นามสกุล</label>
  <input class="form-control" type="text" name="name" value="<?= e(p('name')) ?>" required>
  <label class="form-label mt-2">อีเมล</label>
  <input class="form-control" type="email" name="email" value="<?= e(p('email')) ?>" required>
  <label class="form-label mt-2">เบอร์โทรศัพท์ (ตัวเลข 10 หลัก)</label>
  <input class="form-control" type="text" name="phone" value="<?= e(p('phone')) ?>" required pattern="0[0-9]{9}" maxlength="10" inputmode="numeric" title="ตัวเลข 10 หลัก ขึ้นต้นด้วย 0">
  <label class="form-label mt-2">รหัสผ่าน (อย่างน้อย 8 ตัวอักษร)</label>
  <input class="form-control" type="password" name="password" value="" required minlength="8">
  <label class="form-label mt-2">ยืนยันรหัสผ่าน</label>
  <input class="form-control" type="password" name="password2" value="" required>
  <?php question(p('question')) ?>
  <label class="form-label mt-2">คำตอบ (ใช้ตั้งรหัสผ่านใหม่เมื่อลืม)</label>
  <input class="form-control" type="text" name="answer" value="<?= e(p('answer')) ?>" required>
  <button type="submit" name="submit" class="btn btn-success mt-3">สมัครสมาชิก</button>
</form>
<?php foot() ?>
