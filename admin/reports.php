<?php
require '../init.php';
require __DIR__ . '/../controllers/admin_reports.php';
head('รายงานผลการรีไซเคิล');
?>
<style>
  @media print {
    @page { size: landscape; }
    .table-responsive { overflow: visible !important; }
    .table { font-size: 10pt; }
  }
</style>
<button class="btn btn-success mb-3 d-print-none" onclick="window.print()">พิมพ์รายงาน</button>
<p>วันที่พิมพ์: <?= date('d/m/Y H:i') ?></p>
<p>จำนวนรายการ: <?= count($rows) ?> · ลดคาร์บอนได้รวม <?= number_format($totalCo2, 2) ?> kgCO₂e</p>
<div class="table-responsive">
<table class="table table-striped bg-white">
  <thead>
    <tr>
      <th>วันที่</th><th>สมาชิก</th><th>หมวด</th><th>รายการ</th>
      <th>น้ำหนัก (กก.)</th><th>สถานะ</th><th>แต้ม</th><th>ลด CO₂e (kg)</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= ($r['created_at'] ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-') ?></td>
      <td><?= htmlspecialchars(($r['member']) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars(($r['category']) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars(($r['item_name']) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= number_format((float) $r['weight_kg'], 2) ?></td>
      <td><?= htmlspecialchars(($r['status'] === 'pending' ? 'รอตรวจรับ' : ($r['status'] === 'done' ? 'สำเร็จ' : 'ปฏิเสธ')) ?? '', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= (int) $r['points'] ?></td>
      <td><?= number_format((float) $r['co2_kg'], 2) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php foot() ?>
