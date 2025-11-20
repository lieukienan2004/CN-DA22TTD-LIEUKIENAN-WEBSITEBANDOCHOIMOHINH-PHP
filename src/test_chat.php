<?php
session_start();
require_once 'config/database.php';

echo "<h2>Test Chat API</h2>";

// Test 1: Kiểm tra kết nối database
echo "<h3>1. Kiểm tra database:</h3>";
if ($conn) {
    echo "<p style='color: green;'>✓ Kết nối database thành công</p>";
} else {
    echo "<p style='color: red;'>✗ Lỗi kết nối database</p>";
    exit;
}

// Test 2: Kiểm tra bảng chat_sessions
echo "<h3>2. Kiểm tra bảng chat_sessions:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'chat_sessions'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Bảng chat_sessions tồn tại</p>";
} else {
    echo "<p style='color: red;'>✗ Bảng chat_sessions không tồn tại</p>";
}

// Test 3: Kiểm tra bảng chat_messages
echo "<h3>3. Kiểm tra bảng chat_messages:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'chat_messages'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Bảng chat_messages tồn tại</p>";
} else {
    echo "<p style='color: red;'>✗ Bảng chat_messages không tồn tại</p>";
}

// Test 4: Test API init
echo "<h3>4. Test API init:</h3>";
$_POST['action'] = 'init';
ob_start();
include 'chat_api.php';
$response = ob_get_clean();
echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";

// Test 5: Kiểm tra session
echo "<h3>5. Kiểm tra session:</h3>";
if (isset($_SESSION['chat_session_id'])) {
    echo "<p style='color: green;'>✓ Session ID: " . $_SESSION['chat_session_id'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ Không có session ID</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a></p>";
?>
