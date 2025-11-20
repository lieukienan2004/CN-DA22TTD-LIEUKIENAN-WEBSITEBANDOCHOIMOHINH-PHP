<?php
require_once 'config/database.php';

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

// Kiểm tra cấu trúc bảng
$result = $conn->query("DESCRIBE thongbao");

if ($result) {
    echo "<div class='success'>✅ Bảng thongbao tồn tại</div>";
    
    echo "<h3>Cấu trúc bảng:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $has_link = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'link') {
            $has_link = true;
        }
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if (!$has_link) {
        echo "<div class='error'>❌ Bảng thongbao KHÔNG có cột 'link'</div>";
        echo "<div class='info'>";
        echo "<strong>Cần thêm cột 'link':</strong><br>";
        echo "<code>ALTER TABLE thongbao ADD COLUMN link VARCHAR(255) DEFAULT NULL;</code>";
        echo "</div>";
        
        if (isset($_GET['add_link']) && $_GET['add_link'] == 'yes') {
            $sql = "ALTER TABLE thongbao ADD COLUMN link VARCHAR(255) DEFAULT NULL";
            if ($conn->query($sql)) {
                echo "<div class='success'>✅ Đã thêm cột 'link' thành công!</div>";
                echo "<a href='check_thongbao_columns.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>🔄 Tải lại</a>";
            } else {
                echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
            }
        } else {
            echo "<a href='?add_link=yes' style='display: inline-block; padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0;'>➕ Thêm cột 'link' ngay</a>";
        }
    } else {
        echo "<div class='success'>✅ Bảng thongbao ĐÃ có cột 'link'</div>";
    }
    
} else {
    echo "<div class='error'>❌ Không thể kiểm tra bảng: " . $conn->error . "</div>";
}

echo "<br><a href='checkout.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại Checkout</a>";
?>
