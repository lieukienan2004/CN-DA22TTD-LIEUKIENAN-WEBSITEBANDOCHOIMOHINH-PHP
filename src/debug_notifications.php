<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

echo "<h2>DEBUG THÔNG BÁO</h2>";
echo "<hr>";

// 1. Kiểm tra cột user_type có tồn tại không
echo "<h3>1. Kiểm tra cột user_type:</h3>";
$result = $conn->query("SHOW COLUMNS FROM thongbao LIKE 'user_type'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Cột user_type đã tồn tại</p>";
    $row = $result->fetch_assoc();
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>✗ Cột user_type CHƯA tồn tại - CẦN CHẠY SQL!</p>";
    echo "<p><strong>Chạy lệnh này trong phpMyAdmin:</strong></p>";
    echo "<pre>ALTER TABLE thongbao ADD COLUMN user_type ENUM('user', 'admin') DEFAULT 'user' AFTER user_id;</pre>";
}

echo "<hr>";

// 2. Kiểm tra foreign key
echo "<h3>2. Kiểm tra Foreign Key:</h3>";
$result = $conn->query("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = 'kienan123' 
    AND TABLE_NAME = 'thongbao' 
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: orange;'>⚠ Vẫn còn foreign key constraints:</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<p>- " . $row['CONSTRAINT_NAME'] . "</p>";
    }
    echo "<p><strong>Cần xóa bằng lệnh:</strong></p>";
    echo "<pre>ALTER TABLE thongbao DROP FOREIGN KEY thongbao_ibfk_1;</pre>";
} else {
    echo "<p style='color: green;'>✓ Không có foreign key constraint (OK)</p>";
}

echo "<hr>";

// 3. Test insert thông báo cho admin
echo "<h3>3. Test Insert Thông Báo:</h3>";
$admin_result = $conn->query("SELECT id, username FROM admins LIMIT 1");
if ($admin_result && $admin_result->num_rows > 0) {
    $admin = $admin_result->fetch_assoc();
    echo "<p>Admin ID: {$admin['id']} - Username: {$admin['username']}</p>";
    
    // Kiểm tra xem có cột user_type không
    $columns = $conn->query("SHOW COLUMNS FROM thongbao");
    $has_user_type = false;
    while ($col = $columns->fetch_assoc()) {
        if ($col['Field'] == 'user_type') {
            $has_user_type = true;
            break;
        }
    }
    
    if ($has_user_type) {
        // Insert với user_type
        $test_stmt = $conn->prepare("
            INSERT INTO thongbao (user_id, user_type, type, title, message, link, created_at) 
            VALUES (?, 'admin', 'contact', ?, ?, ?, NOW())
        ");
        $title = "Test thông báo " . date('H:i:s');
        $message = "Đây là thông báo test lúc " . date('Y-m-d H:i:s');
        $link = "admin/contacts.php";
        
        $test_stmt->bind_param("isss", $admin['id'], $title, $message, $link);
        
        if ($test_stmt->execute()) {
            $insert_id = $conn->insert_id;
            echo "<p style='color: green;'>✓ Insert thành công! ID: $insert_id</p>";
        } else {
            echo "<p style='color: red;'>✗ Lỗi insert: " . $conn->error . "</p>";
        }
    } else {
        // Insert không có user_type
        $test_stmt = $conn->prepare("
            INSERT INTO thongbao (user_id, type, title, message, link, created_at) 
            VALUES (?, 'contact', ?, ?, ?, NOW())
        ");
        $title = "Test thông báo " . date('H:i:s');
        $message = "Đây là thông báo test lúc " . date('Y-m-d H:i:s');
        $link = "admin/contacts.php";
        
        $test_stmt->bind_param("isss", $admin['id'], $title, $message, $link);
        
        if ($test_stmt->execute()) {
            $insert_id = $conn->insert_id;
            echo "<p style='color: green;'>✓ Insert thành công! ID: $insert_id</p>";
            echo "<p style='color: orange;'>⚠ Nhưng không có cột user_type - cần thêm cột này!</p>";
        } else {
            echo "<p style='color: red;'>✗ Lỗi insert: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>✗ Không tìm thấy admin!</p>";
}

echo "<hr>";

// 4. Xem dữ liệu thông báo
echo "<h3>4. Dữ Liệu Thông Báo (10 mới nhất):</h3>";
$result = $conn->query("SELECT * FROM thongbao ORDER BY created_at DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>User ID</th><th>User Type</th><th>Type</th><th>Title</th><th>Is Read</th><th>Created</th>";
    echo "</tr>";
    while ($row = $result->fetch_assoc()) {
        $user_type = isset($row['user_type']) ? $row['user_type'] : 'N/A';
        $bg_color = $user_type == 'admin' ? '#ffe6e6' : '#e6f3ff';
        echo "<tr style='background: $bg_color;'>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td><strong>$user_type</strong></td>";
        echo "<td>{$row['type']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>" . ($row['is_read'] ? 'Đã đọc' : '<strong>Chưa đọc</strong>') . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>Chưa có thông báo nào</p>";
}

echo "<hr>";

// 5. Hướng dẫn
echo "<h3>5. Hướng Dẫn Sửa Lỗi:</h3>";
echo "<ol>";
echo "<li>Mở phpMyAdmin</li>";
echo "<li>Chọn database <strong>kienan123</strong></li>";
echo "<li>Tab <strong>SQL</strong></li>";
echo "<li>Copy và chạy các lệnh sau:</li>";
echo "</ol>";

echo "<textarea style='width: 100%; height: 200px; font-family: monospace;'>";
echo "-- Bước 1: Xóa foreign key (nếu có)\n";
echo "ALTER TABLE thongbao DROP FOREIGN KEY thongbao_ibfk_1;\n\n";
echo "-- Bước 2: Thêm cột user_type\n";
echo "ALTER TABLE thongbao ADD COLUMN user_type ENUM('user', 'admin') DEFAULT 'user' AFTER user_id;\n\n";
echo "-- Bước 3: Cập nhật dữ liệu cũ (nếu có)\n";
echo "UPDATE thongbao SET user_type = 'user' WHERE user_type IS NULL;\n";
echo "</textarea>";

echo "<hr>";
echo "<p><a href='admin/index.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Đăng nhập Admin để test</a></p>";

$conn->close();
?>
