<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Test Search Functionality</h1>";

// Test 1: Check if search parameter works
echo "<h2>Test 1: Search Parameter</h2>";
$test_search = "gundam";
$search_safe = $conn->real_escape_string($test_search);
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 1 
        AND (p.name LIKE '%" . $search_safe . "%' OR p.description LIKE '%" . $search_safe . "%')
        LIMIT 5";
$result = $conn->query($sql);
echo "<p>Searching for: <strong>$test_search</strong></p>";
echo "<p>Found: <strong>" . $result->num_rows . "</strong> products</p>";

if ($result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['name']) . " - " . number_format($row['price']) . "đ</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>No products found!</p>";
}

// Test 2: Check search suggestions API
echo "<h2>Test 2: Search Suggestions API</h2>";
echo "<p>Testing API endpoint: <code>search_suggestions.php?q=gundam</code></p>";
echo "<p><a href='search_suggestions.php?q=gundam' target='_blank'>Click to test API</a></p>";

// Test 3: Check if products exist
echo "<h2>Test 3: Total Products in Database</h2>";
$total = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 1")->fetch_assoc();
echo "<p>Total active products: <strong>" . $total['count'] . "</strong></p>";

// Test 4: List all product names
echo "<h2>Test 4: All Product Names</h2>";
$all_products = $conn->query("SELECT name FROM products WHERE status = 1 LIMIT 10");
echo "<ul>";
while ($row = $all_products->fetch_assoc()) {
    echo "<li>" . htmlspecialchars($row['name']) . "</li>";
}
echo "</ul>";

echo "<hr>";
echo "<h2>Test Search Form</h2>";
echo '<form action="products.php" method="GET">
    <input type="text" name="search" placeholder="Enter search term..." style="padding: 10px; width: 300px; border: 2px solid #ec4899; border-radius: 5px;">
    <button type="submit" style="padding: 10px 20px; background: #ec4899; color: white; border: none; border-radius: 5px; cursor: pointer;">Search</button>
</form>';

echo "<hr>";
echo "<p><a href='index.php'>← Back to Home</a></p>";
?>

<style>
    body {
        font-family: 'Inter', sans-serif;
        max-width: 1000px;
        margin: 50px auto;
        padding: 20px;
        background: #f9fafb;
    }
    h1 {
        color: #ec4899;
        border-bottom: 3px solid #ec4899;
        padding-bottom: 10px;
    }
    h2 {
        color: #374151;
        margin-top: 30px;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    ul {
        background: white;
        padding: 20px 40px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    li {
        margin: 10px 0;
        padding: 5px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    code {
        background: #fef3c7;
        padding: 3px 8px;
        border-radius: 5px;
        color: #92400e;
    }
    a {
        color: #ec4899;
        text-decoration: none;
        font-weight: 600;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
