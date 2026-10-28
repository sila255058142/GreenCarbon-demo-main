<?php
require '../init.php';
require __DIR__ . '/../controllers/admin_recycles.php';
head('จัดการรีไซเคิล');
?>
<div class="table-responsive">
<table id="recycleTable" class="table table-striped bg-white align-middle">
  <thead>
    <tr><th>รูป</th><th>วันที่</th><th>สมาชิก</th><th>รายการ</th><th>หมวด</th><th>กก.</th><th>ขนส่ง</th><th>ที่อยู่</th><th>สถานะ</th><th>แต้ม</th><th>CO₂e ที่ลด</th><th>ตรวจรับ</th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td>
        <?php if ($r['image']): ?>
          <a href="../uploads/<?= htmlspecialchars(($r['image']) ?? '', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
            <img src="../uploads/<?= htmlspecialchars(($r['image']) ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="รูปสินค้า" style="max-width:70px;max-height:56px;object-fit:cover">
          </a>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
      <td data-order="<?= strtotime($r['created_at']) ?>"><?= ($r['created_at'] ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-') ?></td>
      <td><?= htmlspecialchars(($r['member']) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars(($r['item_name']) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars(($r['category']) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars(($r['weight_kg']) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= $r['delivery_type'] == 'pickup' ? 'นัดรับที่บ้าน' : 'นำส่งเอง' ?></td>
      <td><?= nl2br(htmlspecialchars(($r['address']) ?? '', ENT_QUOTES, 'UTF-8')) ?></td>
      <td><?= $r['status'] === 'pending' ? 'รอตรวจรับ' : ($r['status'] === 'done' ? 'สำเร็จ' : 'ปฏิเสธ') ?></td>
      <td><?= htmlspecialchars(($r['points']) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars(($r['co2_kg']) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?php if ($r['status'] == 'pending'): ?>
        <form method="post" class="d-flex gap-1">
          <input type="hidden" name="id" value="<?= htmlspecialchars(($r['id']) ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <input class="form-control form-control-sm" style="width:80px" type="number" step="0.01" min="0.01" name="weight_kg" value="<?= htmlspecialchars(($r['weight_kg']) ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="btn btn-sm btn-success">ตรวจรับ</button>
          <button type="submit" name="reject" class="btn btn-sm btn-outline-danger" onclick="return confirm('ปฏิเสธคำขอนี้?')">ปฏิเสธ</button>
        </form>
      <?php endif; ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<script>
new DataTable('#recycleTable', {
  order: []
});
</script>
<?php foot() ?>
