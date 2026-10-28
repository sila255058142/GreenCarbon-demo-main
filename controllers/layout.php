<?php
require_once __DIR__ . '/bootstrap.php';

function notification_count() {
    global $db;
    $u = me();
    $userId = $u['role'] === 'admin' ? null : $u['id'];
    return ((($statement = $db->prepare('SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND user_id <=> ?')) && $statement->bind_param('s', $userId) && $statement->execute() ? ($statement->get_result() ?: true) : false)->fetch_row() ?? [])[0] ?? null;
}
