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

$outfitId = (int)($_POST['id'] ?? 0);
$redirect = $_POST['redirect'] ?? url_path('src/outfits/list.php');

if ($outfitId <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$stmt = $pdo->prepare('SELECT id, top_id, bottom_id, shoe_id, accessory_id FROM '. TBL_OUTFITS .' WHERE id = ? AND user_id = ?');
$stmt->execute([$outfitId, $_SESSION['user_id']]);
$outfit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$outfit) {
    header('Location: ' . $redirect);
    exit;
}

$clothingIds = array_filter([
    $outfit['top_id'] ?? null,
    $outfit['bottom_id'] ?? null,
    $outfit['shoe_id'] ?? null,
    $outfit['accessory_id'] ?? null,
], fn($v) => $v !== null);

$now = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    $upOutfit = $pdo->prepare('UPDATE vw_outfits SET wear_count = wear_count + 1, last_worn_at = ? WHERE id = ? AND user_id = ?');
    $upOutfit->execute([$now, $outfitId, $_SESSION['user_id']]);

    if (!empty($clothingIds)) {
        $placeholders = implode(',', array_fill(0, count($clothingIds), '?'));
        $params = $clothingIds;
        array_unshift($params, $now); // last_worn_at value first
        $params[] = $_SESSION['user_id'];
        $sql = "UPDATE vw_clothes SET wear_count = wear_count + 1, last_worn_at = ?, in_laundry = 1 WHERE id IN ($placeholders) AND user_id = ?";
        $stmtClothes = $pdo->prepare($sql);
        $stmtClothes->execute($params);
    }

    $pdo->commit();
    log_action($pdo, $_SESSION['user_id'], 'outfit_worn', 'outfit', $outfitId, json_encode(['items' => $clothingIds]));
    if (function_exists('emit_socket_event')) {
        emit_socket_event('planner_update', ['user_id' => $_SESSION['user_id'], 'action' => 'wear', 'outfit_id' => $outfitId]);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'outfit_id' => $outfitId, 'items' => $clothingIds]);
    exit;
}

header('Location: ' . $redirect);
exit;
