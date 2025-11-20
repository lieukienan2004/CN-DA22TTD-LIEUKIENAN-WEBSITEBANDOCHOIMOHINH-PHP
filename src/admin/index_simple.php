<?php
// Version đơn giản để test
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "Step 1: Session started<br>";

require_once '../config/database.php';
echo "Step 2: Database connected<br>";

require_once 'includes/auth.php';
echo "Step 3: Auth loaded<br>";

// Test query
$result = $conn->query("SELECT COUNT(*) as total FROM products");
if ($result) {
    $count = $result->fetch_assoc()['total'];
    echo "Step 4: Found $count products<br>";
} else {
    echo "Step 4: Query error: " . $conn->error . "<br>";
}

echo "<br><strong>✓ All tests passed!</strong><br>";
echo "<a href='index.php'>Try full dashboard</a>";
?>
