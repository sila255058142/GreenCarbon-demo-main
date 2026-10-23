<?php
function head($title, $show = true) {
    $u = me();
    $menu = !$u ? [] : ($u['role'] == 'admin' ? MENU_ADMIN : MENU_USER);
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> | GreenLoop</title>
    <link rel="stylesheet" href="<?= UP ?>assets/bootstrap.min.css">
    <link rel="stylesheet" href="<?= UP ?>assets/style.css">
    <script src="<?= UP ?>assets/chart.umd.js"></script>
    </head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark d-print-none">
        <div class="container">
            <a class="navbar-brand" href="<?= UP ?>index.php">♻ GreenLoop</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <div class="navbar-nav me-auto">
                    <?php foreach ($menu as $file => $name): ?>
                        <a class="nav-link" href="<?= UP . $file ?>"><?= $name ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="navbar-nav align-items-lg-center">
                    <?php if (!$u): ?>
                        <a class="nav-link" href="<?= UP ?>login.php">เข้าสู่ระบบ</a>
                        <a class="nav-link" href="<?= UP ?>register.php">สมัครสมาชิก</a>
                    <?php else: ?>
                        <a class="nav-link" href="<?= UP ?>notifications.php">
                            🔔 <span class="badge bg-danger"><?= val('SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND user_id <=> ?', [inbox()]) ?></span>
                        </a>
                        <span class="navbar-text text-white mx-2">
                            <?= pic($u['avatar'], 28) ?> <?= e($u['name']) ?>
                        </span>
                        <a class="nav-link" href="<?= UP ?>logout.php">ออกจากระบบ</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="container py-4">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash'][1] ?>">
                <?= e($_SESSION['flash'][0]) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        <?php if ($show): ?>
            <h3 class="mb-3"><?= $title ?></h3>
        <?php endif; ?>
<?php
}