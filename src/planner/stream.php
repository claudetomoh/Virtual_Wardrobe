<?php
require_once __DIR__ . '/../config.php';
requireLogin();

// SSE endpoint that notifies the client when the user's planner changes.
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
// keep connection open
set_time_limit(0);
ignore_user_abort(true);

// CRITICAL: Close the session immediately to prevent blocking other requests
$userId = $_SESSION['user_id'];
session_write_close();

$lastSent = $_GET['last'] ?? null;
try {
    $stmt = $pdo->prepare('SELECT last_update FROM ' . TBL_PLANNER_UPDATES . ' WHERE user_id = ?');
    $pollCount = 0;
    while (true) {
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $last = $row['last_update'] ?? null;
        if ($last && $last !== $lastSent) {
            $data = json_encode(['type' => 'planner_update', 'updated' => $last]);
            echo "data: $data\n\n";
            @ob_flush(); @flush();
            $lastSent = $last;
            $pollCount = 0;
        }
        // send a comment keep-alive periodically
        echo ": keepalive\n\n";
        @ob_flush(); @flush();
        sleep(2);
        $pollCount++;
        if ($pollCount > 600) break; // self-terminate after long running
    }
} catch (Throwable $e) {
    // ignore errors; push a simple error event
    $msg = json_encode(['type' => 'error', 'message' => 'stream error']);
    echo "data: $msg\n\n";
    @ob_flush(); @flush();
}
exit;
