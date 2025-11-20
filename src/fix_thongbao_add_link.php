<?php
require_once 'config/database.php';

echo "<h2>🔧 Thêm cột 'link' vào bảng thongbao</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
</style>";

// Kiểm tra xem cột link đã tồn tại chưa
$check = $conn->query("SHOW COLUMNS FROM thongbao LIKE 'link'");

if ($check && $check->num_rows > 0) {
    echo "<div class='success'>✅ Cột 'link' đã tồn tại trong bảng thongbao</div>";
} else {
    echo "<div class='info'>⏳ Đang thêm cột 'link' vào bảng thongbao...</div>";
    
    $sql = "ALTER TABLE thongbao ADD COLUMN link VARCHAR(255) DEFAULT NULL AFTER message";
    
    if ($conn->query($sql)) {
        echo "<div class='success'>✅ Đã thêm cột 'link' thành công!</div>";
        echo "<div class='info'>";
        echo "<strong>Cột đã được thêm:</strong><br>";
        echo "- Tên: link<br>";
        echo "- Kiểu: VARCHAR(255)<br>";
        echo "- Mặc định: NULL<br>";
        echo "- Vị trí: Sau cột 'message'";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
    }
}

echo "<br><a href='checkout.php' class='btn'>← Thử đặt hàng lại</a>";
echo "<a href='check_thongbao_columns.php' class='btn'>🔍 Kiểm tra cấu trúc bảng</a>";
?>
