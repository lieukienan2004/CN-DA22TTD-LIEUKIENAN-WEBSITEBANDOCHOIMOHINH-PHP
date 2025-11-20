<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

requireAdmin();

echo "<h2>🗑️ Xóa Thông Báo Đơn Hàng Test (Admin)</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
</style>";

$admin_id = $_SESSION['admin_id'];

// Kiểm tra
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM thongbao WHERE user_id = ? AND user_type = 'admin' AND type = 'order'");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];

echo "<div class='info'>";
echo "<strong>📊 Tìm thấy:</strong> $count thông báo đơn hàng";
echo "</div>";

if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Xóa
    $stmt = $conn->prepare("DELETE FROM thongbao WHERE user_id = ? AND user_type = 'admin' AND type = 'order'");
    $stmt->bind_param("i", $admin_id);
    
    if ($stmt->execute()) {
        $deleted = $stmt->affected_rows;
        echo "<div class='success'>✅ Đã xóa $deleted thông báo đơn hàng!</div>";
        echo "<a href='index.php' class='btn'>← Quay lại Dashboard</a>";
    } else {
        echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
    }
} else {
    if ($count > 0) {
        echo "<a href='?confirm=yes' class='btn' style='background: #ef4444;' onclick='return confirm(\"Xóa $count thông báo?\");'>🗑️ XÓA $count THÔNG BÁO</a>";
    } else {
        echo "<div class='info'>✅ Không có thông báo đơn hàng nào</div>";
    }
    echo "<a href='index.php' class='btn'>← Quay lại</a>";
}
?>
