<?php
require_once __DIR__ . '/../config.php';

$success = '';
$error = '';
$debugLink = '';

function ensure_password_resets_table(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS vw_password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(128) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_exp (user_id, expires_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

ensure_password_resets_table($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            $error = 'Email is required.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM '. TBL_USERS .' WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Always show generic success to avoid enumeration
            $success = 'If an account exists for that email, a reset link has been prepared.';

            if ($user) {
                // clear previous tokens for this user
                $del = $pdo->prepare('DELETE FROM '. TBL_PASSWORD_RESETS .' WHERE user_id = ?');
                $del->execute([$user['id']]);

                $token = bin2hex(random_bytes(32));
                $expires = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');
                $ins = $pdo->prepare('INSERT INTO '. TBL_PASSWORD_RESETS .' (user_id, token, expires_at) VALUES (?, ?, ?)');
                $ins->execute([$user['id'], $token, $expires]);

                // Dev-friendly link (since no mailer configured)
                $debugLink = url_path('src/auth/reset.php?token=' . urlencode($token));
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
    <title>Forgot Password - Virtual Wardrobe</title>
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
            <h2>Forgot Password</h2>
            <p class="auth-subtitle">We'll send you a reset link</p>
        </div>

        <?php if ($error): ?>
            <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success">✅ <?= htmlspecialchars($success) ?></div>
            <?php if ($debugLink): ?>
                <div class="success" style="margin-top:0.4rem; word-break:break-all;">
                    Dev link: <a href="<?=h($debugLink)?>">Reset password</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
            <div class="form-group">
                <label><span class="label-icon icon-mail"></span>Email</label>
                <input type="email" name="email" required placeholder="Enter your account email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
            </div>
            <button type="submit" class="btn btn-primary">Send reset link</button>
        </form>

        <div class="auth-footer">
            <p>Remembered your password? <a href="login.php">Back to login</a></p>
            <a href="<?=h(url_path('public/index.php'))?>" class="back-link">← Back to Home</a>
        </div>
    </div>
</div>
</body>
</html>
