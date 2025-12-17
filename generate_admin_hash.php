<?php
// Generate correct password hash for admin123
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: {$password}\n";
echo "Hash: {$hash}\n\n";

// Verify it works
if (password_verify($password, $hash)) {
    echo "✅ Verification: SUCCESS\n\n";
} else {
    echo "❌ Verification: FAILED\n\n";
}

echo "=== SQL Command to Run in phpMyAdmin ===\n\n";
echo "INSERT INTO vw_users (name, email, password, role, created_at) \n";
echo "VALUES (\n";
echo "    'Admin User',\n";
echo "    'admin@wardrobe.com',\n";
echo "    '{$hash}',\n";
echo "    'admin',\n";
echo "    NOW()\n";
echo ");\n\n";

echo "=== Or if user exists, use UPDATE ===\n\n";
echo "UPDATE vw_users \n";
echo "SET password = '{$hash}', role = 'admin' \n";
echo "WHERE email = 'admin@wardrobe.com';\n";
