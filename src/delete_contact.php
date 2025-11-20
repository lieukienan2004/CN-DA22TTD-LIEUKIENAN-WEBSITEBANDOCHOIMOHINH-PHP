<?php
session_start();
require_once 'config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $sql = "DELETE FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Đã xóa tin nhắn thành công!';
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi xóa tin nhắn.';
    }
    
    $stmt->close();
}

$conn->close();
header('Location: admin_contacts.php');
exit;
?>
