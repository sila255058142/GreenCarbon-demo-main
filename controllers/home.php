<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if ($u && $u['role'] === 'admin') go('admin/index.php');
$userLevel = '';
if ($u) $userLevel = $u['points'] >= 600 ? '🌍 Net Zero Hero' : ($u['points'] >= 300 ? '🌳 ต้นไม้' : ($u['points'] >= 100 ? '🌱 ต้นกล้า' : '🌰 เมล็ดพันธุ์'));
$news = $db->query('SELECT * FROM contents WHERE start_at <= NOW() AND (end_at IS NULL OR end_at > NOW()) ORDER BY start_at DESC')->fetch_all(MYSQLI_ASSOC);
$featuredNews = $news ? array_shift($news) : null;
$sum = $db->query("SELECT COUNT(*) AS n, COALESCE(SUM(weight_kg), 0) AS kg, COALESCE(SUM(co2_kg), 0) AS co2 FROM recycles WHERE status = 'done'")->fetch_assoc();
$memberCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetch_row()[0];
