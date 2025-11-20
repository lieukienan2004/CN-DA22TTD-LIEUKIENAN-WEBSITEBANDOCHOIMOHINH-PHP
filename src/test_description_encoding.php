<?php
require_once 'config/database.php';

echo "<h2>Test Encoding Mô tả Sản phẩm</h2>";

// Test 1: Kiểm tra charset hiện tại
$result = $conn->query("SHOW VARIABLES LIKE 'character_set%'");
echo "<h3>1. Charset Settings:</h3>";
echo "<table border='1'>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Variable_name']}</td><td>{$row['Value']}</td></tr>";
}
echo "</table>";

// Test 2: Thử insert mô tả có ký tự đặc biệt
$test_description = "Gói thẻ hình FIFA 365 2025 Adrenalyn từ PANINI - Thương hiệu đến từ nước Ý.

Panini FIFA 365 Adrenalyn XL - đã trở lại. Tuyệt vời hơn bao giờ hết.

Điểm nổi bật:
- Bộ sưu tập có hơn 400 thẻ
- Mỗi Fans' Favourites đều có các phiên bản";

echo "<h3>2. Test Insert:</h3>";
echo "<p><strong>Mô tả gốc:</strong></p>";
echo "<pre>" . htmlspecialchars($test_description) . "</pre>";

// Thử với prepared statement
$stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, stock) VALUES (?, ?, ?, ?, ?)");
$category_id = 5;
$name = "Test Product - " . date('Y-m-d H:i:s');
$price = 100000;
$stock = 10;

$stmt->bind_param("issdi", $category_id, $name, $test_description, $price, $stock);

if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    echo "<p style='color: green;'>✓ Insert thành công! ID: $insert_id</p>";
    
    // Đọc lại để kiểm tra
    $check = $conn->query("SELECT description FROM products WHERE id = $insert_id")->fetch_assoc();
    echo "<p><strong>Mô tả sau khi lưu:</strong></p>";
    echo "<pre>" . htmlspecialchars($check['description']) . "</pre>";
    
    // So sánh
    if ($check['description'] === $test_description) {
        echo "<p style='color: green;'>✓ Mô tả khớp 100%</p>";
    } else {
        echo "<p style='color: red;'>✗ Mô tả bị thay đổi!</p>";
        echo "<p>Độ dài gốc: " . strlen($test_description) . "</p>";
        echo "<p>Độ dài sau khi lưu: " . strlen($check['description']) . "</p>";
    }
    
    // Xóa test product
    $conn->query("DELETE FROM products WHERE id = $insert_id");
    echo "<p style='color: blue;'>ℹ Đã xóa sản phẩm test</p>";
} else {
    echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
}

// Test 3: Kiểm tra collation của bảng products
echo "<h3>3. Collation của bảng products:</h3>";
$result = $conn->query("SHOW TABLE STATUS WHERE Name = 'products'");
$table_info = $result->fetch_assoc();
echo "<p>Collation: <strong>" . $table_info['Collation'] . "</strong></p>";

// Test 4: Kiểm tra collation của cột description
$result = $conn->query("SHOW FULL COLUMNS FROM products WHERE Field = 'description'");
$column_info = $result->fetch_assoc();
echo "<p>Collation cột description: <strong>" . ($column_info['Collation'] ?? 'NULL') . "</strong></p>";
echo "<p>Type: <strong>" . $column_info['Type'] . "</strong></p>";

echo "<hr>";
echo "<p><a href='admin/products.php'>← Quay lại quản lý sản phẩm</a></p>";
?>
