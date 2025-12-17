<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_path('src/outfits/list.php'));
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    header('Location: ' . url_path('src/outfits/list.php'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$success = false;
if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM '. TBL_OUTFITS .' WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    if ($stmt->rowCount() > 0) {
        $success = true;
        log_action($pdo, $_SESSION['user_id'], 'outfit_delete', 'outfit', $id, null);
        $touch = $pdo->prepare('INSERT INTO '. TBL_PLANNER_UPDATES .' (user_id, last_update) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_update = NOW()');
        $touch->execute([$_SESSION['user_id']]);
        if (function_exists('emit_socket_event')) {
            emit_socket_event('planner_update', ['user_id' => $_SESSION['user_id'], 'action' => 'outfit_delete', 'outfit_id' => $id]);
        }
    }
}

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
if ($isAjax) {
    header('Content-Type: application/json');
    http_response_code($success ? 200 : 404);
    echo json_encode(['success' => $success, 'id' => $id]);
    exit;
}

header('Location: ' . url_path('src/outfits/list.php'));
exit;
?>
