<?php
function head($title, $show = true) {
    $u = me();
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> | GreenLoop</title>
    <base href="<?= str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? '../' : '' ?>">
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/datatables.min.css">
    <script src="assets/chart.umd.js"></script>
    <script src="assets/datatables.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark d-print-none">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">♻ GreenLoop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <div class="navbar-nav me-auto">
                    <?php if ($u && $u['role'] == 'admin'): ?>
                        <a class="nav-link" href="admin/index.php">Dashboard</a>
                        <a class="nav-link" href="admin/users.php">สมาชิก</a>
                        <a class="nav-link" href="admin/contents.php">เนื้อหา</a>
                        <a class="nav-link" href="admin/green.php">ตั้งค่า Green</a>
                        <a class="nav-link" href="admin/recycles.php">รีไซเคิล</a>
                        <a class="nav-link" href="admin/coupons.php">คูปอง</a>
                        <a class="nav-link" href="admin/reports.php">รายงาน</a>
                    <?php elseif ($u): ?>
                        <a class="nav-link" href="recycle.php">♻️ รีไซเคิล</a>
                        <a class="nav-link" href="points.php">🏆 แต้มของฉัน</a>
                        <a class="nav-link" href="coupons.php">🎟️  แลกคูปอง</a>
                        <a class="nav-link" href="profile.php">👤 โปรไฟล์</a>
                    <?php endif; ?>
                </div>
                <div class="navbar-nav align-items-lg-center">
                    <?php if (!$u): ?>
                        <a class="nav-link" href="login.php">เข้าสู่ระบบ</a>
                        <a class="nav-link" href="register.php">สมัครสมาชิก</a>
                    <?php else: ?>
                        <a class="nav-link" href="notifications.php">
                            🔔 <span class="badge bg-danger"><?= notification_count() ?></span>
                        </a>
                        <span class="navbar-text text-white mx-2">
                            <?= htmlspecialchars(($u['name']) ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="container py-4">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash'][1] ?>">
                <?= htmlspecialchars(($_SESSION['flash'][0]) ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        <?php if ($show): ?>
            <h3 class="mb-3"><?= $title ?></h3>
        <?php endif; ?>
<?php
}

function foot() { echo '</main><footer class="text-center text-muted small py-4 d-print-none">♻ GreenLoop · วัดคาร์บอน ลดด้วยการรีไซเคิล เพื่อโลกที่ยั่งยืน</footer><script src="assets/bootstrap.bundle.min.js"></script></body></html>'; }