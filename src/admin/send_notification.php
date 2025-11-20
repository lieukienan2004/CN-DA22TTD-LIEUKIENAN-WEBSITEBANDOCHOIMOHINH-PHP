<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

requireAdmin();

$success = '';
$error = '';

// Xử lý gửi thông báo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'system';
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $link = trim($_POST['link'] ?? '#');
    $send_to = $_POST['send_to'] ?? 'all';
    
    if (empty($title) || empty($message)) {
        $error = 'Vui lòng nhập đầy đủ tiêu đề và nội dung!';
    } else {
        // Lấy danh sách user cần gửi
        if ($send_to === 'all') {
            $users_query = $conn->query("SELECT id FROM users");
            $users = $users_query->fetch_all(MYSQLI_ASSOC);
        } else {
            $users = [['id' => (int)$send_to]];
        }
        
        // Gửi thông báo cho từng user
        $stmt = $conn->prepare("
            INSERT INTO thongbao (user_id, user_type, type, title, message, link, created_at) 
            VALUES (?, 'admin', ?, ?, ?, ?, NOW())
        ");
        
        $sent_count = 0;
        foreach ($users as $user) {
            $stmt->bind_param("issss", $user['id'], $type, $title, $message, $link);
            if ($stmt->execute()) {
                $sent_count++;
            }
        }
        
        $success = "Đã gửi thông báo thành công đến $sent_count người dùng!";
    }
}

// Lấy danh sách user để chọn
$users_list = $conn->query("SELECT id, fullname, email FROM users ORDER BY fullname");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Gửi Thông Báo - Admin</title>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-paper-plane"></i> Gửi Thông Báo</h1>
                <p>Gửi thông báo về ưu đãi, sự kiện đến người dùng</p>
            </div>
            
            <?php if ($success): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-edit"></i> Tạo Thông Báo Mới</h2>
                    <a href="notifications.php" class="btn-link">
                        <i class="fas fa-list"></i> Xem danh sách
                    </a>
                </div>
                
                <div style="padding: 35px;">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-tag"></i> Loại thông báo
                            </label>
                            <div class="type-selector">
                                <div class="type-option">
                                    <input type="radio" name="type" value="promotion" id="type-promotion" checked>
                                    <label for="type-promotion">
                                        <i class="fas fa-gift"></i>
                                        <span>Ưu đãi</span>
                                    </label>
                                </div>
                                <div class="type-option">
                                    <input type="radio" name="type" value="system" id="type-system">
                                    <label for="type-system">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Hệ thống</span>
                                    </label>
                                </div>
                                <div class="type-option">
                                    <input type="radio" name="type" value="product" id="type-product">
                                    <label for="type-product">
                                        <i class="fas fa-box"></i>
                                        <span>Sản phẩm</span>
                                    </label>
                                </div>
                                <div class="type-option">
                                    <input type="radio" name="type" value="order" id="type-order">
                                    <label for="type-order">
                                        <i class="fas fa-shopping-cart"></i>
                                        <span>Đơn hàng</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title" class="form-label">
                                    <i class="fas fa-heading"></i> Tiêu đề
                                </label>
                                <input type="text" id="title" name="title" class="form-control" 
                                       placeholder="VD: Giảm giá 50% tất cả sản phẩm" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="form-label">
                                <i class="fas fa-align-left"></i> Nội dung
                            </label>
                            <textarea id="message" name="message" class="form-control" rows="5"
                                      placeholder="Nhập nội dung thông báo chi tiết..." required></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="link" class="form-label">
                                    <i class="fas fa-link"></i> Link (tùy chọn)
                                </label>
                                <input type="text" id="link" name="link" class="form-control" 
                                       placeholder="VD: products.php hoặc product-detail.php?id=123" value="#">
                            </div>
                            
                            <div class="form-group">
                                <label for="send_to" class="form-label">
                                    <i class="fas fa-users"></i> Gửi đến
                                </label>
                                <select id="send_to" name="send_to" class="form-control">
                                    <option value="all">Tất cả người dùng</option>
                                    <?php while ($user = $users_list->fetch_assoc()): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['fullname']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-gradient-primary">
                                <i class="fas fa-paper-plane"></i> Gửi Thông Báo
                            </button>
                            <a href="notifications.php" class="btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
