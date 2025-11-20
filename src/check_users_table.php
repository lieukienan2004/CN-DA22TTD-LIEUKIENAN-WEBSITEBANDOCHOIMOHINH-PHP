<?php
require_once 'config/database.php';

echo "<h2>🔍 Kiểm tra cấu trúc bảng users</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    th { background: #667eea; color: white; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #c8e6c9; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffcdd2; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

// Kiểm tra cấu trúc bảng users
$result = $conn->query("DESCRIBE users");

if ($result) {
    echo "<div class='success'>✅ Bảng users tồn tại</div>";
    
    echo "<h3>Cấu trúc bảng users:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $has_role = false;
    $role_column = '';
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
        
        // Tìm cột role
        if (stripos($row['Field'], 'role') !== false || stripos($row['Field'], 'type') !== false) {
            $role_column = $row['Field'];
            $has_role = true;
        }
    }
    echo "</table>";
    
    if ($has_role) {
        echo "<div class='success'>✅ Tìm thấy cột role: <strong>$role_column</strong></div>";
        
        // Lấy danh sách admin
        echo "<h3>Danh sách admin:</h3>";
        $admin_query = $conn->query("SELECT id, fullname, email, $role_column FROM users WHERE $role_column = 'admin' OR $role_column = 'Admin'");
        
        if ($admin_query && $admin_query->num_rows > 0) {
            echo "<div class='success'>✅ Có " . $admin_query->num_rows . " admin</div>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Fullname</th><th>Email</th><th>$role_column</th></tr>";
            while ($admin = $admin_query->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$admin['id']}</td>";
                echo "<td>{$admin['fullname']}</td>";
                echo "<td>{$admin['email']}</td>";
                echo "<td>{$admin[$role_column]}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='error'>❌ Không tìm thấy admin nào!</div>";
            echo "<div class='info'>";
            echo "<strong>Thử tìm với các giá trị khác:</strong><br>";
            
            // Thử tìm tất cả giá trị trong cột role
            $all_roles = $conn->query("SELECT DISTINCT $role_column FROM users");
            if ($all_roles) {
                echo "Các giá trị trong cột $role_column:<br>";
                while ($r = $all_roles->fetch_assoc()) {
                    echo "- " . ($r[$role_column] ?? 'NULL') . "<br>";
                }
            }
            echo "</div>";
        }
    } else {
        echo "<div class='error'>❌ Không tìm thấy cột role hoặc type trong bảng users</div>";
    }
    
} else {
    echo "<div class='error'>❌ Không thể kiểm tra bảng: " . $conn->error . "</div>";
}

echo "<br><a href='test_create_order_notification.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại Test</a>";
?>
