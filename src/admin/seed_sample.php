<?php
require_once __DIR__ . '/../config.php';

// WARNING: This script inserts sample data for demo purposes only.
// Run via: php src/admin/seed_sample.php

if (php_sapi_name() !== 'cli') {
    echo "Run this script from the command line.\n";
    exit;
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT id FROM '. TBL_USERS .' WHERE email = ? LIMIT 1');
    $stmt->execute(['demo@wardrobe.local']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $hash = password_hash('demopass', PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO '. TBL_USERS .' (name, email, password) VALUES (?, ?, ?)');
        $ins->execute(['Demo User', 'demo@wardrobe.local', $hash]);
        $userId = $pdo->lastInsertId();
    } else {
        $userId = $user['id'];
    }

    // Add sample vw_clothes (if none exist)
    $count = $pdo->prepare('SELECT COUNT(*) FROM '. TBL_CLOTHES .' WHERE user_id = ?');
    $count->execute([$userId]);
    if ((int)$count->fetchColumn() === 0) {
        // Using placeholder images from a CDN
        $clothes = [
            ['Blue Denim Jacket','top','Blue','https://images.unsplash.com/photo-1551028719-00167b16eac5?w=400&h=400&fit=crop'],
            ['White Tee','top','White','https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop'],
            ['Black Jeans','bottom','Black','https://images.unsplash.com/photo-1542272454315-7f6d6a2c4b90?w=400&h=400&fit=crop'],
            ['Sneakers','shoes','White','https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop'],
            ['Leather Belt','accessory','Brown','https://images.unsplash.com/photo-1624222247344-550fb60583bd?w=400&h=400&fit=crop']
        ];
        $ins = $pdo->prepare('INSERT INTO '. TBL_CLOTHES .' (user_id, name, category, colour, image_path) VALUES (?, ?, ?, ?, ?)');
        foreach ($clothes as $c) {
            $ins->execute([$userId, $c[0], $c[1], $c[2], $c[3]]);
        }
    }

    // Add a sample outfit
    $stmt = $pdo->prepare('SELECT id FROM '. TBL_OUTFITS .' WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        $cids = $pdo->prepare('SELECT id FROM '. TBL_CLOTHES .' WHERE user_id = ? LIMIT 3');
        $cids->execute([$userId]);
        $rows = $cids->fetchAll(PDO::FETCH_COLUMN);
        $ins2 = $pdo->prepare('INSERT INTO '. TBL_OUTFITS .' (user_id, top_id, bottom_id, shoe_id, accessory_id, title) VALUES (?, ?, ?, ?, ?, ?)');
        $ins2->execute([$userId, $rows[0] ?? null, $rows[1] ?? null, $rows[2] ?? null, null, 'Casual Sample']);
    }

    $pdo->commit();
    echo "Sample data inserted for user decomo@wardrobe.local (password: demopass)\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}

?>
