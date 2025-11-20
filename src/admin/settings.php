<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

// Xử lý cập nhật cài đặt
if (isset($_POST['update_settings'])) {
    $site_name = $conn->real_escape_string($_POST['site_name']);
    $site_email = $conn->real_escape_string($_POST['site_email']);
    $site_phone = $conn->real_escape_string($_POST['site_phone']);
    $site_address = $conn->real_escape_string($_POST['site_address']);
    $facebook_url = $conn->real_escape_string($_POST['facebook_url']);
    $instagram_url = $conn->real_escape_string($_POST['instagram_url']);
    $zalo_phone = $conn->real_escape_string($_POST['zalo_phone']);
    
    // Tạo bảng settings nếu chưa có
    $conn->query("CREATE TABLE IF NOT EXISTS settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(100) UNIQUE,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $settings = [
        'site_name' => $site_name,
        'site_email' => $site_email,
        'site_phone' => $site_phone,
        'site_address' => $site_address,
        'facebook_url' => $facebook_url,
        'instagram_url' => $instagram_url,
        'zalo_phone' => $zalo_phone
    ];
    
    foreach ($settings as $key => $value) {
        $conn->query("INSERT INTO settings (setting_key, setting_value) 
                     VALUES ('$key', '$value') 
                     ON DUPLICATE KEY UPDATE setting_value = '$value'");
    }
    
    logActivity($conn, 'update_settings', 'Cập nhật cài đặt website');
    header('Location: settings.php?success=updated');
    exit;
}

// Tạo bảng settings nếu chưa có
$conn->query("CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Lấy cài đặt hiện tại
$settings_result = $conn->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Giá trị mặc định
$site_name = $settings['site_name'] ?? 'KIENANSHOP';
$site_email = $settings['site_email'] ?? 'contact@kienanshop.com';
$site_phone = $settings['site_phone'] ?? '0123456789';
$site_address = $settings['site_address'] ?? 'Hà Nội, Việt Nam';
$facebook_url = $settings['facebook_url'] ?? '';
$instagram_url = $settings['instagram_url'] ?? '';
$zalo_phone = $settings['zalo_phone'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cài đặt - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-cog"></i> Cài đặt</h1>
                    <p>Quản lý cài đặt website</p>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Cập nhật cài đặt thành công!
            </div>
            <?php endif; ?>
            
            <div class="settings-container">
                <!-- Thông tin website -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Thông tin Website</h3>
                    </div>
                    <form method="POST" class="settings-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Tên Website</label>
                                <input type="text" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email liên hệ</label>
                                <input type="email" name="site_email" value="<?php echo htmlspecialchars($site_email); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" name="site_phone" value="<?php echo htmlspecialchars($site_phone); ?>" required>
                            </div>
                            
                            <div class="form-group full-width">
                                <label>Địa chỉ</label>
                                <input type="text" name="site_address" value="<?php echo htmlspecialchars($site_address); ?>" required>
                            </div>
                        </div>
                        
                        <div class="card-header" style="margin-top: 30px;">
                            <h3><i class="fab fa-facebook"></i> Mạng xã hội</h3>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Facebook URL</label>
                                <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($facebook_url); ?>" placeholder="https://facebook.com/...">
                            </div>
                            
                            <div class="form-group">
                                <label>Instagram URL</label>
                                <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($instagram_url); ?>" placeholder="https://instagram.com/...">
                            </div>
                            
                            <div class="form-group">
                                <label>Số Zalo</label>
                                <input type="text" name="zalo_phone" value="<?php echo htmlspecialchars($zalo_phone); ?>" placeholder="0123456789">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu cài đặt
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Thống kê hệ thống -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-server"></i> Thông tin Hệ thống</h3>
                    </div>
                    <div class="system-info">
                        <div class="info-item">
                            <i class="fas fa-database"></i>
                            <div>
                                <strong>Phiên bản Database</strong>
                                <span><?php echo $conn->server_info; ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fab fa-php"></i>
                            <div>
                                <strong>Phiên bản PHP</strong>
                                <span><?php echo phpversion(); ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-box"></i>
                            <div>
                                <strong>Tổng sản phẩm</strong>
                                <span><?php echo $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total']; ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-shopping-cart"></i>
                            <div>
                                <strong>Tổng đơn hàng</strong>
                                <span><?php echo $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total']; ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <strong>Tổng khách hàng</strong>
                                <span><?php echo $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .settings-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .card-header {
            padding-bottom: 20px;
            border-bottom: 2px solid #f3f4f6;
            margin-bottom: 24px;
        }
        
        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header i {
            color: #ec4899;
        }
        
        .settings-form {
            padding: 24px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 2px solid #f3f4f6;
        }
        
        .system-info {
            padding: 24px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            border-radius: 12px;
            border: 2px solid #fbcfe8;
        }
        
        .info-item i {
            font-size: 32px;
            color: #ec4899;
        }
        
        .info-item div {
            display: flex;
            flex-direction: column;
        }
        
        .info-item strong {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        
        .info-item span {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
