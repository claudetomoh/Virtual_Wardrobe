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

// Don't allow deleting own account
if ($targetUserId == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Cannot delete your own account']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Delete user's planned outfits
    $stmt = $pdo->prepare('DELETE FROM '. TBL_OUTFITS .'_planned WHERE user_id = ?');
    $stmt->execute([$targetUserId]);
    
    // Delete user's outfits
    $stmt = $pdo->prepare('DELETE FROM '. TBL_OUTFITS .' WHERE user_id = ?');
    $stmt->execute([$targetUserId]);
    
    // Delete user's clothes
    $stmt = $pdo->prepare('DELETE FROM '. TBL_CLOTHES .' WHERE user_id = ?');
    $stmt->execute([$targetUserId]);
    
    // Delete user's vw_collections items
    $stmt = $pdo->prepare('DELETE FROM '. TBL_COLLECTION_ITEMS .' WHERE collection_id IN (SELECT id FROM '. TBL_COLLECTIONS .' WHERE user_id = ?)');
    $stmt->execute([$targetUserId]);
    
    // Delete user's collections
    $stmt = $pdo->prepare('DELETE FROM '. TBL_COLLECTIONS .' WHERE user_id = ?');
    $stmt->execute([$targetUserId]);
    
    // Delete user
    $stmt = $pdo->prepare('DELETE FROM '. TBL_USERS .' WHERE id = ?');
    $stmt->execute([$targetUserId]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
