<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_path('src/collections/list.php'));
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    header('Location: ' . url_path('src/collections/list.php'));
    exit;
}

$collectionId = (int)($_POST['id'] ?? 0);
$redirect = $_POST['redirect'] ?? url_path('src/collections/list.php');

if ($collectionId <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM '. TBL_COLLECTIONS .' WHERE id = ? AND user_id = ?');
$stmt->execute([$collectionId, $_SESSION['user_id']]);
$collection = $stmt->fetch(PDO::FETCH_ASSOC);

if ($collection) {
    $del = $pdo->prepare('DELETE FROM '. TBL_COLLECTIONS .' WHERE id = ?');
    $del->execute([$collectionId]);
}

header('Location: ' . $redirect);
exit;
