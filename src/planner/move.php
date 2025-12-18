<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$date = $_POST['date'] ?? '';
if ($id <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM ' . TBL_OUTFITS_PLANNED . ' WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$plan) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Plan not found']);
    exit;
}

try {
    $up = $pdo->prepare('UPDATE ' . TBL_OUTFITS_PLANNED . ' SET planned_for = ? WHERE id = ?');
    $up->execute([$date, $id]);
    log_action($pdo, $_SESSION['user_id'], 'plan_move', 'outfit_plan', $id, json_encode(['date' => $date]));
    $touch = $pdo->prepare('INSERT INTO ' . TBL_PLANNER_UPDATES . ' (user_id, last_update) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_update = NOW()');
    $touch->execute([$_SESSION['user_id']]);
    if (function_exists('emit_socket_event')) {
        emit_socket_event('planner_update', ['user_id' => $_SESSION['user_id'], 'action' => 'move', 'plan_id' => $id, 'planned_for' => $date]);
    }
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'DB error']);
}
exit;
