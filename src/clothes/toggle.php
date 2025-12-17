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
$action = $_POST['action'] ?? '';
$redirect = $_POST['redirect'] ?? url_path('src/clothes/list.php');


if ($id <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$stmt = $pdo->prepare('SELECT id, favorite, in_laundry FROM '. TBL_CLOTHES .' WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['success' => false]);
        exit;
    }
    header('Location: ' . $redirect);
    exit;
}

try {
    $success = false;
    switch ($action) {
        case 'toggle_favorite':
            $new = $item['favorite'] ? 0 : 1;
            $update = $pdo->prepare('UPDATE vw_clothes SET favorite = ? WHERE id = ? AND user_id = ?');
            $update->execute([$new, $id, $_SESSION['user_id']]);
            log_action($pdo, $_SESSION['user_id'], 'toggle_favorite', 'clothing', $id, json_encode(['new' => $new]));
            $success = true;
            break;
        case 'toggle_laundry':
            $new = $item['in_laundry'] ? 0 : 1;
            $update = $pdo->prepare('UPDATE vw_clothes SET in_laundry = ? WHERE id = ? AND user_id = ?');
            $update->execute([$new, $id, $_SESSION['user_id']]);
            log_action($pdo, $_SESSION['user_id'], 'toggle_laundry', 'clothing', $id, json_encode(['new' => $new]));
            $success = true;
            break;
        default:
            break;
    }
} catch (Throwable $e) {
    // No-op on failure; redirect regardless
}

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if ($isAjax) {
    header('Content-Type: application/json');
    http_response_code(!empty($success) ? 200 : 400);
    echo json_encode(['success' => !empty($success), 'favorite' => $new ?? null, 'in_laundry' => isset($new) ? $new : null]);
    exit;
}

header('Location: ' . $redirect);
exit;
