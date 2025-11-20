<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập!");
}

echo "<h2>🗑️ Xóa Thông Báo Test</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; cursor: pointer; border: none; }
    .btn-danger { background: #ef4444; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    th { background: #667eea; color: white; }
    tr:hover { background: #f5f5f5; }
</style>";

$user_id = $_SESSION['user_id'];

// Xử lý xóa
if (isset($_POST['delete_selected'])) {
    $ids = $_POST['notification_ids'] ?? [];
    
    if (!empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $conn->prepare("DELETE FROM thongbao WHERE id IN ($placeholders) AND user_id = ?");
        
        // Bind parameters
        $types = str_repeat('i', count($ids)) . 'i';
        $params = array_merge($ids, [$user_id]);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $deleted = $stmt->affected_rows;
            echo "<div class='success'>✅ Đã xóa $deleted thông báo thành công!</div>";
        } else {
            echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='error'>❌ Vui lòng chọn ít nhất một thông báo để xóa!</div>";
    }
}

// Xóa tất cả thông báo "Đơn hàng mới" từ test
if (isset($_POST['delete_all_order'])) {
    $stmt = $conn->prepare("DELETE FROM thongbao WHERE user_id = ? AND type = 'order' AND title LIKE '%Đơn hàng mới%'");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $deleted = $stmt->affected_rows;
        echo "<div class='success'>✅ Đã xóa $deleted thông báo đơn hàng test!</div>";
    } else {
        echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
    }
}

// Xóa TẤT CẢ thông báo
if (isset($_POST['delete_all'])) {
    $stmt = $conn->prepare("DELETE FROM thongbao WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $deleted = $stmt->affected_rows;
        echo "<div class='success'>✅ Đã xóa TẤT CẢ $deleted thông báo!</div>";
    } else {
        echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
    }
}

// Lấy danh sách thông báo
$stmt = $conn->prepare("SELECT * FROM thongbao WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<div class='info'>";
    echo "<strong>📊 Tổng số thông báo:</strong> " . $result->num_rows;
    echo "</div>";
    
    // Form xóa
    echo "<form method='POST' onsubmit='return confirm(\"Bạn có chắc muốn xóa?\");'>";
    
    // Nút xóa nhanh
    echo "<div style='margin: 20px 0; display: flex; gap: 10px;'>";
    echo "<button type='submit' name='delete_all_order' class='btn btn-danger'>🗑️ Xóa tất cả thông báo Đơn hàng</button>";
    echo "<button type='submit' name='delete_all' class='btn btn-danger' onclick='return confirm(\"XÓA TẤT CẢ thông báo? Hành động này không thể hoàn tác!\");'>🗑️ Xóa TẤT CẢ thông báo</button>";
    echo "</div>";
    
    // Bảng thông báo
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<th><input type='checkbox' id='select_all' onclick='toggleAll(this)'></th>";
    echo "<th>ID</th>";
    echo "<th>Type</th>";
    echo "<th>Title</th>";
    echo "<th>Message</th>";
    echo "<th>Trạng thái</th>";
    echo "<th>Thời gian</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    while ($row = $result->fetch_assoc()) {
        $status = $row['is_read'] ? '✅ Đã đọc' : '❌ Chưa đọc';
        $highlight = strpos($row['title'], 'Đơn hàng mới') !== false ? 'background: #fff3cd;' : '';
        
        echo "<tr style='$highlight'>";
        echo "<td><input type='checkbox' name='notification_ids[]' value='{$row['id']}' class='notif-checkbox'></td>";
        echo "<td><strong>#{$row['id']}</strong></td>";
        echo "<td><span style='padding: 4px 8px; background: #667eea; color: white; border-radius: 4px; font-size: 12px;'>{$row['type']}</span></td>";
        echo "<td><strong>" . htmlspecialchars($row['title']) . "</strong></td>";
        echo "<td>" . htmlspecialchars(mb_substr($row['message'], 0, 50)) . "...</td>";
        echo "<td>$status</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($row['created_at'])) . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    
    echo "<button type='submit' name='delete_selected' class='btn btn-danger'>🗑️ Xóa các thông báo đã chọn</button>";
    echo "</form>";
    
} else {
    echo "<div class='info'>ℹ️ Không có thông báo nào</div>";
}

echo "<br><a href='notifications.php' class='btn'>← Quay lại Thông Báo</a>";
?>

<script>
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.notif-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}
</script>
