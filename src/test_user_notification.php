<?php
session_start();
require_once 'config/database.php';

echo "<h2>TEST THÔNG BÁO CHO USER</h2>";
echo "<hr>";

// Kiểm tra user đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>Bạn chưa đăng nhập! <a href='login.php'>Đăng nhập</a></p>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<p>User ID: $user_id</p>";
echo "<p>Email: " . ($_SESSION['user_email'] ?? 'N/A') . "</p>";
echo "<hr>";

// Tạo thông báo test
if (isset($_GET['create'])) {
    $stmt = $conn->prepare("
        INSERT INTO thongbao (user_id, user_type, type, title, message, link, created_at) 
        VALUES (?, 'user', 'system', ?, ?, ?, NOW())
    ");
    
    $title = "Thông báo test " . date('H:i:s');
    $message = "Đây là thông báo test cho user lúc " . date('Y-m-d H:i:s');
    $link = "notifications.php";
    
    $stmt->bind_param("isss", $user_id, $title, $message, $link);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Tạo thông báo thành công! ID: " . $conn->insert_id . "</p>";
        echo "<p><a href='notifications.php'>Xem thông báo</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
    }
    echo "<hr>";
}

// Hiển thị thông báo hiện có
echo "<h3>Thông báo của bạn:</h3>";
$result = $conn->query("SELECT * FROM thongbao WHERE user_id = $user_id AND user_type = 'user' ORDER BY created_at DESC LIMIT 10");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Type</th><th>Title</th><th>Message</th><th>Is Read</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['type']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['message']}</td>";
        echo "<td>" . ($row['is_read'] ? 'Đã đọc' : '<strong>Chưa đọc</strong>') . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Chưa có thông báo nào</p>";
}

echo "<hr>";
echo "<p><a href='?create=1' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Tạo thông báo test</a></p>";
echo "<p><a href='notifications.php'>Xem trang thông báo</a></p>";
?>
