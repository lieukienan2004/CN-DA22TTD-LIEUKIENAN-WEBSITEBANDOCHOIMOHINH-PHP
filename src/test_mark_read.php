<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập!");
}

echo "<h2>🔍 Kiểm tra trạng thái thông báo</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    th { background: #667eea; color: white; }
    tr:hover { background: #f5f5f5; }
    .unread { background: #ffe6e6; font-weight: bold; }
    .read { background: #e6ffe6; }
    .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
    .btn:hover { background: #5568d3; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

$user_id = $_SESSION['user_id'];

// Xử lý đánh dấu tất cả đã đọc
if (isset($_GET['mark_all'])) {
    echo "<div class='info'>⏳ Đang cập nhật...</div>";
    
    $stmt = $conn->prepare("UPDATE thongbao SET is_read = 1 WHERE user_id = ? AND user_type = 'admin' AND type IN ('promotion', 'system', 'order', 'product')");
    $stmt->bind_param("i", $user_id);
    $result = $stmt->execute();
    $affected = $stmt->affected_rows;
    
    if ($result) {
        echo "<div class='info'>✅ Đã cập nhật $affected thông báo thành công!</div>";
    } else {
        echo "<div class='info'>❌ Lỗi: " . $conn->error . "</div>";
    }
    
    echo "<a href='test_mark_read.php' class='btn'>🔄 Tải lại</a>";
    echo "<hr>";
}

// Hiển thị thông tin user
echo "<div class='info'>";
echo "<strong>👤 User ID:</strong> $user_id<br>";
echo "<strong>📧 Session:</strong> " . (isset($_SESSION['email']) ? $_SESSION['email'] : 'N/A');
echo "</div>";

// Lấy tất cả thông báo
$stmt = $conn->prepare("SELECT * FROM thongbao WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<p>❌ Không có thông báo nào.</p>";
} else {
    // Thống kê
    $total = $result->num_rows;
    $unread = 0;
    $read = 0;
    $admin_notifs = 0;
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if ($row['is_read'] == 0) $unread++;
        else $read++;
        if ($row['user_type'] == 'admin') $admin_notifs++;
    }
    
    echo "<div class='info'>";
    echo "<strong>📊 Thống kê:</strong><br>";
    echo "Tổng: $total | Chưa đọc: $unread | Đã đọc: $read | Từ Admin: $admin_notifs";
    echo "</div>";
    
    echo "<a href='?mark_all=1' class='btn'>✅ Đánh dấu tất cả đã đọc</a>";
    echo "<a href='notifications.php' class='btn'>← Quay lại Thông Báo</a>";
    
    echo "<table>";
    echo "<tr>
        <th>ID</th>
        <th>Type</th>
        <th>User Type</th>
        <th>Title</th>
        <th>is_read</th>
        <th>Created</th>
    </tr>";
    
    foreach ($notifications as $notif) {
        $class = $notif['is_read'] == 0 ? 'unread' : 'read';
        $status = $notif['is_read'] == 0 ? '❌ Chưa đọc' : '✅ Đã đọc';
        
        echo "<tr class='$class'>";
        echo "<td>{$notif['id']}</td>";
        echo "<td>{$notif['type']}</td>";
        echo "<td>{$notif['user_type']}</td>";
        echo "<td>" . htmlspecialchars($notif['title']) . "</td>";
        echo "<td>$status ({$notif['is_read']})</td>";
        echo "<td>{$notif['created_at']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<br><div class='info'>";
echo "<strong>🔧 Debug Info:</strong><br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MySQL Version: " . $conn->server_info . "<br>";
echo "Session ID: " . session_id();
echo "</div>";
?>
