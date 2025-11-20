<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

// Lấy thông báo tin nhắn mới
$notifications = [];
$unread_count = 0; // Đếm riêng số thông báo chưa đọc từ bảng thongbao

// Lấy thông báo từ bảng thongbao cho admin hiện tại
$admin_id = $_SESSION['admin_id'];
$notif_query = $conn->query("
    SELECT * FROM thongbao 
    WHERE user_id = $admin_id AND user_type = 'admin' AND is_read = 0
    ORDER BY created_at DESC 
    LIMIT 10
");

if ($notif_query) {
    while ($row = $notif_query->fetch_assoc()) {
        $icon_map = [
            'contact' => 'fa-envelope',
            'order' => 'fa-shopping-cart',
            'product' => 'fa-box',
            'system' => 'fa-info-circle'
        ];
        
        $color_map = [
            'contact' => '#ec4899',
            'order' => '#10b981',
            'product' => '#3b82f6',
            'system' => '#8b5cf6'
        ];
        
        $notifications[] = [
            'id' => $row['id'],
            'type' => $row['type'],
            'title' => htmlspecialchars($row['title']),
            'message' => htmlspecialchars($row['message']),
            'time' => time_elapsed_string($row['created_at']),
            'link' => $row['link'],
            'icon' => $icon_map[$row['type']] ?? 'fa-bell',
            'color' => $color_map[$row['type']] ?? '#667eea'
        ];
        $unread_count++; // Đếm thông báo chưa đọc
    }
}

// Nếu không có thông báo nào, hiển thị thông tin hữu ích
if (empty($notifications)) {
    // Kiểm tra sản phẩm sắp hết hàng (stock <= 5)
    $stock_query = $conn->query("
        SELECT id, name, stock 
        FROM products 
        WHERE stock <= 5 AND stock > 0 AND status = 1 
        ORDER BY stock ASC
        LIMIT 3
    ");
    
    if ($stock_query) {
        while ($row = $stock_query->fetch_assoc()) {
            $notifications[] = [
                'id' => 'stock_' . $row['id'],
                'type' => 'stock',
                'title' => 'Sản phẩm sắp hết hàng',
                'message' => htmlspecialchars($row['name']) . ' - Còn ' . $row['stock'] . ' sản phẩm',
                'time' => 'Cần nhập hàng',
                'link' => 'product_edit.php?id=' . $row['id'],
                'icon' => 'fa-exclamation-triangle',
                'color' => '#f59e0b'
            ];
        }
    }
    
    // Sản phẩm hết hàng hoàn toàn
    $out_stock_query = $conn->query("
        SELECT id, name 
        FROM products 
        WHERE stock = 0 AND status = 1 
        LIMIT 3
    ");
    
    if ($out_stock_query) {
        while ($row = $out_stock_query->fetch_assoc()) {
            $notifications[] = [
                'id' => 'outstock_' . $row['id'],
                'type' => 'stock',
                'title' => 'Sản phẩm hết hàng',
                'message' => htmlspecialchars($row['name']),
                'time' => 'Cần nhập hàng ngay',
                'link' => 'product_edit.php?id=' . $row['id'],
                'icon' => 'fa-exclamation-circle',
                'color' => '#ef4444'
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'count' => $unread_count, // Số thông báo chưa đọc từ bảng thongbao
    'notifications' => $notifications
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
