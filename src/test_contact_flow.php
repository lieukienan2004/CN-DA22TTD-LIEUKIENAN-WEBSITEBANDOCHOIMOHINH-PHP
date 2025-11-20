<?php
require_once 'config/database.php';

echo "<h2>KIỂM TRA LUỒNG GỬI TIN NHẮN LIÊN HỆ</h2>";
echo "<hr>";

// 1. Kiểm tra tin nhắn mới nhất
echo "<h3>1. Tin nhắn liên hệ mới nhất:</h3>";
$contact_result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 3");
if ($contact_result && $contact_result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Created</th></tr>";
    while ($row = $contact_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['subject']}</td>";
        echo "<td><strong>{$row['status']}</strong></td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>Chưa có tin nhắn nào</p>";
}

echo "<hr>";

// 2. Kiểm tra thông báo admin mới nhất
echo "<h3>2. Thông báo admin mới nhất (từ bảng thongbao):</h3>";
$notif_result = $conn->query("SELECT * FROM thongbao WHERE user_type = 'admin' ORDER BY created_at DESC LIMIT 5");
if ($notif_result && $notif_result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Admin ID</th><th>Type</th><th>Title</th><th>Message</th><th>Created</th></tr>";
    while ($row = $notif_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['type']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>" . substr($row['message'], 0, 50) . "...</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>Chưa có thông báo admin nào</p>";
}

echo "<hr>";

// 3. Test gửi tin nhắn
echo "<h3>3. Test gửi tin nhắn:</h3>";
if (isset($_GET['test'])) {
    // Tạo tin nhắn test
    $name = "Test User " . date('H:i:s');
    $phone = "0123456789";
    $email = "test" . time() . "@example.com";
    $subject = "Test";
    $message = "Tin nhắn test lúc " . date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, phone, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'new', NOW())");
    $stmt->bind_param("sssss", $name, $phone, $email, $subject, $message);
    
    if ($stmt->execute()) {
        $contact_id = $conn->insert_id;
        echo "<p style='color: green;'>✓ Tạo tin nhắn thành công! ID: $contact_id</p>";
        
        // Tạo thông báo cho admin
        $admin_query = $conn->query("SELECT id FROM admins");
        if ($admin_query && $admin_query->num_rows > 0) {
            $notify_stmt = $conn->prepare("
                INSERT INTO thongbao (user_id, user_type, type, title, message, link, created_at) 
                VALUES (?, 'admin', 'contact', ?, ?, ?, NOW())
            ");
            
            $notif_title = "Tin nhắn mới từ " . $name;
            $notif_message = substr($message, 0, 100);
            $notif_link = "admin/contacts.php?view=" . $contact_id;
            
            $success_count = 0;
            while ($admin = $admin_query->fetch_assoc()) {
                $notify_stmt->bind_param("isss", $admin['id'], $notif_title, $notif_message, $notif_link);
                if ($notify_stmt->execute()) {
                    $success_count++;
                    echo "<p style='color: green;'>✓ Tạo thông báo cho admin ID {$admin['id']} thành công!</p>";
                } else {
                    echo "<p style='color: red;'>✗ Lỗi tạo thông báo cho admin ID {$admin['id']}: " . $conn->error . "</p>";
                }
            }
            echo "<p><strong>Đã tạo $success_count thông báo cho admin</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ Không tìm thấy admin nào!</p>";
        }
        
        echo "<p><a href='test_contact_flow.php'>Refresh để xem kết quả</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Lỗi tạo tin nhắn: " . $conn->error . "</p>";
    }
} else {
    echo "<p><a href='?test=1' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Gửi tin nhắn test</a></p>";
}

echo "<hr>";
echo "<p><a href='admin/test_notif_api.php'>Xem thông báo admin</a></p>";
?>
