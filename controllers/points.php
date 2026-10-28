<?php
require_once __DIR__ . '/bootstrap.php';
$u = me();
if (!$u) go('login.php', 'กรุณาเข้าสู่ระบบก่อน', 'warning');

$sum = (($statement = $db->prepare("SELECT COUNT(*) AS n, COALESCE(SUM(co2_kg), 0) AS co2 FROM recycles WHERE status = 'done' AND user_id = ?")) && $statement->bind_param(str_repeat('s', count([$u['id']])), ...[$u['id']]) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_assoc();
$top = ($db->query("SELECT id, name, points FROM users WHERE role = 'user' AND status = 'active' ORDER BY points DESC LIMIT 5"))->fetch_all(MYSQLI_ASSOC);
$medals = ['🥇', '🥈', '🥉', '4', '5'];
$points = (int) $u['points'];
$currentLevel = $points >= 600 ? '🌍 Net Zero Hero' : ($points >= 300 ? '🌳 ต้นไม้' : ($points >= 100 ? '🌱 ต้นกล้า' : '🌰 เมล็ดพันธุ์'));
$next = $points >= 600 ? 0 : ($points >= 300 ? 600 : ($points >= 100 ? 300 : 100));
$nextLevel = $points >= 300 ? '🌍 Net Zero Hero' : ($points >= 100 ? '🌳 ต้นไม้' : '🌱 ต้นกล้า');
