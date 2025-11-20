<?php
// Script để chạy update database thêm cột stock_status
require_once 'config/database.php';

echo "Đang thêm cột stock_status vào bảng products...\n";

// Kiểm tra xem cột đã tồn tại chưa
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'stock_status'");
if ($check->num_rows > 0) {
    echo "Cột stock_status đã tồn tại!\n";
} else {
    // Thêm cột stock_status
    $sql1 = "ALTER TABLE products 
             ADD COLUMN stock_status ENUM('in_stock', 'out_of_stock') DEFAULT 'in_stock' AFTER stock";
    
    if ($conn->query($sql1)) {
        echo "✓ Đã thêm cột stock_status thành công!\n";
        
        // Cập nhật giá trị dựa trên stock hiện tại
        $sql2 = "UPDATE products 
                 SET stock_status = CASE 
                     WHEN stock > 0 THEN 'in_stock'
                     ELSE 'out_of_stock'
                 END";
        
        if ($conn->query($sql2)) {
            echo "✓ Đã cập nhật trạng thái cho tất cả sản phẩm!\n";
            echo "\n=== HOÀN THÀNH ===\n";
            echo "Bạn có thể xóa file này sau khi chạy xong.\n";
        } else {
            echo "✗ Lỗi khi cập nhật trạng thái: " . $conn->error . "\n";
        }
    } else {
        echo "✗ Lỗi khi thêm cột: " . $conn->error . "\n";
    }
}

$conn->close();
?>
