<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Thêm cột is_new</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; border-radius: 10px; max-width: 800px; margin: 0 auto; }
        .success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { background: #dbeafe; color: #1e40af; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #ec4899; color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; }
    </style>
</head>
<body>
<div class='box'>";

echo "<h2>🔧 Thêm cột is_new vào bảng products</h2>";

// Kiểm tra xem cột đã tồn tại chưa
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'is_new'");

if ($check->num_rows > 0) {
    echo "<div class='info'>ℹ️ Cột 'is_new' đã tồn tại trong database!</div>";
    echo "<p>Bạn có thể sử dụng tính năng nhãn NEW ngay bây giờ.</p>";
} else {
    echo "<div class='info'>⏳ Đang thêm cột 'is_new'...</div>";
    
    // Thêm cột is_new
    $sql = "ALTER TABLE products ADD COLUMN is_new TINYINT DEFAULT 0 AFTER status";
    
    if ($conn->query($sql)) {
        echo "<div class='success'>✓ Đã thêm cột 'is_new' thành công!</div>";
        echo "<p>Bây giờ bạn có thể đánh dấu sản phẩm là NEW trong trang quản lý.</p>";
    } else {
        echo "<div class='error'>✗ Lỗi: " . $conn->error . "</div>";
    }
}

// Hiển thị cấu trúc bảng
echo "<h3>Cấu trúc bảng products:</h3>";
$result = $conn->query("SHOW COLUMNS FROM products");
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f3f4f6;'><th style='padding: 10px;'>Field</th><th style='padding: 10px;'>Type</th><th style='padding: 10px;'>Null</th><th style='padding: 10px;'>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    $highlight = ($row['Field'] == 'is_new') ? "style='background: #fef3c7;'" : "";
    echo "<tr $highlight>";
    echo "<td style='padding: 8px;'><strong>" . $row['Field'] . "</strong></td>";
    echo "<td style='padding: 8px;'>" . $row['Type'] . "</td>";
    echo "<td style='padding: 8px;'>" . $row['Null'] . "</td>";
    echo "<td style='padding: 8px;'>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr style='margin: 30px 0;'>";
echo "<h3>Bước tiếp theo:</h3>";
echo "<ol>";
echo "<li>Vào <strong>Admin > Quản lý Sản phẩm</strong></li>";
echo "<li>Chọn <strong>Thêm sản phẩm mới</strong> hoặc <strong>Sửa</strong> sản phẩm có sẵn</li>";
echo "<li>Tích vào checkbox <strong>'Đánh dấu là sản phẩm mới'</strong></li>";
echo "<li>Lưu sản phẩm</li>";
echo "<li>Sản phẩm sẽ hiển thị nhãn <span style='background: #dc2626; color: white; padding: 4px 12px; border-radius: 5px; font-weight: bold;'>NEW</span> ở góc trái</li>";
echo "</ol>";

echo "<div style='margin-top: 30px;'>";
echo "<a href='admin/products.php' class='btn'>📦 Quản lý Sản phẩm</a>";
echo "<a href='products.php' class='btn' style='background: #3b82f6;'>🛍️ Xem Trang Sản phẩm</a>";
echo "<a href='index.php' class='btn' style='background: #10b981;'>🏠 Trang Chủ</a>";
echo "</div>";

echo "</div></body></html>";
?>
