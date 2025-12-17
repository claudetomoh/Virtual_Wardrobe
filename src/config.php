<?php
// Load Security class
require_once __DIR__ . '/Security.php';

// Set security headers before any output
Security::setSecurityHeaders();

// Enhanced session configuration with security best practices
Security::configureSession();

// Global configuration and helpers
// Production/School Server Settings
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbUser = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'tomoh.ikfingeh';
$dbPass = getenv('DB_PASS') ?: getenv('MYSQL_ROOT_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: getenv('MYSQL_PWD') ?: 'STCL@ude20@?';
// read DB name from env, fallback to default
$dbName = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'webtech_2025A_tomoh_ikfingeh';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('DB Connection failed: ' . $e->getMessage());
}

// Initialize Security instance
$security = new Security($pdo);

// Initialize Error Handler
require_once __DIR__ . '/ErrorHandler.php';
$appEnv = getenv('APP_ENV') ?: 'development';
$displayErrors = ($appEnv === 'development');
$errorHandler = new ErrorHandler($pdo, true, $displayErrors);

// Set error reporting based on environment
if ($appEnv === 'production') {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

define('APP_BASE_PATH', rtrim(getenv('APP_BASE_PATH') ?: '/Virtual_Wardrobe', '/'));
define('APP_ENV', $appEnv);

// Table name prefix (for shared hosting to avoid conflicts)
define('TABLE_PREFIX', 'vw_');

// Database table names
define('TBL_USERS', TABLE_PREFIX . 'users');
define('TBL_CLOTHES', TABLE_PREFIX . 'clothes');
define('TBL_OUTFITS', TABLE_PREFIX . 'outfits');
define('TBL_OUTFITS_PLANNED', TABLE_PREFIX . 'outfits_planned');
define('TBL_SHARED_OUTFITS', TABLE_PREFIX . 'shared_outfits');
define('TBL_PASSWORD_RESETS', TABLE_PREFIX . 'password_resets');
define('TBL_AUDIT_LOG', TABLE_PREFIX . 'audit_log');
define('TBL_PLANNER_UPDATES', TABLE_PREFIX . 'planner_updates');
define('TBL_COLLECTIONS', TABLE_PREFIX . 'collections');
define('TBL_COLLECTION_ITEMS', TABLE_PREFIX . 'collection_items');
define('TBL_LOGIN_ATTEMPTS', TABLE_PREFIX . 'login_attempts');

// Socket server configuration (optional)
define('SOCKET_SERVER_URL', getenv('SOCKET_SERVER_URL') ?: 'http://localhost:3000');
define('SOCKET_API_KEY', getenv('SOCKET_API_KEY') ?: 'dev-secret-key');
define('SOCKET_JWT_SECRET', getenv('SOCKET_JWT_SECRET') ?: 'socket-secret');

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . url_path('public/index.php'));
        exit;
    }
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirectIfLoggedIn(): void
{
    if (isLoggedIn()) {
        header('Location: ' . url_path('src/clothes/list.php'));
        exit;
    }
}

function url_path(string $path): string
{
    return APP_BASE_PATH . '/' . ltrim($path, '/');
}

function log_action(PDO $pdo, ?int $userId, string $action, string $targetType, ?int $targetId = null, ?string $details = null): void
{
    try {
        $stmt = $pdo->prepare('INSERT INTO '. TBL_AUDIT_LOG .' (user_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $action, $targetType, $targetId, $details]);
    } catch (Throwable $e) {
        // Do not block user flow on audit failure
    }
}

// Emit an event to the Node/socket.io server. Non-blocking and silent on error.
function emit_socket_event(string $event, array $payload = []): void
{
    $url = rtrim(SOCKET_SERVER_URL, '/') . '/emit';
    // structure expected by socket server: { event, user_id, payload, ts, nonce }
    $ts = time();
    $nonce = bin2hex(random_bytes(12));
    $body = ['event' => $event, 'user_id' => $payload['user_id'] ?? null, 'payload' => $payload, 'ts' => $ts, 'nonce' => $nonce];
    $post = json_encode($body);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // add signed JWT to prove this request originated from the PHP app
    $emitterJwt = socket_emitter_jwt();
    $hmac = hash_hmac('sha256', $post, SOCKET_JWT_SECRET);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-SOCKET-KEY: ' . SOCKET_API_KEY,
        'X-EMITTER-JWT: ' . $emitterJwt,
        'X-EMITTER-HMAC: ' . $hmac,
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1500);
    try {
        $resp = curl_exec($ch);
        // ignore response; we don't block user flow
    } catch (Throwable $e) {
        // ignore
    }
    if (is_resource($ch)) curl_close($ch);
}

// Create signed emitter JWT (server to server) with short TTL
function socket_emitter_jwt(int $ttl = 60): string
{
    $secret = SOCKET_JWT_SECRET;
    $hdr = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $hdr = rtrim(strtr($hdr, '+/', '-_'), '=');
    $iat = time();
    $exp = $iat + $ttl;
    $payload = base64_encode(json_encode(['api_key' => SOCKET_API_KEY, 'iss' => 'php-emitter', 'iat' => $iat, 'exp' => $exp]));
    $payload = rtrim(strtr($payload, '+/', '-_'), '=');
    $sig = hash_hmac('sha256', $hdr . '.' . $payload, $secret, true);
    $sig = rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');
    return $hdr . '.' . $payload . '.' . $sig;
}

// Create a simple HS256 JWT for socket.io client auth (expires in seconds)
function socket_jwt_for_user(int $userId, int $ttl = 3600): string
{
    $secret = SOCKET_JWT_SECRET;
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $header = rtrim(strtr($header, '+/', '-_'), '=');
    $iat = time();
    $exp = $iat + $ttl;
    $payload = base64_encode(json_encode(['user_id' => $userId, 'iat' => $iat, 'exp' => $exp]));
    $payload = rtrim(strtr($payload, '+/', '-_'), '=');
    $sig = hash_hmac('sha256', $header . '.' . $payload, $secret, true);
    $sig = rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');
    return $header . '.' . $payload . '.' . $sig;
}
?>
