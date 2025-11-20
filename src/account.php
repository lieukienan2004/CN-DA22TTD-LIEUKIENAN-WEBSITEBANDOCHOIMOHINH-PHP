<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài Khoản - KIENANSHOP</title>
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <link rel="stylesheet" href="assets/css/footer.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .account-container {
            padding: 60px 20px;
            background: var(--light-gray);
            min-height: 70vh;
        }
        
        .account-header {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .account-header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .account-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .account-sidebar {
            background: white;
            padding: 25px;
            border-radius: 20px;
            height: fit-content;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .account-menu {
            list-style: none;
        }
        
        .account-menu li {
            margin-bottom: 10px;
        }
        
        .account-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .account-menu a:hover,
        .account-menu a.active {
            background: var(--gradient-1);
            color: white;
        }
        
        .account-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-item {
            padding: 20px;
            background: var(--light-gray);
            border-radius: 10px;
        }
        
        .info-item label {
            display: block;
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 5px;
        }
        
        .info-item .value {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .order-list {
            margin-top: 20px;
        }
        
        .order-item {
            padding: 20px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-id {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="account-container">
        <div class="container">
            <div class="account-header">
                <div style="display: flex; align-items: center; gap: 25px;">
                    <div class="avatar-container" style="position: relative;">
                        <?php if (isset($user['avatar']) && $user['avatar'] && file_exists($user['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" id="avatarPreview" 
                                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <?php else: ?>
                            <div id="avatarPreview" style="width: 100px; height: 100px; border-radius: 50%; background: var(--gradient-1); display: flex; align-items: center; justify-content: center; color: white; font-size: 40px; font-weight: 700; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <label for="avatarUpload" style="position: absolute; bottom: 0; right: 0; width: 35px; height: 35px; background: var(--gradient-1); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                            <i class="fas fa-camera" style="color: white; font-size: 14px;"></i>
                        </label>
                        <input type="file" id="avatarUpload" accept="image/*" style="display: none;" onchange="uploadAvatar(this)">
                    </div>
                    <div>
                        <h1>Xin chào, <?php echo htmlspecialchars($user['fullname']); ?>!</h1>
                        <p style="color: var(--text-light);">Quản lý thông tin tài khoản của bạn</p>
                    </div>
                </div>
            </div>
            
            <div class="account-grid">
                <div class="account-sidebar">
                    <ul class="account-menu">
                        <li><a href="account.php" class="active"><i class="fas fa-user"></i> Thông tin tài khoản</a></li>
                        <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Đơn hàng của tôi</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </ul>
                </div>
                
                <div class="account-content">
                    <h2 style="margin-bottom: 25px;">Thông Tin Cá Nhân</h2>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Họ và tên</label>
                            <div class="value"><?php echo htmlspecialchars($user['fullname']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <label>Email</label>
                            <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <label>Số điện thoại</label>
                            <div class="value"><?php echo $user['phone'] ? htmlspecialchars($user['phone']) : 'Chưa cập nhật'; ?></div>
                        </div>
                        
                        <div class="info-item">
                            <label>Ngày đăng ký</label>
                            <div class="value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></div>
                        </div>
                    </div>
                    
                    <?php if ($user['address']): ?>
                    <div class="info-item" style="margin-top: 20px;">
                        <label>Địa chỉ</label>
                        <div class="value"><?php echo htmlspecialchars($user['address']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <h2 style="margin-top: 40px; margin-bottom: 20px;">Đơn Hàng Gần Đây</h2>
                    
                    <?php if (empty($orders)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-light);">
                        <i class="fas fa-shopping-bag" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        Bạn chưa có đơn hàng nào
                    </p>
                    <?php else: ?>
                    <div class="order-list">
                        <?php foreach ($orders as $order): 
                            // Lấy chi tiết sản phẩm trong đơn hàng
                            $stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $stmt->bind_param("i", $order['id']);
                            $stmt->execute();
                            $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        ?>
                        <div class="order-item">
                            <div class="order-header">
                                <span class="order-id">#<?php echo $order['id']; ?></span>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo $order['status'] == 'pending' ? 'Đang xử lý' : 'Hoàn thành'; ?>
                                </span>
                            </div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 15px;">
                                Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </p>
                            
                            <!-- Danh sách sản phẩm -->
                            <div style="background: var(--light-gray); padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                <?php foreach ($order_items as $item): ?>
                                <div style="display: flex; gap: 15px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <div style="flex: 1;">
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <p style="color: var(--text-light); font-size: 13px;">
                                            Số lượng: <?php echo $item['quantity']; ?> × <?php echo number_format($item['price']); ?>đ
                                        </p>
                                    </div>
                                    <div style="font-weight: 700; color: var(--primary-color);">
                                        <?php echo number_format($item['price'] * $item['quantity']); ?>đ
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="font-size: 13px; color: var(--text-light);">Người nhận: <?php echo htmlspecialchars($order['fullname']); ?></p>
                                    <p style="font-size: 13px; color: var(--text-light);">SĐT: <?php echo htmlspecialchars($order['phone']); ?></p>
                                </div>
                                <p style="font-weight: 700; color: var(--danger-color); font-size: 18px;">
                                    Tổng: <?php echo number_format($order['total']); ?>đ
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        function uploadAvatar(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Kiểm tra kích thước file
                if (file.size > 5 * 1024 * 1024) {
                    alert('File quá lớn. Vui lòng chọn file nhỏ hơn 5MB');
                    return;
                }
                
                // Kiểm tra loại file
                if (!file.type.match('image.*')) {
                    alert('Vui lòng chọn file ảnh');
                    return;
                }
                
                // Preview ảnh
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        preview.outerHTML = '<img src="' + e.target.result + '" alt="Avatar" id="avatarPreview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">';
                    }
                };
                reader.readAsDataURL(file);
                
                // Upload file
                const formData = new FormData();
                formData.append('avatar', file);
                
                fetch('upload_avatar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Cập nhật avatar thành công!');
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi upload avatar');
                });
            }
        }
    </script>
</body>
</html>
