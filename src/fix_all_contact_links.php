<?php
require_once 'config/database.php';

echo "<h2>Sửa TẤT CẢ Links Thông Báo Contact</h2>";
echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; padding: 10px; background: #d4edda; margin: 5px 0; } .error { color: red; }</style>";

// Sửa tất cả thông báo contact, bất kể link hiện tại là gì
$sql = "
UPDATE thongbao t
INNER JOIN users u ON t.user_id = u.id
INNER JOIN (
    SELECT 
        cm.id as contact_id,
        cm.email,
        t2.id as notif_id,
        ABS(TIMESTAMPDIFF(SECOND, cm.created_at, t2.created_at)) as time_diff
    FROM contact_messages cm
    INNER JOIN users u2 ON cm.email = u2.email
    INNER JOIN thongbao t2 ON t2.user_id = u2.id
    WHERE t2.type = 'contact'
) as matches ON matches.notif_id = t.id
SET t.link = CONCAT('view_my_contact.php?highlight=', matches.contact_id)
WHERE t.type = 'contact'
AND matches.time_diff = (
    SELECT MIN(ABS(TIMESTAMPDIFF(SECOND, cm2.created_at, t.created_at)))
    FROM contact_messages cm2
    WHERE cm2.email = u.email
)
";

if ($conn->query($sql)) {
    $affected = $conn->affected_rows;
    echo "<div class='success'>✅ Đã sửa $affected thông báo!</div>";
} else {
    echo "<p class='error'>❌ Lỗi: " . $conn->error . "</p>";
    echo "<p>Thử phương pháp khác...</p>";
    
    // Phương pháp 2: Sửa từng thông báo
    $result = $conn->query("SELECT * FROM thongbao WHERE type = 'contact'");
    $fixed = 0;
    
    if ($result && $result->num_rows > 0) {
        while ($notif = $result->fetch_assoc()) {
            // Lấy email của user
            $user_result = $conn->query("SELECT email FROM users WHERE id = " . $notif['user_id']);
            if ($user_result && $user_result->num_rows > 0) {
                $user = $user_result->fetch_assoc();
                
                // Tìm contact message gần nhất
                $contact_result = $conn->query("
                    SELECT id FROM contact_messages 
                    WHERE email = '" . $conn->real_escape_string($user['email']) . "' 
                    ORDER BY ABS(TIMESTAMPDIFF(SECOND, created_at, '" . $notif['created_at'] . "'))
                    LIMIT 1
                ");
                
                if ($contact_result && $contact_result->num_rows > 0) {
                    $contact = $contact_result->fetch_assoc();
                    $new_link = "view_my_contact.php?highlight=" . $contact['id'];
                    
                    $update = $conn->prepare("UPDATE thongbao SET link = ? WHERE id = ?");
                    $update->bind_param("si", $new_link, $notif['id']);
                    
                    if ($update->execute()) {
                        echo "<div class='success'>✅ Sửa thông báo #{$notif['id']}: $new_link</div>";
                        $fixed++;
                    }
                }
            }
        }
        
        echo "<div class='success'>✅ Tổng cộng đã sửa: $fixed thông báo</div>";
    }
}

// Hiển thị kết quả
echo "<h3>Thông báo sau khi sửa:</h3>";
$result = $conn->query("SELECT id, type, title, link FROM thongbao WHERE type = 'contact' ORDER BY created_at DESC LIMIT 10");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #667eea; color: white;'><th>ID</th><th>Title</th><th>Link</th><th>Status</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $is_correct = (strpos($row['link'], 'view_my_contact.php?highlight=') === 0);
        $status = $is_correct ? "<span style='color: green;'>✅ Đúng</span>" : "<span style='color: red;'>❌ Sai</span>";
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td><code>" . htmlspecialchars($row['link']) . "</code></td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<br><br>";
echo "<a href='notifications.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Xem Thông Báo</a>";
?>
