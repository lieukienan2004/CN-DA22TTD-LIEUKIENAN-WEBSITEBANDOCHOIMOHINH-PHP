<?php
session_start();
require_once 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập trước!");
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Tạo contact message test
$subject = "Hỗ trợ đơn hàng #1777";
$message = "Xin chào, tôi cần hỗ trợ về đơn hàng của mình. Đơn hàng đã được đặt nhưng chưa nhận được xác nhận. Vui lòng kiểm tra giúp tôi.";

$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, '0123456789', ?, ?, 'replied', NOW())");
$stmt->bind_param("ssss", $user_name, $user_email, $subject, $message);

if ($stmt->execute()) {
    $contact_id = $conn->insert_id;
    
    // Tạo reply từ admin
    $admin_id = 1; // Giả sử admin ID = 1
    $reply_message = "Xin chào! Cảm ơn bạn đã liên hệ. Chúng tôi đã kiểm tra đơn hàng #1777 của bạn. Đơn hàng đang được xử lý và sẽ được giao trong 2-3 ngày tới. Nếu có thắc mắc gì thêm, vui lòng liên hệ lại với chúng tôi.";
    
    $reply_stmt = $conn->prepare("INSERT INTO traloithongbao (contact_id, admin_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $reply_stmt->bind_param("iis", $contact_id, $admin_id, $reply_message);
    $reply_stmt->execute();
    
    // Tạo thông báo
    $notif_title = "Tin nhắn liên hệ của bạn đã được gửi";
    $notif_message = "Chủ đề: " . $subject;
    $notif_link = "view_my_contact.php?highlight=" . $contact_id;
    
    $notif_stmt = $conn->prepare("INSERT INTO thongbao (user_id, type, title, message, link, created_at) VALUES (?, 'contact', ?, ?, ?, NOW())");
    $notif_stmt->bind_param("isss", $user_id, $notif_title, $notif_message, $notif_link);
    $notif_stmt->execute();
    
    echo "✓ Đã tạo dữ liệu test thành công!<br><br>";
    echo "<a href='view_my_contact.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px;'>Xem Tin Nhắn</a> ";
    echo "<a href='notifications.php' style='padding: 10px 20px; background: #764ba2; color: white; text-decoration: none; border-radius: 8px; margin-left: 10px;'>Xem Thông Báo</a>";
} else {
    echo "✗ Lỗi: " . $conn->error;
}
?>
