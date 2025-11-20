<?php
require_once 'config/database.php';

echo "<h2>Sửa mật khẩu Admin</h2>";

// Hash mật khẩu 123123
$password = '123123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Cập nhật vào database
$sql = "UPDATE admins SET password = '$hashed' WHERE username = 'admin'";

if ($conn->query($sql)) {
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3 style='color: #059669;'>✓ Đã cập nhật mật khẩu thành công!</h3>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> 123123</p>";
    echo "<p><strong>Password Hash:</strong> " . substr($hashed, 0, 50) . "...</p>";
    echo "</div>";
    echo "<p><a href='admin/login.php' style='background: #ec4899; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Đăng nhập Admin</a></p>";
} else {
    echo "<p style='color: red;'>Lỗi: " . $conn->error . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a></p>";
?>
