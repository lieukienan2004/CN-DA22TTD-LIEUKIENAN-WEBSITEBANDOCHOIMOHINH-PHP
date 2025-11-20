<?php
require_once 'config/database.php';

echo "<h2>🔧 Thêm cột payment_method vào bảng orders</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

// Kiểm tra xem cột payment_method đã tồn tại chưa
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");

if ($check && $check->num_rows > 0) {
    echo "<div class='success'>✅ Cột 'payment_method' đã tồn tại trong bảng orders</div>";
} else {
    echo "<div class='info'>⏳ Đang thêm cột 'payment_method' vào bảng orders...</div>";
    
    $sql = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(20) DEFAULT 'cod' AFTER address";
    
    if ($conn->query($sql)) {
        echo "<div class='success'>✅ Đã thêm cột 'payment_method' thành công!</div>";
        echo "<div class='info'>";
        echo "<strong>Cột đã được thêm:</strong><br>";
        echo "- Tên: payment_method<br>";
        echo "- Kiểu: VARCHAR(20)<br>";
        echo "- Mặc định: 'cod'<br>";
        echo "- Vị trí: Sau cột 'address'";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
    }
}

// Thêm cột payment_status nếu chưa có
$check_status = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_status'");

if ($check_status && $check_status->num_rows > 0) {
    echo "<div class='success'>✅ Cột 'payment_status' đã tồn tại</div>";
} else {
    echo "<div class='info'>⏳ Đang thêm cột 'payment_status'...</div>";
    
    $sql = "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending' AFTER payment_method";
    
    if ($conn->query($sql)) {
        echo "<div class='success'>✅ Đã thêm cột 'payment_status' thành công!</div>";
    } else {
        echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
    }
}

echo "<br><a href='checkout.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại Checkout</a>";
?>
