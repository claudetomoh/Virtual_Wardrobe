<?php
// Clear PHP opcode cache to force reload of updated files
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared successfully!<br>";
} else {
    echo "ℹ️ OPcache not available or not enabled.<br>";
}

// Clear realpath cache
clearstatcache(true);
echo "✅ Realpath cache cleared!<br>";

echo "<br><strong>Now delete this file and reload your dashboard.</strong><br>";
echo "<br><a href='src/dashboard.php'>Go to Dashboard</a>";
