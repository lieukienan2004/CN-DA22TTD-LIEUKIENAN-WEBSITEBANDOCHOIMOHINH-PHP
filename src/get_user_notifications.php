<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Nếu chỉ cần đếm số thông báo chưa đọc (chỉ thông báo từ admin)
if (isset($_GET['count_only'])) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread 
        FROM thongbao 
        WHERE user_id = ? 
        AND is_read = 0 
        AND user_type = 'admin'
        AND type IN ('promotion', 'system', 'order', 'product')
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'unread_count' => (int)$result['unread']
    ]);
    exit;
}

// Lấy danh sách thông báo (chỉ từ admin)
$stmt = $conn->prepare("
    SELECT * FROM thongbao 
    WHERE user_id = ? 
    AND user_type = 'admin'
    AND type IN ('promotion', 'system', 'order', 'product')
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Đếm số chưa đọc (chỉ từ admin)
$stmt = $conn->prepare("
    SELECT COUNT(*) as unread 
    FROM thongbao 
    WHERE user_id = ? 
    AND is_read = 0 
    AND user_type = 'admin'
    AND type IN ('promotion', 'system', 'order', 'product')
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['unread'];

// Format notifications
$formatted = [];
foreach ($notifications as $notif) {
    $formatted[] = [
        'id' => $notif['id'],
        'type' => $notif['type'],
        'title' => $notif['title'],
        'message' => $notif['message'],
        'link' => $notif['link'],
        'is_read' => (bool)$notif['is_read'],
        'created_at' => $notif['created_at'],
        'time_ago' => time_elapsed_string($notif['created_at'])
    ];
}

echo json_encode([
    'success' => true,
    'unread_count' => (int)$unread_count,
    'notifications' => $formatted
]);

function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d > 0) {
        return $diff->d . ' ngày trước';
    } elseif ($diff->h > 0) {
        return $diff->h . ' giờ trước';
    } elseif ($diff->i > 0) {
        return $diff->i . ' phút trước';
    } else {
        return 'Vừa xong';
    }
}
?>
