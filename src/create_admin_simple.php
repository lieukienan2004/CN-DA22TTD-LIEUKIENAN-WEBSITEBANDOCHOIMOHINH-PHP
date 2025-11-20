<?php
// Script đơn giản để tạo admin
$conn = new mysqli('localhost', 'root', '', 'kienan123');
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

echo "<h2>Tạo tài khoản Admin</h2>";

// Kiểm tra bảng admins có tồn tại không
$check = $conn->query("SHOW TABLES LIKE 'admins'");
if ($check->num_rows == 0) {
    echo "<p style='color: red;'>Bảng 'admins' không tồn tại! Đang tạo bảng...</p>";
    
    $create_table = "CREATE TABLE admins (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        fullname VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
        status TINYINT(1) DEFAULT 1,
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "<p style='color: green;'>✓ Đã tạo bảng admins</p>";
    } else {
        die("<p style='color: red;'>Lỗi tạo bảng: " . $conn->error . "</p>");
    }
}

// Xóa admin cũ
$conn->query("DELETE FROM admins WHERE username = 'admin'");
echo "<p>Đã xóa admin cũ (nếu có)</p>";

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
    echo "<h3 style='color: #065f46;'>✓ Tạo admin thành công!</h3>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>Email:</strong> admin@kienanshop.com</p>";
    echo "</div>";
    
    // Test đăng nhập
    echo "<h3>Test đăng nhập:</h3>";
    $test = $conn->query("SELECT * FROM admins WHERE username = 'admin'");
    if ($test->num_rows > 0) {
        $admin = $test->fetch_assoc();
        echo "<p style='color: green;'>✓ Tìm thấy admin trong database</p>";
        echo "<p>ID: " . $admin['id'] . "</p>";
        echo "<p>Username: " . $admin['username'] . "</p>";
        echo "<p>Status: " . ($admin['status'] ? 'Active' : 'Inactive') . "</p>";
        
        // Test password
        if (password_verify('admin123', $admin['password'])) {
            echo "<p style='color: green;'>✓ Password đúng!</p>";
        } else {
            echo "<p style='color: red;'>✗ Password sai!</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Không tìm thấy admin!</p>";
    }
    
    echo "<br><a href='admin/login.php' style='display: inline-block; padding: 12px 24px; background: #ec4899; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>Đăng nhập Admin</a>";
} else {
    echo "<p style='color: red;'>Lỗi: " . $conn->error . "</p>";
}

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
        background: #f9fafb;
    }
    h2 { color: #1f2937; }
    p { margin: 10px 0; }
</style>
