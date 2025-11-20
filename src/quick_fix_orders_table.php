<?php
require_once 'config/database.php';

echo "<h2>🔧 Quick Fix - Sửa bảng orders</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

$errors = [];
$success = [];

// 1. Thêm cột payment_method
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
if (!$check || $check->num_rows == 0) {
    if ($conn->query("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(20) DEFAULT 'cod' AFTER address")) {
        $success[] = "✅ Đã thêm cột payment_method";
    } else {
        $errors[] = "❌ Lỗi thêm payment_method: " . $conn->error;
    }
} else {
    $success[] = "✅ Cột payment_method đã tồn tại";
}

// 2. Thêm cột payment_status
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_status'");
if (!$check || $check->num_rows == 0) {
    if ($conn->query("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending' AFTER payment_method")) {
        $success[] = "✅ Đã thêm cột payment_status";
    } else {
        $errors[] = "❌ Lỗi thêm payment_status: " . $conn->error;
    }
} else {
    $success[] = "✅ Cột payment_status đã tồn tại";
}

// Hiển thị kết quả
if (!empty($success)) {
    echo "<div class='success'>" . implode("<br>", $success) . "</div>";
}

if (!empty($errors)) {
    echo "<div class='error'>" . implode("<br>", $errors) . "</div>";
}

if (empty($errors)) {
    echo "<div class='info'><strong>🎉 Hoàn tất!</strong> Bây giờ bạn có thể đặt hàng bình thường.</div>";
}

echo "<br><a href='checkout.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>← Thử đặt hàng lại</a>";
?>
