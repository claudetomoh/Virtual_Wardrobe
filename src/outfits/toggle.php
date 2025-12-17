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
$action = $_POST['action'] ?? '';
$redirect = $_POST['redirect'] ?? url_path('src/outfits/list.php');

if ($id <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$stmt = $pdo->prepare('SELECT id, is_favorite FROM '. TBL_OUTFITS .' WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$outfit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$outfit) {
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
            $new = $outfit['is_favorite'] ? 0 : 1;
            $update = $pdo->prepare('UPDATE vw_outfits SET is_favorite = ? WHERE id = ? AND user_id = ?');
            $update->execute([$new, $id, $_SESSION['user_id']]);
                log_action($pdo, $_SESSION['user_id'], 'toggle_favorite', 'outfit', $id, json_encode(['new' => $new]));
            $success = true;
            break;
        default:
            break;
    }
} catch (Throwable $e) {
    // swallow
}

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if ($isAjax) {
    header('Content-Type: application/json');
    http_response_code(!empty($success) ? 200 : 400);
    echo json_encode(['success' => !empty($success), 'favorite' => $new ?? null]);
    exit;
}

header('Location: ' . $redirect);
exit;


