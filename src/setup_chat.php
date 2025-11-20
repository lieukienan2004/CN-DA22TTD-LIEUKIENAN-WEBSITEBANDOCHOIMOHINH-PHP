<?php
require_once 'config/database.php';

echo "<h2>Cài đặt hệ thống Chat</h2>";

// Đọc file SQL
$sql = file_get_contents('chat_system.sql');

// Tách các câu lệnh SQL
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$errors = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    if ($conn->query($statement)) {
        $success++;
        echo "<p style='color: green;'>✓ Thực thi thành công</p>";
    } else {
        $errors++;
        echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
    }
}

echo "<hr>";
echo "<h3>Kết quả:</h3>";
echo "<p>Thành công: $success câu lệnh</p>";
echo "<p>Lỗi: $errors câu lệnh</p>";

if ($errors == 0) {
    echo "<p style='color: green; font-weight: bold;'>✓ Cài đặt chat thành công!</p>";
    echo "<p><a href='index.php'>Về trang chủ</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ Có lỗi xảy ra. Vui lòng kiểm tra lại.</p>";
}

$conn->close();
?>
