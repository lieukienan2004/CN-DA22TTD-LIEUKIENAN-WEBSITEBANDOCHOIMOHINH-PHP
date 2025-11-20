<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập!");
}

// Test link trực tiếp
echo "<h2>Test Links</h2>";
echo "<style>body { font-family: Arial; padding: 20px; } a { display: block; margin: 10px 0; padding: 10px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; }</style>";

echo "<h3>Link tuyệt đối (có /bccnan/):</h3>";
echo "<a href='/bccnan/view_my_contact.php?highlight=22' target='_blank'>/bccnan/view_my_contact.php?highlight=22</a>";

echo "<h3>Link tương đối (không có /bccnan/):</h3>";
echo "<a href='view_my_contact.php?highlight=22' target='_blank'>view_my_contact.php?highlight=22</a>";

echo "<h3>Link đầy đủ:</h3>";
echo "<a href='http://localhost/bccnan/view_my_contact.php?highlight=22' target='_blank'>http://localhost/bccnan/view_my_contact.php?highlight=22</a>";

echo "<hr>";
echo "<h3>Thông tin hệ thống:</h3>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Base Path:</strong> " . dirname($_SERVER['SCRIPT_NAME']) . "</p>";
?>
