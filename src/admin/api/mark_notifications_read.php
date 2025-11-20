<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Kiểm tra admin đăng nhập
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

// Đảm bảo autocommit được bật
$conn->autocommit(TRUE);

$admin_id = $_SESSION['admin_id'];

// Lấy notification ID (nếu có)
$notif_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($notif_id > 0) {
    // Đánh dấu một thông báo cụ thể
    $stmt = $conn->prepare("UPDATE thongbao SET is_read = 1 WHERE id = ? AND user_id = ? AND user_type = 'admin'");
    $stmt->bind_param("ii", $notif_id, $admin_id);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        
        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã đánh dấu thông báo là đã đọc',
                'notification_id' => $notif_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy thông báo hoặc đã được đánh dấu'
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi cập nhật database: ' . $conn->error
        ]);
    }
    
    $stmt->close();
} else {
    // Đánh dấu tất cả thông báo
    $stmt = $conn->prepare("UPDATE thongbao SET is_read = 1 WHERE user_id = ? AND user_type = 'admin' AND is_read = 0");
    $stmt->bind_param("i", $admin_id);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        
        echo json_encode([
            'success' => true,
            'message' => "Đã đánh dấu $affected thông báo là đã đọc",
            'affected_rows' => $affected
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi cập nhật database: ' . $conn->error
        ]);
    }
    
    $stmt->close();
}

$conn->close();
?>
