<?php
require_once 'config/database.php';

echo "<h2>Kiểm Tra Hệ Thống Thông Báo</h2>";

// 1. Kiểm tra bảng thongbao
echo "<h3>1. Cấu trúc bảng thongbao:</h3>";
$result = $conn->query("DESCRIBE thongbao");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Lỗi: Bảng thongbao không tồn tại!</p>";
}

// 2. Kiểm tra dữ liệu thông báo
echo "<h3>2. Dữ liệu trong bảng thongbao:</h3>";
$result = $conn->query("SELECT * FROM thongbao ORDER BY created_at DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User ID</th><th>User Type</th><th>Type</th><th>Title</th><th>Message</th><th>Is Read</th><th>Created At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>" . ($row['user_type'] ?? 'N/A') . "</td>";
        echo "<td>{$row['type']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>" . substr($row['message'], 0, 50) . "...</td>";
        echo "<td>" . ($row['is_read'] ? 'Đã đọc' : 'Chưa đọc') . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>Chưa có thông báo nào trong hệ thống.</p>";
}

// 3. Kiểm tra tin nhắn liên hệ
echo "<h3>3. Tin nhắn liên hệ mới nhất:</h3>";
$result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Created At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['subject']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>Chưa có tin nhắn liên hệ nào.</p>";
}

// 4. Kiểm tra admins
echo "<h3>4. Danh sách Admin:</h3>";
$result = $conn->query("SELECT id, username, fullname, email FROM admins");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Fullname</th><th>Email</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['fullname']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Không có admin nào trong hệ thống!</p>";
}

// 5. Kiểm tra users
echo "<h3>5. Danh sách Users (5 người đầu):</h3>";
$result = $conn->query("SELECT id, email, fullname FROM users LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Email</th><th>Fullname</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['fullname']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>Chưa có user nào đăng ký.</p>";
}

// 6. Test tạo thông báo cho admin
echo "<h3>6. Test tạo thông báo cho admin:</h3>";
$admin_query = $conn->query("SELECT id FROM admins LIMIT 1");
if ($admin_query && $admin_query->num_rows > 0) {
    $admin = $admin_query->fetch_assoc();
    $test_stmt = $conn->prepare("
        INSERT INTO thongbao (user_id, user_type, type, title, message, link, created_at) 
        VALUES (?, 'admin', 'contact', 'Test thông báo', 'Đây là thông báo test', 'admin/contacts.php', NOW())
    ");
    $test_stmt->bind_param("i", $admin['id']);
    if ($test_stmt->execute()) {
        echo "<p style='color: green;'>✓ Tạo thông báo test thành công! ID: " . $conn->insert_id . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Lỗi tạo thông báo: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: red;'>Không tìm thấy admin để test!</p>";
}

$conn->close();
?>
