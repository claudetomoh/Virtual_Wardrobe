<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_path('src/clothes/list.php'));
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    header('Location: ' . url_path('src/clothes/list.php'));
    exit;
}

$redirect = $_POST['redirect'] ?? url_path('src/clothes/list.php');

$stmt = $pdo->prepare('UPDATE vw_clothes SET in_laundry = 0 WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
log_action($pdo, $_SESSION['user_id'], 'laundry_clear', 'clothing', null, null);
// If this is an XHR request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

header('Location: ' . $redirect);
exit;
