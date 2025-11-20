<?php
// Test script để tạo tin nhắn liên hệ mẫu
require_once 'config/database.php';

// Tạo 3 tin nhắn test
$test_messages = [
    ['Nguyễn Văn A', '0901234567', 'test1@gmail.com', 'general', 'Tôi muốn hỏi về sản phẩm'],
    ['Trần Thị B', '0912345678', 'test2@gmail.com', 'order', 'Đơn hàng của tôi đến khi nào?'],
    ['Lê Văn C', '0923456789', 'test3@gmail.com', 'product', 'Sản phẩm này còn hàng không?']
];

foreach ($test_messages as $msg) {
    $sql = "INSERT INTO contact_messages (name, phone, email, subject, message, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'new', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $msg[0], $msg[1], $msg[2], $msg[3], $msg[4]);
    $stmt->execute();
}

echo "✅ Đã tạo 3 tin nhắn test thành công!<br>";
echo "👉 Bây giờ vào trang admin và xem icon chuông sẽ có số đếm màu đỏ<br>";
echo "<a href='admin/index.php'>Vào trang Admin</a>";

$conn->close();
?>
