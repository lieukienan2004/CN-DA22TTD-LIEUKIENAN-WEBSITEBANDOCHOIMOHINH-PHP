<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập!");
}

echo "<h2>📋 Tất cả thông báo của bạn</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    th { background: #667eea; color: white; }
    tr:hover { background: #f5f5f5; }
    .correct { color: green; font-weight: bold; }
    .wrong { color: red; font-weight: bold; }
    a { color: #667eea; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>";

// Lấy tất cả thông báo
$stmt = $conn->prepare("SELECT * FROM thongbao WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<p>Không có thông báo nào.</p>";
} else {
    echo "<table>";
    echo "<tr>
        <th>ID</th>
        <th>Type</th>
        <th>Title</th>
        <th>Link</th>
        <th>Status</th>
        <th>Action</th>
    </tr>";
    
    while ($notif = $result->fetch_assoc()) {
        $link = $notif['link'];
        $is_correct = false;
        $status = "";
        
        if ($notif['type'] === 'contact') {
            $is_correct = (strpos($link, 'view_my_contact.php?highlight=') === 0);
            $status = $is_correct ? "<span class='correct'>✅ Đúng</span>" : "<span class='wrong'>❌ Sai</span>";
        } else {
            $status = "N/A";
        }
        
        echo "<tr>";
        echo "<td>{$notif['id']}</td>";
        echo "<td>{$notif['type']}</td>";
        echo "<td>" . htmlspecialchars($notif['title']) . "</td>";
        echo "<td><code>" . htmlspecialchars($link) . "</code></td>";
        echo "<td>$status</td>";
        echo "<td>";
        if (!empty($link) && $link !== '#') {
            echo "<a href='" . htmlspecialchars($link) . "' target='_blank'>Test</a>";
        }
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<br>";
echo "<a href='notifications.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại Thông Báo</a>";
?>
