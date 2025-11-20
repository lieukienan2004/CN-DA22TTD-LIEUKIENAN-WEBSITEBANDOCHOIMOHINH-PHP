<?php
header('Content-Type: application/json');
require_once 'config/database.php';

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không hợp lệ'
    ]);
    exit;
}

// Lấy dữ liệu từ form
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'general');
$message = trim($_POST['message'] ?? '');

// Validate dữ liệu
$errors = [];

if (empty($name)) {
    $errors[] = 'Vui lòng nhập họ tên';
}

if (empty($phone)) {
    $errors[] = 'Vui lòng nhập số điện thoại';
} elseif (!preg_match('/^[0-9]{10,11}$/', str_replace(' ', '', $phone))) {
    $errors[] = 'Số điện thoại không hợp lệ';
}

if (empty($email)) {
    $errors[] = 'Vui lòng nhập email';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ';
}

if (empty($message)) {
    $errors[] = 'Vui lòng nhập nội dung tin nhắn';
}

// Nếu có lỗi, trả về lỗi
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors)
    ]);
    exit;
}

// Lưu vào database
try {
    $sql = "INSERT INTO contact_messages (name, phone, email, subject, message, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'new', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $phone, $email, $subject, $message);
    
    if ($stmt->execute()) {
        $contact_id = $conn->insert_id;
        
        // Tạo thông báo cho tất cả admin
        $admin_query = $conn->query("SELECT id FROM admins");
        if ($admin_query && $admin_query->num_rows > 0) {
            $notify_stmt = $conn->prepare("
                INSERT INTO thongbao (user_id, user_type, type, title, message, link, created_at) 
                VALUES (?, 'admin', 'contact', ?, ?, ?, NOW())
            ");
            
            $notif_title = "Tin nhắn mới từ " . $name;
            $notif_message = "Chủ đề: " . $subject . " - " . mb_substr($message, 0, 80) . (mb_strlen($message) > 80 ? '...' : '');
            $notif_link = "contacts.php?id=" . $contact_id;
            
            while ($admin = $admin_query->fetch_assoc()) {
                $notify_stmt->bind_param("isss", $admin['id'], $notif_title, $notif_message, $notif_link);
                $notify_stmt->execute();
            }
            $notify_stmt->close();
        }
        
        // Tạo thông báo cho người gửi (nếu đã đăng nhập)
        session_start();
        if (isset($_SESSION['user_id'])) {
            $user_notif_stmt = $conn->prepare("
                INSERT INTO thongbao (user_id, user_type, type, title, message, link, created_at) 
                VALUES (?, 'user', 'contact', ?, ?, ?, NOW())
            ");
            
            $user_notif_title = "Tin nhắn liên hệ của bạn đã được gửi";
            $user_notif_message = "Chủ đề: " . $subject . " - " . mb_substr($message, 0, 80) . (mb_strlen($message) > 80 ? '...' : '');
            $user_notif_link = "view_my_contact.php?highlight=" . $contact_id;
            
            $user_notif_stmt->bind_param("isss", $_SESSION['user_id'], $user_notif_title, $user_notif_message, $user_notif_link);
            $user_notif_stmt->execute();
            $user_notif_stmt->close();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cảm ơn bạn! Tin nhắn của bạn đã được gửi thành công. Chúng tôi sẽ liên hệ lại sớm nhất.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại sau.'
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
