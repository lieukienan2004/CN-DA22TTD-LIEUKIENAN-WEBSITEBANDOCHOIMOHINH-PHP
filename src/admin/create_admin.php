<?php
// Script tạo tài khoản admin mới
// Chạy file này 1 lần rồi xóa đi

require_once '../config/database.php';

// Thông tin admin
$username = 'admin';
$password = 'admin123';
$email = 'admin@kienanshop.vn';
$fullname = 'Administrator';
$role = 'super_admin';

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Xóa admin cũ nếu có
$conn->query("DELETE FROM admins WHERE username = 'admin'");

// Tạo admin mới
$stmt = $conn->prepare("INSERT INTO admins (username, password, email, fullname, role, status) VALUES (?, ?, ?, ?, ?, 1)");
$stmt->bind_param("sssss", $username, $hashed_password, $email, $fullname, $role);

if ($stmt->execute()) {
    echo "<h1 style='color: green;'>✓ Tạo tài khoản admin thành công!</h1>";
    echo "<h2>Thông tin đăng nhập:</h2>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>URL:</strong> <a href='login.php'>login.php</a></p>";
    echo "<hr>";
    echo "<p style='color: red;'><strong>LƯU Ý:</strong> Hãy xóa file create_admin.php này sau khi đăng nhập thành công!</p>";
} else {
    echo "<h1 style='color: red;'>✗ Lỗi: " . $conn->error . "</h1>";
}
?>
