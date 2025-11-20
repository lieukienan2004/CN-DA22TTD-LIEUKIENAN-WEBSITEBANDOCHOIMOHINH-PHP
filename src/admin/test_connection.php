<?php
// Test database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection</h2>";

require_once '../config/database.php';

echo "<p>✅ Database connected successfully!</p>";

// Test tables
$tables = ['products', 'orders', 'users', 'contact_messages', 'categories'];

echo "<h3>Checking Tables:</h3>";
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>✅ Table '$table': $count rows</p>";
    } else {
        echo "<p>❌ Table '$table': Error - " . $conn->error . "</p>";
    }
}

// Test low stock query
echo "<h3>Testing Low Stock Query:</h3>";
$result = $conn->query("
    SELECT p.*, c.name as category_name,
           CASE 
               WHEN stock = 0 THEN 'out'
               WHEN stock <= 3 THEN 'critical'
               WHEN stock <= 5 THEN 'low'
               ELSE 'warning'
           END as stock_level
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE stock <= 5 AND status = 1
    ORDER BY stock ASC 
    LIMIT 8
");

if ($result) {
    echo "<p>✅ Low stock query successful! Found " . $result->num_rows . " products</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<p>- {$row['name']}: Stock = {$row['stock']}</p>";
    }
} else {
    echo "<p>❌ Low stock query failed: " . $conn->error . "</p>";
}

echo "<h3>All tests completed!</h3>";
echo "<p><a href='index.php'>Go to Dashboard</a></p>";
?>
