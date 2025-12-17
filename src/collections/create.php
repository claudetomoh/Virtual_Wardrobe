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

$name = substr(trim($_POST['name'] ?? ''), 0, 150);

if ($name === '') {
    header('Location: ' . url_path('src/collections/list.php'));
    exit;
}

$stmt = $pdo->prepare('INSERT INTO '. TBL_COLLECTIONS .' (user_id, name) VALUES (?, ?)');
$stmt->execute([$_SESSION['user_id'], $name]);

header('Location: ' . url_path('src/collections/list.php'));
exit;
