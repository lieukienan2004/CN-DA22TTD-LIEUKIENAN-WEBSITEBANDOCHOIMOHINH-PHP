<?php
echo "=== KIỂM TRA KẾT NỐI DATABASE ===<br><br>";

// Bước 1: Kiểm tra file config
if (file_exists('config/database.php')) {
    echo "✓ File config/database.php tồn tại<br>";
    require_once 'config/database.php';
    
    // Bước 2: Kiểm tra kết nối
    if (isset($conn) && $conn) {
        echo "✓ Kết nối database thành công<br><br>";
        
        // Bước 3: Kiểm tra bảng thongbao
        $result = $conn->query("SHOW TABLES LIKE 'thongbao'");
        if ($result && $result->num_rows > 0) {
            echo "✓ Bảng thongbao tồn tại<br><br>";
            
            // Bước 4: Xem cấu trúc bảng
            echo "=== CẤU TRÚC BẢNG THONGBAO ===<br>";
            $columns = $conn->query("DESCRIBE thongbao");
            while ($col = $columns->fetch_assoc()) {
                echo "- {$col['Field']} ({$col['Type']})<br>";
            }
            echo "<br>";
            
            // Bước 5: Đếm số thông báo
            $count = $conn->query("SELECT COUNT(*) as total FROM thongbao")->fetch_assoc();
            echo "Tổng số thông báo: {$count['total']}<br><br>";
            
            // Bước 6: Kiểm tra admin
            $admin_count = $conn->query("SELECT COUNT(*) as total FROM admins")->fetch_assoc();
            echo "Tổng số admin: {$admin_count['total']}<br><br>";
            
            if ($admin_count['total'] > 0) {
                $admin = $conn->query("SELECT id, username FROM admins LIMIT 1")->fetch_assoc();
                echo "Admin đầu tiên: ID={$admin['id']}, Username={$admin['username']}<br><br>";
                
                // Bước 7: Thử insert thông báo
                echo "=== THỬ TẠO THÔNG BÁO ===<br>";
                
                // Kiểm tra có cột user_type không
                $has_user_type = false;
                $columns = $conn->query("DESCRIBE thongbao");
                while ($col = $columns->fetch_assoc()) {
                    if ($col['Field'] == 'user_type') {
                        $has_user_type = true;
                        break;
                    }
                }
                
                if ($has_user_type) {
                    echo "✓ Có cột user_type<br>";
                    $sql = "INSERT INTO thongbao (user_id, user_type, type, title, message, link) 
                            VALUES ({$admin['id']}, 'admin', 'contact', 'Test', 'Test message', 'admin/contacts.php')";
                } else {
                    echo "✗ CHƯA có cột user_type - CẦN THÊM!<br>";
                    echo "<strong>Chạy lệnh SQL này:</strong><br>";
                    echo "<code>ALTER TABLE thongbao ADD COLUMN user_type ENUM('user', 'admin') DEFAULT 'user' AFTER user_id;</code><br><br>";
                    $sql = "INSERT INTO thongbao (user_id, type, title, message, link) 
                            VALUES ({$admin['id']}, 'contact', 'Test', 'Test message', 'admin/contacts.php')";
                }
                
                if ($conn->query($sql)) {
                    echo "✓ Tạo thông báo test thành công! ID: " . $conn->insert_id . "<br><br>";
                    
                    // Hiển thị thông báo vừa tạo
                    echo "=== THÔNG BÁO VỪA TẠO ===<br>";
                    $notif = $conn->query("SELECT * FROM thongbao WHERE id = " . $conn->insert_id)->fetch_assoc();
                    echo "<pre>";
                    print_r($notif);
                    echo "</pre>";
                } else {
                    echo "✗ LỖI tạo thông báo: " . $conn->error . "<br>";
                }
            }
            
        } else {
            echo "✗ Bảng thongbao KHÔNG tồn tại<br>";
            echo "Cần chạy: setup_user_notifications.sql<br>";
        }
        
    } else {
        echo "✗ Không thể kết nối database<br>";
    }
    
} else {
    echo "✗ File config/database.php không tồn tại<br>";
}
?>
