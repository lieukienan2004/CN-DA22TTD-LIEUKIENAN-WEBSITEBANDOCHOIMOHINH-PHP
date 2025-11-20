<?php
session_start();
require_once 'config/database.php';

echo "<h2>KIỂM TRA DỮ LIỆU CONTACT</h2>";

// Kiểm tra session
echo "<h3>1. Thông tin đăng nhập:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<p>✓ User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>✓ Email: " . ($_SESSION['user_email'] ?? 'Không có') . "</p>";
    echo "<p>✓ Name: " . ($_SESSION['user_name'] ?? 'Không có') . "</p>";
} else {
    echo "<p style='color: red;'>✗ Chưa đăng nhập!</p>";
}

// Kiểm tra contact_messages
echo "<br><h3>2. Tất cả contact messages trong database:</h3>";
$all_contacts = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 10");

if ($all_contacts && $all_contacts->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Created</th></tr>";
    
    while ($row = $all_contacts->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Không có contact message nào trong database!</p>";
}

// Kiểm tra contact của user hiện tại
if (isset($_SESSION['user_email'])) {
    echo "<br><h3>3. Contact messages của user hiện tại (email: " . $_SESSION['user_email'] . "):</h3>";
    
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $_SESSION['user_email']);
    $stmt->execute();
    $user_contacts = $stmt->get_result();
    
    if ($user_contacts->num_rows > 0) {
        echo "<p style='color: green;'>✓ Tìm thấy " . $user_contacts->num_rows . " tin nhắn</p>";
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #d1fae5;'><th>ID</th><th>Subject</th><th>Message</th><th>Status</th></tr>";
        
        while ($row = $user_contacts->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($row['message'], 0, 100)) . "...</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ User này chưa có tin nhắn liên hệ nào!</p>";
        echo "<p>Hãy tạo tin nhắn test:</p>";
        
        // Tạo tin nhắn test
        $test_name = $_SESSION['user_name'] ?? 'Test User';
        $test_email = $_SESSION['user_email'];
        $test_subject = "Test - Liên hệ hỗ trợ";
        $test_message = "Đây là tin nhắn test để kiểm tra hệ thống. Tôi cần hỗ trợ về đơn hàng.";
        
        $insert = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, '0123456789', ?, ?, 'new', NOW())");
        $insert->bind_param("ssss", $test_name, $test_email, $test_subject, $test_message);
        
        if ($insert->execute()) {
            echo "<p style='color: green; font-weight: bold;'>✓ Đã tạo tin nhắn test thành công!</p>";
            echo "<p><a href='view_my_contact.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px;'>Xem trang view_my_contact.php</a></p>";
        } else {
            echo "<p style='color: red;'>✗ Lỗi tạo tin nhắn: " . $conn->error . "</p>";
        }
    }
}

echo "<br><br>";
echo "<p><a href='view_my_contact.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px;'>Đi tới view_my_contact.php</a></p>";
echo "<p><a href='notifications.php' style='padding: 10px 20px; background: #764ba2; color: white; text-decoration: none; border-radius: 8px;'>Quay lại Thông Báo</a></p>";
?>
