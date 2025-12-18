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

$outfitId = (int)($_POST['outfit_id'] ?? 0);
$plannedFor = $_POST['planned_for'] ?? '';
$seasonHint = $_POST['season_hint'] ?? 'all';
$note = substr(trim($_POST['note'] ?? ''), 0, 255);
$redirect = $_POST['redirect'] ?? url_path('src/planner/list.php');

$validSeasons = ['spring', 'summer', 'fall', 'winter', 'all'];
if (!in_array($seasonHint, $validSeasons, true)) {
    $seasonHint = 'all';
}

if ($outfitId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $plannedFor)) {
    header('Location: ' . $redirect);
    exit;
}

// Ensure outfit belongs to user
$stmt = $pdo->prepare('SELECT id FROM ' . TBL_OUTFITS . ' WHERE id = ? AND user_id = ?');
$stmt->execute([$outfitId, $_SESSION['user_id']]);
$outfit = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$outfit) {
    header('Location: ' . $redirect);
    exit;
}

try {
    $ins = $pdo->prepare('INSERT INTO ' . TBL_OUTFITS_PLANNED . ' (user_id, outfit_id, planned_for, note, season_hint)
                           VALUES (?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE outfit_id = VALUES(outfit_id), note = VALUES(note), season_hint = VALUES(season_hint)');
    $ins->execute([$_SESSION['user_id'], $outfitId, $plannedFor, $note, $seasonHint]);
    log_action($pdo, $_SESSION['user_id'], 'plan_create', 'outfit_plan', null, json_encode(['outfit_id' => $outfitId, 'planned_for' => $plannedFor]));
    // touch vw_planner_updates for SSE clients
    $up = $pdo->prepare('INSERT INTO ' . TBL_PLANNER_UPDATES . ' (user_id, last_update) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_update = NOW()');
    $up->execute([$_SESSION['user_id']]);
    // emit to socket server for real-time updates
    if (function_exists('emit_socket_event')) {
        emit_socket_event('planner_update', ['user_id' => $_SESSION['user_id'], 'action' => 'create', 'outfit_id' => $outfitId, 'planned_for' => $plannedFor]);
    }
} catch (Throwable $e) {
    // Swallow errors for now
}
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'planned_for' => $plannedFor, 'outfit_id' => $outfitId]);
    exit;
}

header('Location: ' . $redirect);
exit;
