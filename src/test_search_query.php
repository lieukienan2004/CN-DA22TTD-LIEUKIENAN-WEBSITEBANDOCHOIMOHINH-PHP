<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Test Search Query</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:15px;border-radius:5px;}</style>";

// Test search
$search = "gundam";
$search_safe = $conn->real_escape_string($search);

$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 1
        AND (p.name LIKE '%" . $search_safe . "%' OR p.description LIKE '%" . $search_safe . "%')
        LIMIT 5";

echo "<h2>Testing Search Query</h2>";
echo "<p>Search term: <strong>$search</strong></p>";
echo "<h3>SQL Query:</h3>";
echo "<pre>" . htmlspecialchars($sql) . "</pre>";

try {
    $result = $conn->query($sql);
    
    if ($result) {
        echo "<p class='success'>✓ Query executed successfully!</p>";
        echo "<p>Found: <strong>" . $result->num_rows . "</strong> products</p>";
        
        if ($result->num_rows > 0) {
            echo "<h3>Results:</h3>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['name']) . " - " . number_format($row['price']) . "đ</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p class='error'>✗ Query failed!</p>";
        echo "<p>Error: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Exception: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Test Direct Search</h2>";
echo '<form method="GET" action="products.php">
    <input type="text" name="search" value="gundam" style="padding:10px;width:300px;border:2px solid #ec4899;border-radius:5px;">
    <button type="submit" style="padding:10px 20px;background:#ec4899;color:white;border:none;border-radius:5px;cursor:pointer;">Search</button>
</form>';

echo "<hr>";
echo "<p><a href='index.php'>← Back to Home</a> | <a href='products.php'>View All Products</a></p>";
?>
