<?php
/**
 * File kiểm tra đường dẫn và cấu hình
 * Truy cập: http://localhost/check_path.php
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm Tra Đường Dẫn - KIENANSHOP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        
        .section h2 {
            color: #374151;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 700;
            color: #4b5563;
            min-width: 200px;
        }
        
        .info-value {
            color: #1f2937;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .status.success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status.error {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        
        .link-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .link-card i {
            font-size: 32px;
        }
        
        .link-card .text {
            flex: 1;
        }
        
        .link-card .title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .link-card .desc {
            font-size: 13px;
            opacity: 0.9;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-check-circle"></i> Kiểm Tra Hệ Thống</h1>
        <p class="subtitle">Thông tin đường dẫn và cấu hình KIENANSHOP</p>
        
        <!-- Server Info -->
        <div class="section">
            <h2><i class="fas fa-server"></i> Thông Tin Server</h2>
            <div class="info-row">
                <div class="info-label">Server Software:</div>
                <div class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">PHP Version:</div>
                <div class="info-value"><?php echo phpversion(); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Document Root:</div>
                <div class="info-value"><?php echo $_SERVER['DOCUMENT_ROOT']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Current Directory:</div>
                <div class="info-value"><?php echo __DIR__; ?></div>
            </div>
        </div>
        
        <!-- URL Info -->
        <div class="section">
            <h2><i class="fas fa-link"></i> Thông Tin URL</h2>
            <div class="info-row">
                <div class="info-label">Server Name:</div>
                <div class="info-value"><?php echo $_SERVER['SERVER_NAME']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Server Port:</div>
                <div class="info-value"><?php echo $_SERVER['SERVER_PORT']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Request URI:</div>
                <div class="info-value"><?php echo $_SERVER['REQUEST_URI']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Script Name:</div>
                <div class="info-value"><?php echo $_SERVER['SCRIPT_NAME']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Base URL:</div>
                <div class="info-value">
                    <?php 
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $base_url = $protocol . '://' . $_SERVER['SERVER_NAME'];
                    if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
                        $base_url .= ':' . $_SERVER['SERVER_PORT'];
                    }
                    $base_url .= dirname($_SERVER['SCRIPT_NAME']);
                    echo $base_url;
                    ?>
                </div>
            </div>
        </div>
        
        <!-- File Check -->
        <div class="section">
            <h2><i class="fas fa-folder-open"></i> Kiểm Tra Files</h2>
            <div class="info-row">
                <div class="info-label">index.php:</div>
                <div class="info-value">
                    <?php echo file_exists('index.php') ? '<span class="status success">✓ Tồn tại</span>' : '<span class="status error">✗ Không tìm thấy</span>'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">admin/index.php:</div>
                <div class="info-value">
                    <?php echo file_exists('admin/index.php') ? '<span class="status success">✓ Tồn tại</span>' : '<span class="status error">✗ Không tìm thấy</span>'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">config/database.php:</div>
                <div class="info-value">
                    <?php echo file_exists('config/database.php') ? '<span class="status success">✓ Tồn tại</span>' : '<span class="status error">✗ Không tìm thấy</span>'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">.htaccess:</div>
                <div class="info-value">
                    <?php echo file_exists('.htaccess') ? '<span class="status success">✓ Tồn tại</span>' : '<span class="status error">✗ Không tìm thấy</span>'; ?>
                </div>
            </div>
        </div>
        
        <!-- Database Check -->
        <div class="section">
            <h2><i class="fas fa-database"></i> Kiểm Tra Database</h2>
            <?php
            try {
                require_once 'config/database.php';
                echo '<div class="info-row">';
                echo '<div class="info-label">Kết nối Database:</div>';
                echo '<div class="info-value"><span class="status success">✓ Thành công</span></div>';
                echo '</div>';
                
                echo '<div class="info-row">';
                echo '<div class="info-label">Database Name:</div>';
                echo '<div class="info-value">' . DB_NAME . '</div>';
                echo '</div>';
                
                // Check tables
                $tables = ['products', 'categories', 'users', 'orders', 'admins'];
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    echo '<div class="info-row">';
                    echo '<div class="info-label">Table ' . $table . ':</div>';
                    echo '<div class="info-value">';
                    echo $result && $result->num_rows > 0 ? '<span class="status success">✓ Tồn tại</span>' : '<span class="status error">✗ Không tìm thấy</span>';
                    echo '</div>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div class="info-row">';
                echo '<div class="info-label">Kết nối Database:</div>';
                echo '<div class="info-value"><span class="status error">✗ Lỗi: ' . $e->getMessage() . '</span></div>';
                echo '</div>';
            }
            ?>
        </div>
        
        <!-- Quick Links -->
        <div class="section">
            <h2><i class="fas fa-rocket"></i> Truy Cập Nhanh</h2>
            <div class="links">
                <a href="index.php" class="link-card">
                    <i class="fas fa-home"></i>
                    <div class="text">
                        <div class="title">Trang Chủ</div>
                        <div class="desc">Website chính</div>
                    </div>
                </a>
                
                <a href="admin/index.php" class="link-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-user-shield"></i>
                    <div class="text">
                        <div class="title">Admin Panel</div>
                        <div class="desc">Quản trị hệ thống</div>
                    </div>
                </a>
                
                <a href="admin/demo-premium.php" class="link-card" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);">
                    <i class="fas fa-magic"></i>
                    <div class="text">
                        <div class="title">Premium Demo</div>
                        <div class="desc">Xem giao diện mới</div>
                    </div>
                </a>
                
                <a href="products.php" class="link-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="text">
                        <div class="title">Sản Phẩm</div>
                        <div class="desc">Danh sách sản phẩm</div>
                    </div>
                </a>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #eff6ff; border-radius: 12px; border-left: 4px solid #3b82f6;">
            <h3 style="color: #1e40af; margin-bottom: 10px;">
                <i class="fas fa-info-circle"></i> Hướng Dẫn
            </h3>
            <p style="color: #1e3a8a; line-height: 1.6;">
                <strong>Nếu bạn gặp lỗi 404:</strong><br>
                1. Kiểm tra URL có đúng không<br>
                2. Đảm bảo file tồn tại (xem phần "Kiểm Tra Files" ở trên)<br>
                3. Xóa cache browser (Ctrl + F5)<br>
                4. Kiểm tra Apache có đang chạy không<br>
                5. Kiểm tra mod_rewrite có được bật không
            </p>
        </div>
    </div>
</body>
</html>
