<?php
// Clear PHP opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ Opcache cleared!<br>";
} else {
    echo "✗ Opcache not enabled<br>";
}

// Clear realpath cache
clearstatcache(true);
echo "✓ Realpath cache cleared!<br>";

echo "<hr>";
echo "<p><a href='products.php'>Test Products Page</a></p>";
echo "<p><a href='test_search_query.php'>Test Search Query</a></p>";
?>
