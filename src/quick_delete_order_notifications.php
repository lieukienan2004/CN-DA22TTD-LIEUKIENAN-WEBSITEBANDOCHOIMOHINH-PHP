<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập!");
}

echo "<h2>🗑️ Xóa Nhanh Thông Báo Đơn Hàng Test</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
</style>";

$user_id = $_SESSION['user_id'];

// Kiểm tra trước khi xóa
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM thongbao WHERE user_id = ? AND type = 'order' AND (title LIKE '%Đơn hàng mới%' OR title LIKE '%test%')");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];

echo "<div class='info'>";
echo "<strong>📊 Tìm thấy:</strong> $count thông báo đơn hàng test";
echo "</div>";

if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Thực hiện xóa
    $stmt = $conn->prepare("DELETE FROM thongbao WHERE user_id = ? AND type = 'order' AND (title LIKE '%Đơn hàng mới%' OR title LIKE '%test%')");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $deleted = $stmt->affected_rows;
        echo "<div class='success'>✅ Đã xóa $deleted thông báo thành công!</div>";
        echo "<a href='notifications.php' class='btn'>← Quay lại Thông Báo</a>";
    } else {
        echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
    }
} else {
    // Hiển thị nút xác nhận
    if ($count > 0) {
        echo "<a href='?confirm=yes' class='btn' style='background: #ef4444;' onclick='return confirm(\"Xóa $count thông báo?\");'>🗑️ XÓA $count THÔNG BÁO</a>";
    } else {
        echo "<div class='info'>✅ Không có thông báo test nào để xóa</div>";
    }
    echo "<a href='notifications.php' class='btn'>← Quay lại</a>";
}
?>
