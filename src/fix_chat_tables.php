<?php
require_once 'config/database.php';

echo "<h2>Sửa lỗi bảng Chat</h2>";

// Xóa bảng cũ
echo "<h3>Xóa bảng cũ...</h3>";
$conn->query("DROP TABLE IF EXISTS chat_messages");
echo "<p style='color: green;'>✓ Đã xóa chat_messages</p>";

// Tạo lại bảng mới (không có foreign key)
echo "<h3>Tạo lại bảng mới...</h3>";
$sql = "CREATE TABLE chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(100) NOT NULL,
    sender_type ENUM('user', 'admin') NOT NULL,
    sender_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'product_link', 'image') DEFAULT 'text',
    product_id INT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(session_id),
    INDEX(created_at)
)";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Tạo bảng chat_messages thành công</p>";
} else {
    echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
}

echo "<hr>";
echo "<h3>Hoàn tất!</h3>";
echo "<p>Bây giờ chat sẽ hoạt động bình thường.</p>";
echo "<p><a href='index.php'>Về trang chủ</a></p>";
?>
