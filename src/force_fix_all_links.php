<?php
require_once 'config/database.php';

echo "<h2>🔧 FORCE FIX - Sửa TẤT CẢ Links</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .success { color: green; padding: 10px; background: #d4edda; margin: 5px 0; border-radius: 5px; }
    .error { color: red; padding: 10px; background: #f8d7da; margin: 5px 0; border-radius: 5px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #667eea; color: white; }
</style>";

// Lấy TẤT CẢ thông báo contact
$result = $conn->query("SELECT * FROM thongbao WHERE type = 'contact' ORDER BY id DESC");

if (!$result) {
    die("<div class='error'>❌ Lỗi query: " . $conn->error . "</div>");
}

$total = $result->num_rows;
$fixed = 0;
$errors = 0;

echo "<div class='success'>📊 Tìm thấy $total thông báo contact</div>";

if ($total > 0) {
    echo "<h3>Đang xử lý...</h3>";
    
    while ($notif = $result->fetch_assoc()) {
        // Lấy user email
        $user_result = $conn->query("SELECT email FROM users WHERE id = " . $notif['user_id']);
        
        if ($user_result && $user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            
            // Tìm contact message gần nhất của user
            $contact_result = $conn->query("
                SELECT id, subject FROM contact_messages 
                WHERE email = '" . $conn->real_escape_string($user['email']) . "' 
                ORDER BY ABS(TIMESTAMPDIFF(SECOND, created_at, '" . $notif['created_at'] . "'))
                LIMIT 1
            ");
            
            if ($contact_result && $contact_result->num_rows > 0) {
                $contact = $contact_result->fetch_assoc();
                $new_link = "view_my_contact.php?highlight=" . $contact['id'];
                
                // Kiểm tra xem link có khác không
                if ($notif['link'] !== $new_link) {
                    $update = $conn->prepare("UPDATE thongbao SET link = ? WHERE id = ?");
                    $update->bind_param("si", $new_link, $notif['id']);
                    
                    if ($update->execute()) {
                        echo "<div class='success'>✅ #{$notif['id']}: <strong>" . htmlspecialchars($notif['title']) . "</strong><br>";
                        echo "Cũ: <code>" . htmlspecialchars($notif['link']) . "</code><br>";
                        echo "Mới: <code>$new_link</code></div>";
                        $fixed++;
                    } else {
                        echo "<div class='error'>❌ Lỗi sửa #{$notif['id']}: " . $conn->error . "</div>";
                        $errors++;
                    }
                } else {
                    echo "<div style='color: #666; padding: 5px; margin: 2px 0;'>⏭ #{$notif['id']}: Đã đúng, bỏ qua</div>";
                }
            } else {
                echo "<div class='error'>⚠ Không tìm thấy contact message cho thông báo #{$notif['id']}</div>";
                $errors++;
            }
        } else {
            echo "<div class='error'>⚠ Không tìm thấy user cho thông báo #{$notif['id']}</div>";
            $errors++;
        }
    }
}

echo "<hr>";
echo "<h2>📊 Kết quả cuối cùng</h2>";
echo "<div class='success'><strong>✅ Đã sửa:</strong> $fixed thông báo</div>";
if ($errors > 0) {
    echo "<div class='error'><strong>❌ Lỗi:</strong> $errors</div>";
}

// Hiển thị tất cả thông báo sau khi sửa
echo "<h3>Tất cả thông báo contact sau khi sửa:</h3>";
$final_result = $conn->query("SELECT id, title, link FROM thongbao WHERE type = 'contact' ORDER BY id DESC");

if (!$final_result) {
    echo "<div class='error'>Lỗi query: " . $conn->error . "</div>";
} elseif ($final_result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Title</th><th>Link</th><th>Status</th></tr>";
    
    while ($row = $final_result->fetch_assoc()) {
        $is_correct = (strpos($row['link'], 'view_my_contact.php?highlight=') === 0);
        $status_color = $is_correct ? 'green' : 'red';
        $status_icon = $is_correct ? '✅' : '❌';
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td><code>" . htmlspecialchars($row['link']) . "</code></td>";
        echo "<td style='color: $status_color; font-weight: bold;'>$status_icon</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<br><br>";
echo "<a href='notifications.php' style='display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>🔔 Xem Thông Báo</a>";
?>
