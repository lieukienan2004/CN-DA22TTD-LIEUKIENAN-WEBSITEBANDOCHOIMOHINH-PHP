<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['admin_id'])) {
    // Log activity
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) VALUES (?, 'logout', 'Đăng xuất hệ thống', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("is", $_SESSION['admin_id'], $ip);
    $stmt->execute();
}

session_destroy();
header('Location: login.php');
exit;
?>
