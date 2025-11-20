<?php
// Test API trực tiếp
session_start();
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Chat API Direct</h2>";

// Test 1: Init
echo "<h3>1. Test Init:</h3>";
$_POST['action'] = 'init';
$_POST['name'] = 'Test User';

ob_start();
include 'chat_api.php';
$response = ob_get_clean();

echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";

$data = json_decode($response, true);
if ($data && isset($data['success']) && $data['success']) {
    echo "<p style='color: green;'>✓ Init thành công</p>";
    echo "<p>Session ID: " . $data['session_id'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ Init thất bại</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a></p>";
?>
