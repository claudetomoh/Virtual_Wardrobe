<?php
require_once __DIR__ . '/../config.php';

// Destroy active session record
if (isset($_SESSION['user_id'])) {
    // $security->destroyActiveSession($_SESSION['user_id']); // Disabled - table not in schema
    $security->logSecurityEvent('logout', $_SESSION['user_id'], ['ip' => Security::getClientIP()]);
}

// Session is started in config.php; clear and destroy safely.
$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE) {
	session_destroy();
}

header('Location: ' . url_path('public/index.php'));
exit;
?>