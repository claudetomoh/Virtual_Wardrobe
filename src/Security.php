<?php
/**
 * Security Helper Class
 * Provides advanced security features including rate limiting,
 * session management, and security headers
 */

class Security {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Check if IP address or email has exceeded login attempts
     * @param string $identifier (IP address or email)
     * @param string $type ('ip' or 'email')
     * @param int $maxAttempts Maximum failed attempts allowed
     * @param int $timeWindow Time window in minutes
     * @return bool True if rate limit exceeded
     */
    public function checkRateLimit($identifier, $type = 'ip', $maxAttempts = 5, $timeWindow = 15) {
        $column = ($type === 'email') ? 'email' : 'ip_address';
        $timeLimit = date('Y-m-d H:i:s', strtotime("-{$timeWindow} minutes"));
        
        $sql = "SELECT COUNT(*) as attempt_count 
                FROM '. TBL_LOGIN_ATTEMPTS .' 
                WHERE {$column} = ? 
                AND attempted_at > ? 
                AND success = FALSE";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier, $timeLimit]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['attempt_count'] >= $maxAttempts;
    }
    
    /**
     * Log a login attempt
     * @param string $ipAddress
     * @param string $email
     * @param bool $success
     */
    public function logLoginAttempt($ipAddress, $email = null, $success = false) {
        $sql = "INSERT INTO '. TBL_LOGIN_ATTEMPTS .' (ip_address, email, success, attempted_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$ipAddress, $email, $success ? 1 : 0]);
        
        // Clean up old attempts (older than 24 hours)
        $this->cleanupOldAttempts();
    }
    
    /**
     * Clear login attempts for successful login
     * @param string $identifier
     * @param string $type
     */
    public function clearLoginAttempts($identifier, $type = 'ip') {
        $column = ($type === 'email') ? 'email' : 'ip_address';
        $sql = "DELETE FROM '. TBL_LOGIN_ATTEMPTS .' WHERE {$column} = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier]);
    }
    
    /**
     * Clean up login attempts older than 24 hours
     */
    private function cleanupOldAttempts() {
        $sql = "DELETE FROM '. TBL_LOGIN_ATTEMPTS .' WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $this->pdo->exec($sql);
    }
    
    /**
     * Get client IP address (supports proxies)
     * @return string
     */
    public static function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Set security headers to prevent common attacks
     */
    public static function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (adjust as needed)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com; " .
               "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
               "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com data:; " .
               "img-src 'self' data: https: blob:; " .
               "connect-src 'self' http://localhost:3000 ws://localhost:3000;";
        header("Content-Security-Policy: {$csp}");
        
        // HSTS (HTTP Strict Transport Security) - only for HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Permissions Policy (formerly Feature Policy)
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    }
    
    /**
     * Enhanced session configuration with security best practices
     */
    public static function configureSession() {
        // Session cookie configuration
        $sessionConfig = [
            'lifetime' => 0, // Session cookie (expires when browser closes)
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // HTTPS only
            'httponly' => true, // Prevent JavaScript access
            'samesite' => 'Lax' // CSRF protection
        ];
        
        session_set_cookie_params($sessionConfig);
        
        // Additional session security
        ini_set('session.use_strict_mode', '1'); // Reject uninitialized session IDs
        ini_set('session.cookie_httponly', '1'); // Prevent XSS
        ini_set('session.use_only_cookies', '1'); // Prevent session fixation
        ini_set('session.cookie_secure', $sessionConfig['secure'] ? '1' : '0');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Session timeout (30 minutes of inactivity)
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            $timeout = 30 * 60; // 30 minutes
            if (time() - $_SESSION['LAST_ACTIVITY'] > $timeout) {
                session_unset();
                session_destroy();
                session_start();
            }
        }
        $_SESSION['LAST_ACTIVITY'] = time();
        
        // Session IP validation (optional - can break some legitimate use cases)
        if (isset($_SESSION['IP_ADDRESS'])) {
            if ($_SESSION['IP_ADDRESS'] !== self::getClientIP()) {
                // IP changed - possible session hijacking
                session_unset();
                session_destroy();
                session_start();
            }
        } else {
            $_SESSION['IP_ADDRESS'] = self::getClientIP();
        }
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } else if (time() - $_SESSION['CREATED'] > 3600) {
            // Regenerate every hour
            session_regenerate_id(true);
            $_SESSION['CREATED'] = time();
        }
    }
    
    /**
     * Validate and sanitize user input
     * @param mixed $input
     * @param string $type (email, url, int, float, string, html)
     * @return mixed Sanitized input or false on failure
     */
    public static function sanitizeInput($input, $type = 'string') {
        switch ($type) {
            case 'email':
                $sanitized = filter_var($input, FILTER_SANITIZE_EMAIL);
                return filter_var($sanitized, FILTER_VALIDATE_EMAIL) ? $sanitized : false;
                
            case 'url':
                $sanitized = filter_var($input, FILTER_SANITIZE_URL);
                return filter_var($sanitized, FILTER_VALIDATE_URL) ? $sanitized : false;
                
            case 'int':
                return filter_var($input, FILTER_VALIDATE_INT) !== false ? 
                       (int)$input : false;
                
            case 'float':
                return filter_var($input, FILTER_VALIDATE_FLOAT) !== false ? 
                       (float)$input : false;
                
            case 'html':
                // Allow safe HTML (using htmlspecialchars)
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
                
            case 'string':
            default:
                // Remove all HTML tags and trim
                return trim(strip_tags($input));
        }
    }
    
    /**
     * Generate secure random token
     * @param int $length Token length
     * @return string Hex token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Password strength validator
     * @param string $password
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Log security events
     * @param string $event Event type
     * @param int $userId User ID (optional)
     * @param array $details Additional details
     */
    public function logSecurityEvent($event, $userId = null, $details = []) {
        $ip = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $detailsJson = json_encode($details);
        
        $sql = "INSERT INTO '. TBL_AUDIT_LOG .' (user_id, action, ip_address, user_agent, details, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $event, $ip, $userAgent, $detailsJson]);
        } catch (PDOException $e) {
            // Fail silently - don't break application
            error_log("Security event logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check if user session is valid
     * @param int $userId
     * @return bool
     */
    public function validateUserSession($userId) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $userId) {
            return false;
        }
        
        // Check if session exists in active_sessions table
        $sessionId = session_id();
        $sql = "SELECT id FROM active_sessions 
                WHERE user_id = ? AND session_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $sessionId]);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Create active session record
     * @param int $userId
     */
    public function createActiveSession($userId) {
        $sessionId = session_id();
        $ip = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // Remove old sessions for this user (keep last 5)
        $sql = "DELETE FROM active_sessions 
                WHERE user_id = ? AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM active_sessions 
                        WHERE user_id = ? 
                        ORDER BY last_activity DESC 
                        LIMIT 5
                    ) AS keep_sessions
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $userId]);
        
        // Insert new session
        $sql = "INSERT INTO active_sessions (user_id, session_id, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $sessionId, $ip, $userAgent]);
    }
    
    /**
     * Update session activity timestamp
     * @param int $userId
     */
    public function updateSessionActivity($userId) {
        $sessionId = session_id();
        $sql = "UPDATE active_sessions SET last_activity = NOW() 
                WHERE user_id = ? AND session_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $sessionId]);
    }
    
    /**
     * Destroy active session
     * @param int $userId
     */
    public function destroyActiveSession($userId) {
        $sessionId = session_id();
        $sql = "DELETE FROM active_sessions WHERE user_id = ? AND session_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $sessionId]);
    }
}
