<?php
// File này sẽ tự động thêm cột avatar vào bảng users
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Setup Avatar Column</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #10b981; padding: 15px; background: #d1fae5; border-radius: 5px; margin: 10px 0; }
        .error { color: #ef4444; padding: 15px; background: #fee2e2; border-radius: 5px; margin: 10px 0; }
        .info { color: #3b82f6; padding: 15px; background: #dbeafe; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #ec4899; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Setup Avatar Column</h1>";

try {
    // Kiểm tra xem cột avatar đã tồn tại chưa
    $check = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    
    if ($check->num_rows > 0) {
        echo "<div class='info'>✅ Cột 'avatar' đã tồn tại trong bảng users.</div>";
    } else {
        // Thêm cột avatar
        $sql = "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER address";
        
        if ($conn->query($sql)) {
            echo "<div class='success'>✅ Đã thêm cột 'avatar' vào bảng users thành công!</div>";
        } else {
            echo "<div class='error'>❌ Lỗi khi thêm cột: " . $conn->error . "</div>";
        }
    }
    
    // Kiểm tra thư mục uploads
    $upload_dir = 'uploads/avatars/';
    if (!file_exists($upload_dir)) {
        if (mkdir($upload_dir, 0777, true)) {
            echo "<div class='success'>✅ Đã tạo thư mục uploads/avatars/</div>";
        } else {
            echo "<div class='error'>❌ Không thể tạo thư mục uploads/avatars/</div>";
        }
    } else {
        echo "<div class='info'>✅ Thư mục uploads/avatars/ đã tồn tại.</div>";
    }
    
    echo "<div class='success'>
            <h3>✅ Setup hoàn tất!</h3>
            <p>Bây giờ bạn có thể upload avatar trong trang tài khoản.</p>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi: " . $e->getMessage() . "</div>";
}

echo "
        <a href='account.php' class='btn'>Đi đến trang tài khoản</a>
        <a href='index.php' class='btn' style='background: #6b7280;'>Về trang chủ</a>
    </div>
</body>
</html>";

$conn->close();
?>
