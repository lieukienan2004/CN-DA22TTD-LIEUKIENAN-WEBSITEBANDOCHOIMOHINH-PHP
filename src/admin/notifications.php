<?php
session_start();
require_once '../config/database.php';
require_once 'includes/auth.php';

requireAdmin();

// Xử lý xóa thông báo
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notif_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM thongbao WHERE id = ?");
    $stmt->bind_param("i", $notif_id);
    $stmt->execute();
    header('Location: notifications.php?deleted=1');
    exit;
}

// Lấy thống kê
$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as `read`
    FROM thongbao 
    WHERE user_type = 'admin'
");
$stats = $stats_query->fetch_assoc();

// Lấy danh sách thông báo đã gửi (chỉ thông báo từ admin)
$notifications_query = $conn->query("
    SELECT 
        t.*,
        u.fullname,
        u.email
    FROM thongbao t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.user_type = 'admin'
    ORDER BY t.created_at DESC
    LIMIT 100
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Thông Báo - Admin</title>
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
                    <h1><i class="fas fa-bell"></i> Quản Lý Thông Báo</h1>
                    <p>Xem và quản lý các thông báo đã gửi đến người dùng</p>
                </div>
            </div>
            
            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Đã xóa thông báo thành công!
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['total']); ?></h3>
                        <p>Tổng thông báo</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['unread']); ?></h3>
                        <p>Chưa đọc</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['read']); ?></h3>
                        <p>Đã đọc</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total'] > 0 ? round(($stats['read'] / $stats['total']) * 100) : 0; ?>%</h3>
                        <p>Tỷ lệ đọc</p>
                    </div>
                </div>
            </div>
            
            <!-- Notifications Table -->
            <div class="dashboard-card">
                <?php if ($notifications_query && $notifications_query->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Loại</th>
                                <th>Tiêu đề</th>
                                <th>Người nhận</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($notif = $notifications_query->fetch_assoc()): 
                                $type_icons = [
                                    'promotion' => 'fa-gift',
                                    'system' => 'fa-info-circle',
                                    'product' => 'fa-box',
                                    'order' => 'fa-shopping-cart'
                                ];
                                $icon = $type_icons[$notif['type']] ?? 'fa-bell';
                                
                                $type_names = [
                                    'promotion' => 'Ưu đãi',
                                    'system' => 'Hệ thống',
                                    'product' => 'Sản phẩm',
                                    'order' => 'Đơn hàng'
                                ];
                            ?>
                            <tr>
                                <td><strong>#<?php echo $notif['id']; ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo $notif['type']; ?>">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                        <?php echo $type_names[$notif['type']] ?? $notif['type']; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                                    <br>
                                    <small style="color: #9ca3af;">
                                        <?php echo htmlspecialchars(mb_substr($notif['message'], 0, 50)); ?>...
                                    </small>
                                </td>
                                <td>
                                    <?php if ($notif['fullname']): ?>
                                        <div class="user-info">
                                            <div class="user-avatar-placeholder">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <strong><?php echo htmlspecialchars($notif['fullname']); ?></strong>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">
                                            <i class="fas fa-user-slash"></i> <em>User đã xóa</em>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $notif['is_read'] ? 'badge-completed' : 'badge-pending'; ?>">
                                        <?php echo $notif['is_read'] ? 'Đã đọc' : 'Chưa đọc'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="confirmDelete(<?php echo $notif['id']; ?>)" class="btn-icon btn-danger" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>Chưa có thông báo nào</h3>
                    <p>Bắt đầu gửi thông báo đến người dùng của bạn</p>
                    <a href="send_notification.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Gửi thông báo đầu tiên
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }
        
        .stat-details h3 {
            font-size: 28px;
            font-weight: 800;
            color: #1f2937;
            margin: 0 0 4px 0;
        }
        
        .stat-details p {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
        }
        
        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #1f2937;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header p {
            font-size: 15px;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .badge-promotion {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .badge-system {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }
        
        .badge-product {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        
        .badge-order {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .empty-state {
            padding: 80px 20px;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 80px;
            color: #e5e7eb;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #6b7280;
            margin-bottom: 24px;
        }
        
        .data-table tbody tr:hover {
            background: #fdf2f8;
        }
    </style>
    
    <script>
        function confirmDelete(id) {
            if (confirm('Bạn có chắc muốn xóa thông báo này?')) {
                window.location.href = 'notifications.php?delete=' + id;
            }
        }
        
        function openQuickNotificationModal() {
            const modalHTML = `
                <div style="padding: 10px 0;">
                    <h2 style="margin: 0 0 25px 0; font-size: 26px; font-weight: 800; color: #1f2937; display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-bolt" style="color: #667eea;"></i> 
                        Gửi Thông Báo Nhanh
                    </h2>
                    
                    <form method="POST" action="send_notification.php" style="display: flex; flex-direction: column; gap: 20px;">
                        <!-- Loại thông báo -->
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; margin-bottom: 12px; color: #374151; font-size: 14px;">
                                <i class="fas fa-tag" style="color: #667eea;"></i> Loại thông báo
                            </label>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                                <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all 0.3s;" class="radio-label">
                                    <input type="radio" name="type" value="promotion" checked style="width: 18px; height: 18px; cursor: pointer;">
                                    <i class="fas fa-gift" style="color: #f59e0b; font-size: 20px;"></i>
                                    <span style="font-weight: 600; color: #374151;">Ưu đãi</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all 0.3s;" class="radio-label">
                                    <input type="radio" name="type" value="system" style="width: 18px; height: 18px; cursor: pointer;">
                                    <i class="fas fa-info-circle" style="color: #8b5cf6; font-size: 20px;"></i>
                                    <span style="font-weight: 600; color: #374151;">Hệ thống</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all 0.3s;" class="radio-label">
                                    <input type="radio" name="type" value="product" style="width: 18px; height: 18px; cursor: pointer;">
                                    <i class="fas fa-box" style="color: #3b82f6; font-size: 20px;"></i>
                                    <span style="font-weight: 600; color: #374151;">Sản phẩm</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all 0.3s;" class="radio-label">
                                    <input type="radio" name="type" value="order" style="width: 18px; height: 18px; cursor: pointer;">
                                    <i class="fas fa-shopping-cart" style="color: #10b981; font-size: 20px;"></i>
                                    <span style="font-weight: 600; color: #374151;">Đơn hàng</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Tiêu đề -->
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; margin-bottom: 8px; color: #374151; font-size: 14px;">
                                <i class="fas fa-heading" style="color: #667eea;"></i> Tiêu đề
                            </label>
                            <input type="text" name="title" placeholder="VD: Giảm giá 50% tất cả sản phẩm" required
                                   style="width: 100%; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 15px; transition: all 0.3s;"
                                   onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
                                   onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        </div>
                        
                        <!-- Nội dung -->
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; margin-bottom: 8px; color: #374151; font-size: 14px;">
                                <i class="fas fa-align-left" style="color: #667eea;"></i> Nội dung
                            </label>
                            <textarea name="message" placeholder="Nhập nội dung thông báo..." required rows="3"
                                      style="width: 100%; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 15px; resize: vertical; transition: all 0.3s; font-family: inherit;"
                                      onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
                                      onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'"></textarea>
                        </div>
                        
                        <!-- Gửi đến tất cả (hidden field) -->
                        <input type="hidden" name="send_to" value="all">
                        <input type="hidden" name="link" value="#">
                        
                        <!-- Thông tin -->
                        <div style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%); padding: 12px 16px; border-radius: 10px; border-left: 4px solid #667eea;">
                            <div style="display: flex; align-items: center; gap: 10px; color: #667eea; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-info-circle"></i>
                                <span>Thông báo sẽ được gửi đến <strong>tất cả người dùng</strong></span>
                            </div>
                        </div>
                        
                        <!-- Nút gửi -->
                        <button type="submit" 
                                style="padding: 16px 32px; font-size: 16px; border: none; border-radius: 12px; cursor: pointer; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 16px rgba(102,126,234,0.3);"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(102,126,234,0.4)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(102,126,234,0.3)'">
                            <i class="fas fa-bolt"></i> 
                            Gửi Ngay
                        </button>
                    </form>
                </div>
            `;
            
            const modal = createModal(modalHTML, { maxWidth: '600px' });
            
            // Add hover effects to radio labels
            setTimeout(() => {
                const labels = modal.querySelectorAll('.radio-label');
                labels.forEach(label => {
                    label.addEventListener('mouseenter', function() {
                        this.style.borderColor = '#667eea';
                        this.style.background = 'rgba(102,126,234,0.05)';
                    });
                    label.addEventListener('mouseleave', function() {
                        if (!this.querySelector('input').checked) {
                            this.style.borderColor = '#e5e7eb';
                            this.style.background = 'white';
                        }
                    });
                    label.addEventListener('click', function() {
                        labels.forEach(l => {
                            l.style.borderColor = '#e5e7eb';
                            l.style.background = 'white';
                        });
                        this.style.borderColor = '#667eea';
                        this.style.background = 'rgba(102,126,234,0.05)';
                    });
                });
            }, 100);
        }
    </script>
</body>
</html>
