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

if ($outfit) {
    $del = $pdo->prepare('DELETE FROM '. TBL_SHARED_OUTFITS .' WHERE outfit_id = ? AND user_id = ?');
    $del->execute([$outfitId, $_SESSION['user_id']]);
}

header('Location: ' . $redirect);
exit;
