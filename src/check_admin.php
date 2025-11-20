<?php
require_once 'config/database.php';

// Kiểm tra xem có admin nào không
$result = $conn->query('SELECT COUNT(*) as total FROM admins');
$count = $result->fetch_assoc()['total'];

if ($count == 0) {
    echo 'Không có admin nào. Tạo admin mặc định...<br>';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admins (username, password, fullname, email, role, status) 
                  VALUES ('admin', '$password', 'Administrator', 'admin@kienanshop.com', 'super_admin', 1)");
    echo 'Đã tạo admin:<br>';
    echo 'Username: admin<br>';
    echo 'Password: admin123<br>';
} else {
    echo 'Đã có ' . $count . ' admin trong hệ thống.<br>';
    
    // Hiển thị danh sách admin
    $admins = $conn->query('SELECT id, username, fullname, email, role FROM admins');
    echo '<br>Danh sách admin:<br>';
    while ($admin = $admins->fetch_assoc()) {
        echo "- ID: {$admin['id']}, Username: {$admin['username']}, Name: {$admin['fullname']}, Role: {$admin['role']}<br>";
    }
}

$conn->close();
?>
