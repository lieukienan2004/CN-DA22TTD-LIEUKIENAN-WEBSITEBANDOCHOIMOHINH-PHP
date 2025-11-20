<?php
require_once 'config/database.php';

// Lấy tất cả sản phẩm
$result = $conn->query("SELECT id, name, description FROM products");

echo "<h2>Kiểm tra và sửa mô tả sản phẩm</h2>";

while ($product = $result->fetch_assoc()) {
    echo "<h3>Sản phẩm: " . htmlspecialchars($product['name']) . "</h3>";
    echo "<p><strong>Mô tả hiện tại (raw):</strong><br>";
    echo "<pre>" . htmlspecialchars($product['description']) . "</pre></p>";
    
    // Sửa mô tả
    $fixed_description = $product['description'];
    
    // Loại bỏ slashes
    $fixed_description = stripslashes($fixed_description);
    
    // Chuyển đổi \\r\\n thành xuống dòng thực
    $fixed_description = str_replace(['\\r\\n', '\\r', '\\n'], "\n", $fixed_description);
    
    echo "<p><strong>Mô tả sau khi sửa:</strong><br>";
    echo nl2br(htmlspecialchars($fixed_description)) . "</p>";
    
    // Cập nhật vào database
    $stmt = $conn->prepare("UPDATE products SET description = ? WHERE id = ?");
    $stmt->bind_param("si", $fixed_description, $product['id']);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Đã cập nhật thành công</p>";
    } else {
        echo "<p style='color: red;'>✗ Lỗi cập nhật: " . $conn->error . "</p>";
    }
    
    echo "<hr>";
}

echo "<p><strong>Hoàn tất!</strong> <a href='products.php'>Xem sản phẩm</a></p>";
?>
