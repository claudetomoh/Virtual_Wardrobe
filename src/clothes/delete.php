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

$id = (int)($_POST['id'] ?? 0);
$success = false;
if ($id > 0) {
    // ensure ownership
    $stmt = $pdo->prepare('SELECT image_path FROM '. TBL_CLOTHES .' WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        $deleteStmt = $pdo->prepare('DELETE FROM '. TBL_CLOTHES .' WHERE id = ? AND user_id = ?');
        $deleteStmt->execute([$id, $_SESSION['user_id']]);
        $success = true;
        log_action($pdo, $_SESSION['user_id'], 'clothes_delete', 'clothing', $id, json_encode(['image_path' => $item['image_path']]));
        // remove file if it exists
        $imagePath = __DIR__ . '/../../public/' . ltrim(str_replace(url_path('public/'), '', $item['image_path']), '/');
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
// respond with JSON if AJAX called
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
if ($isAjax) {
    header('Content-Type: application/json');
    http_response_code($success ? 200 : 404);
    echo json_encode(['success' => $success, 'id' => $id]);
    exit;
}

header('Location: ' . url_path('src/clothes/list.php'));
exit;
