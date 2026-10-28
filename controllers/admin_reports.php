<?php
require_once __DIR__ . '/bootstrap.php';
$admin = me();
if (!$admin) go('../login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');
if ($admin['role'] !== 'admin') go('../index.php', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'danger');

$rows = ($db->query("SELECT r.created_at, u.name AS member, c.name AS category, r.item_name,
        r.weight_kg, r.status, r.points, r.co2_kg
    FROM recycles r
    JOIN users u ON u.id = r.user_id
    JOIN categories c ON c.id = r.category_id
    ORDER BY r.created_at DESC"))->fetch_all(MYSQLI_ASSOC);
$totalCo2 = array_sum(array_column($rows, 'co2_kg'));
