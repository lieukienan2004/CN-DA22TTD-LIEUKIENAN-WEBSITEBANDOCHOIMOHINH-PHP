<?php
require_once 'config/database.php';

echo "<h2>Sửa Links Thông Báo</h2>";
echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; } .error { color: red; }</style>";

// Sửa tất cả thông báo contact có link sai
$sql = "
UPDATE thongbao t
INNER JOIN users u ON t.user_id = u.id
INNER JOIN contact_messages cm ON cm.email = u.email
SET t.link = CONCAT('view_my_contact.php?highlight=', cm.id)
WHERE t.type = 'contact'
AND (t.link LIKE 'contact.php%' OR t.link IS NULL OR t.link = '' OR t.link = '#' OR t.link = 'notifications.php' OR t.link = 'view_my_contact.php')
AND ABS(TIMESTAMPDIFF(SECOND, cm.created_at, t.created_at)) = (
    SELECT MIN(ABS(TIMESTAMPDIFF(SECOND, cm2.created_at, t.created_at)))
    FROM contact_messages cm2
    WHERE cm2.email = u.email
)
";

if ($conn->query($sql)) {
    $affected = $conn->affected_rows;
    echo "<p class='success'>✅ Đã sửa $affected thông báo!</p>";
} else {
    echo "<p class='error'>❌ Lỗi: " . $conn->error . "</p>";
}

// Hiển thị kết quả
echo "<h3>Thông báo sau khi sửa:</h3>";
$result = $conn->query("SELECT id, type, title, link FROM thongbao WHERE type = 'contact' ORDER BY created_at DESC LIMIT 10");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Type</th><th>Title</th><th>Link</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['type']}</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td><code>" . htmlspecialchars($row['link']) . "</code></td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<br><br>";
echo "<a href='notifications.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Xem Thông Báo</a>";
?>
