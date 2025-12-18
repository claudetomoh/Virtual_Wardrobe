<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_path('src/planner/list.php'));
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    header('Location: ' . url_path('src/planner/list.php'));
    exit;
}

$planId = (int)($_POST['id'] ?? 0);
$redirect = $_POST['redirect'] ?? url_path('src/planner/list.php');

if ($planId <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM ' . TBL_OUTFITS_PLANNED . ' WHERE id = ? AND user_id = ?');
$stmt->execute([$planId, $_SESSION['user_id']]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);

if ($plan) {
    $del = $pdo->prepare('DELETE FROM ' . TBL_OUTFITS_PLANNED . ' WHERE id = ?');
    $del->execute([$planId]);
    log_action($pdo, $_SESSION['user_id'], 'plan_delete', 'outfit_plan', $planId, null);
    $touch = $pdo->prepare('INSERT INTO ' . TBL_PLANNER_UPDATES . ' (user_id, last_update) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_update = NOW()');
    $touch->execute([$_SESSION['user_id']]);
    if (function_exists('emit_socket_event')) {
        emit_socket_event('planner_update', ['user_id' => $_SESSION['user_id'], 'action' => 'delete', 'plan_id' => $planId]);
    }
}

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'id' => $planId]);
    exit;
}

header('Location: ' . $redirect);
exit;
