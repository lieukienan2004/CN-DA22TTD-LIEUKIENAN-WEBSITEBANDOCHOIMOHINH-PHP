<?php
require_once 'config/database.php';

echo '<h2>Reset Admin Account</h2>';

// Xóa admin cũ nếu có
$conn->query("DELETE FROM admins WHERE username = 'admin'");

// Tạo admin mới
$password = password_hash('admin123', PASSWORD_DEFAULT);
$result = $conn->query("INSERT INTO admins (username, password, fullname, email, role, status) 
              VALUES ('admin', '$password', 'Administrator', 'admin@kienanshop.com', 'super_admin', 1)");

if ($result) {
    echo '<div style="padding: 20px; background: #d1fae5; color: #065f46; border-radius: 8px; margin: 20px 0;">';
    echo '<h3>✓ Tạo tài khoản admin thành công!</h3>';
    echo '<p><strong>Username:</strong> admin</p>';
    echo '<p><strong>Password:</strong> admin123</p>';
    echo '<p><strong>Email:</strong> admin@kienanshop.com</p>';
    echo '<p><strong>Role:</strong> super_admin</p>';
    echo '</div>';
    echo '<a href="admin/login.php" style="display: inline-block; padding: 12px 24px; background: #ec4899; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">Đăng nhập ngay</a>';
} else {
    echo '<div style="padding: 20px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin: 20px 0;">';
    echo '<h3>✗ Lỗi khi tạo admin</h3>';
    echo '<p>' . $conn->error . '</p>';
    echo '</div>';
}

// Hiển thị danh sách admin hiện có
echo '<h3 style="margin-top: 40px;">Danh sách Admin hiện có:</h3>';
$admins = $conn->query('SELECT id, username, fullname, email, role, status FROM admins');
if ($admins->num_rows > 0) {
    echo '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
    echo '<tr style="background: #f3f4f6;">';
    echo '<th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb;">ID</th>';
    echo '<th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb;">Username</th>';
    echo '<th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb;">Fullname</th>';
    echo '<th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb;">Email</th>';
    echo '<th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb;">Role</th>';
    echo '<th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb;">Status</th>';
    echo '</tr>';
    while ($admin = $admins->fetch_assoc()) {
        echo '<tr>';
        echo '<td style="padding: 12px; border: 1px solid #e5e7eb;">' . $admin['id'] . '</td>';
        echo '<td style="padding: 12px; border: 1px solid #e5e7eb;"><strong>' . $admin['username'] . '</strong></td>';
        echo '<td style="padding: 12px; border: 1px solid #e5e7eb;">' . $admin['fullname'] . '</td>';
        echo '<td style="padding: 12px; border: 1px solid #e5e7eb;">' . $admin['email'] . '</td>';
        echo '<td style="padding: 12px; border: 1px solid #e5e7eb;">' . $admin['role'] . '</td>';
        echo '<td style="padding: 12px; border: 1px solid #e5e7eb;">' . ($admin['status'] ? '✓ Active' : '✗ Inactive') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p>Không có admin nào trong hệ thống.</p>';
}

$conn->close();
?>

<style>
    body {
        font-family: 'Inter', sans-serif;
        max-width: 900px;
        margin: 40px auto;
        padding: 20px;
        background: #f9fafb;
    }
    h2 {
        color: #1f2937;
        font-size: 32px;
        margin-bottom: 20px;
    }
    h3 {
        color: #374151;
        font-size: 20px;
    }
</style>
