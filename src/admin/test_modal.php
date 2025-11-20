<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Test Modal - Admin</title>
</head>
<body>
    <?php 
    session_start();
    $_SESSION['admin_fullname'] = 'Test Admin';
    $_SESSION['admin_role'] = 'admin';
    include 'includes/sidebar.php'; 
    ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-vial"></i> Test Modal</h1>
                <p>Kiểm tra modal hiển thị đúng</p>
            </div>
            
            <div style="padding: 40px; text-align: center;">
                <button onclick="testModal()" class="btn-gradient-primary" style="padding: 15px 30px; font-size: 16px;">
                    <i class="fas fa-paper-plane"></i> Mở Modal Test
                </button>
            </div>
        </div>
    </div>
    
    <?php include 'includes/scripts.php'; ?>
    
    <script>
        function testModal() {
            const modalHTML = `
                <h2 style="margin: 0 0 20px 0; font-size: 24px; font-weight: 700; color: #1f2937;">
                    <i class="fas fa-paper-plane" style="color: #667eea;"></i> Gửi Thông Báo
                </h2>
                
                <form style="display: flex; flex-direction: column; gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">
                            Tiêu đề
                        </label>
                        <input type="text" placeholder="Nhập tiêu đề thông báo" 
                               style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px;">
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">
                            Nội dung
                        </label>
                        <textarea placeholder="Nhập nội dung thông báo" rows="4"
                                  style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; resize: vertical;"></textarea>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">
                            Gửi đến
                        </label>
                        <select style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px;">
                            <option>Tất cả người dùng</option>
                            <option>Người dùng cụ thể</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-gradient-primary" 
                            style="padding: 14px 28px; font-size: 15px; border: none; border-radius: 10px; cursor: pointer; font-weight: 700;">
                        <i class="fas fa-paper-plane"></i> Gửi Thông Báo
                    </button>
                </form>
            `;
            
            createModal(modalHTML, { maxWidth: '600px' });
        }
    </script>
</body>
</html>
