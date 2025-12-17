<?php
require_once __DIR__ . '/../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'All fields are required';
        } else if ($password !== $confirm) {
            $error = 'Passwords do not match';
        } else if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM '. TBL_USERS .' WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO '. TBL_USERS .' (name, email, password) VALUES (?, ?, ?)');
                $stmt->execute([$name, $email, $hash]);
                $userId = $pdo->lastInsertId();
                session_regenerate_id(true);
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                header('Location: ' . url_path('src/clothes/list.php'));
                exit;
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
    <title>Register - Virtual Wardrobe</title>
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
                <h2>Create Account</h2>
                <p class="auth-subtitle">Join Virtual Wardrobe today</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                <div class="form-group">
                    <label><span class="label-icon icon-user"></span>Name</label>
                    <input type="text" name="name" required placeholder="Enter your name" value="<?=htmlspecialchars($_POST['name'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label><span class="label-icon icon-mail"></span>Email</label>
                    <input type="email" name="email" required placeholder="Enter your email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label><span class="label-icon icon-lock"></span>Password</label>
                    <input type="password" name="password" required placeholder="Enter password (min 6 characters)">
                </div>
                <div class="form-group">
                    <label><span class="label-icon icon-lock"></span>Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="Confirm your password">
                </div>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p><a href="forgot.php">Forgot your password?</a></p>
                <a href="<?=h(url_path('public/index.php'))?>" class="back-link">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
