<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập!");
}

echo "<h2>🔍 Kiểm tra cấu trúc bảng thongbao</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    th { background: #667eea; color: white; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

// 1. Kiểm tra cấu trúc bảng
echo "<h3>📋 Cấu trúc bảng thongbao:</h3>";
$result = $conn->query("DESCRIBE thongbao");
echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// 2. Kiểm tra indexes
echo "<h3>🔑 Indexes:</h3>";
$result = $conn->query("SHOW INDEX FROM thongbao");
echo "<table><tr><th>Key Name</th><th>Column</th><th>Unique</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['Key_name']}</td>";
    echo "<td>{$row['Column_name']}</td>";
    echo "<td>" . ($row['Non_unique'] == 0 ? 'Yes' : 'No') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Test UPDATE query
echo "<h3>🧪 Test UPDATE query:</h3>";
$user_id = $_SESSION['user_id'];

// Lấy một thông báo chưa đọc
$stmt = $conn->prepare("SELECT id, title, is_read FROM thongbao WHERE user_id = ? AND is_read = 0 LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $notif = $result->fetch_assoc();
    echo "<div class='info'>";
    echo "<strong>Thông báo test:</strong><br>";
    echo "ID: {$notif['id']}<br>";
    echo "Title: {$notif['title']}<br>";
    echo "is_read TRƯỚC: {$notif['is_read']}";
    echo "</div>";
    
    // Thử UPDATE
    $stmt = $conn->prepare("UPDATE thongbao SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notif['id'], $user_id);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        echo "<div class='success'>";
        echo "✅ UPDATE thành công!<br>";
        echo "Affected rows: $affected";
        echo "</div>";
        
        // Kiểm tra lại
        $stmt = $conn->prepare("SELECT is_read FROM thongbao WHERE id = ?");
        $stmt->bind_param("i", $notif['id']);
        $stmt->execute();
        $check = $stmt->get_result()->fetch_assoc();
        
        echo "<div class='info'>";
        echo "is_read SAU: {$check['is_read']}";
        echo "</div>";
        
        if ($check['is_read'] == 1) {
            echo "<div class='success'>✅ Cập nhật database THÀNH CÔNG!</div>";
        } else {
            echo "<div class='error'>❌ Cập nhật database THẤT BẠI!</div>";
        }
        
        // Rollback để test lại
        $stmt = $conn->prepare("UPDATE thongbao SET is_read = 0 WHERE id = ?");
        $stmt->bind_param("i", $notif['id']);
        $stmt->execute();
        echo "<div class='info'>🔄 Đã rollback để test lại</div>";
        
    } else {
        echo "<div class='error'>❌ Lỗi UPDATE: " . $conn->error . "</div>";
    }
} else {
    echo "<div class='info'>ℹ️ Không có thông báo chưa đọc để test</div>";
}

// 4. Kiểm tra tất cả thông báo của user
echo "<h3>📊 Thông báo của bạn:</h3>";
$stmt = $conn->prepare("SELECT id, type, title, is_read, created_at FROM thongbao WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<table>";
echo "<tr><th>ID</th><th>Type</th><th>Title</th><th>is_read</th><th>Created</th></tr>";
while ($row = $result->fetch_assoc()) {
    $status = $row['is_read'] == 1 ? '✅' : '❌';
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['type']}</td>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td>$status {$row['is_read']}</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><a href='notifications.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại Thông Báo</a>";
?>
