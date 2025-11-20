<?php
session_start();
require_once 'config/database.php';

// Giả lập đăng nhập
if (!isset($_SESSION['user_id'])) {
    // Lấy user đầu tiên để test
    $user = $conn->query("SELECT * FROM users LIMIT 1")->fetch_assoc();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['fullname'];
        echo "<p style='color: green;'>✓ Đã đăng nhập test với user: " . $user['email'] . "</p>";
    }
}

echo "<h2>KIỂM TRA VIEW_MY_CONTACT.PHP</h2>";

if (isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];
    echo "<p>Email đang đăng nhập: <strong>" . $email . "</strong></p>";
    
    // Kiểm tra contact messages
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo "<p>Số lượng tin nhắn: <strong>" . count($messages) . "</strong></p>";
    
    if (count($messages) > 0) {
        echo "<h3>Danh sách tin nhắn:</h3>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Subject</th><th>Message</th><th>Status</th><th>Created</th></tr>";
        
        foreach ($messages as $msg) {
            echo "<tr>";
            echo "<td>" . $msg['id'] . "</td>";
            echo "<td>" . htmlspecialchars($msg['subject']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($msg['message'], 0, 50)) . "...</td>";
            echo "<td>" . $msg['status'] . "</td>";
            echo "<td>" . $msg['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<br><p><a href='view_my_contact.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px;'>Xem trang view_my_contact.php</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Không có tin nhắn nào cho email này!</p>";
        echo "<p>Hãy tạo tin nhắn test:</p>";
        
        // Tạo tin nhắn test
        $test_subject = "Test - Liên hệ từ " . $email;
        $test_message = "Đây là tin nhắn test để kiểm tra hệ thống thông báo.";
        $test_name = $_SESSION['user_name'] ?? 'Test User';
        
        $insert = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES (?, ?, '0123456789', ?, ?, 'new')");
        $insert->bind_param("ssss", $test_name, $email, $test_subject, $test_message);
        
        if ($insert->execute()) {
            echo "<p style='color: green;'>✓ Đã tạo tin nhắn test! <a href='test_view_contact.php'>Refresh trang này</a></p>";
        } else {
            echo "<p style='color: red;'>✗ Lỗi tạo tin nhắn: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>Chưa đăng nhập!</p>";
}
?>
