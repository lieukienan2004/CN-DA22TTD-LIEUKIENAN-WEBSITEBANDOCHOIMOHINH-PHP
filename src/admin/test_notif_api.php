<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

echo "<h2>TEST NOTIFICATION API</h2>";
echo "<hr>";

echo "<h3>Session Info:</h3>";
echo "Admin ID: " . ($_SESSION['admin_id'] ?? 'NOT SET') . "<br>";
echo "Admin Username: " . ($_SESSION['admin_username'] ?? 'NOT SET') . "<br>";
echo "<br>";

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    
    echo "<h3>Query thông báo:</h3>";
    $sql = "SELECT * FROM thongbao WHERE user_id = $admin_id AND user_type = 'admin' AND is_read = 0 ORDER BY created_at DESC LIMIT 10";
    echo "<code>$sql</code><br><br>";
    
    $result = $conn->query($sql);
    
    if ($result) {
        echo "Số thông báo tìm thấy: " . $result->num_rows . "<br><br>";
        
        if ($result->num_rows > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>User ID</th><th>User Type</th><th>Type</th><th>Title</th><th>Message</th><th>Created</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['user_id']}</td>";
                echo "<td>{$row['user_type']}</td>";
                echo "<td>{$row['type']}</td>";
                echo "<td>{$row['title']}</td>";
                echo "<td>{$row['message']}</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>Không có thông báo nào cho admin ID: $admin_id</p>";
            
            // Kiểm tra tất cả thông báo admin
            echo "<h3>Tất cả thông báo admin trong hệ thống:</h3>";
            $all_result = $conn->query("SELECT * FROM thongbao WHERE user_type = 'admin' ORDER BY created_at DESC");
            if ($all_result && $all_result->num_rows > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Title</th><th>Is Read</th><th>Created</th></tr>";
                while ($row = $all_result->fetch_assoc()) {
                    $highlight = ($row['user_id'] == $admin_id) ? "background: yellow;" : "";
                    echo "<tr style='$highlight'>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['user_id']}</td>";
                    echo "<td>{$row['type']}</td>";
                    echo "<td>{$row['title']}</td>";
                    echo "<td>" . ($row['is_read'] ? 'Đã đọc' : 'Chưa đọc') . "</td>";
                    echo "<td>{$row['created_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<p><em>Dòng màu vàng là thông báo của bạn (nếu có)</em></p>";
            }
        }
    } else {
        echo "<p style='color: red;'>Lỗi query: " . $conn->error . "</p>";
    }
    
    echo "<hr>";
    echo "<h3>Test API Response:</h3>";
    echo "<a href='get_notifications.php' target='_blank'>Xem JSON response</a>";
    
} else {
    echo "<p style='color: red;'>Chưa đăng nhập admin!</p>";
}
?>
