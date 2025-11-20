<?php
// Test đơn giản không dùng session_start
require_once 'config/database.php';

echo "<h2>Test Chat - Simple</h2>";

// Test bảng
echo "<h3>Kiểm tra bảng:</h3>";

$tables = ['chat_sessions', 'chat_messages', 'admin_online'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Bảng $table tồn tại</p>";
    } else {
        echo "<p style='color: red;'>✗ Bảng $table KHÔNG tồn tại</p>";
    }
}

// Test insert
echo "<h3>Test insert session:</h3>";
$test_session = 'test_' . time();
$stmt = $conn->prepare("INSERT INTO chat_sessions (session_id, user_name) VALUES (?, 'Test User')");
$stmt->bind_param("s", $test_session);
if ($stmt->execute()) {
    echo "<p style='color: green;'>✓ Insert thành công</p>";
} else {
    echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
}

// Xóa test data
$conn->query("DELETE FROM chat_sessions WHERE session_id = '$test_session'");

echo "<hr>";
echo "<h3>Kết luận:</h3>";
echo "<p>Nếu tất cả đều ✓ thì database OK, lỗi nằm ở JavaScript</p>";
echo "<p><a href='index.php'>Về trang chủ</a></p>";
?>
