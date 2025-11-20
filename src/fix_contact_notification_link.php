<?php
require_once 'config/database.php';

echo "<h2>SỬA LINK THÔNG BÁO LIÊN HỆ</h2>";

// Kiểm tra link hiện tại
echo "<h3>Trước khi sửa:</h3>";
$result = $conn->query("SELECT id, title, type, link FROM thongbao WHERE type = 'contact' ORDER BY created_at DESC LIMIT 10");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Title</th><th>Type</th><th>Link Cũ</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . $row['type'] . "</td>";
        echo "<td style='color: red; font-weight: bold;'>" . htmlspecialchars($row['link']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Không có thông báo liên hệ nào</p>";
}

// Cập nhật link
echo "<br><h3>Đang cập nhật...</h3>";
$update = $conn->query("UPDATE thongbao SET link = 'view_my_contact.php' WHERE type = 'contact'");

if ($update) {
    $affected = $conn->affected_rows;
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✓ Đã cập nhật $affected thông báo!</p>";
} else {
    echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
}

// Kiểm tra sau khi sửa
echo "<br><h3>Sau khi sửa:</h3>";
$result2 = $conn->query("SELECT id, title, type, link FROM thongbao WHERE type = 'contact' ORDER BY created_at DESC LIMIT 10");

if ($result2 && $result2->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Title</th><th>Type</th><th>Link Mới</th></tr>";
    
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . $row['type'] . "</td>";
        echo "<td style='color: green; font-weight: bold;'>" . htmlspecialchars($row['link']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><br>";
echo "<a href='notifications.php' style='display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 12px; font-weight: 600;'>";
echo "✓ Xong! Đi tới trang Thông Báo";
echo "</a>";
?>
