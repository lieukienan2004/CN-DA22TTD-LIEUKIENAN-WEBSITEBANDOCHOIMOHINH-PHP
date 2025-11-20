<?php
// File test đơn giản
echo "PHP đang hoạt động!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";

// Test database connection
try {
    require_once '../config/database.php';
    echo "✓ Database connected successfully!<br>";
    echo "Database: " . DB_NAME . "<br>";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='index.php'>Go to Admin Dashboard</a>";
?>
