<?php
require_once 'config/database.php';

echo "<h2>🔍 Kiểm tra bảng admins</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    th { background: #667eea; color: white; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

// Kiểm tra bảng admins
$check = $conn->query("SHOW TABLES LIKE 'admins'");

if ($check && $check->num_rows > 0) {
    echo "<div class='success'>✅ Bảng admins tồn tại</div>";
    
    // Xem cấu trúc
    echo "<h3>Cấu trúc bảng admins:</h3>";
    $result = $conn->query("DESCRIBE admins");
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
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
    
    // Lấy danh sách admin
    echo "<h3>Danh sách admin:</h3>";
    $admin_query = $conn->query("SELECT * FROM admins");
    
    if ($admin_query && $admin_query->num_rows > 0) {
        echo "<div class='success'>✅ Có " . $admin_query->num_rows . " admin</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Fullname</th><th>Email</th><th>Role</th></tr>";
        while ($admin = $admin_query->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$admin['id']}</td>";
            echo "<td>" . ($admin['username'] ?? 'N/A') . "</td>";
            echo "<td>" . ($admin['fullname'] ?? 'N/A') . "</td>";
            echo "<td>" . ($admin['email'] ?? 'N/A') . "</td>";
            echo "<td>" . ($admin['role'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div class='info'>";
        echo "<strong>✅ Kết luận:</strong><br>";
        echo "Hệ thống sử dụng bảng <strong>admins</strong> riêng để quản lý admin.<br>";
        echo "Cần sửa code để query từ bảng <strong>admins</strong> thay vì <strong>users WHERE role='admin'</strong>";
        echo "</div>";
        
    } else {
        echo "<div class='error'>❌ Không có admin nào trong bảng!</div>";
    }
    
} else {
    echo "<div class='error'>❌ Bảng admins KHÔNG tồn tại</div>";
    echo "<div class='info'>";
    echo "Hệ thống có thể sử dụng một trong các cách sau:<br>";
    echo "1. Bảng users với cột role/type<br>";
    echo "2. Bảng admins riêng<br>";
    echo "3. Bảng user_roles<br><br>";
    echo "Hãy kiểm tra các bảng khác...";
    echo "</div>";
    
    // Liệt kê tất cả các bảng
    echo "<h3>Tất cả các bảng trong database:</h3>";
    $tables = $conn->query("SHOW TABLES");
    echo "<ul>";
    while ($table = $tables->fetch_array()) {
        echo "<li><strong>" . $table[0] . "</strong></li>";
    }
    echo "</ul>";
}

echo "<br><a href='test_create_order_notification.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại Test</a>";
?>
