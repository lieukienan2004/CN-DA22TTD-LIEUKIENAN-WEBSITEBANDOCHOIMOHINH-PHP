<?php
require_once 'config/database.php';

echo "<h2>🔧 Thêm cột order_code vào bảng orders</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

// Kiểm tra xem cột order_code đã tồn tại chưa
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_code'");

if ($check && $check->num_rows > 0) {
    echo "<div class='success'>✅ Cột 'order_code' đã tồn tại trong bảng orders</div>";
} else {
    echo "<div class='info'>⏳ Đang thêm cột 'order_code' vào bảng orders...</div>";
    
    $sql = "ALTER TABLE orders ADD COLUMN order_code VARCHAR(50) UNIQUE AFTER id";
    
    if ($conn->query($sql)) {
        echo "<div class='success'>✅ Đã thêm cột 'order_code' thành công!</div>";
        
        // Tạo mã đơn hàng cho các đơn hàng cũ
        echo "<div class='info'>⏳ Đang tạo mã đơn hàng cho các đơn cũ...</div>";
        
        $orders = $conn->query("SELECT id FROM orders WHERE order_code IS NULL");
        $count = 0;
        
        while ($order = $orders->fetch_assoc()) {
            $order_code = 'DH' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
            $conn->query("UPDATE orders SET order_code = '$order_code' WHERE id = {$order['id']}");
            $count++;
        }
        
        echo "<div class='success'>✅ Đã tạo mã cho $count đơn hàng cũ!</div>";
    } else {
        echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
    }
}

echo "<br><a href='checkout.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại Checkout</a>";
?>
