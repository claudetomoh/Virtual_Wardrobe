<?php
/**
 * Error Handler Class
 * Centralized error handling, logging, and custom error pages
 */

class ErrorHandler {
    private $pdo;
    private $logToDatabase;
    private $displayErrors;
    
    public function __construct($pdo, $logToDatabase = true, $displayErrors = false) {
        $this->pdo = $pdo;
        $this->logToDatabase = $logToDatabase;
        $this->displayErrors = $displayErrors;
        
        // Register error and exception handlers
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        // Don't log if error reporting is off
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $errorLevel = $this->getErrorLevel($errno);
        $this->logError($errorLevel, $errstr, $errfile, $errline);
        
        // Display error in development only
        if ($this->displayErrors) {
            echo "<b>Error [{$errno}]:</b> {$errstr} in <b>{$errfile}</b> on line <b>{$errline}</b><br>";
        }
        
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public function handleException($exception) {
        $this->logError(
            'critical',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        if ($this->displayErrors) {
            echo "<h1>Exception</h1>";
            echo "<p><b>Message:</b> " . $exception->getMessage() . "</p>";
            echo "<p><b>File:</b> " . $exception->getFile() . "</p>";
            echo "<p><b>Line:</b> " . $exception->getLine() . "</p>";
            echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        } else {
            $this->showErrorPage(500);
        }
    }
    
    /**
     * Handle fatal errors during shutdown
     */
    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->logError(
                'critical',
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            if (!$this->displayErrors) {
                $this->showErrorPage(500);
            }
        }
    }
    
    /**
     * Log error to database and file
     */
    public function logError($level, $message, $file = null, $line = null, $trace = null) {
        // Log to database if enabled and available
        if ($this->logToDatabase && $this->pdo) {
            try {
                $userId = $_SESSION['user_id'] ?? null;
                $ip = Security::getClientIP();
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
                
                $sql = "INSERT INTO error_logs 
                        (user_id, error_level, error_message, error_file, error_line, 
                         stack_trace, request_uri, request_method, ip_address, user_agent, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $userId, $level, $message, $file, $line, 
                    $trace, $requestUri, $requestMethod, $ip, $userAgent
                ]);
            } catch (PDOException $e) {
                // Fail silently - don't break application
                error_log("Failed to log error to database: " . $e->getMessage());
            }
        }
        
        // Always log to PHP error log
        $logMessage = "[$level] $message in $file on line $line";
        error_log($logMessage);
        
        // Log to custom file if writable
        $logFile = __DIR__ . '/../logs/app_errors.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        if (is_writable($logDir)) {
            $timestamp = date('Y-m-d H:i:s');
            $logEntry = "[{$timestamp}] [{$level}] {$message} in {$file}:{$line}\n";
            if ($trace) {
                $logEntry .= "Stack trace:\n{$trace}\n";
            }
            $logEntry .= str_repeat('-', 80) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
    }
    
    /**
     * Get error level string from PHP error constant
     */
    private function getErrorLevel($errno) {
        switch ($errno) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return 'error';
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return 'warning';
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'info';
            default:
                return 'error';
        }
    }
    
    /**
     * Show custom error page
     */
    public function showErrorPage($code = 500) {
        http_response_code($code);
        
        $errorPages = [
            404 => __DIR__ . '/../public/errors/404.php',
            403 => __DIR__ . '/../public/errors/403.php',
            500 => __DIR__ . '/../public/errors/500.php'
        ];
        
        $errorPage = $errorPages[$code] ?? $errorPages[500];
        
        if (file_exists($errorPage)) {
            require $errorPage;
        } else {
            // Fallback error page
            $this->showFallbackError($code);
        }
        exit;
    }
    
    /**
     * Show fallback error page if custom page not found
     */
    private function showFallbackError($code) {
        $messages = [
            403 => 'Forbidden - You don\'t have permission to access this resource.',
            404 => 'Page Not Found - The page you\'re looking for doesn\'t exist.',
            500 => 'Internal Server Error - Something went wrong. We\'re working on it!'
        ];
        
        $message = $messages[$code] ?? 'An error occurred.';
        
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Error {$code}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .error-message {
            font-size: 1.5rem;
            margin: 1rem 0;
        }
        .error-link {
            display: inline-block;
            margin-top: 2rem;
            padding: 1rem 2rem;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            transition: background 0.3s;
        }
        .error-link:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class='error-container'>
        <h1 class='error-code'>{$code}</h1>
        <p class='error-message'>{$message}</p>
        <a href='/' class='error-link'>← Go Home</a>
    </div>
</body>
</html>";
    }
    
    /**
     * Get recent errors from database
     */
    public function getRecentErrors($limit = 50, $level = null) {
        if (!$this->pdo) {
            return [];
        }
        
        $sql = "SELECT * FROM error_logs";
        $params = [];
        
        if ($level) {
            $sql .= " WHERE error_level = ?";
            $params[] = $level;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Mark error as resolved
     */
    public function resolveError($errorId) {
        if (!$this->pdo) {
            return false;
        }
        
        $sql = "UPDATE error_logs SET resolved = TRUE WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$errorId]);
    }
    
    /**
     * Delete old error logs
     */
    public function cleanupOldErrors($days = 30) {
        if (!$this->pdo) {
            return false;
        }
        
        $sql = "DELETE FROM error_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$days]);
    }
}
