<?php
require_once __DIR__ . '/../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $clientIP = Security::getClientIP();

        // Check rate limiting by IP
        if ($security->checkRateLimit($clientIP, 'ip', 5, 15)) {
            $error = 'Too many login attempts. Please try again in 15 minutes.';
            $security->logSecurityEvent('rate_limit_exceeded', null, ['ip' => $clientIP, 'email' => $email]);
        } 
        // Check rate limiting by email
        else if (!empty($email) && $security->checkRateLimit($email, 'email', 5, 15)) {
            $error = 'Too many login attempts for this account. Please try again in 15 minutes.';
            $security->logSecurityEvent('rate_limit_exceeded', null, ['ip' => $clientIP, 'email' => $email]);
        }
        else if (empty($email) || empty($password)) {
            $error = 'Email and password are required';
        } else {
            $stmt = $pdo->prepare('SELECT id, name, password FROM '. TBL_USERS .' WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Successful login
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                
                // Clear login attempts
                $security->clearLoginAttempts($clientIP, 'ip');
                $security->clearLoginAttempts($email, 'email');
                
                // Log successful login
                $security->logLoginAttempt($clientIP, $email, true);
                $security->logSecurityEvent('login_success', $user['id'], ['ip' => $clientIP]);
                
                // Create active session
                $security->createActiveSession($user['id']);
                
                header('Location: ' . url_path('src/clothes/list.php'));
                exit;
            } else {
                // Failed login - log attempt
                $security->logLoginAttempt($clientIP, $email, false);
                $security->logSecurityEvent('login_failed', null, ['ip' => $clientIP, 'email' => $email]);
                $error = 'Invalid email or password';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Virtual Wardrobe</title>
    <link rel="stylesheet" href="<?=h(url_path('public/css/auth.css'))?>">
</head>
<body>
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="<?=h(url_path('public/media/wardrobe-bg.mp4'))?>" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <img src="<?=h(url_path('public/images/wardrobe-icon.png'))?>" alt="Wardrobe Icon" onerror="this.style.display='none'">
                <h2>Welcome Back</h2>
                <p class="auth-subtitle">Login to your wardrobe</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                <div class="form-group">
                    <label><span class="label-icon icon-mail"></span>Email</label>
                    <input type="email" name="email" required placeholder="Enter your email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label><span class="label-icon icon-lock"></span>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="auth-footer">
                <p><a href="forgot.php">Forgot your password?</a></p>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <a href="<?=h(url_path('public/index.php'))?>" class="back-link">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
