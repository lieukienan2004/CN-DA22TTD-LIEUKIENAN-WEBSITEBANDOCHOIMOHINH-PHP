<?php
require_once 'config/database.php';

echo "<h2>Thêm cột is_new vào bảng products</h2>";

// Kiểm tra xem cột đã tồn tại chưa
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'is_new'");

if ($check->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ Cột 'is_new' đã tồn tại!</p>";
} else {
    // Thêm cột is_new
    $sql = "ALTER TABLE products ADD COLUMN is_new TINYINT DEFAULT 0 AFTER status";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Đã thêm cột 'is_new' thành công!</p>";
    } else {
        echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
    }
}

// Hiển thị cấu trúc bảng
echo "<h3>Cấu trúc bảng products:</h3>";
$result = $conn->query("SHOW COLUMNS FROM products");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><a href='admin/products.php'>← Quay lại Quản lý Sản phẩm</a></p>";
?>
