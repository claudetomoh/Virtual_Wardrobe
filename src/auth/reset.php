<?php
require_once __DIR__ . '/../config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

function load_reset(PDO $pdo, string $token): ?array {
    if (!$token) return null;
    $stmt = $pdo->prepare('SELECT pr.user_id, pr.token, pr.expires_at, u.email FROM '. TBL_PASSWORD_RESETS .' pr JOIN '. TBL_USERS .' u ON pr.user_id = u.id WHERE pr.token = ? LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return null;
    if (strtotime($row['expires_at'] ?? '0') < time()) return null;
    return $row;
}

$resetRow = load_reset($pdo, $token);
if (!$resetRow) {
    $error = 'This reset link is invalid or has expired.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $token = $_POST['token'] ?? '';
        $resetRow = load_reset($pdo, $token);
        if (!$resetRow) {
            $error = 'This reset link is invalid or has expired.';
        } else {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare('UPDATE '. TBL_USERS .' SET password = ? WHERE id = ?');
                $upd->execute([$hash, $resetRow['user_id']]);
                $del = $pdo->prepare('DELETE FROM '. TBL_PASSWORD_RESETS .' WHERE user_id = ?');
                $del->execute([$resetRow['user_id']]);
                $success = 'Your password has been reset. You can now log in.';
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
    <title>Reset Password - Virtual Wardrobe</title>
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
            <h2>Reset Password</h2>
            <p class="auth-subtitle">Choose a new password</p>
        </div>

        <?php if ($error): ?>
            <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success">✅ <?= htmlspecialchars($success) ?></div>
            <div class="auth-footer" style="margin-top:0.6rem;">
                <a class="btn btn-primary" href="login.php">Go to login</a>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
            <input type="hidden" name="token" value="<?=h($token)?>">
            <div class="form-group">
                <label><span class="label-icon icon-lock"></span>New Password</label>
                <input type="password" name="password" required placeholder="Enter new password (min 6 characters)">
            </div>
            <div class="form-group">
                <label><span class="label-icon icon-lock"></span>Confirm Password</label>
                <input type="password" name="confirm_password" required placeholder="Confirm new password">
            </div>
            <button type="submit" class="btn btn-primary" <?php if(!$resetRow || $error) echo 'disabled'; ?>>Reset Password</button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="login.php">Back to login</a>
        </div>
    </div>
</div>
</body>
</html>
