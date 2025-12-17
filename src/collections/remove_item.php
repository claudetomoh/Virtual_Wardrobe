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

$collectionItemId = (int)($_POST['id'] ?? 0);
$redirect = $_POST['redirect'] ?? url_path('src/collections/list.php');

if ($collectionItemId <= 0) {
    header('Location: ' . $redirect);
    exit;
}

// Ensure the item belongs to a collection owned by the user
$stmt = $pdo->prepare('SELECT ci.id, ci.collection_id, ci.item_type, ci.item_id, c.name AS collection_name FROM '. TBL_COLLECTION_ITEMS .' ci INNER JOIN '. TBL_COLLECTIONS .' c ON ci.collection_id = c.id WHERE ci.id = ? AND c.user_id = ?');
$stmt->execute([$collectionItemId, $_SESSION['user_id']]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {
    $del = $pdo->prepare('DELETE FROM '. TBL_COLLECTION_ITEMS .' WHERE id = ?');
    $del->execute([$collectionItemId]);
    log_action(
        $pdo,
        $_SESSION['user_id'],
        'collection_item_remove',
        'collection_item',
        $collectionItemId,
        json_encode([
            'collection_id' => (int)$item['collection_id'],
            'collection_name' => $item['collection_name'],
            'item_type' => $item['item_type'],
            'item_id' => (int)$item['item_id'],
        ])
    );
}

header('Location: ' . $redirect);
exit;
