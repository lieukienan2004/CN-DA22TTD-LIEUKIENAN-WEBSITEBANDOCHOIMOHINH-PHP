<?php
session_start();
require_once '../config/database.php';

// Đảm bảo autocommit được bật
$conn->autocommit(TRUE);

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
    exit;
}

// Lấy notification ID
$notif_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($notif_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Debug: Kiểm tra thông báo trước khi update
$check_stmt = $conn->prepare("SELECT id, is_read, user_id FROM thongbao WHERE id = ?");
$check_stmt->bind_param("i", $notif_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$before_update = $check_result->fetch_assoc();
$check_stmt->close();

// Cập nhật trạng thái
$stmt = $conn->prepare("UPDATE thongbao SET is_read = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notif_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    
    // Debug: Kiểm tra sau khi update
    $check_stmt = $conn->prepare("SELECT id, is_read, user_id FROM thongbao WHERE id = ?");
    $check_stmt->bind_param("i", $notif_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $after_update = $check_result->fetch_assoc();
    $check_stmt->close();
    
    if ($affected > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã đánh dấu thông báo là đã đọc',
            'notification_id' => $notif_id,
            'affected_rows' => $affected,
            'debug' => [
                'before' => $before_update,
                'after' => $after_update,
                'user_id' => $_SESSION['user_id']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy thông báo hoặc đã được đánh dấu',
            'debug' => [
                'notification_id' => $notif_id,
                'user_id' => $_SESSION['user_id'],
                'before' => $before_update,
                'after' => $after_update,
                'affected_rows' => $affected
            ]
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

// Đảm bảo commit transaction
if ($conn->commit()) {
    // Commit thành công
} else {
    error_log("Lỗi commit: " . $conn->error);
}

$conn->close();
?>
