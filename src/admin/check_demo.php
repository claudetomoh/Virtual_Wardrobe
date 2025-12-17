<?php
require_once __DIR__ . '/../config.php';

$stmt = $pdo->query("SELECT name, image_path FROM '. TBL_CLOTHES .' WHERE user_id = (SELECT id FROM '. TBL_USERS .' WHERE email = 'demo@wardrobe.local') LIMIT 10");
echo "Demo User Clothing Items:\n";
echo str_repeat("=", 50) . "\n";
foreach ($stmt->fetchAll() as $row) {
    echo $row['name'] . "\n";
    echo "  Path: " . $row['image_path'] . "\n\n";
}
