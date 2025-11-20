<?php
require_once 'config/database.php';

// Đồng bộ stock_status dựa trên số lượng stock
$sql = "UPDATE products 
        SET stock_status = CASE 
            WHEN stock > 0 THEN 'in_stock'
            ELSE 'out_of_stock'
        END";

if ($conn->query($sql)) {
    echo "✅ Đã đồng bộ trạng thái hàng thành công!<br>";
    
    // Hiển thị kết quả
    $result = $conn->query("SELECT id, name, stock, stock_status FROM products ORDER BY id");
    
    echo "<table border='1' style='border-collapse: collapse; margin-top: 20px;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th style='padding: 10px;'>ID</th>
            <th style='padding: 10px;'>Tên sản phẩm</th>
            <th style='padding: 10px;'>Số lượng</th>
            <th style='padding: 10px;'>Trạng thái</th>
          </tr>";
    
    while ($row = $result->fetch_assoc()) {
        $status_color = $row['stock_status'] == 'in_stock' ? 'green' : 'red';
        $status_text = $row['stock_status'] == 'in_stock' ? 'Còn hàng' : 'Hết hàng';
        
        echo "<tr>";
        echo "<td style='padding: 10px; text-align: center;'>{$row['id']}</td>";
        echo "<td style='padding: 10px;'>{$row['name']}</td>";
        echo "<td style='padding: 10px; text-align: center;'>{$row['stock']}</td>";
        echo "<td style='padding: 10px; text-align: center; color: {$status_color}; font-weight: bold;'>{$status_text}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "❌ Lỗi: " . $conn->error;
}

$conn->close();
?>
