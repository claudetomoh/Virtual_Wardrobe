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

$stmt = $pdo->prepare('SELECT id FROM '. TBL_OUTFITS .' WHERE id = ? AND user_id = ?');
$stmt->execute([$outfitId, $_SESSION['user_id']]);
$outfit = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$outfit) {
    header('Location: ' . $redirect);
    exit;
}

$token = bin2hex(random_bytes(16));
$daysValid = (int)($_POST['expiry_days'] ?? 7);
if ($daysValid < 1 || $daysValid > 30) {
    $daysValid = 7;
}
$expiresAt = (new DateTimeImmutable('+' . $daysValid . ' days'))->format('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();
    // Remove old shares for this outfit/user
    $del = $pdo->prepare('DELETE FROM '. TBL_SHARED_OUTFITS .' WHERE outfit_id = ? AND user_id = ?');
    $del->execute([$outfitId, $_SESSION['user_id']]);

    $ins = $pdo->prepare('INSERT INTO '. TBL_SHARED_OUTFITS .' (outfit_id, user_id, token, expires_at, is_public) VALUES (?, ?, ?, ?, 1)');
    $ins->execute([$outfitId, $_SESSION['user_id'], $token, $expiresAt]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

header('Location: ' . $redirect . (strpos($redirect, '?') === false ? '?' : '&') . 'share_token=' . urlencode($token) . '&outfit_id=' . (int)$outfitId);
exit;
