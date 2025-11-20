<?php
require_once 'config/database.php';

echo "<h2>Tạo tài khoản Admin</h2>";

// Xóa admin cũ nếu có
$conn->query("DELETE FROM admins WHERE username = 'admin'");

// Tạo admin mới
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$fullname = 'Administrator';
$email = 'admin@kienanshop.com';
$role = 'super_admin';

$sql = "INSERT INTO admins (username, password, fullname, email, role, status) 
        VALUES ('$username', '$password', '$fullname', '$email', '$role', 1)";

if ($conn->query($sql)) {
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3 style='color: #059669;'>✓ Tạo tài khoản admin thành công!</h3>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>Email:</strong> admin@kienanshop.com</p>";
    echo "<p><strong>Role:</strong> super_admin</p>";
    echo "</div>";
    echo "<p><a href='admin/login.php' style='background: #ec4899; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Đăng nhập Admin</a></p>";
} else {
    echo "<p style='color: red;'>Lỗi: " . $conn->error . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a></p>";
?>
