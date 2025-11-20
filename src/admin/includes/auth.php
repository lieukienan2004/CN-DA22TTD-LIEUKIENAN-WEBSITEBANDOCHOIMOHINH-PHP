<?php
// Hàm kiểm tra đăng nhập admin
function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Kiểm tra đăng nhập admin (tự động chạy khi include file này)
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Hàm kiểm tra quyền
function checkPermission($required_role = 'admin') {
    $role_hierarchy = [
        'super_admin' => 3,
        'admin' => 2,
        'moderator' => 1
    ];
    
    $user_level = $role_hierarchy[$_SESSION['admin_role']] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    if ($user_level < $required_level) {
        header('Location: index.php?error=permission_denied');
        exit;
    }
}

// Hàm log hoạt động
function logActivity($conn, $action, $description = '') {
    try {
        // Kiểm tra xem admin_id có tồn tại không
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }
        
        // Kiểm tra xem bảng admin_logs có tồn tại không
        $check_table = $conn->query("SHOW TABLES LIKE 'admin_logs'");
        if ($check_table->num_rows == 0) {
            // Tạo bảng nếu chưa có
            $conn->query("CREATE TABLE IF NOT EXISTS admin_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                admin_id INT,
                action VARCHAR(100),
                description TEXT,
                ip_address VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX(admin_id)
            )");
        }
        
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("isss", $_SESSION['admin_id'], $action, $description, $ip);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        // Bỏ qua lỗi log, không ảnh hưởng đến chức năng chính
        return false;
    }
}
?>
