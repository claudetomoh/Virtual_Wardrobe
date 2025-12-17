<?php
// One-time admin seeder. Run via: php src/admin/seed_admin.php
require_once __DIR__ . '/../config.php';

$email = 'admin@wardrobe.com';
$password = 'NewAdmin123!';
$name = 'Admin';

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT id, role FROM '. TBL_USERS .' WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Upgrade role if needed
        if ($existing['role'] !== 'admin') {
            $up = $pdo->prepare('UPDATE '. TBL_USERS .' SET role = "admin" WHERE id = ?');
            $up->execute([$existing['id']]);
            echo "Updated existing user to admin role.\n";
        } else {
            echo "Admin already exists.\n";
        }
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO '. TBL_USERS .' (name, email, password, role) VALUES (?, ?, ?, "admin")');
        $ins->execute([$name, $email, $hash]);
        echo "Admin created: {$email}\n";
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
