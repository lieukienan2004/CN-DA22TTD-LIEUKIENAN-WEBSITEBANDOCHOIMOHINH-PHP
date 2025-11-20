<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập trước!");
}

echo "<h2>Sửa Links Thông Báo</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
    .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
</style>";

$fixed_count = 0;
$error_count = 0;

// 1. Sửa các thông báo contact có link sai
echo "<h3>1. Sửa thông báo contact</h3>";

// Tìm các thông báo contact có link không đúng format
$result = $conn->query("
    SELECT * FROM thongbao 
    WHERE type = 'contact' 
    AND (link IS NULL OR link = '' OR link = '#' OR link = 'notifications.php' OR link = 'view_my_contact.php')
");

if ($result && $result->num_rows > 0) {
    while ($notif = $result->fetch_assoc()) {
        // Tìm contact_id từ message hoặc title
        // Thử extract từ message nếu có pattern "Chủ đề: ..."
        $contact_id = null;
        
        // Tìm contact message gần nhất của user
        $user_check = $conn->query("SELECT id FROM users WHERE id = " . $notif['user_id']);
        if ($user_check && $user_check->num_rows > 0) {
            $user = $user_check->fetch_assoc();
            $user_email_result = $conn->query("SELECT email FROM users WHERE id = " . $user['id']);
            if ($user_email_result && $user_email_result->num_rows > 0) {
                $user_data = $user_email_result->fetch_assoc();
                
                // Tìm contact message gần nhất
                $contact_result = $conn->query("
                    SELECT id FROM contact_messages 
                    WHERE email = '" . $conn->real_escape_string($user_data['email']) . "' 
                    ORDER BY ABS(TIMESTAMPDIFF(SECOND, created_at, '" . $notif['created_at'] . "'))
                    LIMIT 1
                ");
                
                if ($contact_result && $contact_result->num_rows > 0) {
                    $contact = $contact_result->fetch_assoc();
                    $contact_id = $contact['id'];
                }
            }
        }
        
        if ($contact_id) {
            $new_link = "view_my_contact.php?highlight=" . $contact_id;
            $update = $conn->prepare("UPDATE thongbao SET link = ? WHERE id = ?");
            $update->bind_param("si", $new_link, $notif['id']);
            
            if ($update->execute()) {
                echo "<div class='success'>✓ Đã sửa thông báo ID {$notif['id']}: $new_link</div>";
                $fixed_count++;
            } else {
                echo "<div class='error'>✗ Lỗi sửa thông báo ID {$notif['id']}: " . $conn->error . "</div>";
                $error_count++;
            }
        } else {
            echo "<div class='info'>⚠ Không tìm thấy contact_id cho thông báo ID {$notif['id']}</div>";
        }
    }
} else {
    echo "<div class='info'>Không có thông báo contact nào cần sửa.</div>";
}

// 2. Thêm contact_id vào các thông báo đã có link đúng
echo "<h3>2. Cập nhật contact_id (nếu bảng có cột này)</h3>";

// Kiểm tra xem bảng có cột contact_id không
$columns = $conn->query("SHOW COLUMNS FROM thongbao LIKE 'contact_id'");
if ($columns && $columns->num_rows > 0) {
    $result = $conn->query("
        SELECT * FROM thongbao 
        WHERE type = 'contact' 
        AND link LIKE 'view_my_contact.php?highlight=%'
        AND (contact_id IS NULL OR contact_id = 0)
    ");
    
    if ($result && $result->num_rows > 0) {
        while ($notif = $result->fetch_assoc()) {
            // Extract contact_id từ link
            if (preg_match('/highlight=(\d+)/', $notif['link'], $matches)) {
                $contact_id = (int)$matches[1];
                
                $update = $conn->prepare("UPDATE thongbao SET contact_id = ? WHERE id = ?");
                $update->bind_param("ii", $contact_id, $notif['id']);
                
                if ($update->execute()) {
                    echo "<div class='success'>✓ Đã cập nhật contact_id=$contact_id cho thông báo ID {$notif['id']}</div>";
                    $fixed_count++;
                }
            }
        }
    } else {
        echo "<div class='info'>Không có thông báo nào cần cập nhật contact_id.</div>";
    }
} else {
    echo "<div class='info'>Bảng thongbao chưa có cột contact_id. Chạy file add_contact_id_to_thongbao.sql nếu muốn thêm.</div>";
}

echo "<hr>";
echo "<h3>Tổng kết</h3>";
echo "<div class='success'>✓ Đã sửa: $fixed_count</div>";
if ($error_count > 0) {
    echo "<div class='error'>✗ Lỗi: $error_count</div>";
}

echo "<br><br>";
echo "<a href='check_notification_links.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-right: 10px;'>Kiểm tra lại</a>";
echo "<a href='notifications.php' style='padding: 10px 20px; background: #764ba2; color: white; text-decoration: none; border-radius: 8px;'>Xem Thông Báo</a>";
?>
