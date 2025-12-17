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

$collectionId = (int)($_POST['collection_id'] ?? 0);
$itemType = $_POST['item_type'] ?? '';
$itemId = (int)($_POST['item_id'] ?? 0);
$redirect = $_POST['redirect'] ?? url_path('src/collections/list.php');

$validTypes = ['clothing', 'outfit'];
if ($collectionId <= 0 || $itemId <= 0 || !in_array($itemType, $validTypes, true)) {
    header('Location: ' . $redirect);
    exit;
}

// Ensure collection belongs to current user
$colStmt = $pdo->prepare('SELECT id FROM '. TBL_COLLECTIONS .' WHERE id = ? AND user_id = ?');
$colStmt->execute([$collectionId, $_SESSION['user_id']]);
$collection = $colStmt->fetch(PDO::FETCH_ASSOC);
if (!$collection) {
    header('Location: ' . $redirect);
    exit;
}

// Ensure the item belongs to the user
if ($itemType === 'clothing') {
    $itemStmt = $pdo->prepare('SELECT id FROM '. TBL_CLOTHES .' WHERE id = ? AND user_id = ?');
} else {
    $itemStmt = $pdo->prepare('SELECT id FROM '. TBL_OUTFITS .' WHERE id = ? AND user_id = ?');
}
$itemStmt->execute([$itemId, $_SESSION['user_id']]);
$item = $itemStmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    header('Location: ' . $redirect);
    exit;
}

// Avoid duplicates
$dupStmt = $pdo->prepare('SELECT 1 FROM '. TBL_COLLECTION_ITEMS .' WHERE collection_id = ? AND item_type = ? AND item_id = ?');
$dupStmt->execute([$collectionId, $itemType, $itemId]);
if (!$dupStmt->fetch()) {
    $ins = $pdo->prepare('INSERT INTO '. TBL_COLLECTION_ITEMS .' (collection_id, item_type, item_id) VALUES (?, ?, ?)');
    $ins->execute([$collectionId, $itemType, $itemId]);
}

header('Location: ' . $redirect);
exit;
