<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Categories API</h2>";

// Test lấy danh mục
echo "<h3>1. Kiểm tra bảng categories:</h3>";
$result = $conn->query("SELECT id, name, status FROM categories");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Name</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['status']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Không có danh mục nào</p>";
}

// Test API
echo "<h3>2. Test API get_categories:</h3>";
$result = $conn->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name");
$categories = $result->fetch_all(MYSQLI_ASSOC);
echo "<pre>" . json_encode(['success' => true, 'categories' => $categories], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

// Test sản phẩm
echo "<h3>3. Test sản phẩm theo danh mục:</h3>";
$result = $conn->query("SELECT category_id, COUNT(*) as count FROM products WHERE status = 1 GROUP BY category_id");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Category ID</th><th>Số sản phẩm</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['category_id']}</td><td>{$row['count']}</td></tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a></p>";
?>
