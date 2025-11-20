<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']);
    exit;
}

// Cập nhật trạng thái thanh toán
$stmt = $conn->prepare("UPDATE orders SET payment_status = 'completed' WHERE id = ?");
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Đã xác nhận thanh toán thành công'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cập nhật: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>
