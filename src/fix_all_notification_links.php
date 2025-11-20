<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập trước!");
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fix Links</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 5px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 5px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 5px 0; }
    </style>
</head>
<body>
    <h1>🔧 Sửa Notification Links</h1>
    
    <?php
    $fixed = 0;
    $errors = 0;
    
    // Lấy tất cả thông báo contact
    $result = $conn->query("SELECT * FROM thongbao WHERE type = 'contact'");
    
    if ($result && $result->num_rows > 0) {
        echo "<div class='card'>";
        echo "<h2>Đang sửa " . $result->num_rows . " thông báo contact...</h2>";
        
        while ($notif = $result->fetch_assoc()) {
            // Lấy user email
            $user_result = $conn->query("SELECT email FROM users WHERE id = " . $notif['user_id']);
            if ($user_result && $user_result->num_rows > 0) {
                $user = $user_result->fetch_assoc();
                
                // Tìm contact message gần nhất của user này
                $contact_result = $conn->query("
                    SELECT id, subject FROM contact_messages 
                    WHERE email = '" . $conn->real_escape_string($user['email']) . "' 
                    ORDER BY ABS(TIMESTAMPDIFF(SECOND, created_at, '" . $notif['created_at'] . "'))
                    LIMIT 1
                ");
                
                if ($contact_result && $contact_result->num_rows > 0) {
                    $contact = $contact_result->fetch_assoc();
                    $new_link = "view_my_contact.php?highlight=" . $contact['id'];
                    
                    // Update link
                    $update = $conn->prepare("UPDATE thongbao SET link = ? WHERE id = ?");
                    $update->bind_param("si", $new_link, $notif['id']);
                    
                    if ($update->execute()) {
                        echo "<div class='success'>✅ Thông báo #{$notif['id']}: <strong>" . htmlspecialchars($notif['title']) . "</strong><br>";
                        echo "→ Link mới: <code>$new_link</code></div>";
                        $fixed++;
                    } else {
                        echo "<div class='error'>❌ Lỗi sửa thông báo #{$notif['id']}: " . $conn->error . "</div>";
                        $errors++;
                    }
                } else {
                    echo "<div class='info'>⚠ Không tìm thấy contact message cho thông báo #{$notif['id']}</div>";
                }
            }
        }
        
        echo "</div>";
    } else {
        echo "<div class='info'>Không có thông báo contact nào cần sửa.</div>";
    }
    
    echo "<div class='card'>";
    echo "<h2>📊 Kết quả</h2>";
    echo "<p><strong>Đã sửa:</strong> $fixed</p>";
    if ($errors > 0) {
        echo "<p><strong>Lỗi:</strong> $errors</p>";
    }
    echo "</div>";
    ?>
    
    <div class="card">
        <a href="debug_links.php" style="display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">Kiểm tra lại</a>
        <a href="notifications.php" style="display: inline-block; padding: 10px 20px; background: #764ba2; color: white; text-decoration: none; border-radius: 5px;">Xem Thông Báo</a>
    </div>
</body>
</html>
