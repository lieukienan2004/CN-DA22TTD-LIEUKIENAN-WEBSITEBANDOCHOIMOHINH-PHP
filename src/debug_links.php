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
    <title>Debug Links</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 Debug Notification Links</h1>
    
    <div class="card">
        <h2>Thông tin Project</h2>
        <p><strong>Document Root:</strong> <code><?php echo $_SERVER['DOCUMENT_ROOT']; ?></code></p>
        <p><strong>Script Path:</strong> <code><?php echo __FILE__; ?></code></p>
        <p><strong>Base URL:</strong> <code><?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']); ?></code></p>
    </div>
    
    <div class="card">
        <h2>Thông báo của bạn</h2>
        <?php
        $stmt = $conn->prepare("SELECT * FROM thongbao WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (empty($notifications)) {
            echo "<p class='warning'>⚠ Không có thông báo nào!</p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Type</th><th>Title</th><th>Link trong DB</th><th>Status</th></tr>";
            
            foreach ($notifications as $notif) {
                $link = $notif['link'];
                $status = "";
                
                if (empty($link) || $link === '#' || $link === 'notifications.php') {
                    $status = "<span class='error'>❌ Link không hợp lệ</span>";
                } else {
                    // Kiểm tra file
                    $file_path = $link;
                    if (strpos($link, '?') !== false) {
                        $file_path = substr($link, 0, strpos($link, '?'));
                    }
                    
                    if (file_exists($file_path)) {
                        $status = "<span class='success'>✅ OK</span>";
                    } else {
                        $status = "<span class='error'>❌ File không tồn tại: $file_path</span>";
                    }
                }
                
                echo "<tr>";
                echo "<td>{$notif['id']}</td>";
                echo "<td>{$notif['type']}</td>";
                echo "<td>" . htmlspecialchars($notif['title']) . "</td>";
                echo "<td><code>" . htmlspecialchars($link) . "</code></td>";
                echo "<td>$status</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
        ?>
    </div>
    
    <div class="card">
        <h2>Contact Messages của bạn</h2>
        <?php
        $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->bind_param("s", $_SESSION['user_email']);
        $stmt->execute();
        $contacts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (empty($contacts)) {
            echo "<p class='warning'>⚠ Không có contact message nào!</p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Subject</th><th>Status</th><th>Link đúng phải là</th></tr>";
            
            foreach ($contacts as $contact) {
                $correct_link = "view_my_contact.php?highlight=" . $contact['id'];
                echo "<tr>";
                echo "<td>{$contact['id']}</td>";
                echo "<td>" . htmlspecialchars($contact['subject']) . "</td>";
                echo "<td>{$contact['status']}</td>";
                echo "<td><code>$correct_link</code></td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
        ?>
    </div>
    
    <div class="card">
        <h2>🔧 Actions</h2>
        <a href="fix_all_notification_links.php" style="display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">Sửa tất cả links</a>
        <a href="notifications.php" style="display: inline-block; padding: 10px 20px; background: #764ba2; color: white; text-decoration: none; border-radius: 5px;">Xem Thông Báo</a>
    </div>
</body>
</html>
