<?php
require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

// Check if user is admin
$stmt = $pdo->prepare('SELECT role FROM '. TBL_USERS .' WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

if ($userRole !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$targetUserId = $input['user_id'] ?? null;

if (!$targetUserId) {
    echo json_encode(['success' => false, 'error' => 'Missing user_id']);
    exit;
}

// Don't allow changing own role
if ($targetUserId == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Cannot change your own role']);
    exit;
}

try {
    // Get current role
    $stmt = $pdo->prepare('SELECT role FROM '. TBL_USERS .' WHERE id = ?');
    $stmt->execute([$targetUserId]);
    $currentRole = $stmt->fetchColumn();
    
    if (!$currentRole) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    
    // Toggle role
    $newRole = $currentRole === 'admin' ? 'user' : 'admin';
    
    $updateStmt = $pdo->prepare('UPDATE '. TBL_USERS .' SET role = ? WHERE id = ?');
    $updateStmt->execute([$newRole, $targetUserId]);
    
    echo json_encode(['success' => true, 'new_role' => $newRole]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
