<?php
session_start();
require_once 'config/database.php';

// Giả lập đăng nhập admin để test
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Giả lập user ID
}

echo "<h2>🧪 Test Tạo Thông Báo Đơn Hàng</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// Bước 1: Kiểm tra cột link
echo "<h3>1️⃣ Kiểm tra cột 'link' trong bảng thongbao</h3>";
$check = $conn->query("SHOW COLUMNS FROM thongbao LIKE 'link'");
if ($check && $check->num_rows > 0) {
    echo "<div class='success'>✅ Cột 'link' đã tồn tại</div>";
    $has_link = true;
} else {
    echo "<div class='error'>❌ Cột 'link' CHƯA tồn tại</div>";
    echo "<div class='info'><a href='fix_thongbao_add_link.php' class='btn'>➕ Thêm cột 'link' ngay</a></div>";
    $has_link = false;
}

// Bước 2: Kiểm tra admin
echo "<h3>2️⃣ Kiểm tra admin trong hệ thống</h3>";
$admin_query = $conn->query("SELECT id, fullname, email, role FROM users WHERE role = 'admin'");
if ($admin_query && $admin_query->num_rows > 0) {
    echo "<div class='success'>✅ Tìm thấy " . $admin_query->num_rows . " admin</div>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Tên</th><th>Email</th><th>Role</th></tr>";
    while ($admin = $admin_query->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$admin['id']}</td>";
        echo "<td>{$admin['fullname']}</td>";
        echo "<td>{$admin['email']}</td>";
        echo "<td>{$admin['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ Không tìm thấy admin nào!</div>";
}

// Bước 3: Test tạo thông báo
if ($has_link && isset($_GET['test']) && $_GET['test'] == 'create') {
    echo "<h3>3️⃣ Test tạo thông báo</h3>";
    
    $order_id = 999; // Giả lập order ID
    $fullname = "Test User";
    $total = 1000000;
    
    try {
        $admin_query = $conn->query("SELECT id FROM users WHERE role = 'admin'");
        if ($admin_query) {
            $created = 0;
            while ($admin = $admin_query->fetch_assoc()) {
                $notif_title = "Đơn hàng mới #" . $order_id;
                $notif_message = "Từ " . htmlspecialchars($fullname) . " - " . number_format($total) . "đ";
                $notif_link = "admin/order_detail.php?id=" . $order_id;
                
                echo "<div class='info'>";
                echo "<strong>Tạo thông báo cho admin ID: {$admin['id']}</strong><br>";
                echo "Title: $notif_title<br>";
                echo "Message: $notif_message<br>";
                echo "Link: $notif_link";
                echo "</div>";
                
                $notif_stmt = $conn->prepare("INSERT INTO thongbao (user_id, user_type, type, title, message, link) VALUES (?, 'admin', 'order', ?, ?, ?)");
                if ($notif_stmt) {
                    $notif_stmt->bind_param("isss", $admin['id'], $notif_title, $notif_message, $notif_link);
                    if ($notif_stmt->execute()) {
                        echo "<div class='success'>✅ Đã tạo thông báo thành công! ID: " . $conn->insert_id . "</div>";
                        $created++;
                    } else {
                        echo "<div class='error'>❌ Lỗi execute: " . $notif_stmt->error . "</div>";
                    }
                } else {
                    echo "<div class='error'>❌ Lỗi prepare: " . $conn->error . "</div>";
                }
            }
            
            if ($created > 0) {
                echo "<div class='success'>✅ Đã tạo $created thông báo test!</div>";
                echo "<a href='admin/index.php' class='btn'>🔔 Xem thông báo admin</a>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Exception: " . $e->getMessage() . "</div>";
    }
    
    echo "<br><a href='test_create_order_notification.php' class='btn'>🔄 Tải lại</a>";
} else if ($has_link) {
    echo "<h3>3️⃣ Sẵn sàng test</h3>";
    echo "<a href='?test=create' class='btn' style='background: #10b981;'>🧪 Tạo thông báo test</a>";
}

// Bước 4: Xem thông báo hiện có
echo "<h3>4️⃣ Thông báo hiện có trong database</h3>";
$notif_query = $conn->query("SELECT * FROM thongbao WHERE user_type = 'admin' ORDER BY created_at DESC LIMIT 10");
if ($notif_query && $notif_query->num_rows > 0) {
    echo "<div class='success'>✅ Có " . $notif_query->num_rows . " thông báo admin</div>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Title</th><th>Message</th><th>Link</th><th>is_read</th><th>Created</th></tr>";
    while ($notif = $notif_query->fetch_assoc()) {
        $read_status = $notif['is_read'] ? '✅' : '❌';
        echo "<tr>";
        echo "<td>{$notif['id']}</td>";
        echo "<td>{$notif['user_id']}</td>";
        echo "<td>{$notif['type']}</td>";
        echo "<td>" . htmlspecialchars($notif['title']) . "</td>";
        echo "<td>" . htmlspecialchars($notif['message']) . "</td>";
        echo "<td>" . htmlspecialchars($notif['link'] ?? 'NULL') . "</td>";
        echo "<td>$read_status</td>";
        echo "<td>{$notif['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='info'>ℹ️ Chưa có thông báo admin nào</div>";
}

echo "<br><a href='checkout.php' class='btn'>← Quay lại Checkout</a>";
?>
