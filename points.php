<?php
// แต้มสะสม ระดับสมาชิก และอันดับ (กลไกสร้างแรงจูงใจ)
require 'init.php';
require __DIR__ . '/controllers/points.php';
$medals = ['🥇', '🥈', '🥉', '4', '5'];
$points = (int) $u['points'];
$currentLevel = $points >= 600 ? '🌍 Net Zero Hero' : ($points >= 300 ? '🌳 ต้นไม้' : ($points >= 100 ? '🌱 ต้นกล้า' : '🌰 เมล็ดพันธุ์'));
$next = $points >= 600 ? 0 : ($points >= 300 ? 600 : ($points >= 100 ? 300 : 100));
$nextLevel = $points >= 300 ? '🌍 Net Zero Hero' : ($points >= 100 ? '🌳 ต้นไม้' : '🌱 ต้นกล้า');
head('แต้มของฉัน', false);
?>
<div class="hero text-center mb-4">
  <div class="fs-1" aria-hidden="true">👤</div>
  <div class="fs-1 lh-sm"><?= $currentLevel ?></div>
  <p class="lead"><?= $u['points'] ?> แต้ม<?= $next ? ' · อีก ' . ($next - $u['points']) . ' แต้ม จะเลื่อนเป็น ' . $nextLevel : ' · ระดับสูงสุดแล้ว' ?></p>
  <?php if ($next): ?>
    <div class="progress"><div class="progress-bar bg-warning" style="width:<?= round($u['points'] / $next * 100) ?>%"></div></div>
  <?php endif; ?>
</div>
<div class="row justify-content-center">
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">✅ รีไซเคิลสำเร็จ</small><h4 class="text-success mb-0"><?= $sum['n'] ?> ครั้ง</h4></div></div>
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">🌍 คาร์บอนที่ลดได้</small><h4 class="text-success mb-0"><?= $sum['co2'] ?> kgCO₂e</h4></div></div>
  <div class="col-6 col-lg-3"><div class="card card-body box text-center mb-3"><small class="text-muted">🌳 เทียบเท่าต้นไม้</small><h4 class="text-success mb-0"><?= round($sum['co2'] / 9.5, 1) ?> ต้น</h4></div></div>
</div>
<div class="row">
  <div class="col-lg-6">
    <div class="card card-body mb-3">
      <h5>🏅 อันดับสมาชิกแต้มสูงสุด</h5>
      <table class="table mb-0">
        <?php foreach ($top as $i => $t): ?>
          <?php $memberLevel = $t['points'] >= 600 ? '🌍 Net Zero Hero' : ($t['points'] >= 300 ? '🌳 ต้นไม้' : ($t['points'] >= 100 ? '🌱 ต้นกล้า' : '🌰 เมล็ดพันธุ์')); ?>
          <tr class="<?= $t['id'] == $u['id'] ? 'table-success' : '' ?>"><td><?= $medals[$i] ?></td><td><?= htmlspecialchars(($t['name']) ?? '', ENT_QUOTES, 'UTF-8') ?></td><td><?= $memberLevel ?></td><td class="text-end"><?= $t['points'] ?> แต้ม</td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-body mb-3">
      <h5>✨ วิธีรับแต้ม</h5>
      <table class="table mb-0">
        <tr><td><a href="recycle.php">♻️ รีไซเคิลขยะอิเล็กทรอนิกส์ 1 กก.</a></td><td class="text-end">+20</td></tr>
      </table>
      <a class="btn btn-success mt-3" href="coupons.php">🎟️ ใช้แต้มแลกคูปอง</a>
    </div>
  </div>
</div>
<?php foot() ?>
