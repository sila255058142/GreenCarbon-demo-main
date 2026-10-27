<?php
// จัดการของรางวัลและคำขอใช้คูปอง
require '../init.php';
$admin = need_admin();
expire_coupons();

if (isset($_POST['save_reward'])) {
    $title = p('title'); $description = p('description'); $cost = p('points_cost');
    $stock = p('stock'); $days = p('coupon_valid_days'); $id = p('id');

    if (mb_strlen($title) < 2 || mb_strlen($description) > 500 || !ctype_digit($cost) || (int) $cost < 1
        || ($stock !== '' && (!ctype_digit($stock) || (int) $stock < 0))
        || !ctype_digit($days) || (int) $days < 1 || (int) $days > 365) {
        go('coupons.php', 'กรุณาตรวจสอบชื่อ แต้ม จำนวนสต็อก และวันหมดอายุ (1-365 วัน)', 'danger');
    }
// var_dump($_FILES['image']); exit;
    $image = upload('image');
    if (!$image && bad_upload('image')) go('coupons.php', bad_upload('image'), 'danger');

    if ($id) {
        // กรณีแก้ไข: ถ้าไม่ได้อัปโหลดรูปใหม่ ให้เก็บรูปเดิมไว้
        $old_img = val('SELECT image FROM rewards WHERE id = ?', [$id]);
        $image = $image ?: $old_img;
        $sql = 'UPDATE rewards SET title = ?, description = ?, points_cost = ?, stock = ?, coupon_valid_days = ?, image = ? WHERE id = ?';
        $values = [$title, $description, (int) $cost, $stock === '' ? null : (int) $stock, (int) $days, $image, $id];
    } else {
        // กรณีเพิ่มใหม่
        $sql = 'INSERT INTO rewards (title, description, points_cost, stock, coupon_valid_days, image) VALUES (?, ?, ?, ?, ?, ?)';
        $values = [$title, $description, (int) $cost, $stock === '' ? null : (int) $stock, (int) $days, $image];
    }

    // ทำการบันทึกข้อมูลลงฐานข้อมูลก่อน แล้วค่อย Redirect
    q($sql, $values);
    go('coupons.php', 'บันทึกของรางวัลแล้ว');
}
if (isset($_POST['toggle_reward'])) {
    q('UPDATE rewards SET is_active = 1 - is_active WHERE id = ?', [p('id')]);
    go('coupons.php', 'เปลี่ยนสถานะของรางวัลแล้ว');
}
if (isset($_POST['review'])) {
    $db->begin_transaction();
    $request = one('SELECT * FROM coupon_redemptions WHERE id = ? FOR UPDATE', [p('id')]);
    if (!$request || $request['status'] != 'pending') {
        $db->rollback();
        go('coupons.php', 'คำขอนี้ถูกดำเนินการแล้วหรือไม่พบคำขอ', 'danger');
    }

    if (isset($_POST['reject'])) {
        q("UPDATE coupon_redemptions SET status = 'rejected', reviewed_at = NOW(), reviewed_by = ?, stock_reserved = 0 WHERE id = ? AND status = 'pending'", [$admin['id'], $request['id']]);
        q('UPDATE users SET points = points + ? WHERE id = ?', [$request['points_spent'], $request['user_id']]);
        q('UPDATE rewards SET stock = IF(stock IS NULL OR ? = 0, stock, stock + 1) WHERE id = ?', [$request['stock_reserved'], $request['reward_id']]);
        q("INSERT INTO point_transactions (user_id, amount, description, related_type, related_id) VALUES (?, ?, ?, 'coupon_refund', ?)",
            [$request['user_id'], $request['points_spent'], 'คืนแต้มจากคำขอแลกคูปองที่ไม่ผ่าน', $request['id']]);
        $db->commit();
        notify($request['user_id'], 'คำขอแลกคูปองไม่ผ่าน', 'คืน ' . $request['points_spent'] . ' แต้มให้แล้ว', 'coupons.php');
        go('coupons.php', 'ไม่อนุมัติคำขอและคืนแต้มให้สมาชิกแล้ว', 'warning');
    }

    $reward = one('SELECT title, coupon_valid_days FROM rewards WHERE id = ? FOR UPDATE', [$request['reward_id']]);
    do {
        $code = 'GL-' . strtoupper(bin2hex(random_bytes(6)));
    } while (val('SELECT 1 FROM coupon_redemptions WHERE coupon_code = ?', [$code]));
    $expiresAt = date('Y-m-d H:i:s', time() + (int) $reward['coupon_valid_days'] * 86400);
    q("UPDATE coupon_redemptions SET status = 'approved', coupon_code = ?, expires_at = ?, reviewed_at = NOW(), reviewed_by = ?
        WHERE id = ? AND status = 'pending'", [$code, $expiresAt, $admin['id'], $request['id']]);
    $db->commit();
    notify($request['user_id'], 'คูปองได้รับการอนุมัติ', 'ใช้รหัส ' . $code . ' แลก ' . $reward['title'] . ' ภายใน ' . dt($expiresAt), 'coupons.php');
    go('coupons.php', 'อนุมัติแล้ว ออกคูปอง ' . $code . ' หมดอายุ ' . dt($expiresAt));
}

$rewards = all('SELECT * FROM rewards ORDER BY is_active DESC, id DESC');
$requests = all("SELECT cr.*, u.name AS member, r.title AS reward_title FROM coupon_redemptions cr
    JOIN users u ON u.id = cr.user_id JOIN rewards r ON r.id = cr.reward_id
    WHERE (? = '' OR cr.status = ?) AND (u.name LIKE ? OR r.title LIKE ?)
    ORDER BY cr.status = 'pending' DESC, cr.id DESC",
    [g('status'), g('status'), like(), like()]);
$statuses = ['pending' => 'รออนุมัติ', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ไม่อนุมัติ', 'used' => 'ใช้แล้ว/รอจัดส่ง', 'expired' => 'หมดอายุ'];
$statusOptions = '<option value="">ทุกสถานะ</option>';
foreach ($statuses as $value => $label) {
    $statusOptions .= "<option value='$value'" . (g('status') == $value ? ' selected' : '') . ">$label</option>";
}

head('จัดการคูปอง');
?>
<h5>🎁 จัดการของรางวัล</h5>
<?php foreach (array_merge($rewards, [null]) as $r): $newReward = $r === null; ?>
  <form method="post" class="card card-body <?= $newReward ? 'mb-4' : 'mb-2' ?>"  enctype="multipart/form-data">
    <?php if ($newReward): ?><h6>➕ เพิ่มของรางวัล</h6><?php else: ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><?php endif; ?>
  <div class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label">ชื่อของรางวัล</label><input class="form-control" name="title" value="<?= e($r['title'] ?? '') ?>" required minlength="2"></div>
    <div class="col-md-3"><label class="form-label">รายละเอียด/เงื่อนไข</label><input class="form-control" name="description" value="<?= e($r['description'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">แต้มที่ใช้</label><input class="form-control" name="points_cost" type="number" min="1" value="<?= $r['points_cost'] ?? '' ?>" required></div>
    <div class="col-md-2"><label class="form-label">สต็อก (ว่าง=ไม่จำกัด)</label><input class="form-control" name="stock" type="number" min="0" value="<?= e($r['stock'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">อายุคูปอง (วัน)</label><input class="form-control" name="coupon_valid_days" type="number" min="1" max="365" value="<?= $r['coupon_valid_days'] ?? 30 ?>" required></div>
     <?php input('รูปสินค้า (ไม่บังคับ JPG PNG GIF ไม่เกิน 2MB)', 'image', 'file', '', 'accept="image/*"') ?>
      <button class="btn btn-success" name="save_reward"><?= $newReward ? 'เพิ่มรางวัล' : 'บันทึก' ?></button>
      <?php if (!$newReward): ?><button class="btn btn-outline-secondary" name="toggle_reward"><?= $r['is_active'] ? 'ปิด' : 'เปิดป' ?></button><?php endif; ?>
    </div>
  </div>
  <?php if (!$newReward): ?><small class="text-muted mt-2">สถานะ: <?= $r['is_active'] ? 'เปิดให้แลก' : 'ปิดรับแลก' ?> · คงเหลือ <?= $r['stock'] === null ? 'ไม่จำกัด' : number_format($r['stock']) ?> ชิ้น</small><?php endif; ?>
  </form>
<?php endforeach; ?>

<h5>📋 คำขอแลกคูปอง / เตรียมจัดส่ง</h5>
<?php search("<select class='form-select w-auto' name='status'>$statusOptions</select>"); ?>
<div class="table-responsive">
  <table class="table table-striped bg-white align-middle">
    <tr><th>วันที่</th><th>สมาชิก</th><th>ของรางวัล</th><th>แต้ม</th><th>สถานะ</th><th>คูปอง/หมดอายุ</th><th>ข้อมูลจัดส่ง</th><th>ดำเนินการ</th></tr>
    <?php foreach ($requests as $r): ?>
      <tr>
        <td><?= dt($r['created_at']) ?></td><td><?= e($r['member']) ?></td><td><?= e($r['reward_title']) ?></td>
        <td><?= number_format($r['points_spent']) ?></td>
        <td><?= $statuses[$r['status']] ?></td>
        <td><?= $r['coupon_code'] ? '<code>' . e($r['coupon_code']) . '</code>' . ($r['expires_at'] ? '<br><small>' . dt($r['expires_at']) . '</small>' : '') : '-' ?></td>
        <td><?= $r['status'] == 'used' ? e($r['recipient_name']) . '<br>' . e($r['recipient_phone']) . '<br>' . nl2br(e($r['shipping_address'])) : '-' ?></td>
        <td><?php if ($r['status'] == 'pending'): ?>
          <form method="post" class="d-flex gap-1 ">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn btn-sm btn-success" name="review">อนุมัติ</button>
            <button class="btn btn-sm btn-outline-danger" name="reject" onclick="return confirm('ไม่อนุมัติและคืนแต้มให้สมาชิก?')">ไม่อนุมัติ</button>
          </form>
        <?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php if (!$requests): ?><div class="alert alert-secondary">ไม่มีคำขอแลกคูปอง</div><?php endif; ?>
<?php foot() ?>
