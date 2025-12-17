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

$id = (int)($_POST['id'] ?? 0);
$note = substr(trim($_POST['note'] ?? ''), 0, 255);
$season = $_POST['season_hint'] ?? 'all';

$validSeasons = ['spring','summer','fall','winter','all'];
if (!in_array($season, $validSeasons, true)) $season = 'all';

$stmt = $pdo->prepare('SELECT id, user_id FROM '. TBL_OUTFITS .'_planned WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$plan) {
    header('Location: ' . url_path('src/planner/list.php'));
    exit;
}

try {
    $up = $pdo->prepare('UPDATE vw_outfits_planned SET note = ?, season_hint = ? WHERE id = ?');
    $up->execute([$note, $season, $id]);
    log_action($pdo, $_SESSION['user_id'], 'plan_update', 'outfit_plan', $id, json_encode(['note' => $note, 'season' => $season]));
    $touch = $pdo->prepare('INSERT INTO '. TBL_PLANNER_UPDATES .' (user_id, last_update) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_update = NOW()');
    $touch->execute([$_SESSION['user_id']]);
    if (function_exists('emit_socket_event')) {
        emit_socket_event('planner_update', ['user_id' => $_SESSION['user_id'], 'action' => 'update', 'plan_id' => $id, 'note' => $note, 'season' => $season]);
    }
} catch (Throwable $e) {
    // ignore
}

// Respond to AJAX calls with JSON
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'id' => $id, 'note' => $note, 'season' => $season]);
    exit;
}

header('Location: ' . url_path('src/planner/list.php'));
exit;
